<?php
/**
 * Based on WP_Media_List_Table class.
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
 * @version    2.3.9
 */

class WP_User_Avatar_List_Table extends WP_List_Table {
	/**
	 * Constructor
	 * @since 1.8
	 * @param array $args
	 * @uses array $avatars
	 * @uses object $post
	 * @uses int $wpua_avatar_default
	 * @uses get_query_var()
	 * @uses have_posts()
	 * @uses the_post()
	 * @uses wp_edit_attachments_query
	 * @uses WP_Query()
	 * @uses wp_reset_query()
	 */
	public function __construct( $args = array() ) {
		global $avatars, $post, $wpua_avatar_default;

		$paged = ( get_query_var('page') ) ? get_query_var(' page' ) : 1;

		$q = array(
			'paged'          => $paged,
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => '-1',
			'meta_query'     => array(
				array(
					'key'     => '_wp_attachment_wp_user_avatar',
					'value'   => '',
					'compare' => '!='
				),
			),
		);

		$avatars_wp_query = new WP_Query( $q );

		$avatars = array();

		while ( $avatars_wp_query->have_posts() ) {
			$avatars_wp_query->the_post();

			$avatars[] = $post->ID;
		}

		wp_reset_query();

		// Include default avatar
		$avatars[] = $wpua_avatar_default;

		parent::__construct( array(
			'plural' => 'media',
			'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
		) );
	}

	/**
	 * Only users with edit_users capability can use this section
	 * @since 1.8
	 * @uses current_user_can()
	 */
	public function ajax_user_can() {
		return current_user_can( 'edit_users' );
	}

	/**
	 * Search form
	 * @since 1.8
	 * @param string $text
	 * @param int $input_id
	 * @uses _admin_search_query()
	 * @uses has_items()
	 * @uses submit_button()
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			printf( '<input type="hidden" name="orderby" value="%s" />', esc_attr( $_REQUEST['orderby'] ) );
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			 printf( '<input type="hidden" name="order" value="%s" />', esc_attr( $_REQUEST['order'] ) );
		}
		if ( ! empty( $_REQUEST['post_mime_type'] ) ) {
			printf( '<input type="hidden" name="post_mime_type" value="%s" />', esc_attr( $_REQUEST['post_mime_type'] ) );
		}
		if ( ! empty( $_REQUEST['detached'] ) ) {
			printf( '<input type="hidden" name="detached" value="%s" />', esc_attr( $_REQUEST['detached'] ) );
		}
		?>

		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>

			<input type="hidden" id="page" name="page" value="wp-user-avatar-library" />

			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />

			<?php submit_button( $text, 'button', false, false, array( 'id' => 'search-submit' ) ); ?>
		</p>

		<?php
	}

	/**
	 * Return only avatars and paginate results
	 * @since 1.8
	 * @uses array $avatars
	 * @uses wp_edit_attachments_query()
	 */
	public function prepare_items() {
		global $avail_post_mime_types, $avatars, $lost, $post, $post_mime_types, $wp_query, $wpdb;

		$q = $_REQUEST;

		if ( ! is_array( $q ) ) {
			$q = array();
		}

		$q['post__in'] = $avatars;

		list( $post_mime_types, $avail_post_mime_types ) = wp_edit_attachments_query( $q );

		$this->is_trash = isset( $_REQUEST['status'] ) && 'trash' == $_REQUEST['status'];

		$this->set_pagination_args( array(
			'total_items' => $wp_query->found_posts,
			'total_pages' => $wp_query->max_num_pages,
			'per_page'    => $wp_query->query_vars['posts_per_page'],
		) );
	}

