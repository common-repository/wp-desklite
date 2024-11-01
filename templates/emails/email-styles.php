<?php
/**
 * Email Styles
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load colors.
$bg        = get_option( 'wpdl_email_background_color' );
$body      = get_option( 'wpdl_email_body_background_color' );
$text      = get_option( 'wpdl_email_text_color' );
$base      = get_option( 'wpdl_email_base_color' );

$body_darker_3   = wpdl_hex_darker( $body, 3 );
$body_darker_10  = wpdl_hex_darker( $body, 10 );

$text_lighter_20 = wpdl_hex_lighter( $text, 20 );
$text_lighter_40 = wpdl_hex_lighter( $text, 40 );
$text_darker_40  = wpdl_hex_darker( $text, 40 );

$text_footer 	 = wpdl_hex_is_light( $bg ) ? wpdl_hex_darker( $bg, 30 ) : wpdl_hex_lighter( $bg, 30 );

?>
body {
	padding: 0;
}

#wrapper {
	background-color: <?php echo esc_attr( $bg ); ?>;
	margin: 0;
	padding: 70px 0;
	-webkit-text-size-adjust: none !important;
	width: 100%;
}

#template_container {
	background-color: <?php echo esc_attr( $body ); ?>;
}

#template_header_image {
	text-align: center;
}

#template_header_image img {
	width: auto;
	max-height: 60px;
}

#template_header_image p {
	margin: 0 0 24px;
}

#template_header {
	border-bottom: 0;
	font-weight: bold;
	line-height: 100%;
	vertical-align: middle;
	font-family: "Roboto", "Helvetica Neue", Helvetica, OpenSans, "Open Sans", Arial, sans-serif;
	color: <?php echo esc_attr( $text ); ?>;
}

#template_footer {
	border: 0;
	color: <?php echo esc_attr( $text_footer ); ?>;
	font-family: "Roboto", "Helvetica Neue", Helvetica, OpenSans, "Open Sans", Arial, sans-serif;
	font-size: 12px;
	text-align: center;
	padding: 24px !important;
	line-height: 150%;
}

#template_footer p {
	margin: 0 !important;
}

#body_content {
	background-color: <?php echo esc_attr( $body ); ?>;
}

#body_content table td {
	padding: 0 48px;
}

#body_content table table {
	margin: 0 0 24px 0 !important;
}

#body_content table table td {
	padding: 0 24px 0 0 !important;
}

#body_content_inner {
	color: <?php echo esc_attr( $text_lighter_20 ); ?>;
	font-family: "Roboto", "Helvetica Neue", Helvetica, OpenSans, "Open Sans", Arial, sans-serif;
	font-size: 14px;
	line-height: 150%;
	text-align: left;
}

#header_wrapper {
	padding: 30px 48px !important;
	display: block;
}

h1 {
	color: <?php echo esc_attr( $text ); ?>;
	font-family: "Roboto", "Helvetica Neue", Helvetica, OpenSans, "Open Sans", Arial, sans-serif;
	font-size: 22px;
	line-height: 32px;
	font-weight: 300;
	margin: 0;
	text-align: left;
}

h2 {
	margin: 0 0 24px 0;
	font-size: 17px;
	line-height: 22px;
	color: <?php echo esc_attr( $text_darker_40 ); ?>;
	font-family: "Roboto", "Helvetica Neue", Helvetica, OpenSans, "Open Sans", Arial, sans-serif;
	text-align: left;
	font-weight: bold;
}

a {
	color: <?php echo esc_attr( $base ); ?>;
	font-weight: normal;
	text-decoration: underline;
}

img {
	border: none;
	display: inline-block;
	height: auto;
	outline: none;
	text-decoration: none;
	text-transform: capitalize;
	vertical-align: middle;
}

p {
	margin: 0 0 24px 0;
}

p.tight {
	margin: 0 0 5px 0;
}

p.code {
	font-size: 32px;
	line-height: 36px;
	text-align: center;
	padding: 5px;
	background: <?php echo esc_attr( $body_darker_3 ); ?>;
	color: <?php echo esc_attr( $text_darker_40 ); ?>;
}

p.lighter {
	font-size: 12px;
	line-height: 20px;
	padding-top: 20px;
	color: <?php echo esc_attr( $text_lighter_40 ); ?>;
	border-top: 1px solid <?php echo esc_attr( $body_darker_3 ); ?>;
}

p.hr {
	border-bottom: 1px solid <?php echo esc_attr( $body_darker_3 ); ?>;
	padding-top: 10px !important;
	margin: 0 0 24px 0 !important;
}

p.button {
	text-align: center;
	padding-top: 10px !important;
}

p strong {
	color: <?php echo esc_attr( $text_darker_40 ); ?>;
}

.link {
	background: <?php echo esc_attr( $base ); ?>;
	color: <?php echo esc_attr( $body ); ?>;
	padding: 8px 20px !important;
	border-radius: 999px !important;
	text-decoration: none !important;
	display: inline-block;
	text-align: center;
}

.link2 {
	color: <?php echo esc_attr( $text ); ?>;
	background-color: <?php echo esc_attr( $body_darker_3 ); ?>;
	border-radius: 999px;
	padding: 5px 14px !important;
	font-size: 13px !important;
	display: inline-block;
	margin: 0 10px 0 0;
	text-decoration: none !important;
}
<?php

?>