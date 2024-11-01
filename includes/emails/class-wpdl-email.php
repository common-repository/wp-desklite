<?php
/**
 * WPDL_Email class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WPDL_Email', false ) ) {
	return;
}

/**
 * WPDL_Email class.
 */
class WPDL_Email extends WPDL_Settings_API {

	/**
	 * Email method ID.
	 */
	public $id;

	/**
	 * Email method title.
	 */
	public $title;

	/**
	 * 'yes' if the method is enabled.
	 */
	public $enabled;

	/**
	 * Description for the email.
	 */
	public $description;

	/**
	 * Default heading.
	 */
	public $heading = '';

	/**
	 * Default subject.
	 */
	public $subject = '';

	/**
	 * Plain text template path.
	 */
	public $template_plain;

	/**
	 * HTML template path.
	 */
	public $template_html;

	/**
	 * Template path.
	 */
	public $template_base;

	/**
	 * Recipients for the email.
	 */
	public $recipient;

	/**
	 * Object this email is for.
	 */
	public $object;

	/**
	 * Mime boundary (for multipart emails).
	 */
	public $mime_boundary;

	/**
	 * Mime boundary header (for multipart emails).
	 */
	public $mime_boundary_header;

	/**
	 * True when email is being sent.
	 */
	public $sending;

	/**
	 * True when the email notification is sent manually only.
	 */
	protected $manual = false;

	/**
	 * True when the email notification is sent to users.
	 */
	protected $is_user_email = false;

	/**
	 *  List of preg* regular expression patterns to search for,
	 *  used in conjunction with $plain_replace.
	 *  https://raw.github.com/ushahidi/wp-silcc/master/class.html2text.inc
	 *
	 *  @var array $plain_search
	 *  @see $plain_replace
	 */
	public $plain_search = array(
		"/\r/",                                                  // Non-legal carriage return.
		'/&(nbsp|#0*160);/i',                                    // Non-breaking space.
		'/&(quot|rdquo|ldquo|#0*8220|#0*8221|#0*147|#0*148);/i', // Double quotes.
		'/&(apos|rsquo|lsquo|#0*8216|#0*8217);/i',               // Single quotes.
		'/&gt;/i',                                               // Greater-than.
		'/&lt;/i',                                               // Less-than.
		'/&#0*38;/i',                                            // Ampersand.
		'/&amp;/i',                                              // Ampersand.
		'/&(copy|#0*169);/i',                                    // Copyright.
		'/&(trade|#0*8482|#0*153);/i',                           // Trademark.
		'/&(reg|#0*174);/i',                                     // Registered.
		'/&(mdash|#0*151|#0*8212);/i',                           // mdash.
		'/&(ndash|minus|#0*8211|#0*8722);/i',                    // ndash.
		'/&(bull|#0*149|#0*8226);/i',                            // Bullet.
		'/&(pound|#0*163);/i',                                   // Pound sign.
		'/&(euro|#0*8364);/i',                                   // Euro sign.
		'/&(dollar|#0*36);/i',                                   // Dollar sign.
		'/&[^&\s;]+;/i',                                         // Unknown/unhandled entities.
		'/[ ]{2,}/',                                             // Runs of spaces, post-handling.
	);

	/**
	 *  List of pattern replacements corresponding to patterns searched.
	 */
	public $plain_replace = array(
		'',                                             // Non-legal carriage return.
		' ',                                            // Non-breaking space.
		'"',                                            // Double quotes.
		"'",                                            // Single quotes.
		'>',                                            // Greater-than.
		'<',                                            // Less-than.
		'&',                                            // Ampersand.
		'&',                                            // Ampersand.
		'(c)',                                          // Copyright.
		'(tm)',                                         // Trademark.
		'(R)',                                          // Registered.
		'--',                                           // mdash.
		'-',                                            // ndash.
		'*',                                            // Bullet.
		'£',                                            // Pound sign.
		'EUR',                                          // Euro sign. € ?.
		'$',                                            // Dollar sign.
		'',                                             // Unknown/unhandled entities.
		' ',                                             // Runs of spaces, post-handling.
	);

	/**
	 * Strings to find/replace in subjects/headings.
	 */
	protected $placeholders = array();

	/**
	 * Strings to find in subjects/headings.
	 */
	public $find = array();

