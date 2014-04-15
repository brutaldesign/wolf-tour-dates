<?php
/**
 * WolfTourDates Core Functions
 *
 * Functions available on both the front-end and admin.
 *
 * @author WpWolf
 * @package WolfTourDates/Functions
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Get template part (for templates like the show-loop).
 *
 * @param mixed $slug
 * @param string $name (default: '')
 */
function wolf_tour_dates_get_template_part( $slug, $name = '' ) {
	global $wolf_tour_dates;
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/wolf_tour_dates/slug-name.php
	if ( $name )
		$template = locate_template( array( "{$slug}-{$name}.php", "{$wolf_tour_dates->template_url}{$slug}-{$name}.php" ) );

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( $wolf_tour_dates->plugin_path() . "/templates/{$slug}-{$name}.php" ) )
		$template = $wolf_tour_dates->plugin_path() . "/templates/{$slug}-{$name}.php";

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/wolf_tour_dates/slug.php
	if ( ! $template )
		$template = locate_template( array( "{$slug}.php", "{$wolf_tour_dates->template_url}{$slug}.php" ) );

	if ( $template )
		load_template( $template, false );
}

/**
 * Get other templates (e.g. ticket attributes) passing attributes and including the file.
 *
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function wolf_tour_dates_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	global $wolf_tour_dates;

	if ( $args && is_array( $args ) )
		extract( $args );

	$located = wolf_tour_dates_locate_template( $template_name, $template_path, $default_path );

	do_action( 'wolf_tour_dates_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'wolf_tour_dates_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param mixed $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function wolf_tour_dates_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	global $wolf_tour_dates;

	if ( ! $template_path ) $template_path = $wolf_tour_dates->template_url;
	if ( ! $default_path ) $default_path   = $wolf_tour_dates->plugin_path() . '/templates/';

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters( 'wolf_tour_dates_locate_template', $template, $template_name, $template_path );
}