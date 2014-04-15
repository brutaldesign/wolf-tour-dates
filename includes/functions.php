<?php
/**
 * WolfTourDates Functions
 *
 * Hooked-in functions for WolfTourDates related events on the front-end.
 *
 * @author WpWolf
 * @package WolfTourDates/Functions
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'wolf_get_show_date' ) ) {
	/**
	 * Returns show date
	 *
	 * @param string $date, bool $custom
	 * @return string
	 */
	function wolf_get_show_date( $date, $custom = true ) {
		global $wolf_tour_dates;
		return $wolf_tour_dates->get_show_date( $date, $custom );
	}
}

if ( ! function_exists( 'wolf_is_past_show' ) ) {
	/**
	 * Check if a show date is past
	 *
	 * @param string $date
	 * @return bool
	 */
	function wolf_is_past_show( $date ) {
		global $wolf_tour_dates;
		return $wolf_tour_dates->is_past_show( $date );
	}
}

if ( ! function_exists( 'wolf_get_shows_widget' ) ) {
	/**
	 * Widget function
	 *
	 * Displays the show list in the widget
	 *
	 * @param int $count, string $url, bool $link
	 * @return string
	 */
	function wolf_get_shows_widget( $count = 10, $url = null, $link = null ) {
		global $wolf_tour_dates;
		return $wolf_tour_dates->widget( $count, $url, $link );
	}
}

if ( ! function_exists( 'wolf_get_shows_thumbnail_url' ) ) {
	/**
	 * Returns post thumbnail URL
	 *
	 * @param string $format, int $post_id
	 * @return string
	 */
	function wolf_get_show_thumbnail_url( $format = 'medium', $post_id = null ) {
		global $wolf_tour_dates;
		return $wolf_tour_dates->get_post_thumbnail_url( $format, $post_id );
	}
}

if ( ! function_exists( 'wolf_tour_dates_output_upcoming_shows_title' ) ) {
	/**
	 * Output "Upcoming Shows" before the tour date list
	 *
	 * It can be overwritten in a theme
	 *
	 * @param string $before
	 * @param string $after
	 * @return string
	 */
	function wolf_tour_dates_output_upcoming_shows_title( $before = '', $after = '' ) {

		echo wp_kses_post( $before );
		_e( 'Upcoming Shows', 'wolf' );
		echo wp_kses_post( $after );

	}
}

if ( ! function_exists( 'wolf_tour_dates_output_past_shows_title' ) ) {
	/**
	 * Output "Past Shows" before the tour date list
	 *
	 * It can be overwritten in a theme
	 *
	 * @param string $before
	 * @param string $after
	 * @return string
	 */
	function wolf_tour_dates_output_past_shows_title( $before = '', $after = '' ) {

		echo wp_kses_post( $before );
		_e( 'Past Shows', 'wolf' );
		echo wp_kses_post( $after );

	}
}