	/**
	 * Links to available table views
	 * @since 1.8
	 * @uses array $avatars
	 * @uses add_query_arg()
	 * @uses number_format_i18n()
	 * @return array
	 */
	public function get_views() {
		global $avatars;

		$type_links   = array();
		$_total_posts = count( array_filter( $avatars ) );
		$class        = ( empty( $_GET['post_mime_type'] ) && ! isset( $_GET['status'] ) ) ? ' class="current"' : '';

		$type_links['all']  = sprintf(
			' <a href="%s">',
			esc_url( add_query_arg( array(
				'page' => 'wp-user-avatar-library',
			), 'admin.php') )
		);
		$type_links['all'] .= sprintf(
			/* translators: uploaded files */
			_x( 'All %s', 'uploaded files', 'one-user-avatar' ),
			sprintf( '<span class="count">(%s)</span>', number_format_i18n( $_total_posts ) )
		);
		$type_links['all'] .= '</a>';

		return $type_links;
	}

	/**
	 * Bulk action available with this table
	 * @since 1.8
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array();

		$actions['delete'] = esc_html__( 'Delete Permanently','one-user-avatar' );

		return $actions;
	}

	/**
	 * Current action from bulk actions list
	 * @since 1.8
	 * @uses current_action()
	 * @return string|bool
	 */
	public function current_action() {
		return parent::current_action();
	}

	/**
	 * Checks whether table has items
	 * @since 1.8
	 * @uses have_posts()
	 * @return bool
	 */
	public function has_items() {
		return have_posts();
	}

	/**
	 * Message displayed when no items
	 * @since 1.8
	 */
	public function no_items() {
		_e( 'No media attachments found.', 'one-user-avatar' );
	}

	/**
	 * Columns in this table
	 * @since 1.8
	 * @return array
	 */
	public function get_columns() {
		$columns = array();

		$columns['cb']     = '<input type="checkbox" />';
		$columns['icon']   = '';
		$columns['title']  = esc_html_x( 'File', 'column name', 'one-user-avatar' );
		$columns['author'] = esc_html__( 'Author','one-user-avatar', 'one-user-avatar' );
		$columns['parent'] = esc_html_x( 'Uploaded to', 'column name', 'one-user-avatar' );
		$columns['date']   = esc_html_x( 'Date', 'column name', 'one-user-avatar' );

		return $columns;
	}

