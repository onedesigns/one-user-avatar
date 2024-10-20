<?php
/**
 * Defines widgets.
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

class WP_User_Avatar_Profile_Widget extends WP_Widget {
	/**
	 * Constructor
	 * @since 1.9.4
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'widget_wp_user_avatar',
			'description' => sprintf(
				/* translators: [avatar_upload] shortcode */
				__( 'Insert %s', 'one-user-avatar' ),
				'[avatar_upload]'
			),
		);

		parent::__construct( 'wp_user_avatar_profile', __( 'One User Avatar', 'one-user-avatar' ), $widget_ops );
	}

	/**
	 * Add [avatar_upload] to widget
	 * @since 1.9.4
	 * @param array $args
	 * @param array $instance
	 * @uses object $wp_user_avatar
	 * @uses bool $wpua_allow_upload
	 * @uses object $wpua_shortcode
	 * @uses add_filter()
	 * @uses apply_filters()
	 * @uses is_user_logged_in()
	 * @uses remove_filter()
	 * @uses wpua_edit_shortcode()
	 * @uses wpua_is_author_or_above()
	 */
	public function widget($args, $instance) {
		global $wp_user_avatar, $wpua_allow_upload, $wpua_shortcode;

		extract( $args );

		$instance = apply_filters( 'wpua_widget_instance', $instance );
		$title    = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$text     = apply_filters( 'widget_text',  empty( $instance['text'] )  ? '' : $instance['text'],  $instance );

		// Show widget only for users with permission
		if ( $wp_user_avatar->wpua_is_author_or_above() || ( 1 == (bool) $wpua_allow_upload && is_user_logged_in() ) ) {
			echo $before_widget;

			if ( ! empty( $title ) ) {
				echo $before_title . esc_html( $title ) . $after_title;
			}

			if ( ! empty( $text ) ) {
				echo '<div class="textwidget">';
				echo wp_kses_post( ! empty( $instance['filter'] ) ? wpautop( $text ) : $text );
				echo '</div>';
			}

			// Remove profile title
			add_filter( 'wpua_profile_title', '__return_null' );

			// Get [avatar_upload] shortcode
			echo $wpua_shortcode->wpua_edit_shortcode( '' );

			// Add back profile title
			remove_filter('wpua_profile_title', '__return_null');
		}
	}

	/**
	 * Set title
	 * @since 1.9.4
	 * @param array $instance
	 * @uses wp_parse_args()
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'text'  => '',
		) );
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'one-user-avatar' ); ?>
			</label>

			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( wp_kses( $instance['title'], 'data' ) ); ?>" />
		</p>

		<label for="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>"><?php esc_html_e( 'Description:', 'one-user-avatar' ); ?></label>

		<textarea class="widefat" rows="3" cols="20" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>"><?php echo esc_textarea( $instance['text'] ); ?></textarea>

		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter' ) ); ?>" type="checkbox" <?php checked( isset( $instance['filter'] ) ? $instance['filter'] : 0 ); ?> />

			<label for="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>">
				<?php esc_html_e( 'Automatically add paragraphs', 'one-user-avatar' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Update widget
	 * @since 1.9.4
	 * @param array $new_instance
	 * @param array $old_instance
	 * @uses current_user_can()
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = wp_kses( $new_instance['title'], 'data' );

		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['text'] =	$new_instance['text'];
		} else {
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes( $new_instance['text'] ) ) );
		}

		$instance['filter'] = isset( $new_instance['filter'] );

		return $instance;
	}
}
