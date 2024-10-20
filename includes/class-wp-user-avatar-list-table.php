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
 * @version    2.5.0
 */

class WP_User_Avatar_List_Table extends WP_List_Table {

	/**
	 * Holds the total number of avatars.
	 *
	 * @since 2.5.0
	 * @var int
	 */
	private $total_count;

	/**
	 * Holds the number of trashed avatars.
	 *
	 * @since 2.5.0
	 * @var int
	 */
	private $trash_count;

	/**
	 * Whether the current view is the trash.
	 *
	 * @since 2.5.0
	 * @var bool
	 */
	private $is_trash;

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
		global $avatars, $post, $wpdb, $wpua_avatar_default;

		$post_type = get_post_type_object( 'attachment' );
		$states    = 'inherit';

		if ( current_user_can( $post_type->cap->read_private_posts ) ) {
			$states .= ',private';
		}

		$status = ! empty( $_GET['status'] ) ? $_GET['status'] : $states;
		$paged  = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;

		$query = array(
			'paged'          => $paged,
			'post_type'      => 'attachment',
			'post_status'    => $status,
			'posts_per_page' => '-1',
			'meta_query'     => array(
				array(
					'key'     => '_wp_attachment_wp_user_avatar',
					'value'   => '',
					'compare' => '!='
				),
			),
		);

		$avatars_wp_query = new WP_Query( $query );

		$avatars = array();

		while ( $avatars_wp_query->have_posts() ) {
			$avatars_wp_query->the_post();

			$avatars[] = $post->ID;
		}

		wp_reset_query();

		if ( 'trash' != $status ) {
			// Include default avatar
			$avatars[] = $wpua_avatar_default;
		}

		$post_status = array( "'inherit'" );

		if ( current_user_can( $post_type->cap->read_private_posts ) ) {
			$post_status[] = "'private'";
		}

