<?php
/**
 * WolfTourDates Template Functions
 *
 * Functions used in the template files to output content - in most cases hooked in via the template actions. All functions are pluggable.
 *
 * @author WpWolf
 * @package WolfTourDates/Templates
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/** Global ****************************************************************/

if ( ! function_exists( 'wolf_tour_dates_output_content_wrapper' ) ) {

	/**
	 * Output the start of the page wrapper.
	 *
	 * @return string
	 */
	function wolf_tour_dates_output_content_wrapper() {
		wolf_tour_dates_get_template( 'global/wrapper-start.php' );
	}
}

if ( ! function_exists( 'wolf_tour_dates_output_content_wrapper_end' ) ) {

	/**
	 * Output the end of the page wrapper.
	 *
	 * @return string
	 */
	function wolf_tour_dates_output_content_wrapper_end() {
		wolf_tour_dates_get_template( 'global/wrapper-end.php' );
	}
}