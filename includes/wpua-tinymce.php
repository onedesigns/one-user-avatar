<?php
/**
 * TinyMCE button for Visual Editor.
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
 * Add TinyMCE button
 * @since 1.9.5
 * @uses add_filter()
 * @uses get_user_option()
 */
function wpua_add_buttons() {
	// Add only in Rich Editor mode
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'wpua_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'wpua_register_button' );
	}
}
add_action( 'init', 'wpua_add_buttons' );

/**
 * Register TinyMCE button
 * @since 1.9.5
 * @param array $buttons
 * @return array
 */
function wpua_register_button( $buttons ) {
	array_push( $buttons, 'separator', 'wpUserAvatar' );

	return $buttons;
}

/**
 * Load TinyMCE plugin
 * @since 1.9.5
 * @param array $plugin_array
 * @return array
 */
function wpua_add_tinymce_plugin( $plugins ) {
	$plugins['wpUserAvatar'] = WPUA_JS_URL . 'tinymce-editor_plugin.js';

	return $plugins;
}

function wpua_tinymce_enqueue_scripts( $hook_suffix ) {
	switch ( $hook_suffix ) {
		case 'one-user-avatar_tinymce-window':
			wp_enqueue_style( 'one-user-avatar-tinymce-window', WPUA_CSS_URL . 'tinymce-window.css' );

			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'one-user-avatar-tinymce-popup',      includes_url( 'js/tinymce/tiny_mce_popup.js' ) );
			wp_enqueue_script( 'one-user-avatar-tinymce-form-utils', includes_url( 'js/tinymce/utils/form_utils.js' ) );
			wp_enqueue_script( 'one-user-avatar-tinymce-window',     WPUA_JS_URL . 'tinymce-window.js' );

			break;

		case 'post.php':
			wp_localize_script( 'editor', 'one_user_avatar_tinymce_editor_args', array(
				'insert_avatar' => __( 'Insert Avatar', 'one-user-avatar' ),
			) );

			break;
	}
}
add_action( 'admin_enqueue_scripts', 'wpua_tinymce_enqueue_scripts' );

/**
 * Call TinyMCE window content via admin-ajax
 * @since 1.4
 */
function wpua_ajax_tinymce() {
	include_once( WPUA_INC . 'wpua-tinymce-window.php' );

	die();
}
add_action( 'wp_ajax_wp_user_avatar_tinymce', 'wpua_ajax_tinymce' );
