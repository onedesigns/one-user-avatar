<?php
/**
 * Let's get started!
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
		define( 'WPUA_VERSION',    '2.5.0' );
		define( 'WPUA_FOLDER',     basename( dirname( One_User_Avatar::plugin_file_path() ) ) );
		define( 'WPUA_DIR',        One_User_Avatar::plugin_dir_path() );
		define( 'WPUA_INC',        WPUA_DIR . 'includes' . '/' );
		define( 'WPUA_URL',        plugin_dir_url( WPUA_FOLDER ) . WPUA_FOLDER . '/' );
		define( 'WPUA_ASSETS_URL', WPUA_URL . 'assets'.'/' );
		define( 'WPUA_CSS_URL',    WPUA_ASSETS_URL . 'css'.'/' );
		define( 'WPUA_JS_URL',     WPUA_ASSETS_URL . 'js'.'/' );
		define( 'WPUA_IMG_URL',    WPUA_ASSETS_URL . 'images'.'/' );
		define( 'WPUA_INC_URL',    WPUA_URL . 'includes'.'/' );
	}

	/**
	 * WordPress includes used in plugin
	 * @since 1.9.2
	 * @uses is_admin()
	 */
	private function _load_wp_includes() {
		if ( ! is_admin() ) {
			// wp_handle_upload
			require_once( ABSPATH . 'wp-admin/includes/file.php' );

			// wp_generate_attachment_metadata
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// image_add_caption
			require_once( ABSPATH . 'wp-admin/includes/media.php' );

			// submit_button
			require_once( ABSPATH . 'wp-admin/includes/template.php' );
		}

		// add_screen_option
		require_once( ABSPATH . 'wp-admin/includes/screen.php' );
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
		if ( 1 == (bool) $wpua_tinymce ) {
			require_once( WPUA_INC.'wpua-tinymce.php' );
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
