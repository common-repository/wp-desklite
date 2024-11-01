<?php
/**
 * Email Footer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
															</div>
														</td>
													</tr>
												</table>
												<!-- End Content -->
											</td>
										</tr>
									</table>
									<!-- End Body -->
								</td>
							</tr>
						</table>
						<div id="template_footer">
							<?php echo wpautop( wp_kses_post( wptexturize( apply_filters( 'wpdl_email_footer_text', get_option( 'wpdl_email_footer_text' ) ) ) ) ); ?>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</body>
</html>