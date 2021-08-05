<?php
/**
 * Admin page to change plugin options.
 *
 * @package    One User Avatar
 * @author     Bangbay Siboliban
 * @author     Flippercode
 * @author     ProfilePress
 * @author     One Designs
 * @copyright  2013-2014 Bangbay Siboliban
 * @copyright  2014-2020 Flippercode
 * @copyright  2020-2021 ProfilePress
 * @copyright  2021 One Designs
 * @version    2.3.6
 */

/**
 * @since 1.4
 * @uses bool $show_avatars
 * @uses string $upload_size_limit_with_units
 * @uses object $wpua_admin
 * @uses bool $wpua_allow_upload
 * @uses bool $wpua_disable_gravatar
 * @uses bool $wpua_edit_avatar
 * @uses bool $wpua_resize_crop
 * @uses int int $wpua_resize_h
 * @uses bool $wpua_resize_upload
 * @uses int $wpua_resize_w
 * @uses object $wpua_subscriber
 * @uses bool $wpua_tinymce
 * @uses int $wpua_upload_size_limit
 * @uses string $wpua_upload_size_limit_with_units
 * @uses admin_url()
 * @uses apply_filters()
 * @uses checked()
 * @uses do_action()
 * @uses do_settings_fields()
 * @uses get_option()
 * @uses settings_fields()
 * @uses submit_button()
 * @uses wpua_add_default_avatar()
 */

global $show_avatars,
       $upload_size_limit_with_units,
	   $wpua_admin,
	   $wpua_allow_upload,
	   $wpua_disable_um_avatars,
	   $wpua_force_file_uploader,
	   $wpua_disable_gravatar,
	   $wpua_edit_avatar,
	   $wpua_resize_crop,
	   $wpua_resize_h,
	   $wpua_resize_upload,
	   $wpua_resize_w,
	   $wpua_subscriber,
	   $wpua_tinymce,
	   $wpua_upload_size_limit,
	   $wpua_upload_size_limit_with_units;

$updated = false;

if ( isset( $_GET['settings-updated'] ) && 'true' == $_GET['settings-updated'] ) {
	$updated = true;
}

$hide_size   = true !== (bool) $wpua_allow_upload  ? ' style="display:none;"' : '';
$hide_resize = true !== (bool) $wpua_resize_upload ? ' style="display:none;"' : '';

$wpua_options_page_title = __( 'One User Avatar', 'one-user-avatar' );

/**
 * Filter admin page title
 * @since 1.9
 * @param string $wpua_options_page_title
 */
$wpua_options_page_title = apply_filters( 'wpua_options_page_title', $wpua_options_page_title );
?>