	/**
	 * Sortable columns in this table
	 * @since 1.8
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'title'  => 'title',
			'author' => 'author',
			'date'   => array( 'date', true ),
		);
	}

	/**
	 * Display for rows in table
	 * @since 1.8
	 * @uses object $post
	 * @uses object $wpdb
	 * @uses object $wpua_functions
	 * @uses add_filter()
	 * @uses _draft_or_post_title()
	 * @uses _media_states()
	 * @uses current_user_can()
	 * @uses get_attached_file()
	 * @uses get_current_user_id()
	 * @uses get_edit_post_link()
	 * @uses get_edit_user_link()
	 * @uses get_post_mime_type()
	 * @uses get_the_author()
	 * @uses get_the_author_meta()
	 * @uses get_userdata()
	 * @uses have_posts()
	 * @uses the_post()
	 * @uses wpua_get_attachment_image()
	 */
	public function display_rows() {
		global $post, $wpdb, $wpua_functions;

		add_filter( 'the_title','esc_html' );

		$alt = '';

		while ( have_posts() ) :
			the_post();

			$user_can_edit = current_user_can( 'edit_post', $post->ID );

			if ( $this->is_trash && 'trash' != $post->post_status || ! $this->is_trash && 'trash' == $post->post_status ) {
				continue;
			}

			$alt        = ( 'alternate' == $alt ) ? '' : 'alternate';
			$post_owner = (get_current_user_id() == $post->post_author) ? 'self' : 'other';
			$tr_class   = trim( $alt . ' author-' . $post_owner . ' status-' . $post->post_status );
			$att_title  = _draft_or_post_title();
			?>

			<tr id="post-<?php echo esc_attr( $post->ID ); ?>" class="<?php echo esc_attr( $tr_class ); ?>" valign="top">
				<?php
				list( $columns, $hidden ) = $this->get_column_info();

				foreach ( $columns as $column_name => $column_display_name ) {
					$class = sprintf( '%1$s column-%1$s', esc_attr( $column_name ) );

					if ( in_array( $column_name, $hidden ) ) {
						$class .= ' hidden';
					}

					$class = sanitize_html_class( $class );

					switch ( $column_name ) {
						case 'cb':
							?>

							<th scope="row" class="check-column">
								<?php if ( $user_can_edit ) : ?>
									<label class="screen-reader-text" for="cb-select-<?php the_ID(); ?>">
										<?php
										/* translators: post title */
										echo esc_html( sprintf( __( 'Select %s','one-user-avatar' ), $att_title ) );
										?>
									</label>

									<input type="checkbox" name="media[]" id="cb-select-<?php the_ID(); ?>" value="<?php the_ID(); ?>" />
								<?php endif; ?>
							</th>

							<?php
							break;

						case 'icon':
							?>

							<td class="media-icon <?php echo esc_attr( $class ); ?>">
								<?php
								if ( $thumb = $wpua_functions->wpua_get_attachment_image( $post->ID, array( 80, 60 ), true ) ) {
									if ( $this->is_trash || ! $user_can_edit ) {
										echo wp_kses_post( $thumb );
									} else {
										?>
										<a href="<?php echo esc_url( get_edit_post_link( $post->ID, true ) ); ?>" title="<?php echo esc_attr( sprintf( __( 'Edit %s' ), sprintf( '&#8220;%s&#8221;', $att_title ) ) ); ?>">
											<?php echo wp_kses_post( $thumb ); ?>
										</a>
										<?php
									}
								}
								?>
							</td>

							<?php
							break;

						case 'title':
							?>

							<td class="<?php echo esc_attr( $class ); ?>">
								<strong>
									<?php
									if ( $this->is_trash || ! $user_can_edit ) {
										echo esc_html( $att_title );
									} else {
										?>
										<a
											href="<?php echo esc_url( get_edit_post_link( $post->ID, true ) ); ?>"
											title="<?php echo esc_attr( sprintf( __( 'Edit %s' ), sprintf( '&#8220;%s&#8221;', $att_title ) ) ); ?>"
										>
											<?php echo esc_html( $att_title ); ?>
										</a>
										<?php
									}

									_media_states( $post );
									?>
								</strong>

								<p>
									<?php
									if ( preg_match( '/^.*?\.(\w+)$/', get_attached_file( $post->ID ), $matches ) ) {
										echo esc_html( strtoupper( $matches[1] ) );
									} else {
										echo esc_html( strtoupper( str_replace('image/', '', get_post_mime_type() ) ) );
									}
									?>
								</p>

								<?php echo $this->row_actions( $this->_get_row_actions( $post, $att_title ) ); ?>
							</td>

							<?php
							break;

						case 'author':
							?>

							<td class="<?php echo esc_attr( $class ); ?>">
								<?php
								printf(
									'<a href="%s">%s</a>',
									esc_url( add_query_arg( array( 'author' => get_the_author_meta( 'ID' ) ), 'upload.php' ) ),
									get_the_author()
								);
								?>
							</td>

							<?php
							break;

						case 'date':
							if( '0000-00-00 00:00:00' == $post->post_date ) {
								$h_time = __( 'Unpublished','one-user-avatar' );
							} else {
								$m_time = $post->post_date;
								$time   = get_post_time( 'G', true, $post, false );
								if ( ( abs( $t_diff = time() - $time ) ) < DAY_IN_SECONDS ) {
									if ( 0 > $t_diff ) {
										$h_time = sprintf(
											/* translators: time from now */
											_x( '%s from now', 'time from now', 'one-user-avatar' ),
											human_time_diff( $time)
										);
									} else {
										$h_time = sprintf(
											/* translators: time ago */
											_x( '%s ago', 'time ago', 'one-user-avatar' ),
											human_time_diff( $time )
										);
									}
								} else {
									$h_time = mysql2date( __( 'Y/m/d', 'one-user-avatar' ), $m_time );
								}
							}
							?>

							<td class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $h_time); ?></td>

							<?php
							break;

						case 'parent':
							global $blog_id, $wpdb;

							// Find all users with this WPUA
							$wpua_metakey = $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar';
							$wpuas        = $wpdb->get_results( $wpdb->prepare(
								"SELECT wpum.user_id FROM $wpdb->usermeta AS wpum, $wpdb->users AS wpu WHERE wpum.meta_key = %s AND wpum.meta_value = %d AND wpum.user_id = wpu.ID ORDER BY wpu.user_login",
								$wpua_metakey,
								$post->ID
							) );

							// Find users without WPUA
							$nowpuas = $wpdb->get_results( $wpdb->prepare(
								"SELECT wpu.ID FROM $wpdb->users AS wpu, $wpdb->usermeta AS wpum WHERE wpum.meta_key = %s AND wpum.meta_value = %d AND wpum.user_id = wpu.ID ORDER BY wpu.user_login",
								$wpua_metakey,
								''
							) );

							$user_array = array();
							?>

							<td class="<?php echo esc_attr( $class ); ?>">
								<strong>
									<?php
									if ( ! empty( $wpuas ) ) {
										foreach ( $wpuas as $usermeta ) {
											$user         = get_userdata( $usermeta->user_id );
											$user_array[] = sprintf(
												'<a href="%s">%s</a>',
												esc_url( get_edit_user_link( $user->ID ) ),
												$user->user_login
											);
										}
									} else {
										foreach ( $nowpuas as $usermeta ) {
											$user         = get_userdata($usermeta->ID);
											$user_array[] = sprintf(
												'<a href="%s">%s</a>',
												esc_url( get_edit_user_link($user->ID) ),
												$user->user_login
											);
										}
									}

									echo wp_kses_post( implode( ', ', array_filter( $user_array ) ) );
									?>
								</strong>
							</td>

							<?php
							break;
					}
				}
				?>

