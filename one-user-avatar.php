<?php
/*
Plugin Name:  One User Avatar
Plugin URI:   https://onedesigns.com/plugins/one-user-avatar/
Description:  Use any image from your WordPress Media Library as a custom user avatar. Add your own Default Avatar. Fork of WP User Avatar v2.2.16.
Author:       One Designs
Author URI:   https://onedesigns.com/
Version:      2.5.0
Text Domain:  one-user-avatar
Domain Path:  /languages/

One User Avatar
Copyright (c) 2021 One Designs https://onedesigns.com/
License: GPLv2
Source: https://github.com/onedesigns/one-user-avatar

One User Avatar is based on WP User Avatar v2.2.16
Copyright (c) 2020-2021 ProfilePress https://profilepress.net/
Copyright (c) 2014-2020 Flippercode https://www.flippercode.com/
Copyright (c) 2013-2014 Bangbay Siboliban http://bangbay.com/
License: GPLv2
Source: https://github.com/profilepress/wp-user-avatar

One User Avatar is distributed under the terms of the GNU GPL

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Plugin activation hook
 * @since 2.5.0
 * @uses add_option()
 */
function one_user_avatar_activate() {
	if ( class_exists( 'WP_User_Avatar_Setup' ) ) {
		return;
	}

	// Settings saved to wp_options
	add_option( 'avatar_default_wp_user_avatar',       '' );
	add_option( 'wp_user_avatar_allow_upload',        '0' );
	add_option( 'wp_user_avatar_disable_um_avatars',  '0' );
	add_option( 'wp_user_avatar_force_file_uploader', '0' );
	add_option( 'wp_user_avatar_disable_gravatar',    '0' );
	add_option( 'wp_user_avatar_edit_avatar',         '1' );
	add_option( 'wp_user_avatar_resize_crop',         '0' );
	add_option( 'wp_user_avatar_resize_h',           '96' );
	add_option( 'wp_user_avatar_resize_upload',       '0' );
	add_option( 'wp_user_avatar_resize_w',           '96' );
	add_option( 'wp_user_avatar_tinymce',             '1' );
	add_option( 'wp_user_avatar_upload_size_limit',   '0' );

	// If Gravatar was disabled before, reset default avatar to 'wp_user_avatar'
	if (
		get_option( 'wp_user_avatar_disable_gravatar' )
		&&
		'wp_user_avatar' != get_option( 'avatar_default' )
	) {
		update_option( 'avatar_default', 'wp_user_avatar' );
	}

	if ( wp_next_scheduled( 'wpua_has_gravatar_cron_hook' ) ) {
		$cron = get_option( 'cron' );

		foreach ( $cron as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( array_key_exists( 'wpua_has_gravatar_cron_hook', $value ) ) {
					unset( $cron[ $key ] );
				}
			}
		}

		update_option( 'cron', $cron );
	}
}
register_activation_hook( __FILE__, 'one_user_avatar_activate' );

/**
 * Plugin deactivation hook
 * @since 2.5.0
 * @uses int $blog_id
 * @uses object $wpdb
 * @uses get_blog_prefix()
 * @uses get_option()
 * @uses update_option()
 */
function one_user_avatar_deactivate() {
	global $blog_id, $wpdb;

	$wp_user_roles = $wpdb->get_blog_prefix( $blog_id ) . 'user_roles';

	// Get user roles and capabilities
	$user_roles = get_option( $wp_user_roles );

	// Remove subscribers edit_posts capability
	unset( $user_roles['subscriber']['capabilities']['edit_posts'] );

	update_option( $wp_user_roles, $user_roles );

	if ( 'wp_user_avatar' == get_option( 'avatar_default' ) ) {
		// Reset default avatar to Mystery Man
		update_option( 'avatar_default', 'mystery' );
	}
}
register_deactivation_hook( __FILE__, 'one_user_avatar_deactivate' );

class One_User_Avatar {
	/**
	 * Check for conflicts and load plugin
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		// Check for conflict
		if ( class_exists( 'WP_User_Avatar_Setup' ) ) {
			// Add admin notice
			add_action( 'admin_notices', array( $this, 'conflict_admin_notice' ) );

			// Bail
			return;
		}

		// Load plugin
		require_once( plugin_dir_path( self::plugin_file_path() ) . 'includes/class-wp-user-avatar-setup.php' );
	}

	/**
	 * Access plugin file path globally
	 *
	 * @since 2.3.0
	 */
	public static function plugin_file_path() {
		return __FILE__;
	}

	/**
	 * Access plugin directory path globally
	 *
	 * @since 2.3.0
	 */
	public static function plugin_dir_path() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Print admin notice error in case of plugin conflict
	 *
	 * @since 2.3.1
	 */
	public function conflict_admin_notice() {
		global $pagenow, $status, $pagenum, $s;

		if ( 'plugins.php' != $pagenow ) {
			return;
		}

		$plugin_file = 'wp-user-avatar/wp-user-avatar.php';

		if ( ! current_user_can( 'deactivate_plugin', $plugin_file ) ) {
			return;
		}

		$url = admin_url(
			sprintf(
				'plugins.php?action=deactivate&plugin=%s',
				urlencode( $plugin_file )
			)
		);

		if ( ! empty( $status ) ) {
			add_query_arg( 'plugin_status', urlencode( $status ), $url );
		}

		if ( ! empty( $pagenum ) ) {
			add_query_arg( 'paged', urlencode( $pagenum ), $url );
		}

		if ( ! empty( $s ) ) {
			add_query_arg( 'paged', urlencode( $s ), $url );
		}

		$url = wp_nonce_url( $url, sprintf( 'deactivate-plugin_%s', $plugin_file ) );

		$message = sprintf(
			/* translators: placeholder for <a> and </a> tags. */
			__( 'The plugin One User Avatar is a replacement for the old WP User Avatar plugin. Please %1$sdeactivate WP User Avatar%2$s to start using it.', 'one-user-avatar' ),
			sprintf( '<a href="%s">', esc_url( $url ) ),
			'</a>'
		);

		?>

		<div class="notice notice-error">
			<p><?php echo wp_kses( $message, 'data' ); ?></p>
		</div>

		<?php
	}
}

/**
 * Load Plugin
 *
 * @since 2.3.0
 */
function one_user_avatar() {
	global $one_user_avatar;

	if ( ! isset( $one_user_avatar ) ) {
		$one_user_avatar = new One_User_Avatar();
	}

	return $one_user_avatar;
}
add_action( 'plugins_loaded', 'one_user_avatar', 0 );
