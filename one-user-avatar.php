<?php
/*
Plugin Name:  One User Avatar
Plugin URI:   https://onedesigns.com/plugins/one-user-avatar/
Description:  Use any image from your WordPress Media Library as a custom user avatar. Add your own Default Avatar. Fork of WP User Avatar v2.2.16.
Author:       One Designs
Author URI:   https://onedesigns.com/
Version:      2.3.9
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
