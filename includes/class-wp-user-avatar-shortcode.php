<?php
/**
 * Defines shortcodes.
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

class WP_User_Avatar_Shortcode {
	/**
	 * Constructor
	 * @since 1.8
	 * @uses object $wp_user_avatar
	 * @uses add_action()
	 * @uses add_shortcode()
	 */
	public function __construct() {
		global $wp_user_avatar;

		add_shortcode( 'avatar', array( $this, 'wpua_shortcode' ) );
		add_shortcode( 'avatar_upload', array( $this, 'wpua_edit_shortcode' ) );

		// Add avatar and scripts to avatar_upload
		add_action( 'wpua_show_profile', array( $wp_user_avatar, 'wpua_action_show_user_profile' ) );
		add_action( 'wpua_show_profile', array( $wp_user_avatar, 'wpua_media_upload_scripts' ) );
		add_action( 'wpua_update',       array( $wp_user_avatar, 'wpua_action_process_option_update' ) );

		// Add error messages to avatar_upload
		add_action( 'wpua_update_errors', array( $wp_user_avatar, 'wpua_upload_errors' ), 10, 3 );
	}

	/**
	 * Display shortcode
	 * @since 1.4
	 * @param array $atts
	 * @param string $content
	 * @uses array $_wp_additional_image_sizes
	 * @uses array $all_sizes
	 * @uses int $blog_id
	 * @uses object $post
	 * @uses object $wpdb
	 * @uses do_shortcode()
	 * @uses get_attachment_link()
	 * @uses get_blog_prefix()
	 * @uses get_option()
	 * @uses get_user_by()
	 * @uses get_query_var()
	 * @uses get_the_author_meta()
	 * @uses get_user_meta()
	 * @uses get_wp_user_avatar_src()
	 * @uses get_wp_user_avatar()
	 * @uses image_add_caption()
	 * @uses is_author()
	 * @uses shortcode_atts()
	 * @return string
	 */
	public function wpua_shortcode( $atts, $content = null ) {
		global $all_sizes, $blog_id, $post, $wpdb;

		// Set shortcode attributes
		extract( shortcode_atts( array(
			'user'   => '',
			'size'   => '96',
			'align'  => '',
			'link'   => '',
			'target' => '',
		), $atts ) );

		// Find user by ID, login, slug, or e-mail address
		if ( ! empty( $user ) ) {
			if ( 'current' == $user ) {
				$user = wp_get_current_user();
			} else {
				$user = is_numeric( $user ) ? get_user_by( 'id',    $atts['user'] ) : get_user_by( 'login', $atts['user'] );
				$user = empty( $user )      ? get_user_by( 'slug',  $atts['user'] ) : $user;
				$user = empty( $user )      ? get_user_by( 'email', $atts['user'] ) : $user;
			}
		} else {
			// Find author's name if id_or_email is empty
			$author_name = get_query_var( 'author_name' );

			if ( is_author() ) {
				// On author page, get user by page slug
				$user = get_user_by( 'slug', $author_name );
			} else {
				// On post, get user by author meta
				$user_id = get_the_author_meta( 'ID' );
				$user    = get_user_by( 'id', $user_id );
			}
		}

		// Numeric sizes leave as-is
		$get_size = $size;

		// Check for custom image sizes if there are captions
		if ( ! empty( $content ) ) {
			if ( in_array( $size, $all_sizes ) ) {
				if ( in_array( $size, array( 'original', 'large', 'medium', 'thumbnail' ) ) ) {
					$get_size = ( $size == 'original' ) ? get_option( 'large_size_w' ) : get_option( $size . '_size_w' );
				} else {
					$get_size = $_wp_additional_image_sizes[ $size ]['width'];
				}
			}
		}

		// Get user ID
		$id_or_email = ! empty( $user ) ? $user->ID : 'unknown@gravatar.com';

		// Check if link is set
		if ( ! empty( $link ) ) {
			// CSS class is same as link type, except for URL
			$link_class = $link;

			if ( 'file' == $link ) {
				// Get image src
				$link = get_wp_user_avatar_src( $id_or_email, 'original' );
			} elseif ( $link == 'attachment' ) {
				// Get attachment URL
				$link = get_attachment_link( get_the_author_meta( $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', $id_or_email ) );
			} else {
				// URL
				$link_class = 'custom';
			}

			// Link target
			if ( ! in_array( $target, array( '_blank', '_self', '_parent', '_top' ) ) ) {
				$target = '';
			}

			// Wrap the avatar inside the link
			$html = sprintf(
				'<a href="%s" class="wp-user-avatar-link wp-user-avatar-%s"%s>%s</a>',
				esc_url( $link ),
				esc_attr( $link_class ),
				( $target ? sprintf( ' target="%s"', esc_attr( $target ) ) : '' ),
				get_wp_user_avatar( $id_or_email, $get_size, $align )
			);
		} else {
			$html = get_wp_user_avatar( $id_or_email, $get_size, $align );
		}

		// Check if caption is set
		if ( ! empty( $content ) ) {
			// Get attachment ID
			$wpua    = get_user_meta( $id_or_email, $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', true );

			// Clean up caption
			$content = trim( $content );
			$content = preg_replace( '/\r|\n/', '', $content );
			$content = preg_replace( '/<\/p><p>/', '', $content, 1 );
			$content = preg_replace( '/<\/p><p>$/', '', $content );
			$content = str_replace( '</p><p>', '<br /><br />', $content );

			$avatar  = do_shortcode( image_add_caption( $html, $wpua, $content, $title = '', $align, $link, $get_size, $alt = ''));
		} else {
			$avatar  = $html;
		}

		// The srcset attribute will be restored in 'the_content' filter
		$avatar = str_replace( ' srcset=', ' data-srcset=', $avatar );

		return wp_kses_post( $avatar );
	}

	/**
	 * Update user
	 * @since 1.8
	 * @param bool $user_id
	 * @uses add_query_arg()
	 * @uses apply_filters()
	 * @uses do_action_ref_array()
	 * @uses wp_get_referer()
	 * @uses wp_redirect()
	 * @uses wp_safe_redirect()
	 */
	private function wpua_edit_user( $user_id = 0 ) {
		$update = $user_id ? true : false;
		$user   = new stdClass;
		$errors = new WP_Error();

		do_action_ref_array( 'wpua_update_errors', array( &$errors, $update, &$user ) );

		if ( $errors->get_error_codes() ) {
			// Return with errors
			return $errors;
		}
	}

	/**
	 * Edit shortcode
	 * @since 1.8
	 * @param array $atts
	 * @uses $wp_user_avatar
	 * @uses $wpua_allow_upload
	 * @uses current_user_can()
	 * @uses do_action()
	 * @uses get_error_messages()
	 * @uses get_user_by()
	 * @uses is_user_logged_in()
	 * @uses is_wp_error()
	 * @uses shortcode_atts()
	 * @uses wpua_edit_form()
	 * @uses wpua_edit_user()
	 * @uses wpua_is_author_or_above()
	 * @return string
	 */
	public function wpua_edit_shortcode( $atts ) {
		global $current_user, $errors, $wp_user_avatar, $wpua_allow_upload;

		// Shortcode only works for users with permission
		if ( $wp_user_avatar->wpua_is_author_or_above() || ( 1 == (bool) $wpua_allow_upload && is_user_logged_in() ) ) {
			extract( shortcode_atts( array( 'user' => '' ), $atts ) );

			// Default user is current user
			$valid_user = $current_user;

			// Find user by ID, login, slug, or e-mail address
			if ( ! empty( $user ) ) {
				$get_user = is_numeric( $user ) ? get_user_by( 'id',    $user ) : get_user_by( 'login', $user );
				$get_user = empty( $get_user )  ? get_user_by( 'slug',  $user ) : $get_user;
				$get_user = empty( $get_user )  ? get_user_by( 'email', $user ) : $get_user;

				// Check if current user can edit this user
				$valid_user = current_user_can( 'edit_user', $get_user->ID ) ? $get_user : null;
			}

			$output = '';

			// Show form only for valid user
			if ( $valid_user ) {
				// Save
				if (
					( isset( $_POST['submit'] ) && $_POST['submit'] )
					&&
					( isset( $_POST['wpua_action'] ) && 'update' == $_POST['wpua_action'] )
					&&
					( isset( $_POST[ '_wpnonce'] ) && wp_verify_nonce( $_POST[ '_wpnonce'], 'update-user_' . $valid_user->ID ) )
				) {
					ob_start();

					do_action( 'wpua_update', $valid_user->ID );

					// Check for errors
					$errors = $this->wpua_edit_user( $valid_user->ID );

					// Errors
					if ( isset( $errors ) && is_wp_error( $errors ) ) {
						printf( '<div class="error"><p>%s</p></div>', implode( "</p>\n<p>", $errors->get_error_messages() ) );
					} else {
						printf( '<div class="success"><p><strong>%s</strong></p></div>', __( 'Profile updated.', 'one-user-avatar' ) );
					}

					$output .= ob_get_clean();
				}

				// Edit form
				$output .= $this->wpua_edit_form( $valid_user );

				return wp_kses( $output, array_merge( wp_kses_allowed_html( 'post' ), array(
					'form'  => array(
						'id'      => true,
						'class'   => true,
						'action'  => true,
						'class'   => true,
						'method'  => true,
						'enctype' => true,
					),
					'input' => array(
						'type'    => true,
						'name'    => true,
						'id'      => true,
						'class'   => true,
						'value'   => true,
					),
				) ) );
			}
		}
	}

	/**
	 * Edit form
	 * @since 1.8
	 * @param object $user
	 * @uses do_action()
	 * @uses submit_button()
	 * @uses wp_nonce_field()
	 */
	private function wpua_edit_form($user) {
		ob_start();
		?>

		<form id="wpua-edit-<?php echo esc_attr( $user->ID ); ?>" class="wpua-edit" action="" method="post" enctype="multipart/form-data">
			<?php do_action( 'wpua_show_profile', $user ); ?>

			<input type="hidden" name="wpua_action" value="update" />
			<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user->ID ); ?>" />

			<?php wp_nonce_field( 'update-user_' . $user->ID ); ?>

			<?php submit_button( __( 'Update Profile', 'one-user-avatar' ) ); ?>
		</form>

		<?php

		return ob_get_clean();
	}
}

/**
 * Initialize
 * @since 1.9.2
 */
function wpua_shortcode_init() {
	global $wpua_shortcode;

	if ( ! isset( $wpua_shortcode ) ) {
		$wpua_shortcode = new WP_User_Avatar_Shortcode();
	}

	return $wpua_shortcode;
}
add_action( 'init', 'wpua_shortcode_init' );