			</tr>

			<?php
		endwhile;
	}

	/**
	 * Actions for rows in table
	 * @since 1.8
	 * @uses object $post
	 * @uses string $att_title
	 * @uses _draft_or_post_title()
	 * @uses current_user_can()
	 * @uses get_edit_post_link()
	 * @uses get_permalink()
	 * @uses wp_nonce_url()
	 * @return array
	 */
	public function _get_row_actions( $post, $att_title ) {
		$actions = array();

		if ( current_user_can( 'edit_post', $post->ID ) && ! $this->is_trash ) {
			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( get_edit_post_link( $post->ID, true ) ),
				__( 'Edit', 'one-user-avatar' )
			);
		}

		if ( current_user_can( 'delete_post', $post->ID ) ) {
			if ( $this->is_trash ) {
				$actions['untrash'] = sprintf(
					'<a class="submitdelete" href="%s">%s</a>',
					wp_nonce_url( sprintf( 'post.php?action=untrash&amp;post=%s', $post->ID ), 'untrash-post_' . $post->ID ),
					__( 'Restore', 'one-user-avatar' )
				);
			} elseif ( EMPTY_TRASH_DAYS && MEDIA_TRASH ) {
				$actions['trash'] = sprintf(
					'<a class="submitdelete" href="%s">%s</a>',
					wp_nonce_url( sprintf( 'post.php?action=trash&amp;post=%s', $post->ID ), 'trash-post_' . $post->ID),
					__( 'Trash', 'one-user-avatar' )
				);
			}

			if ( $this->is_trash || ! EMPTY_TRASH_DAYS || ! MEDIA_TRASH ) {
				$actions['delete'] = sprintf(
					'<a class="submitdelete" href="%s">%s</a>',
					wp_nonce_url( sprintf( 'post.php?action=delete&amp;post=%s', $post->ID ), 'delete-post_'.$post->ID ),
					__( 'Delete Permanently', 'one-user-avatar' )
				);
			}
		}

		if ( ! $this->is_trash ) {
			$title = _draft_or_post_title( $post->post_parent );

			$actions['view'] = sprintf(
				'<a href="%s" title="%s" rel="permalink">%s</a>',
				get_permalink( $post->ID ),
				esc_attr( sprintf( __( 'View %s' ), sprintf( '&#8220;%s&#8221;', $title ) ) ),
				__( 'View', 'one-user-avatar' )
			);
		}

		return $actions;
	}
}
