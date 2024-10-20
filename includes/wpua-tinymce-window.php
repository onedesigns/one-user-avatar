<?php
/**
 * TinyMCE modal window.
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
 * @version    2.5.0
 */

/**
 * @since 1.2.1
 * @uses get_users()
 */

if ( ! defined('ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$hook_suffix = 'one-user-avatar_tinymce-window';

?><!DOCTYPE html>
<html>
<head>
	<title><?php esc_html_e( 'One User Avatar', 'one-user-avatar' ); ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>" />
	<base target="_self" />
	<?php
	/**
	 * Enqueue scripts.
	 *
	 * @since 2.3.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	do_action( 'admin_enqueue_scripts', $hook_suffix );

	/**
	 * Fires when styles are printed for this specific page based on $hook_suffix.
	 *
	 * @since 2.3.0
	 */
	do_action( "admin_print_styles-{$hook_suffix}" ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

	/**
	 * Fires when styles are printed for all admin pages.
	 *
	 * @since 2.3.0
	 */
	do_action( 'admin_print_styles' );

	/**
	 * Fires when scripts are printed for this specific page based on $hook_suffix.
	 *
	 * @since 2.3.0
	 */
	do_action( "admin_print_scripts-{$hook_suffix}" ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

	/**
	 * Fires when scripts are printed for all admin pages.
	 *
	 * @since 2.3.0
	 */
	do_action( 'admin_print_scripts' );

	/**
	 * Fires in head section for this specific admin page.
	 *
	 * The dynamic portion of the hook, `$hook_suffix`, refers to the hook suffix
	 * for the admin page.
	 *
	 * @since 2.3.0
	 */
	do_action( "admin_head-{$hook_suffix}" ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

	/**
	 * Fires in head section for all admin pages.
	 *
	 * @since 2.3.0
	 */
	do_action( 'admin_head' );
	?>
</head>

<body id="link" class="wp-core-ui" onload="document.body.style.display='';" style="display:none;">
	<div id="wpua-tabs">
		<ul>
			<li><a href="#wpua"><?php esc_html_e( 'Profile Picture', 'one-user-avatar' ); ?></a></li>
			<li><a href="#wpua-upload"><?php esc_html_e( 'Upload', 'one-user-avatar' ); ?></a></li>
		</ul>

		<form name="wpUserAvatar" action="#">
			<div id="wpua">
				<p>
					<label for="<?php echo esc_attr( 'wp_user_avatar_user' ); ?>"><strong><?php esc_html_e( 'User Name', 'one-user-avatar' ); ?>:</strong></label>

					<select id="<?php echo esc_attr( 'wp_user_avatar_user' ); ?>" name="<?php echo esc_attr( 'wp_user_avatar_user' ); ?>">
						<option value=""></option>

						<?php
						$users = get_users();

						foreach($users as $user) :
							?>

							<option value="<?php echo esc_attr( $user->user_login ); ?>"><?php echo esc_html( $user->display_name ); ?></option>

							<?php
						endforeach;
						?>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( 'wp_user_avatar_size' ); ?>"><strong><?php esc_html_e( 'Size:', 'one-user-avatar' ); ?></strong></label>

					<select id="<?php echo esc_attr( 'wp_user_avatar_size' ); ?>" name="<?php echo esc_attr('wp_user_avatar_size'); ?>">
						<option value=""></option>
						<option value="original"><?php  esc_html_e( 'Original Size', 'one-user-avatar' ); ?></option>
						<option value="large"><?php     esc_html_e( 'Large',         'one-user-avatar' ); ?></option>
						<option value="medium"><?php    esc_html_e( 'Medium',        'one-user-avatar' ); ?></option>
						<option value="thumbnail"><?php esc_html_e( 'Thumbnail',     'one-user-avatar' ); ?></option>
						<option value="custom"><?php    esc_html_e( 'Custom',        'one-user-avatar' ); ?></option>
					</select>
				</p>

				<p id="<?php echo esc_attr( 'wp_user_avatar_size_number_section' ); ?>">
					<label for="<?php echo esc_attr( 'wp_user_avatar_size_number' ); ?>"><?php esc_html_e( 'Size:', 'one-user-avatar' ); ?></label>

					<input type="text" size="8" id="<?php echo esc_attr( 'wp_user_avatar_size_number' ); ?>" name="<?php echo esc_attr ( 'wp_user_avatar_size' ); ?>" value="" />
				</p>

				<p>
					<label for="<?php echo esc_attr( 'wp_user_avatar_align' ); ?>"><strong><?php esc_html_e( 'Alignment:', 'one-user-avatar' ); ?></strong></label>

					<select id="<?php echo esc_attr( 'wp_user_avatar_align' ); ?>" name="<?php echo esc_attr( 'wp_user_avatar_align' ); ?>">
						<option value=""></option>
						<option value="center"><?php esc_html_e( 'Center','one-user-avatar' ); ?></option>
						<option value="left"><?php   esc_html_e( 'Left',  'one-user-avatar' ); ?></option>
						<option value="right"><?php  esc_html_e( 'Right', 'one-user-avatar' ); ?></option>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( 'wp_user_avatar_link' ); ?>"><strong><?php esc_html_e( 'Link To:', 'one-user-avatar' ); ?></strong></label>

					<select id="<?php echo esc_attr( 'wp_user_avatar_link' ); ?>" name="<?php echo esc_attr( 'wp_user_avatar_link' ); ?>">
						<option value=""></option>
						<option value="file"><?php       esc_html_e('Image File',     'one-user-avatar'); ?></option>
						<option value="attachment"><?php esc_html_e('Attachment Page','one-user-avatar'); ?></option>
						<option value="custom-url"><?php esc_html_e('Custom URL',     'one-user-avatar'); ?></option>
					</select>
				</p>

				<p id="<?php echo esc_attr( 'wp_user_avatar_link_external_section' ); ?>">
					<label for="<?php echo esc_attr( 'wp_user_avatar_link_external' ); ?>"><?php esc_html_e( 'URL:', 'one-user-avatar' ); ?></label>

					<input type="text" size="36" id="<?php echo esc_attr( 'wp_user_avatar_link_external' ); ?>" name="<?php echo esc_attr( 'wp_user_avatar_link_external' ); ?>" value="" />
				</p>

				<p>
					<label for="<?php echo esc_attr( 'wp_user_avatar_target' ); ?>"></label>

					<input type="checkbox" id="<?php echo esc_attr( 'wp_user_avatar_target' ); ?>" name="<?php echo esc_attr( 'wp_user_avatar_target' ); ?>" value="_blank" /> <strong><?php esc_html_e( 'Open link in a new window', 'one-user-avatar' ); ?></strong>
				</p>

				<p>
					<label for="<?php echo esc_attr( 'wp_user_avatar_caption' ); ?>"><strong><?php esc_html_e( 'Caption', 'one-user-avatar' ); ?>:</strong></label>

					<textarea cols="36" rows="2" id="<?php echo esc_attr( 'wp_user_avatar_caption' ); ?>" name="<?php echo esc_attr( 'wp_user_avatar_size' ); ?>"></textarea>
				</p>

				<div class="mceActionPanel">
					<input type="submit" id="insert" class="button-primary" name="insert" value="<?php esc_html_e( 'Insert into Post', 'one-user-avatar' ); ?>" onclick="wpuaInsertAvatar();" />
				</div>
			</div>

			<div id="wpua-upload" style="display:none;">
				<p id="<?php echo esc_attr( 'wp_user_avatar_upload' ); ?>">
					<label for="<?php echo esc_attr( 'wp_user_avatar_upload' ); ?>"><strong><?php esc_html_e( 'Upload', 'one-user-avatar' ); ?>:</strong></label>

					<input type="text" size="36" id="<?php echo esc_attr( 'wp_user_avatar_upload' ); ?>" name="<?php echo esc_attr( 'wp_user_avatar_upload' ); ?>" value="<?php echo esc_attr( '[avatar_upload]' ); ?>" readonly="readonly" />
				</p>

				<div class="mceActionPanel">
					<input type="submit" id="insert" class="button-primary" name="insert" value="<?php esc_html_e( 'Insert into Post', 'one-user-avatar' ); ?>" onclick="wpuaInsertAvatarUpload();" />
				</div>
			</div>
		</form>
	</div>
</body>
</html>
