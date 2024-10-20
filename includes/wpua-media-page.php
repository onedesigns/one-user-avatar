<?php
/**
 * Media Library view of all avatars in use.
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
 * @since 1.8
 * @uses object $wpua_admin
 * @uses _wpua_get_list_table()
 * @uses add_query_arg()
 * @uses check_admin_referer()
 * @uses current_action()
 * @uses current_user_can()
 * @uses display()
 * @uses esc_url()
 * @uses find_posts_div()
 * @uses get_pagenum()
 * @uses get_search_query
 * @uses number_format_i18n()
 * @uses prepare_items()
 * @uses remove_query_arg()
 * @uses search_box()
 * @uses views()
 * @uses wp_delete_attachment()
 * @uses wp_die()
 * @uses wp_get_referer()
 * @uses wp_redirect()
 * @uses wp_unslash()
 */

/** WordPress Administration Bootstrap */
require_once( ABSPATH . 'wp-admin/admin.php' );

if ( ! current_user_can( 'upload_files' ) ) {
	wp_die( __( 'You do not have permission to upload files.', 'one-user-avatar' ) );
}

global $wpua_admin;

$wp_list_table = $wpua_admin->_wpua_get_list_table( 'WP_User_Avatar_List_Table' );

$wp_list_table->prepare_items();

$message = '';

if ( ! empty( $_GET['deleted'] ) && absint( $_GET['deleted'] ) ) {
	$deleted = absint( $_GET['deleted'] );

	$message = sprintf(
		_n(
			/* translators: %s: Number of media files. */
			'%s avatar permanently deleted.',
			/* translators: %s: Number of media files. */
			'%s avatars permanently deleted.',
			$deleted,
			'one-user-avatar'
		),
		number_format_i18n( $_GET['deleted'] )
	);

	$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'deleted' ), $_SERVER['REQUEST_URI'] );

	unset( $_GET['deleted'] );
}

if ( ! empty( $_GET['trashed'] ) && absint( $_GET['trashed'] ) ) {
	$trashed  = absint( $_GET['trashed'] );
	$post_ids = isset( $_GET['ids'] ) ? explode( ',', $_GET['ids'] ) : array();

	$message = sprintf(
		_n(
			/* translators: %s: Number of media files. */
			'%s avatar moved to the Trash.',
			/* translators: %s: Number of media files. */
			'%s avatars moved to the Trash.',
			$trashed,
			'one-user-avatar'
		),
		number_format_i18n( $trashed )
	);

	if ( ! empty( $post_ids ) ) {
		$message .= sprintf(
			' <a href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url(
				add_query_arg(
					array(
						'page'     => 'wp-user-avatar-library',
						'doaction' => 'undo',
						'action'   => 'untrash',
						'media'    => $post_ids,
					),
					'admin.php'
				),
				'bulk-media'
			) ),
			__( 'Undo', 'one-user-avatar' )
		);
	}

	$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'trashed' ), $_SERVER['REQUEST_URI'] );

	unset( $_GET['trashed'] );
}

if ( ! empty( $_GET['untrashed'] ) && absint( $_GET['untrashed'] ) ) {
	$untrashed = absint( $_GET['untrashed'] );

	$message = sprintf(
		_n(
			'Avatar restored from the Trash.',
			/* translators: %s: Number of media files. */
			'%s avatars restored from the Trash.',
			$untrashed
		),
		number_format_i18n( $untrashed )
	);

	$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'untrashed' ), $_SERVER['REQUEST_URI'] );

	unset( $_GET['untrashed'] );
}

?>

<div class="wrap">
	<h2>
		<?php esc_html_e('Avatars','one-user-avatar'); ?>

		<?php if ( ! empty( $_REQUEST['s'] ) ) : ?>
			<span class="subtitle">
				<?php
				printf(
					/* translators: search query */
					__( 'Search results for %s','one-user-avatar' ),
					sprintf( '&#8220;%s&#8221;', get_search_query() )
				);
				?>
			</span>
		<?php endif; ?>
	</h2>

	<?php
		if ( ! empty( $message ) ) {
			wp_admin_notice(
				$message,
				array(
					'id'                 => 'message',
					'additional_classes' => array( 'updated' ),
					'dismissible'        => true,
				)
			);
		}
	?>

	<?php $wp_list_table->views(); ?>

	<form class="wpua-media-form" id="posts-filter" action="" method="get">
		<?php $wp_list_table->search_box( __('Search','one-user-avatar'), 'media' ); ?>

		<?php $wp_list_table->display(); ?>

		<div id="ajax-response"></div>

		<?php find_posts_div(); ?>

		<br class="clear" />
	</form>
</div>
