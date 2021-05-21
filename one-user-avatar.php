<?php
/*
Plugin Name: One User Avatar
Plugin URI: https://onedesigns.com/plugins/one-user-avatar/
Description: Use any image from your WordPress Media Library as a custom user avatar. Add your own Default Avatar. Fork of WP User Avatar v2.2.16.
Author: One Designs
Author URI: https://onedesigns.com/
Version: 2.3.0
Text Domain: one-user-avatar
Domain Path: /languages/

One User Avatar
Copyright (c) 2021 One Designs https://onedesigns.com/

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

One User Avatar bundles the following third-party resources:

jQuery UI Slider v1.12.1
Copyright (c) 2021 jQuery Foundation
License: MIT
Source: https://github.com/jquery/jquery-ui
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( class_exists( 'WP_User_Avatar_Setup' ) ) {
    // return;
}

/**
 * Let's get started!
 */
class WP_User_Avatar_Setup {
	/**
	 * Constructor
	 * @since 1.9.2
	 */
	public function __construct() {
		$this->_define_constants();
		$this->_load_wp_includes();
		$this->_load_wpua();
	}

	/**
	 * Define paths
	 * @since 1.9.2
	 */
	private function _define_constants() {
		define( 'WPUA_VERSION', '2.3.0' );
		define( 'WPUA_FOLDER',  basename( dirname( __FILE__ ) ) );
		define( 'WPUA_DIR',     plugin_dir_path( __FILE__ ) );
		define( 'WPUA_INC',     WPUA_DIR . 'includes' . '/' );
		define( 'WPUA_URL',     plugin_dir_url( WPUA_FOLDER ) . WPUA_FOLDER . '/' );
		define( 'WPUA_INC_URL', WPUA_URL . 'includes'.'/' );
	}

	/**
	 * WordPress includes used in plugin
	 * @since 1.9.2
	 * @uses is_admin()
	 */
	private function _load_wp_includes() {
		if ( ! is_admin() ) {
			// wp_handle_upload
			require_once( ABSPATH.'wp-admin/includes/file.php' );

            // wp_generate_attachment_metadata
			require_once( ABSPATH.'wp-admin/includes/image.php' );

            // image_add_caption
			require_once( ABSPATH.'wp-admin/includes/media.php' );

			// submit_button
			require_once( ABSPATH.'wp-admin/includes/template.php' );
		}

		// add_screen_option
		require_once(ABSPATH.'wp-admin/includes/screen.php');
	}

	/**
	 * Load One User Avatar
	 * @since 1.9.2
	 * @uses bool $wpua_tinymce
	 * @uses is_admin()
	 */
	private function _load_wpua() {
		global $wpua_tinymce;

		require_once( WPUA_INC . 'wpua-globals.php' );
		require_once( WPUA_INC . 'wpua-functions.php' );
		require_once( WPUA_INC . 'class-wp-user-avatar-admin.php' );
		require_once( WPUA_INC . 'class-wp-user-avatar.php' );
		require_once( WPUA_INC . 'class-wp-user-avatar-functions.php' );
		require_once( WPUA_INC . 'class-wp-user-avatar-shortcode.php' );
		require_once( WPUA_INC . 'class-wp-user-avatar-subscriber.php' );
		require_once( WPUA_INC . 'class-wp-user-avatar-update.php' );
		require_once( WPUA_INC . 'class-wp-user-avatar-widget.php' );

		// Load TinyMCE only if enabled
		if ( (bool) $wpua_tinymce == 1 ) {
			require_once(WPUA_INC.'wpua-tinymce.php');
		}
	}
}

function wp_user_avatar_setup() {
    global $wp_user_avatar_setup;

    if ( ! isset( $wp_user_avatar_setup ) ) {
        $wp_user_avatar_setup = new WP_User_Avatar_Setup();
    }

    return $wp_user_avatar_setup;
}

/**
 * Initialize
 */
wp_user_avatar_setup();
