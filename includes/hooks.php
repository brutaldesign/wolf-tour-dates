<?php
/**
 * WolfTourDates Hooks
 *
 * Action/filter hooks used for WolfTourDates functions/templates
 *
 * @author WpWolf
 * @package WolfTourDates/Templates
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/** Template Hooks ********************************************************/

if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {

	/**
	 * Content Wrappers
	 *
	 * @see wolf_tour_dates_output_content_wrapper()
	 * @see wolf_tour_dates_output_content_wrapper_end()
	 */
	add_action( 'wolf_tour_dates_before_main_content', 'wolf_tour_dates_output_content_wrapper', 10 );
	add_action( 'wolf_tour_dates_after_main_content', 'wolf_tour_dates_output_content_wrapper_end', 10 );

}
