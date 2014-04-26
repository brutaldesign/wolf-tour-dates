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

if ( ! function_exists( 'wolf_tour_dates_google_map_meta_hook' ) ) {
	/**
	 * Output google map
	 *
	 * Check the old URL and output an iframe or output the new iframe code directly
	 *
	 * @param string|array $metadata - Always null for post metadata.
	 * @param int $object_id - Post ID for post metadata
	 * @param string $meta_key - metadata key.
	 * @param bool $single - Indicates if processing only a single $metadata value or array of values.
	 * @return Original or Modified $metadata.
	 */
	function wolf_tour_dates_google_map_meta_hook( $metadata, $object_id, $meta_key, $single ) {
		
		if ( '_wolf_show_map' == $meta_key ) {
			
			$cache = wp_cache_get( $object_id, 'post_meta'); // get meta values

			if ( $cache && isset( $cache[ $meta_key ] ) && isset( $cache[ $meta_key ][0] ) ) {
				
				$metadata = $cache[ $meta_key ][0];

				// get the src value if value is an embed code
				if ( preg_match( '/src="([^"]*)"/i', $metadata, $match ) ) {
					if ( $match && isset( $match[1] ) ) {
						$metadata = str_replace( '&amp;output=embed', '', $match[1] );
					}
				}
			}
		}
		
		return $metadata;
	}
	add_filter( 'get_post_metadata', 'wolf_tour_dates_google_map_meta_hook', true, 4 );
}