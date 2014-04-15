<?php
/**
 * Upcoming Shows Widget
 *
 * Displays upcoming shows widget
 *
 * @author WpWolf
 * @category Widgets
 * @package WolfTourDates/Widgets
 * @since 1.0.0
 * @extends WP_Widget
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WTD_Upcoming_Shows_Widget extends WP_Widget {

	/**
	 * constructor
	 *
	 */
	function WTD_Upcoming_Shows_Widget() {

		// Widget settings
		$ops = array( 'classname' => 'widget_upcoming_shows', 'description' => __( 'Display your upcoming shows', 'wolf' ) );

		// Create the widget
		$this->WP_Widget( 'widget_upcoming_shows', __( 'Upcoming Shows', 'wolf' ), $ops );
		
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		
		extract( $args );
		
		$title = apply_filters( 'widget_title', $instance['title'] );
		$desc  = '<p>' . wp_kses_post( $instance['desc'] ) . '</p>';
		$count = isset( $instance['count'] ) ? $instance['count'] : 10;
		$url   = isset( $instance['url'] ) ? esc_url( $instance['url'] ) : '';
		echo wp_kses_post( $before_widget );
		if ( ! empty( $title ) ) echo wp_kses_post( $before_title . $title . $after_title );
		if ( $instance['desc'] ) echo wp_kses_post( $desc );
		wolf_get_shows_widget( $count, $url );
		echo wp_kses_post( $after_widget );
	
	}

	/**
	 * update function.
	 *
	 * @see WP_Widget->update
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['desc']  = wp_kses_post( $new_instance['desc'] );
		$instance['count'] = absint( $new_instance['count'] );
		$instance['url']   = esc_url( $new_instance['url'] );

		return $instance;
	}

	/**
	 * form function.
	 *
	 * @see WP_Widget->form
	 * @param array $instance
	 */
	function form( $instance ) {

		// Set up some default widget settings
		$defaults = array(
			'title' => __( 'Upcoming Shows', 'wolf' ), 
			'count' => 10, 
			'desc' => '',
			'url' => '',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'wolf' ); ?>:</label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo sanitize_text_field( $this->get_field_name( 'title' ) ); ?>" value="<?php echo sanitize_text_field( $instance['title'] ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'desc' ) ); ?>"><?php _e( 'Optional Text', 'wolf' ); ?>:</label>
			<textarea class="widefat"  id="<?php echo esc_attr( $this->get_field_id( 'desc' ) ); ?>" name="<?php echo sanitize_text_field( $this->get_field_name( 'desc' ) ); ?>" ><?php echo wp_kses_post( $instance['desc'] ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) );  ?>"><?php _e( 'Count', 'wolf' ); ?>:</label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) );  ?>" name="<?php echo sanitize_text_field( $this->get_field_name( 'count' ) ); ?>" value="<?php echo absint( $instance['count'] ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>"><?php _e( 'Shows page URL', 'wolf' ); ?>:</label>
			<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'url' ) ); ?>" name="<?php echo sanitize_text_field( $this->get_field_name( 'url' ) ); ?>" value="<?php echo esc_url( $instance['url'] ); ?>">
		</p>
		<?php
	}

}