	/**
	 * Strings to replace in subjects/headings.
	 */
	public $replace = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Find/replace.
		if ( empty( $this->placeholders ) ) {
			$this->placeholders = array(
				'{site_title}' => $this->get_blogname(),
			);
		}

		// Init settings.
		$this->init_form_fields();
		$this->init_settings();

		// Default template base if not declared in child constructor.
		if ( is_null( $this->template_base ) ) {
			$this->template_base = wpdl()->plugin_path() . '/templates/';
		}

		$this->email_type = $this->get_option( 'email_type' );
		$this->enabled    = $this->get_option( 'enabled' );

		add_action( 'phpmailer_init', array( $this, 'handle_multipart' ) );
		add_action( 'wpdl_update_options_email_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Handle multipart mail.
	 */
	public function handle_multipart( $mailer ) {
		if ( $this->sending && 'multipart' === $this->get_email_type() ) {
			$mailer->AltBody = wordwrap(
				preg_replace( $this->plain_search, $this->plain_replace, strip_tags( $this->get_content_plain() ) )
			);
			$this->sending   = false;
		}
		return $mailer;
	}

	/**
	 * Format email string.
	 */
	public function format_string( $string ) {
		$find    = array_keys( $this->placeholders );
		$replace = array_values( $this->placeholders );

		// If using legacy find replace, add those to our find/replace arrays first. @todo deprecate in 4.0.0.
		$find    = array_merge( (array) $this->find, $find );
		$replace = array_merge( (array) $this->replace, $replace );

		// Take care of blogname which is no longer defined as a valid placeholder.
		$find[]    = '{blogname}';
		$replace[] = $this->get_blogname();

		// If using the older style filters for find and replace, ensure the array is associative and then pass through filters. @todo deprecate in 4.0.0.
		if ( has_filter( 'wpdl_email_format_string_replace' ) || has_filter( 'wpdl_email_format_string_find' ) ) {
			$legacy_find    = $this->find;
			$legacy_replace = $this->replace;

			foreach ( $this->placeholders as $find => $replace ) {
				$legacy_key                    = sanitize_title( str_replace( '_', '-', trim( $find, '{}' ) ) );
				$legacy_find[ $legacy_key ]    = $find;
				$legacy_replace[ $legacy_key ] = $replace;
			}

			$string = str_replace( apply_filters( 'wpdl_email_format_string_find', $legacy_find, $this ), apply_filters( 'wpdl_email_format_string_replace', $legacy_replace, $this ), $string );
		}

		/**
		 * Filter for main find/replace.
		 */
		return apply_filters( 'wpdl_email_format_string', str_replace( $find, $replace, $string ), $this );
	}

	/**
	 * Set the locale to the site locale for user emails.
	 */
	public function setup_locale() {
		if ( $this->is_user_email() && apply_filters( 'wpdl_email_setup_locale', true ) ) {
			wpdl_switch_to_site_locale();
		}
	}

	/**
	 * Restore the locale to the default locale. Use after finished with setup_locale.
	 */
	public function restore_locale() {
		if ( $this->is_user_email() && apply_filters( 'wpdl_email_restore_locale', true ) ) {
			wpdl_restore_locale();
		}
	}

	/**
	 * Get email subject.
	 */
	public function get_default_subject() {
		return $this->subject;
	}

	/**
	 * Get email heading.
	 */
	public function get_default_heading() {
		return $this->heading;
	}

	/**
	 * Get email subject.
	 */
	public function get_subject() {
		return apply_filters( 'wpdl_email_subject_' . $this->id, $this->format_string( $this->get_option( 'subject', $this->get_default_subject() ) ), $this->object );
	}

	/**
	 * Get email heading.
	 */
	public function get_heading() {
		return apply_filters( 'wpdl_email_heading_' . $this->id, $this->format_string( $this->get_option( 'heading', $this->get_default_heading() ) ), $this->object );
	}

	/**
	 * Get valid recipients.
	 */
	public function get_recipient() {
		$recipient  = apply_filters( 'wpdl_email_recipient_' . $this->id, $this->recipient, $this->object );
		$recipients = array_map( 'trim', explode( ',', $recipient ) );
		$recipients = array_filter( $recipients, 'is_email' );
		return implode( ', ', $recipients );
	}

	/**
	 * Get email headers.
	 */
	public function get_headers() {
		$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";

		if ( $this->get_from_address() && $this->get_from_name() ) {
			$header .= 'Reply-to: ' . $this->get_from_name() . ' <' . $this->get_from_address() . ">\r\n";
		}

		return apply_filters( 'wpdl_email_headers', $header, $this->id, $this->object );
	}

	/**
	 * Get email attachments.
	 */
	public function get_attachments() {
		return apply_filters( 'wpdl_email_attachments', array(), $this->id, $this->object );
	}

	/**
	 * Return email type.
	 */
	public function get_email_type() {
		return $this->email_type && class_exists( 'DOMDocument' ) ? $this->email_type : 'plain';
	}

	/**
	 * Get email content type.
	 */
	public function get_content_type() {
		switch ( $this->get_email_type() ) {
			case 'html':
				return 'text/html';
			case 'multipart':
				return 'multipart/alternative';
			default:
				return 'text/plain';
		}
	}

	/**
	 * Return the email's title
	 */
	public function get_title() {
		return apply_filters( 'wpdl_email_title', $this->title, $this );
	}

	/**
	 * Return the email's description
	 */
	public function get_description() {
		return apply_filters( 'wpdl_email_description', $this->description, $this );
	}

	/**
	 * Proxy to parent's get_option and attempt to localize the result using gettext.
	 */
	public function get_option( $key, $empty_value = null ) {
		$value = parent::get_option( $key, $empty_value );
		return apply_filters( 'wpdl_email_get_option', $value, $this, $value, $key, $empty_value );
	}

	/**
	 * Checks if this email is enabled and will be sent.
	 */
	public function is_enabled() {
		return apply_filters( 'wpdl_email_enabled_' . $this->id, 'yes' === $this->enabled, $this->object );
	}

	/**
	 * Checks if this email is manually sent
	 */
	public function is_manual() {
		return $this->manual;
	}

	/**
	 * Checks if this email is user related.
	 */
	public function is_user_email() {
		return $this->is_user_email;
	}

	/**
	 * Get WordPress blog name.
	 */
	public function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * Get email content.
	 */
	public function get_content() {
		$this->sending = true;

		if ( 'plain' === $this->get_email_type() ) {
			$email_content = wordwrap( preg_replace( $this->plain_search, $this->plain_replace, strip_tags( $this->get_content_plain() ) ), 70 );
		} else {
			$email_content = $this->get_content_html();
		}

		return $email_content;
	}

	/**
	 * Apply inline styles to dynamic content.
	 */
	public function style_inline( $content ) {
		if ( in_array( $this->get_content_type(), array( 'text/html', 'multipart/alternative' ), true ) ) {
			ob_start();
			wpdl_get_template( 'emails/email-styles.php' );
			$css = apply_filters( 'wpdl_email_styles', ob_get_clean(), $this );

			if ( $this->supports_emogrifier() ) {
				$emogrifier_class = '\\Pelago\\Emogrifier';
				if ( ! class_exists( $emogrifier_class ) ) {
					include_once dirname( dirname( __FILE__ ) ) . '/libraries/class-emogrifier.php';
				}
				try {
					$emogrifier = new $emogrifier_class( $content, $css );
					$content    = $emogrifier->emogrify();
				} catch ( Exception $e ) {

				}
			} else {
				$content = '<style type="text/css">' . $css . '</style>' . $content;
			}
		}
		return $content;
	}

	/**
	 * Return if emogrifier library is supported.
	 */
	protected function supports_emogrifier() {
		return class_exists( 'DOMDocument' ) && version_compare( PHP_VERSION, '5.5', '>=' );
	}

	/**
	 * Get the email content in plain text format.
	 */
	public function get_content_plain() {
		return '';
	}

	/**
	 * Get the email content in HTML format.
	 */
	public function get_content_html() {
		return '';
	}

	/**
	 * Get the from name for outgoing emails.
	 */
	public function get_from_name() {
		$from_name = apply_filters( 'wpdl_email_from_name', get_option( 'wpdl_email_from_name' ), $this );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	/**
	 * Get the from address for outgoing emails.
	 */
	public function get_from_address() {
		$from_address = apply_filters( 'wpdl_email_from_address', get_option( 'wpdl_email_from_address' ), $this );
		return sanitize_email( $from_address );
	}

	/**
	 * Send an email.
	 */
	public function send( $to, $subject, $message, $headers, $attachments ) {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		$message              = apply_filters( 'wpdl_mail_content', $this->style_inline( $message ) );
		$mail_callback        = apply_filters( 'wpdl_mail_callback', 'wp_mail', $this );
		$mail_callback_params = apply_filters( 'wpdl_mail_callback_params', array( $to, $subject, $message, $headers, $attachments ), $this );
		$return               = call_user_func_array( $mail_callback, $mail_callback_params );

		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		return $return;
	}

	/**
	 * Initialise Settings Form Fields - these are generic email options most will use.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'wp-desklite' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'wp-desklite' ),
				'default' => 'yes',
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'wp-desklite' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => sprintf( wp_kses_post( __( 'Available placeholders: %s', 'wp-desklite' ) ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' ),
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'    => array(
				'title'       => __( 'Email heading', 'wp-desklite' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => sprintf( wp_kses_post( __( 'Available placeholders: %s', 'wp-desklite' ) ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' ),
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'wp-desklite' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wp-desklite' ),
				'default'     => 'html',
				'class'       => 'email_type wpdl-select small',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Email type options.
	 */
	public function get_email_type_options() {
		$types = array( 'plain' => __( 'Plain text', 'wp-desklite' ) );

		if ( class_exists( 'DOMDocument' ) ) {
			$types['html']      = __( 'HTML', 'wp-desklite' );
			$types['multipart'] = __( 'Multipart', 'wp-desklite' );
		}

		return $types;
	}

	/**
	 * Admin Panel Options Processing.
	 */
	public function process_admin_options() {
		// Save regular options.
		parent::process_admin_options();

		$post_data = $this->get_post_data();

		// Save templates.
		if ( isset( $post_data['template_html_code'] ) ) {
			$this->save_template( $post_data['template_html_code'], $this->template_html );
		}
		if ( isset( $post_data['template_plain_code'] ) ) {
			$this->save_template( $post_data['template_plain_code'], $this->template_plain );
		}
	}

	/**
	 * Get template.
	 */
	public function get_template( $type ) {
		$type = basename( $type );

		if ( 'template_html' === $type ) {
			return $this->template_html;
		} elseif ( 'template_plain' === $type ) {
			return $this->template_plain;
		}
		return '';
	}

	/**
	 * Save the email templates.
	 */
	protected function save_template( $template_code, $template_path ) {
		if ( current_user_can( 'edit_themes' ) && ! empty( $template_code ) && ! empty( $template_path ) ) {
			$saved = false;
			$file  = get_stylesheet_directory() . '/' . wpdl()->template_path() . $template_path;
			$code  = wp_unslash( $template_code );

			if ( is_writeable( $file ) ) {
				$f = fopen( $file, 'w+' );

				if ( false !== $f ) {
					fwrite( $f, $code );
					fclose( $f );
					$saved = true;
				}
			}

			if ( ! $saved ) {
				$redirect = add_query_arg( 'wpdl_error', urlencode( __( 'Could not write to template file.', 'wp-desklite' ) ) );
				wp_safe_redirect( $redirect );
				exit;
			}
		}
	}

	/**
	 * Get the template file in the current theme.
	 */
	public function get_theme_template_file( $template ) {
		return get_stylesheet_directory() . '/' . apply_filters( 'wpdl_template_directory', 'wp-desklite', $template ) . '/' . $template;
	}

	/**
	 * Move template action.
	 *
	 * @param string $template_type Template type.
	 */
	protected function move_template_action( $template_type ) {
		$template = $this->get_template( $template_type );
		if ( ! empty( $template ) ) {
			$theme_file = $this->get_theme_template_file( $template );

			if ( wp_mkdir_p( dirname( $theme_file ) ) && ! file_exists( $theme_file ) ) {

				// Locate template file.
				$core_file     = $this->template_base . $template;
				$template_file = apply_filters( 'wpdl_locate_core_template', $core_file, $template, $this->template_base, $this->id );

				// Copy template file.
				copy( $template_file, $theme_file );

				/**
				 * Action hook fired after copying email template file.
				 *
				 * @param string $template_type The copied template type
				 * @param string $email The email object
				 */
				do_action( 'wpdl_copy_email_template', $template_type, $this );

				?>
				<div class="updated">
					<p><?php echo esc_html__( 'Template file copied to theme.', 'wp-desklite' ); ?></p>
				</div>
				<?php
			}
		}
	}

	/**
	 * Delete template action.
	 *
	 * @param string $template_type Template type.
	 */
	protected function delete_template_action( $template_type ) {
		$template = $this->get_template( $template_type );

		if ( $template ) {
			if ( ! empty( $template ) ) {
				$theme_file = $this->get_theme_template_file( $template );

				if ( file_exists( $theme_file ) ) {
					unlink( $theme_file );

					/**
					 * Action hook fired after deleting template file.
					 */
					do_action( 'wpdl_delete_email_template', $template_type, $this );
					?>
					<div class="updated">
						<p><?php echo esc_html__( 'Template file deleted from theme.', 'wp-desklite' ); ?></p>
					</div>
					<?php
				}
			}
		}
	}

	/**
	 * Admin actions.
	 */
	protected function admin_actions() {
		// Handle any actions.
		if (
			( ! empty( $this->template_html ) || ! empty( $this->template_plain ) )
			&& ( ! empty( $_GET['move_template'] ) || ! empty( $_GET['delete_template'] ) )
			&& 'GET' === $_SERVER['REQUEST_METHOD']
		) {
			if ( empty( $_GET['_wpdl_email_nonce'] ) || ! wp_verify_nonce( wpdl_clean( wp_unslash( $_GET['_wpdl_email_nonce'] ) ), 'wpdl_email_template_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'wp-desklite' ) );
			}

			if ( ! current_user_can( 'edit_themes' ) ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'wp-desklite' ) );
			}

			if ( ! empty( $_GET['move_template'] ) ) {
				$this->move_template_action( wpdl_clean( wp_unslash( $_GET['move_template'] ) ) );
			}

			if ( ! empty( $_GET['delete_template'] ) ) {
				$this->delete_template_action( wpdl_clean( wp_unslash( $_GET['delete_template'] ) ) );
			}
		}
	}

	/**
	 * Admin Options.
	 */
	public function admin_options() {
		// Do admin actions.
		$this->admin_actions();
		?>
		<h2><?php echo esc_html( $this->get_title() ); ?> <?php wpdl_back_link( esc_html__( 'Return to emails', 'wp-desklite' ), admin_url( 'admin.php?page=wpdl-settings&tab=email' ) ); ?></h2>

		<?php echo wpautop( wp_kses_post( $this->get_description() ) ); ?>

		<?php
		/**
		 * Action hook fired before displaying email settings.
		 */
		do_action( 'wpdl_email_settings_before', $this );
		?>

		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>

		<?php
		/**
		 * Action hook fired after displaying email settings.
		 */
		do_action( 'wpdl_email_settings_after', $this );
		?>

		<?php

		if ( current_user_can( 'edit_themes' ) && ( ! empty( $this->template_html ) || ! empty( $this->template_plain ) ) ) {
			?>
			<div id="template">
				<?php
				$templates = array(
					'template_html'  => esc_html__( 'HTML template', 'wp-desklite' ),
					'template_plain' => esc_html__( 'Plain text template', 'wp-desklite' ),
				);

				foreach ( $templates as $template_type => $title ) :
					$template = $this->get_template( $template_type );

					if ( empty( $template ) ) {
						continue;
					}

					$local_file    = $this->get_theme_template_file( $template );
					$core_file     = $this->template_base . $template;
					$template_file = apply_filters( 'wpdl_locate_core_template', $core_file, $template, $this->template_base, $this->id );
					$template_dir  = apply_filters( 'wpdl_template_directory', 'wp-desklite', $template );

					?>
					<div class="template <?php echo esc_attr( $template_type ); ?>">
						<h4><?php echo wp_kses_post( $title ); ?></h4>

						<?php if ( file_exists( $local_file ) ) : ?>
							<p>
								<a href="#" class="button toggle_editor"></a>

								<?php if ( is_writable( $local_file ) ) : ?>
									<a href="<?php echo esc_url( wp_nonce_url( remove_query_arg( array( 'move_template', 'saved' ), add_query_arg( 'delete_template', $template_type ) ), 'wpdl_email_template_nonce', '_wpdl_email_nonce' ) ); ?>" class="delete_template button">
										<?php esc_html_e( 'Delete template file', 'wp-desklite' ); ?>
									</a>
								<?php endif; ?>

								<?php
								/* translators: %s: Path to template file */
								printf( esc_html__( 'This template has been overridden by your theme and can be found in: %s.', 'wp-desklite' ), '<code>' . esc_html( trailingslashit( basename( get_stylesheet_directory() ) ) . $template_dir . '/' . $template ) . '</code>' );
								?>
							</p>

							<div class="editor" style="display:none">
								<textarea class="code" cols="25" rows="20"
								<?php
								if ( ! is_writable( $local_file ) ) :
									?>
									readonly="readonly" disabled="disabled"
								<?php else : ?>
									data-name="<?php echo esc_attr( $template_type ) . '_code'; ?>"<?php endif; ?>><?php echo esc_html( file_get_contents( $local_file ) ); ?></textarea>
							</div>
						<?php elseif ( file_exists( $template_file ) ) : ?>
							<p>
								<a href="#" class="button toggle_editor"></a>

								<?php
								$emails_dir    = get_stylesheet_directory() . '/' . $template_dir . '/emails';
								$templates_dir = get_stylesheet_directory() . '/' . $template_dir;
								$theme_dir     = get_stylesheet_directory();

								if ( is_dir( $emails_dir ) ) {
									$target_dir = $emails_dir;
								} elseif ( is_dir( $templates_dir ) ) {
									$target_dir = $templates_dir;
								} else {
									$target_dir = $theme_dir;
								}

								if ( is_writable( $target_dir ) ) :
									?>
									<a href="<?php echo esc_url( wp_nonce_url( remove_query_arg( array( 'delete_template', 'saved' ), add_query_arg( 'move_template', $template_type ) ), 'wpdl_email_template_nonce', '_wpdl_email_nonce' ) ); ?>" class="button">
										<?php esc_html_e( 'Copy file to theme', 'wp-desklite' ); ?>
									</a>
								<?php endif; ?>

								<?php
								/* translators: 1: Path to template file 2: Path to theme folder */
								printf( esc_html__( 'To override and edit this email template copy %1$s to your theme folder: %2$s.', 'wp-desklite' ), '<code>' . esc_html( plugin_basename( $template_file ) ) . '</code>', '<code>' . esc_html( trailingslashit( basename( get_stylesheet_directory() ) ) . $template_dir . '/' . $template ) . '</code>' );
								?>
							</p>

							<div class="editor" style="display:none">
								<textarea class="code" readonly="readonly" disabled="disabled" cols="25" rows="20"><?php echo esc_html( file_get_contents( $template_file ) ); ?></textarea>
							</div>
						<?php else : ?>
							<p><?php esc_html_e( 'File was not found.', 'wp-desklite' ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<?php
			wpdl_enqueue_js(
				"jQuery( 'select.email_type' ).change( function() {

					var val = jQuery( this ).val();

					jQuery( '.template_plain, .template_html' ).show();

					if ( val != 'multipart' && val != 'html' ) {
						jQuery('.template_html').hide();
					}

					if ( val != 'multipart' && val != 'plain' ) {
						jQuery('.template_plain').hide();
					}

				}).change();

				var view = '" . esc_js( __( 'View template', 'wp-desklite' ) ) . "';
				var hide = '" . esc_js( __( 'Hide template', 'wp-desklite' ) ) . "';

				jQuery( 'a.toggle_editor' ).text( view ).toggle( function() {
					jQuery( this ).text( hide ).closest(' .template' ).find( '.editor' ).slideToggle();
					return false;
				}, function() {
					jQuery( this ).text( view ).closest( '.template' ).find( '.editor' ).slideToggle();
					return false;
				} );

				jQuery( 'a.delete_template' ).click( function() {
					if ( window.confirm('" . esc_js( __( 'Are you sure you want to delete this template file?', 'wp-desklite' ) ) . "') ) {
						return true;
					}

					return false;
				});

				jQuery( '.editor textarea' ).change( function() {
					var name = jQuery( this ).attr( 'data-name' );

					if ( name ) {
						jQuery( this ).attr( 'name', name );
					}
				});"
			);
		}
	}
}