		$this->total_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT( 1 )
				FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta
				ON $wpdb->posts.id = $wpdb->postmeta.post_id
				WHERE $wpdb->posts.post_type = '%s'
				AND $wpdb->posts.post_status IN ( " . join( ', ', $post_status ) . " )
				AND $wpdb->postmeta.meta_key = '%s'
				AND $wpdb->postmeta.meta_value != ''",
				'attachment',
				'_wp_attachment_wp_user_avatar'
			)
		);

		if ( ! empty( $wpua_avatar_default ) ) {
			// Include default avatar
			++$this->total_count;
		}

		$this->trash_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT( 1 )
				FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta
				ON $wpdb->posts.id = $wpdb->postmeta.post_id
				WHERE $wpdb->posts.post_type = '%s'
				AND $wpdb->posts.post_status = '%s'
				AND $wpdb->postmeta.meta_key = '%s'
				AND $wpdb->postmeta.meta_value != ''",
				'attachment',
				'trash',
				'_wp_attachment_wp_user_avatar'
			)
		);

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

		if ( empty( $avatars ) ) {
			return;
		}

		$post_type = get_post_type_object( 'attachment' );
		$states    = 'inherit';

		if ( current_user_can( $post_type->cap->read_private_posts ) ) {
			$states .= ',private';
		}

		$status = ! empty( $_GET['status'] ) ? $_GET['status'] : $states;
		$paged  = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;

		$query = array(
			'page'              => $paged,
			'post__in'          => $avatars,
			'attachment-filter' => $status,
		);

		list( $post_mime_types, $avail_post_mime_types ) = wp_edit_attachments_query( $query );

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
	 * @uses add_query_arg()
	 * @uses number_format_i18n()
	 * @return array
	 */
	public function get_views() {
		$links = array();

		$links['all']  = array(
			'url'     => esc_url( add_query_arg( array(
					'page' => 'wp-user-avatar-library',
				), 'admin.php') ),
			'label'   => sprintf(
					/* translators: uploaded files */
					_x( 'All %s', 'uploaded files', 'one-user-avatar' ),
					sprintf( '<span class="count">(%s)</span>', number_format_i18n( $this->total_count ) )
				),
			'current' => empty( $_GET['post_mime_type'] ) && ! isset( $_GET['status'] ),
		);

		if ( $this->trash_count && ( $this->is_trash || ( defined( 'MEDIA_TRASH' ) && MEDIA_TRASH ) ) ) {
			$links['trash']  = array(
				'url'     => esc_url( add_query_arg( array(
						'page'   => 'wp-user-avatar-library',
						'status' => 'trash',
					), 'admin.php') ),
				'label'   => sprintf(
						/* translators: uploaded files */
						_x( 'Trash %s', 'trashed files', 'one-user-avatar' ),
						sprintf( '<span class="count">(%s)</span>', number_format_i18n( $this->trash_count ) )
					),
				'current' => $this->is_trash,
			);
		}

		return $this->get_views_links( $links );
	}

	/**
	 * Bulk action available with this table
	 * @since 1.8
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array();

		if ( $this->is_trash ) {
			$actions['untrash'] = esc_html__( 'Restore', 'one-user-avatar' );
			$actions['delete'] = esc_html__( 'Delete Permanently', 'one-user-avatar' );
		} elseif ( ! EMPTY_TRASH_DAYS || ! MEDIA_TRASH ) {
			$actions['delete'] = esc_html__( 'Delete Permanently', 'one-user-avatar' );
		} else {
			$actions['trash'] = esc_html__( 'Move to Trash', 'one-user-avatar' );
		}

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
		_e( 'No avatars found.', 'one-user-avatar' );
	}

	/**
	 * Columns in this table
	 * @since 1.8
	 * @return array
	 */
	public function get_columns() {
		$columns = array();

		$columns['cb']     = '<input type="checkbox" />';
		$columns['title']  = esc_html_x( 'File', 'column name', 'one-user-avatar' );
		$columns['author'] = esc_html__( 'Author','one-user-avatar', 'one-user-avatar' );
		$columns['parent'] = esc_html_x( 'Attached to', 'column name', 'one-user-avatar' );
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

		while ( have_posts() ) :
			the_post();

			$user_can_edit = current_user_can( 'edit_post', $post->ID );
			$post_owner    = (get_current_user_id() == $post->post_author) ? 'self' : 'other';
			$tr_class      = trim( ' author-' . $post_owner . ' status-' . $post->post_status );
			$att_title     = _draft_or_post_title();
			?>

			<tr id="post-<?php echo esc_attr( $post->ID ); ?>" class="<?php echo esc_attr( $tr_class ); ?>">
				<?php
				list( $columns, $hidden ) = $this->get_column_info();

				foreach ( $columns as $column_name => $column_display_name ) {
					$class = sprintf( '%1$s column-%1$s', esc_attr( $column_name ) );

					if ( in_array( $column_name, $hidden ) ) {
						$class .= ' hidden';
					}

					$class = join( ' ', array_map( 'sanitize_html_class', explode( ' ', $class ) ) );

					switch ( $column_name ) {
						case 'cb':
							?>

							<th scope="row" class="check-column">
								<?php if ( $user_can_edit ) : ?>
									<label class="label-covers-full-cell" for="cb-select-<?php the_ID(); ?>">
										<span class="screen-reader-text">
											<?php
											/* translators: post title */
											echo esc_html( sprintf( __( 'Select %s','one-user-avatar' ), $att_title ) );
											?>
										</span>
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
								if ( $thumb = $wpua_functions->wpua_get_attachment_image( $post->ID, array( 60, 60 ), true ) ) {
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
							$thumb = $wpua_functions->wpua_get_attachment_image( $post->ID, array( 60, 60 ), true );
							?>

							<td class="<?php echo esc_attr( $class ); ?> has-row-actions column-primary">
								<strong<?php if ( $thumb ) : ?> class="has-media-icon"<?php endif; ?>>
									<?php
									if ( $this->is_trash || ! $user_can_edit ) {
										if ( $thumb ) :
											?>
											<span class="media-icon image-icon">
												<?php echo wp_kses_post( $thumb ); ?>
											</span>
											<?php
										endif;

										echo esc_html( $att_title );
									} else {
										?>
										<a
											href="<?php echo esc_url( get_edit_post_link( $post->ID, true ) ); ?>"
											aria-label="<?php echo esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $att_title ) ); ?>"
										>
											<?php if ( $thumb ) : ?>
												<span class="media-icon image-icon">
													<?php echo wp_kses_post( $thumb ); ?>
												</span>
											<?php endif; ?>

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

							$user_array = array();

							if ( ! empty( $wpuas ) ) {
								foreach ( array_slice( $wpuas, 0, 3 ) as $usermeta ) {
									$user         = get_userdata( $usermeta->user_id );
									$user_array[] = sprintf(
										'<strong><a href="%s">%s</a></strong>',
										esc_url( get_edit_user_link( $user->ID ) ),
										$user->user_login
									);
								}

								$count = count( $wpuas );

								if ( 3 < $count ) {
									$user_array[] = sprintf(
										_x(
											/* translators: %d: number of users. */
											'+%d more',
											'avatar user count',
											'one-user-avatar'
										),
										number_format_i18n( $count - 3 )
									);
								}
							}
							?>

							<td class="<?php echo esc_attr( $class ); ?>">
								<?php echo wp_kses_post(
									! empty( $user_array ) ? join( ', ', $user_array ) : __( '(Unattached)', 'one-user-avatar' )
								); ?>
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
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( get_edit_post_link( $post->ID, true ) ),
				/* translators: %s: Avatar title. */
				esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'one-user-avatar' ), $att_title ) ),
				__( 'Edit', 'one-user-avatar' )
			);
		}

		if ( current_user_can( 'delete_post', $post->ID ) ) {
			if ( $this->is_trash ) {
				$actions['untrash'] = sprintf(
					'<a href="%s" class="submitdelete aria-button-if-js" aria-label="%s">%s</a>',
					wp_nonce_url( sprintf( 'post.php?action=untrash&amp;post=%s', $post->ID ), 'untrash-post_' . $post->ID ),
					/* translators: %s: Avatar title. */
					esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash', 'one-user-avatar' ), $att_title ) ),
					__( 'Restore', 'one-user-avatar' )
				);
			} elseif ( EMPTY_TRASH_DAYS && MEDIA_TRASH ) {
				$actions['trash'] = sprintf(
					'<a href="%s" class="submitdelete aria-button-if-js" aria-label="%s">%s</a>',
					wp_nonce_url( sprintf( 'post.php?action=trash&amp;post=%s', $post->ID ), 'trash-post_' . $post->ID),
					/* translators: %s: Avatar title. */
					esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash', 'one-user-avatar' ), $att_title ) ),
					__( 'Trash', 'one-user-avatar' )
				);
			}

			if ( $this->is_trash || ! EMPTY_TRASH_DAYS || ! MEDIA_TRASH ) {
				$show_confirmation = ( ! $this->is_trash && ! MEDIA_TRASH ) ? ' show-confirmation' : '';

				$actions['delete'] = sprintf(
					'<a href="%s" class="submitdelete aria-button-if-js%s" aria-label="%s">%s</a>',
					wp_nonce_url( sprintf( 'post.php?action=delete&amp;post=%s', $post->ID ), 'delete-post_'.$post->ID ),
					$show_confirmation,
					/* translators: %s: Avatar title. */
					esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently', 'one-user-avatar' ), $att_title ) ),
					__( 'Delete Permanently', 'one-user-avatar' )
				);
			}
		}

		if ( ! $this->is_trash ) {
			$permalink = get_permalink( $post->ID );

			if ( $permalink ) {
				$actions['view'] = sprintf(
					'<a href="%s" aria-label="%s" rel="bookmark">%s</a>',
					esc_url( $permalink ),
					/* translators: %s: Avatar title. */
					esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'one-user-avatar' ), $att_title ) ),
					__( 'View', 'one-user-avatar' )
				);
			}
		}

		return $actions;
	}

}