<div class="wrap">
	<h2><?php echo $wpua_options_page_title; ?></h2>

	<table>
		<tr valign="top">
			<td align="top">
				<form method="post" action="<?php echo admin_url('options.php'); ?>">

					<?php settings_fields('wpua-settings-group'); ?>

					<?php do_settings_fields('wpua-settings-group', ""); ?>

					<table class="form-table">
						<?php
							// Format settings in table rows
							$wpua_before_settings = array();

							/**
							 * Filter settings at beginning of table
							 * @since 1.9
							 * @param array $wpua_before_settings
							 */
							$wpua_before_settings = apply_filters( 'wpua_before_settings', $wpua_before_settings );

							echo implode( '', $wpua_before_settings );
						?>

						<tr valign="top">
							<th scope="row"><?php _e( 'Settings', 'one-user-avatar' ); ?></th>

							<td>
								<?php
									// Format settings in fieldsets
									$wpua_settings = array();

									$wpua_settings['tinymce'] = sprintf(
										'<fieldset>
											<label for="wp_user_avatar_tinymce">
												<input name="wp_user_avatar_tinymce" type="checkbox" id="wp_user_avatar_tinymce" value="1" %s />
												%s
											</label>
										</fieldset>',
										checked( $wpua_tinymce, true, false ),
										__( 'Add avatar button to Visual Editor', 'one-user-avatar' )
									);

									$wpua_settings['upload'] = sprintf(
										'<fieldset>
											<label for="wp_user_avatar_allow_upload">
												<input name="wp_user_avatar_allow_upload" type="checkbox" id="wp_user_avatar_allow_upload" value="1" %s />
												%s
											</label>
										</fieldset>',
										checked( $wpua_allow_upload, true, false ),
										__( 'Allow Contributors & Subscribers to upload avatars', 'one-user-avatar' )
									);

									$wpua_settings['gravatar'] = sprintf(
										'<fieldset>
											<label for="wp_user_avatar_disable_gravatar">
												<input name="wp_user_avatar_disable_gravatar" type="checkbox" id="wp_user_avatar_disable_gravatar" value="1" %s />
												%s
											</label>
										</fieldset>',
										checked( $wpua_disable_gravatar, true, false ),
										__( 'Disable Gravatar and use only local avatars', 'one-user-avatar' )
									);

									if ( function_exists( 'um_get_avatar' ) ) {
										$wpua_settings['disable_um_avatars'] = sprintf(
											'<fieldset>
												<label for="wp_user_avatar_disable_um_avatars">
													<input name="wp_user_avatar_disable_um_avatars" type="checkbox" id="wp_user_avatar_disable_um_avatars" value="1" %s />
													%s
												</label>
											</fieldset>',
											checked( $wpua_disable_um_avatars, true, false ),
											__( 'Replace the custom avatars functionality in the Ultimate Member plugin', 'one-user-avatar' )
										);
									}

									$wpua_settings['force_file_uploader'] = sprintf(
										'<fieldset>
											<label for="wp_user_avatar_force_file_uploader">
												<input name="wp_user_avatar_force_file_uploader" type="checkbox" id="wp_user_avatar_force_file_uploader" value="1" %s />
												%s
											</label>
											<p class="description">%s</p>
										</fieldset>',
										checked( $wpua_force_file_uploader, true, false ),
										__( 'Always use the browser file uploader to upload avatars', 'one-user-avatar' ),
										__( 'Check this if another plugin is conflicting with the WordPress Media Uploader.', 'one-user-avatar' )
									);

									/**
									 * Filter main settings
									 * @since 1.9
									 * @param array $wpua_settings
									 */
									$wpua_settings = apply_filters( 'wpua_settings', $wpua_settings );

									echo implode( '', $wpua_settings );
								?>
							</td>
						</tr>
					</table>

					<?php
						// Format settings in table
						$wpua_subscriber_settings = array();

						ob_start();
					?>

					<div id="wpua-contributors-subscribers"<?php echo $hide_size; ?>>
						<table class="form-table">
							<tr valign="top">
								<th scope="row">
									<label for="wp_user_avatar_upload_size_limit">'
										<?php _e( 'Upload Size Limit', 'one-user-avatar' ); ?>
										<?php _e( '(only for Contributors & Subscribers)', 'one-user-avatar' ); ?>
									</label>
								</th>

								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span>
												<?php _e( 'Upload Size Limit', 'one-user-avatar' ); ?>
												<?php _e( '(only for Contributors & Subscribers)', 'one-user-avatar' ); ?>
											</span>
										</legend>

										<input name="wp_user_avatar_upload_size_limit" type="range" id="wp_user_avatar_upload_size_limit" value="<?php echo esc_attr( $wpua_upload_size_limit ); ?>" min="0" max="<?php echo esc_attr( wp_max_upload_size() ); ?>" class="regular-text" />

										<span id="wpua-readable-size"><?php echo $wpua_upload_size_limit_with_units; ?></span>

										<span id="wpua-readable-size-error"><?php printf(
											/* translators: file name */
											__( '%s exceeds the maximum upload size for this site.', 'one-user-avatar' ),
											''
										); ?></span>

										<p class="description">
											<?php
												printf(
													/* translators: file size in KB */
													__( 'Maximum upload file size: %s.', 'one-user-avatar' ),
													esc_html( wp_max_upload_size() ) . esc_html( sprintf( ' bytes (%s)', $upload_size_limit_with_units ) )
												);
											?>
										</p>
									</fieldset>

									<fieldset>
										<label for="wp_user_avatar_edit_avatar">
											<input name="wp_user_avatar_edit_avatar" type="checkbox" id="wp_user_avatar_edit_avatar" value="1" <?php checked( $wpua_edit_avatar ); ?> />

											<?php _e( 'Allow users to edit avatars', 'one-user-avatar' ); ?>
										</label>
									</fieldset>

									<fieldset>
										<label for="wp_user_avatar_resize_upload">
											<input name="wp_user_avatar_resize_upload" type="checkbox" id="wp_user_avatar_resize_upload" value="1" <?php checked( $wpua_resize_upload ); ?> />

											<?php _e( 'Resize avatars on upload', 'one-user-avatar' ); ?>
										</label>
									</fieldset>

									<fieldset id="wpua-resize-sizes"'.$hide_resize.'>
										<label for="wp_user_avatar_resize_w"><?php _e( 'Width', 'one-user-avatar' ); ?></label>

										<input name="wp_user_avatar_resize_w" type="number" step="1" min="0" id="wp_user_avatar_resize_w" value="<?php echo esc_attr( get_option( 'wp_user_avatar_resize_w' ) ); ?>" class="small-text" />

										<label for="wp_user_avatar_resize_h"><?php _e( 'Height', 'one-user-avatar' ); ?></label>

										<input name="wp_user_avatar_resize_h" type="number" step="1" min="0" id="wp_user_avatar_resize_h" value="<?php echo esc_attr( get_option( 'wp_user_avatar_resize_h' ) ); ?>" class="small-text" />

										<br />

										<input name="wp_user_avatar_resize_crop" type="checkbox" id="wp_user_avatar_resize_crop" value="1" <?php checked( '1', $wpua_resize_crop ); ?> />

										<label for="wp_user_avatar_resize_crop"><?php _e( 'Crop avatars to exact dimensions', 'one-user-avatar' ); ?></label>
									</fieldset>
								</td>
							</tr>
						</table>
					</div>

					<?php
						$wpua_subscriber_settings['subscriber-settings'] = ob_get_clean();

						/**
						 * Filter Subscriber settings
						 * @since 1.9
						 * @param array $wpua_subscriber_settings
						 */
						$wpua_subscriber_settings = apply_filters( 'wpua_subscriber_settings', $wpua_subscriber_settings );

						echo implode( '', $wpua_subscriber_settings );
					?>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e( 'Avatar Display', 'one-user-avatar' ); ?></th>

							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span>
											<?php _e( 'Avatar Display', 'one-user-avatar' ); ?>
										</span>
									</legend>

									<label for="show_avatars">
										<input type="checkbox" id="show_avatars" name="show_avatars" value="1" <?php checked( $show_avatars ); ?> />

										<?php _e( 'Show Avatars', 'one-user-avatar' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>

						<tr valign="top" id="avatar-rating"<?php echo ( 1 === (bool) $wpua_disable_gravatar ) ? ' style="display:none"' : '' ?>>
							<th scope="row"><?php _e( 'Maximum Rating', 'one-user-avatar' ); ?></th>

							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span>
											<?php _e( 'Maximum Rating', 'one-user-avatar' ); ?>
										</span>
									</legend>

									<?php
										$ratings = array(
											'G'  => __( 'G &#8212; Suitable for all audiences', 'one-user-avatar' ),
											'PG' => __( 'PG &#8212; Possibly offensive, usually for audiences 13 and above', 'one-user-avatar' ),
											'R'  => __( 'R &#8212; Intended for adult audiences above 17', 'one-user-avatar' ),
											'X'  => __( 'X &#8212; Even more mature than above', 'one-user-avatar' ),
										);

										foreach ( $ratings as $key => $rating ) :
											?>
											<label>
												<input type="radio" name="avatar_rating" value="<?php echo esc_attr( $key ); ?>" <?php checked( $key, get_option( 'avatar_rating' ) ); ?> />
												<?php echo $rating; ?>
											</label>

											<br />
											<?php
										endforeach;
									?>
								</fieldset>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e( 'Default Avatar', 'one-user-avatar' ); ?></th>

							<td class="defaultavatarpicker">
								<fieldset>
									<legend class="screen-reader-text">
										<span>
											<?php _e( 'Default Avatar', 'one-user-avatar' ); ?>
										</span>
									</legend>

									<?php _e( 'For users without a custom avatar of their own, you can either display a generic logo or a generated one based on their e-mail address.', 'one-user-avatar' ); ?>

									<br />

									<?php echo $wpua_admin->wpua_add_default_avatar(); ?>
								</fieldset>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>
				</form>
			</td>
		</tr>
	</table>
</div>
