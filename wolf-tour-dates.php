<?php
/**
 * Plugin Name: Wolf Tour Dates
 * Plugin URI: http://wpwolf.com/plugin/wolf-tour-dates
 * Description: A plugin to manage your tour dates
 * Version: 1.0.9
 * Author: WpWolf
 * Author URI: http://wpwolf.com
 * Requires at least: 3.5
 * Tested up to: 3.8.3
 *
 * Text Domain: wolf
 * Domain Path: /lang/
 *
 * @package WolfTourDates
 * @author WpWolf
 *
 * Being a free product, this plugin is distributed as-is without official support. 
 * Verified customers however, who have purchased a premium theme
 * at http://themeforest.net/user/BrutalDesign/portfolio?ref=BrutalDesign
 * will have access to support for this plugin in the forums
 * http://help.wpwolf.com/
 *
 * Copyright (C) 2014 Constantin Saguin
 * This WordPress Plugin is a free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * It is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * See http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Wolf_Tour_Dates' ) ) {
	/**
	 * Main Wolf_Tour_Dates Class
	 *
	 * Contains the main functions for Wolf_Tour_Dates
	 *
	 * @version 1.0.9
	 * @since 1.0.0
	 * @package WolfTourDates
	 * @author WpWolf
	 */
	class Wolf_Tour_Dates {

		/**
		 * @var string
		 */
		private $version = '1.0.9';

		/**
		 * @var string
		 */
		private $update_url = 'http://plugins.wpwolf.com/update';

		/**
		 * @var object
		 */
		private $wpdb;

		/**
		 * @var string
		 */
		public $plugin_url;

		/**
		 * @var string
		 */
		public $plugin_path;

		/**
		 * @var string
		 */
		public $template_url;

		/**
		 * Wolf_Tour_Dates Constructor.
		 */
		public function __construct() {

			global $wpdb;
			$this->wpdb = &$wpdb;

			define( 'WOLF_TOUR_DATES_VERSION', $this->plugin_url );
			define( 'WOLF_TOUR_DATES_URL', $this->plugin_url );
			
			// Flush rewrite rules on activation
			register_activation_hook( __FILE__, array( $this, 'activate' ) );

			// plugin update notification
			add_action( 'admin_init', array( $this, 'update' ), 5 );

			// register settings
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			// add option sub-menu
			add_action( 'admin_menu', array( $this, 'add_options_menu' ) );

			// Include required files
			$this->includes();

			add_action( 'init', array( $this, 'init' ), 0 );
			add_action( 'init', array( $this, 'include_template_functions' ), 25 );

			// set default options
			add_action( 'after_setup_theme', array( $this, 'default_options' ) );
			
			// shortcode
			add_shortcode( 'wolf_tour_dates', array( $this, 'shortcode' ) );

			// Widget
			add_action( 'widgets_init', array( $this, 'register_widgets' ) );

			// styles
			add_action( 'wp_print_styles', array( $this, 'print_styles' ) );
		
		}

		/**
		 * Activation function
		 */
		public function activate( $network_wide ) {

			// not used yet

		}

		/**
		 * plugin update notification.
		 */
		public function update() {
			
			$plugin_data     = get_plugin_data( __FILE__ );
			$current_version = $plugin_data['Version'];
			$plugin_slug     = plugin_basename( dirname( __FILE__ ) );
			$plugin_path     = plugin_basename( __FILE__ );
			$remote_path     = $this->update_url . '/' . $plugin_slug;
			
			if ( ! class_exists( 'Wolf_WP_Update' ) )
				include_once( 'classes/class-wp-update.php' );
			
			$wolf_plugin_update = new Wolf_WP_Update( $current_version, $remote_path, $plugin_path );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		public function includes() {

			if ( ! is_admin() || defined( 'DOING_AJAX' ) )
				$this->frontend_includes();

			// Metabox class
			include_once( 'classes/class-metabox.php' );

			// Core functions
			include_once( 'includes/core-functions.php' );

		}

		/**
		 * Include required frontend files.
		 */
		public function frontend_includes() {
			
			// Functions
			include_once( 'includes/hooks.php' ); // Template hooks used on the front-end
			include_once( 'includes/functions.php' ); // Contains functions for various front-end events
			
		}

		/**
		 * Function used to Init WolfDiscography Template Functions - This makes them pluggable by plugins and themes.
		 */
		public function include_template_functions() {
			
			include_once( 'includes/template.php' );

		}

		/**
		 * register_widgets function.
		 */
		public function register_widgets() {
			
			// Include
			include_once( 'classes/class-widget-upcoming-shows.php' );

			// Register widgets
			register_widget( 'WTD_Upcoming_Shows_Widget' );
			
		}

		/**
		 * Init WolfTourDates when WordPress Initialises.
		 */
		public function init() {

			// Set up localisation
			$this->load_plugin_textdomain();

			// Variables
			$this->template_url = apply_filters( 'wolf_tour_dates_template_url', 'wolf-tour-dates/' );

			// Classes/actions loaded for the frontend and for ajax requests
			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {

				// Hooks
				add_filter( 'template_include', array( $this, 'template_loader' ) );

			}

			add_filter( 'manage_show_posts_columns', array( $this, 'admin_columns_head_shows' ), 10 );  
			add_action( 'manage_show_posts_custom_column', array( $this, 'admin_columns_content_shows' ), 10, 2 );  

			// register post type
			$this->register_post_type();

			// add metaboxes
			$this->metaboxes();

		}

		/**
		 * Load Localisation files.
		 */
		public function load_plugin_textdomain() {

			$domain = 'wolf';
			$locale = apply_filters( 'wolf', get_locale(), $domain );
			load_textdomain( $domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo' );
			load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		}

		/**
		 * Load a template.
		 *
		 * Handles template usage so that we can use our own templates instead of the themes.
		 *
		 * Templates are in the 'templates' folder. Wolf Tour Dates looks for theme
		 * overrides in /theme/wolf-tour-dates/ by default
		 *
		 *
		 * @param mixed $template
		 * @return string
		 */
		public function template_loader( $template ) {

			$find = array();
			$file = '';

			if ( is_single() && get_post_type() == 'show' ) {

				$file   = 'single-show.php';
				$find[] = $file;
				$find[] = $this->template_url . $file;

			}

			if ( $file ) {
				$template = locate_template( $find );
				if ( ! $template ) $template = $this->plugin_path() . '/templates/' . $file;
			}

			return $template;
		}

		/**
		 * Print CSS styles
		 */
		public function print_styles() { 
			wp_register_style( 'wolf-tour-dates', $this->plugin_url() . '/assets/css/tour-dates.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'wolf-tour-dates' );
		}

		/**
		 * Register post type
		 */
		public function register_post_type() {

			$labels = array( 
				'name' => __( 'Shows', 'wolf' ),
				'singular_name' => __( 'Show', 'wolf' ),
				'add_new' => __( 'Add New', 'wolf' ),
				'add_new_item' => __( 'Add New Show', 'wolf' ),
				'all_items'  => __( 'All Shows', 'wolf' ),
				'edit_item' => __( 'Edit Show', 'wolf' ),
				'new_item' => __( 'New Show', 'wolf' ),
				'view_item' => __( 'View Show', 'wolf' ),
				'search_items' => __( 'Search Shows', 'wolf' ),
				'not_found' => __( 'No shows found', 'wolf' ),
				'not_found_in_trash' => __( 'No shows found in Trash', 'wolf' ),
				'parent_item_colon' => '',
				'menu_name' => __( 'Tour Dates', 'wolf' ),
			);

			$args = array( 

				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => false,
				'rewrite' => array( 'slug' => 'show' ),
				'capability_type' => 'post',
				'has_archive' => false,
				'hierarchical' => false,
				'menu_position' => 5,
				'taxonomies' => array(),
				'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'comments' ),
				'exclude_from_search' => false,
				'menu_icon' => 'dashicons-calendar',
			);

			register_post_type( 'show', $args );

		}

		/**
		 * Add options menu
		 */
		public function add_options_menu() {

			$post_type_name = 'show';
			add_submenu_page( 'edit.php?post_type='.$post_type_name, __( 'Options', 'wolf' ), __( 'Options', 'wolf' ), 'edit_plugins', 'wolf-tour-dates-options', array( $this, 'options_form' ) );
		}

		/**
		 * Add metaboxes
		 */
		public function metaboxes() {
			$metabox = array(
				'Show Details' => array(

					'title' => __( 'Show Details', 'wolf' ),
					'page' => array( 'show' ),
					'metafields' => array(

						array(
							'label'	=> __( 'Artist (leave this field empty if it\'s always the same artist', 'wolf' ),
							'id'	=> '_wolf_show_artist',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Date', 'wolf' ),
							'id'	=> '_wolf_show_date',
							'type'	=> 'datepicker',
						),

						array(
							'label'	=> __( 'City', 'wolf' ),
							'id'	=> '_wolf_show_city',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Time', 'wolf' ),
							'id'	=> '_wolf_show_time',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Country', 'wolf' ),
							'id'	=> '_wolf_show_country',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Country - short ( e.g : GER for Germany )', 'wolf' ),
							'id'	=> '_wolf_show_country_short',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'State', 'wolf' ),
							'id'	=> '_wolf_show_state',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Venue', 'wolf' ),
							'id'	=> '_wolf_show_venue',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Address', 'wolf' ),
							'id'	=> '_wolf_show_address',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Zip', 'wolf' ),
							'id'	=> '_wolf_show_zip',
							'type'	=> 'text',
						),


						array(
							'label'	=> __( 'Phone', 'wolf' ),
							'id'	=> '_wolf_show_phone',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Contact Email', 'wolf' ),
							'id'	=> '_wolf_show_email',
							'type'	=> 'text',
						),
						array(
							'label'	=> __( 'Contact Website', 'wolf' ),
							'id'	=> '_wolf_show_website',
							'type'	=> 'url',
						),

						array(
							'label'	=> __( 'Google map', 'wolf' ),
							'desc'   => sprintf( __( '<a href="%s" target="_blank">Where to find it?</a>', 'wolf' ), 'http://media.wpwolf.com/screenshots/googlemap-src.jpg' ),
							'id'	=> '_wolf_show_map',
							'type'	=> 'url',
						),

						array(
							'label'	=> __( 'Facebook event page', 'wolf' ),
							'id'	=> '_wolf_show_fb',
							'type'	=> 'url',
						),

						array(
							'label'	=> __( 'Buy Ticket link', 'wolf' ),
							'id'	=> '_wolf_show_ticket',
							'desc'   => 'http://www.example.com',
							'type'	=> 'url',
						),

						array(
							'label'	=> __( 'Price (e.g : $15)', 'wolf' ),
							'id'	=> '_wolf_show_price',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Free', 'wolf' ),
							'id'	=> '_wolf_show_free',
							'type'	=> 'checkbox',
						),

						array(
							'label'	=> __( 'Sold Out', 'wolf' ),
							'id'	=> '_wolf_show_soldout',
							'type'	=> 'checkbox',
						),

						array(
							'label'	=> __( 'Cancelled', 'wolf' ),
							'id'	=> '_wolf_show_cancel',
							'type'	=> 'checkbox',
						),

					)
				),
			);

			if ( class_exists( 'Wolf_Tour_Dates_Metabox' ) ) {
				$wolf_do_tour_dates_metabox = new Wolf_Tour_Dates_Metabox( $metabox );
			}
		}

		/**
		 * Add show column head in admin posts list
		 *
		 * @param array $columns
		 * @return array $columns
		 */  
		public function admin_columns_head_shows( $columns ) {  

			$columns['wtd_show_date']   = __( 'Show Date', 'wolf' );
			$columns['wtd_show_place']  = __( 'Place', 'wolf' );
			$columns['wtd_show_venue']  = __( 'Venue', 'wolf' );
			$columns['wtd_show_status'] = __( 'Status', 'wolf' );
			return $columns;  
		}  

		/**
		 * Add show column in admin posts list
		 *
		 * @param string $column_name
		 * @param int $post_id
		 */ 
		public function admin_columns_content_shows( $column_name, $post_id ) {  
			
			$date      = get_post_meta( $post_id, '_wolf_show_date', true );
			$cancelled = get_post_meta( $post_id, '_wolf_show_cancel', true );
			$soldout   = get_post_meta( $post_id, '_wolf_show_soldout', true );
			$status    = __( 'upcoming', 'wolf' );

			if ( $this->is_past_show( $date ) ) {
				
				$status = __( 'past', 'wolf' );

			} elseif ( $cancelled ) {

				$status = __( 'cancelled', 'wolf' );

			} elseif ( $soldout ) {

				$status = __( 'sold out', 'wolf' );

			}

			$city    = get_post_meta( $post_id, '_wolf_show_city', true );
			$country = get_post_meta( $post_id, '_wolf_show_country_short', true );
			$state   = get_post_meta( $post_id, '_wolf_show_state', true );
			$venue   = get_post_meta( $post_id, '_wolf_show_venue', true );
			$place   = $city;

			if ( $country && ! $state ) {
				$place = $city . ', ' . $country;
			}

			if ( ! $country && $state ) {
				$place = $city . ', ' . $state;
			}

			if ( $country && $state ) {
				$place = $city . ', ' . $state . ' (' . $country . ')';
			}

			if ( $column_name == 'wtd_show_date' ) {
				
				if ( $date ) echo wp_kses_post( $this->get_show_date( $date, false ) );

			}

			if ( $column_name == 'wtd_show_place' ) {
				
				if ( $place ) echo sanitize_text_field( $place );

			} 

			if ( $column_name == 'wtd_show_venue' ) {
				
				if ( $venue ) echo sanitize_text_field( $venue );

			}

			if ( $column_name == 'wtd_show_status' ) {
				
				if ( $status ) echo sanitize_text_field( $status );

			} 
		} 

		/**
		 * Set default options
		 */
		public function default_options() {
			global $options;

			if ( false === get_option( 'wolf_tour_dates_settings' )  ) {

				$default = array(
					
					'past_shows' => 1,
					'single_page' => 1,
					'date_format' => 'wolf_date',

				);

				add_option( 'wolf_tour_dates_settings', $default );
			}
		}

		/**
		 * Get option
		 *
		 * @param string $value
		 * @return string
		 */
		public function get_option( $value = null ) {
			
			global $options;

			$wolf_tour_dates_settings = get_option( 'wolf_tour_dates_settings' );

			if ( isset( $wolf_tour_dates_settings[ $value ] ) ) {
				return $wolf_tour_dates_settings[ $value ];
			}
		}

		/**
		 * Register options
		 */
		public function register_settings() {
			register_setting( 'wolf-tour-dates-settings', 'wolf_tour_dates_settings', array( $this, 'settings_validate' ) );
			add_settings_section( 'wolf-tour-dates-settings', '', array( $this, 'section_intro' ), 'wolf-tour-dates-settings' );
			add_settings_field( 'date_format', __( 'Date Format in shows table', 'wolf' ), array( $this, 'setting_date_format' ), 'wolf-tour-dates-settings', 'wolf-tour-dates-settings' );
			add_settings_field( 'past_shows', __( 'Display Past Shows', 'wolf' ), array( $this, 'setting_past_shows' ), 'wolf-tour-dates-settings', 'wolf-tour-dates-settings' );
			add_settings_field( 'single_page', __( 'Link to single page', 'wolf' ), array( $this, 'setting_single_page' ), 'wolf-tour-dates-settings', 'wolf-tour-dates-settings' );
			add_settings_field( 'instructions', __( 'Instructions', 'wolf' ), array( $this, 'setting_instructions' ), 'wolf-tour-dates-settings', 'wolf-tour-dates-settings' );

		}

		/**
		 * Validate options
		 *
		 * @param array $input
		 * @return array $input
		 */
		public function settings_validate( $input ) {
			
			$input['past_shows']  = absint( $input['past_shows'] );
			$input['single_page'] = absint( $input['single_page'] );
			return $input;
		}

		/**
		 * Debug section
		 */
		public function section_intro() {
			// debug
			//global $options;
			//var_dump(get_option( 'wolf_tour_dates_settings' ));
		}

		/**
		 * Date format setting
		 */
		public function setting_date_format() {
			$date_format_custom = 'F j, Y';

			$date_format = $this->get_option( 'date_format' );

			if ( $date_format == '\c\u\s\t\o\m' ) {
				$date_format_custom = $this->get_option( 'date_format_custom' );
			}

			$checked = ' checked="checked"';
			?>
			<legend class="screen-reader-text"><span><?php _e( 'Date Format', 'wolf' ); ?></span></legend>
			<label title='wolf_date'>
				<input type='radio' name='wolf_tour_dates_settings[date_format]' value='wolf_date' <?php if ( $this->get_option( 'date_format' ) == 'wolf_date' ) echo wp_kses_post( $checked ); ?>/> 
				<span><?php echo wp_kses_post( $this->custom_date_format( date( 'm-d-y' ) ) ); ?> (<?php _e( 'Custom Style', 'wolf' ); ?>)</span>
			</label><br />
			<label title='F j, Y'>
				<input type='radio' name='wolf_tour_dates_settings[date_format]' value='F j, Y' <?php if ( $this->get_option( 'date_format' ) == 'F j, Y' ) echo wp_kses_post( $checked ); ?>/> 
				<span><?php echo sanitize_text_field( date( 'F j, Y' ) ); ?></span>
			</label><br />
			<label title='Y/m/d'>
				<input type='radio' name='wolf_tour_dates_settings[date_format]' value='Y/m/d' <?php if ( $this->get_option( 'date_format' ) == 'Y/m/d' ) echo wp_kses_post( $checked ); ?>/> 
				<span><?php echo sanitize_text_field( date( 'Y/m/d' ) ); ?></span>
			</label><br />
			<label title='m/d/Y'>
				<input type='radio' name='wolf_tour_dates_settings[date_format]' value='m/d/Y' <?php if ( $this->get_option( 'date_format' ) == 'm/d/Y' ) echo wp_kses_post( $checked ); ?>/> 
				<span><?php echo sanitize_text_field( date( 'm/d/Y' ) ); ?></span>
			</label><br />
			<label title='d/m/Y'>
				<input type='radio' name='wolf_tour_dates_settings[date_format]' value='d/m/Y' <?php if ( $this->get_option( 'date_format' ) == 'd/m/Y' ) echo wp_kses_post( $checked ); ?>/> 
				<span><?php echo sanitize_text_field( date( 'd/m/Y' ) ); ?></span>
			</label><br />
			
			<label>
				<input type="radio" name="wolf_tour_dates_settings[date_format]" id="date_format_custom_radio" value="\c\u\s\t\o\m" <?php if ( $this->get_option( 'date_format' ) == '\c\u\s\t\o\m' ) echo wp_kses_post( $checked ); ?>/> Custom: </label>
			<input type="text" name="wolf_tour_dates_settings[date_format_custom]" value="<?php echo sanitize_text_field( $date_format_custom ); ?>" class="small-text" /> <span class="example"> <?php echo sanitize_text_field( date( $date_format_custom ) ); ?></span> <span class='spinner'></span>
			<p><a href="http://codex.wordpress.org/Formatting_Date_and_Time"><?php _e( 'Documentation on date and time formatting', 'wolf' ); ?></a>.</p>
			<?php
		}

		/**
		 * Display past shows settings
		 */
		public function setting_past_shows() {
			?>
			<input type="hidden" name="wolf_tour_dates_settings[past_shows]" value="0">
			<label for="wolf_tour_dates_settings[past_shows]"><input type="checkbox" name="wolf_tour_dates_settings[past_shows]" value="1" <?php echo intval( $this->get_option( 'past_shows' ) ) == 1 ? ' checked="checked"' : ''; ?>>
			</label>
			<?php
		}

		/**
		 * Link shows in list to sinlge page option
		 */
		public function setting_single_page() {
			?>
			<input type="hidden" name="wolf_tour_dates_settings[single_page]" value="0">
			<label for="wolf_tour_dates_settings[single_page]"><input type="checkbox" name="wolf_tour_dates_settings[single_page]" value="1" <?php echo intval( $this->get_option( 'single_page' ) ) == 1 ? ' checked="checked"' : ''; ?>>
			</label>
			<?php
		}

		/**
		 * Display additional instructions
		 */
		public function setting_instructions() {
			?>
			<p><?php _e( 'To display your tour dates list, paste the following shortcode in a post or page :', 'wolf' ); ?></p>
			<p><code>[wolf_tour_dates]</code></p>
			<p><?php _e( 'Additionally, you can add some attributes.', 'wolf' ); ?></p>
			<p><code>[wolf_tour_dates count="10" past="true|false" link="true|false"]</code></p>
			<p><?php _e( 'The "past" attribute: display the past shows or not.', 'wolf' ); ?></p>
			<p><?php _e( 'The "link" attribute: link your shows to the single page or not.', 'wolf' ); ?></p>
			<?php
		}

		/**
		 * Options form
		 */
		public function options_form() {
			?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h2><?php _e( 'Tour Dates Options', 'wolf' ); ?></h2>
				<?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) { ?>
				<div id="setting-error-settings_updated" class="updated settings-error"> 
					<p><strong><?php _e( 'Settings saved.', 'wolf' ); ?></strong></p>
				</div>
				<?php } ?>
				<form action="options.php" method="post">
					<?php settings_fields( 'wolf-tour-dates-settings' ); ?>
					<?php do_settings_sections( 'wolf-tour-dates-settings' ); ?>
					<p class="submit"><input name="save" type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'wolf' ); ?>" /></p>
				</form>
			</div>
			<?php
		}

		/**
		 * Returns show date
		 *
		 * @param string $date, bool $custom
		 * @return string
		 */
		public function get_show_date( $date = null, $custom ) {
			
			list( $month, $day, $year ) = explode( '-', $date );
			$sql_date = $year . '-' . $month . '-' . $day . ' 00:00:00';

			$format = $custom ? $this->get_option( 'date_format' ) : get_option( 'date_format' );

			if ( $format == '\c\u\s\t\o\m' ) {
				$format = $this->get_option( 'date_format_custom' );
			}

			if ( $date && $format != 'wolf_date' ) {

				return mysql2date( $format, $sql_date );
					
			
			} elseif ( $date ) {

				return $this->custom_date_format( $date );

			}
		}

		/**
		 * Check if a show date is past
		 *
		 * @param string $date
		 * @return bool
		 */
		public function is_past_show( $date = null ) {
			
			if ( $date ) {
				list( $month, $day, $year ) = explode( '-', $date );
				$sql_date = $year . '-' . $month . '-' . $day . ' 00:00:00';

				$interval = ( strtotime( date( 'Y-m-d H:i:s' ) ) - strtotime( $sql_date ) );

				return $interval > 0;
			}
			
		}

		/**
		 * Format date
		 *
		 * @param string $date
		 * @return string
		 */
		public function custom_date_format( $date ) {
			list( $monthnbr, $day, $year ) = explode( '-', $date );
			$search                        = array( '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' );
			$replace                       = array( __( 'Jan', 'wolf' ), __( 'Feb', 'wolf' ), __( 'Mar', 'wolf' ), __( 'Apr', 'wolf' ), __( 'May', 'wolf' ), __( 'Jun', 'wolf' ), __( 'Jul', 'wolf' ), __( 'Aug', 'wolf' ), __( 'Sep', 'wolf' ), __( 'Oct', 'wolf' ), __( 'Nov', 'wolf' ), __( 'Dec', 'wolf' ) );
			$month                         = str_replace( $search, $replace, $monthnbr );
			
			return '<span class="wolf-custom-show-date">
			<span class="wolf-show-day">' . $day . '</span>
			<span class="wolf-show-month">' . $month . '</span>
			<span class="wolf-show-year">' . $year . '</span>
			</span>';
		}

		/**
		 * "order by" SQL filter
		 *
		 * @param string $orderby
		 * @return string
		 */
		public function order_by( $orderby ) {
			$wpdb        = $this->wpdb;
			$meta        = $wpdb->prefix . 'postmeta';
			$new_orderby = str_replace( "$meta.meta_value", "STR_TO_DATE( $meta.meta_value, '%m-%d-%Y' )", $orderby );
			return $new_orderby;
		}

		/**
		 * "where" SQL filter
		 *
		 * for future shows
		 *
		 * @param string $where
		 * @return string
		 */
		public function future_where( $where ) { // future shows
			$wpdb   = $this->wpdb;
			$meta   = $wpdb->prefix . 'postmeta';
			$meta   = $wpdb->prefix . 'postmeta';
			$where .= "AND STR_TO_DATE( $meta.meta_value,'%m-%d-%Y' ) >= CURDATE()";
			return $where;
		}

		/**
		 * "where" SQL filter
		 *
		 * for past shows
		 *
		 * @param string $where
		 * @return string
		 */
		public function past_where( $where ) { // past shows
			$wpdb   = $this->wpdb;
			$meta   = $wpdb->prefix . 'postmeta';
			$meta   = $wpdb->prefix . 'postmeta';
			$where .= "AND STR_TO_DATE( $meta.meta_value,'%m-%d-%Y' ) < CURDATE()";
			return $where;
		}

		/**
		 * Custom SQL query for future shows
		 *
		 * @param int $count
		 * @return object
		 */
		public function future_shows_query( $count ) {

			add_filter( 'posts_orderby', array( $this, 'order_by' ), 10, 1 );
			add_filter( 'posts_where', array( $this, 'future_where' ), 10,  1 );
			
			$today = date( 'm-d-Y' );
			$args  = array(
				'post_type' => 'show',
				'meta_key' => '_wolf_show_date',
				'orderby' => 'meta_value',
				'order' => 'ASC',
				'posts_per_page' => $count,
			);
			
			$query = new WP_Query( $args );

			remove_filter( 'posts_where', array( $this, 'future_where' ), 10, 1 );
			remove_filter( 'posts_orderby', array( $this, 'order_by' ), 10, 1 );

			return $query;

		}

		/**
		 * Custom SQL query for past shows
		 *
		 * @param int $count
		 * @return object
		 */
		public function past_shows_query( $count ) {
			add_filter( 'posts_orderby', array( $this, 'order_by' ), 10, 1 );
			add_filter( 'posts_where', array( $this, 'past_where' ), 10, 1 );
			$today = date( 'm-d-Y' );
			$args  = array(
				'post_type' => 'show',
				'meta_key' => '_wolf_show_date',
				'orderby' => 'meta_value',
				'order' => 'DESC',
				'posts_per_page' => $count,
			);
			
			$query = new WP_Query( $args );

			remove_filter( 'posts_where', array( $this, 'past_where' ), 10, 1 );
			remove_filter( 'posts_orderby', array( $this, 'order_by' ), 10, 1 );

			return $query;
		}

		/**
		 * Loop function
		 *
		 * Display the shows list
		 *
		 * @param int $count
		 * @param bool $past
		 * @param bool $link
		 * @return string
		 */
		public function tour_dates( $count = 100, $past = null, $link = null ) {
			
			$wpdb = $this->wpdb;

			$display_past_show = $this->get_option( 'past_shows' );

			if ( $past == 'false' ) {
				
				$display_past_show = false;
			
			} elseif ( $past == 'true' ) {

				$display_past_show = true;
			
			}

			$display_link = $this->get_option( 'single_page' );

			if ( $link == 'false' ) {
				
				$display_link = false;
			
			} elseif ( $link == 'true' ) {

				$display_link = true;
			
			}
			
			$upcoming_shows = $this->future_shows_query( $count );
			$past_shows     = $this->past_shows_query( $count );

			echo '<div class="wolf-tour-dates">';
		
			if ( $upcoming_shows->have_posts() ) :

				if ( $display_past_show ) {

					wolf_tour_dates_output_upcoming_shows_title( '<h2>', '</h2>' );

				}
				
				echo '<table class="wolf-upcoming-shows wolf-shows">';
				while ( $upcoming_shows->have_posts() ) : $upcoming_shows->the_post();

					$post_id = get_the_ID();

					$date    = $this->get_show_date( get_post_meta( $post_id, '_wolf_show_date', true ), true );
					$city    = get_post_meta( $post_id, '_wolf_show_city', true );
					$country = get_post_meta( $post_id, '_wolf_show_country_short', true );
					$state   = get_post_meta( $post_id, '_wolf_show_state', true );

					$place = $city;

					if ( $country && ! $state ) {
						$place = $city . ', ' . $country;
					}

					if ( ! $country && $state ) {
						$place = $city . ', ' . $state;
					}

					if ( $country && $state ) {
						$place = $city . ', ' . $state . ' ( ' . $country . ' )';
					}

					$venue         = get_post_meta( $post_id, '_wolf_show_venue', true );
					$ticket        = get_post_meta( $post_id, '_wolf_show_ticket', true );
					$cancelled     = get_post_meta( $post_id, '_wolf_show_cancel', true );
					$soldout       = get_post_meta( $post_id, '_wolf_show_soldout', true );
					$free          = get_post_meta( $post_id, '_wolf_show_free', true );
					$facebook_page = get_post_meta( $post_id, '_wolf_show_fb', true );
					$price         = get_post_meta( $post_id, '_wolf_show_price', true );
					$ticket_text   = ( $price ) ? sprintf( __( 'Buy ticket - %s', 'wolf' ), $price ) : __( 'Buy ticket', 'wolf' );
					$artist        = get_post_meta( $post_id, '_wolf_show_artist', true );

					if ( ! $cancelled && ! $soldout && $ticket ) {

						$action = '<a target="_blank" href="' . esc_url( $ticket ) . '" class="wolf-show-ticket-button">'. sanitize_text_field( $ticket_text ) .'</a>';
					}

					if ( $cancelled ) {

						$action = '<span class="wolf-show-label cancelled">' . __( 'cancelled', 'wolf' ) . '</span>';

					}

					if ( $soldout ) {
						$action = '<span class="wolf-show-label sold-out">' . __( 'sold out!', 'wolf' ) . '</span>';
					}	

					if ( $free ) {

						$action = '<span class="wolf-show-label">' . __( 'free', 'wolf' ) . '</span>';

					}

					if ( ! $cancelled && ! $soldout && ! $ticket && ! $free ) {

						$action = '';

					}

					$custom_class = '';

					if ( $display_link && ! $cancelled ) {

						$custom_class = 'wolf-show-linked';

					} elseif ( $cancelled ) {

						$custom_class = 'wolf-show-cancelled';
					}
					?>
					<tr class="wolf-single-date <?php echo sanitize_html_class( $custom_class );  ?>">
						<td class="wolf-show-date"><?php echo  wp_kses_post( $date ); ?></td>

						<?php if ( $artist ) : ?>
						<td><?php echo sanitize_text_field( $artist ); ?></td>
						<?php endif; ?>

						<td class="wolf-show-entry">
							<?php if ( $display_link ) : ?>
							<a title="<?php _e( 'View details', 'wolf' ); ?>" class="wolf-show-entry-link" href="<?php echo get_permalink( $post_id ); ?>">
							<?php endif; ?>
								<strong><?php echo sanitize_text_field( $place ); ?></strong> <span class="wolf-show-mobile-show"><?php echo sanitize_text_field( $venue ); ?></span>
							<?php if ( $display_link ) : ?>
							</a>
							<?php endif; ?>
						</td>
						
						<td class="wolf-show-venue wolf-show-mobile-hide"><?php echo sanitize_text_field( $venue ); ?></td>
						
						<td class="wolf-show-icons">
						<?php if ( $facebook_page ) : ?>
							<a href="<?php echo esc_url( $facebook_page ); ?>" class="wolf-show-facebook" target="_blank" title="<?php _e( 'View the facebook event page', 'wolf' ); ?>"></a>
						<?php endif; ?>	
						<?php if ( has_post_thumbnail( $post_id ) ) :
							$img = wolf_get_show_thumbnail_url( 'full', $post_id );
						?><a class="wolf-show-flyer" href="<?php echo esc_url( $img ); ?>" title="<?php _e( 'View flyer', 'wolf' ); ?>"></a>
						<?php endif; ?>
						</td>
						
						<td class="wolf-show-action">
							<?php echo wp_kses_post( $action ); ?>
						</td>
					</tr>
				<?php 
				endwhile;

				echo '</table>';
			
			else :
				
				?><p><?php _e( 'No upcoming shows scheduled.', 'wolf' ); ?></p><?php

			
			endif; 
			wp_reset_postdata();


			if ( $past_shows->have_posts() && $display_past_show ) :
				
				wolf_tour_dates_output_past_shows_title( '<h2>', '</h2>' );

				echo '<table class="wolf-past-shows wolf-shows">';
				while ( $past_shows->have_posts() ) : $past_shows->the_post();
					
					$post_id = get_the_ID();
					$date    = $this->get_show_date( get_post_meta( $post_id, '_wolf_show_date', true ), true );
					$city    = get_post_meta( $post_id, '_wolf_show_city', true );
					$country = get_post_meta( $post_id, '_wolf_show_country_short', true );
					$state   = get_post_meta( $post_id, '_wolf_show_state', true );
					$place   = $city;

					if ( $country && ! $state ) {
						$place = $city . ', ' . $country;
					}

					if ( ! $country && $state ) {
						$place = $city . ', ' . $state;
					}

					if ( $country && $state ) {
						$place = $city . ', ' . $state . ' ( ' . $country . ' )';
					}

					$venue         = get_post_meta( $post_id, '_wolf_show_venue', true );
					$cancelled     = get_post_meta( $post_id, '_wolf_show_cancel', true );
					$facebook_page = get_post_meta( $post_id, '_wolf_show_fb', true );
					$action        = ' ';
					$artist        = get_post_meta( $post_id, '_wolf_show_artist', true );
					
					$custom_class = '';

					if ( $display_link && ! $cancelled ) {

						$custom_class = 'wolf-show-linked';

					}

					if ( ! $cancelled ) :
						?>
						<tr class="wolf-single-date <?php echo sanitize_html_class( $custom_class );  ?>">
							<td class="wolf-show-date"><?php echo wp_kses_post( $date ); ?></td>

							<?php if ( $artist ) : ?>
							<td><?php echo sanitize_text_field( $artist ); ?></td>
							<?php endif; ?>

							<td class="wolf-show-entry">
								<?php if ( $display_link ) : ?>
								<a title="<?php _e( 'View details', 'wolf' ); ?>" class="wolf-show-entry-link" href="<?php echo get_permalink( $post_id ); ?>">
								<?php endif; ?>
									<strong><?php echo sanitize_text_field( $place ); ?></strong> <span class="wolf-show-mobile-show"><?php echo sanitize_text_field( $venue ); ?></span>
								<?php if ( $display_link ) : ?>
								</a>
								<?php endif; ?>
							</td>
							
							<td class="wolf-show-venue wolf-show-mobile-hide"><?php echo sanitize_text_field( $venue ); ?></td>
							
							<td class="wolf-show-icons">
							<?php if ( $facebook_page ) : ?>
								<a href="<?php echo esc_url( $facebook_page ); ?>" class="wolf-show-facebook" target="_blank" title="<?php _e( 'View the facebook event page', 'wolf' ); ?>"></a>
							<?php endif; ?>	
							<?php if ( has_post_thumbnail( $post_id ) ) :
								$img = wolf_get_show_thumbnail_url( 'full', $post_id );
							?><a class="wolf-show-flyer" href="<?php echo esc_url( $img ); ?>" title="<?php _e( 'View flyer', 'wolf' ); ?>"></a>
							<?php endif; ?>
							</td>
							
							<td class="wolf-show-action">
								<?php echo wp_kses_post( $action ); ?>
							</td>
						</tr>
					<?php endif;
				endwhile;

				echo '</table>';

			endif;
			wp_reset_postdata();
			echo '</div>';
		}

		/**
		 * Widget function
		 *
		 * Displays the show list in the widget
		 *
		 * @param int $count, string $url, bool $link
		 * @param string $url
		 * @param bool $link
		 * @return string
		 */
		public function widget( $count, $url, $link ) {

			$wpdb = $this->wpdb;

			$display_link = $this->get_option( 'single_page' );

			$upcoming_shows = $this->future_shows_query( $count );

			if ( $upcoming_shows->have_posts() ) {

				echo '<table class="wolf-upcoming-shows wolf-shows">';
				while ( $upcoming_shows->have_posts() ) : $upcoming_shows->the_post();

					$post_id = get_the_ID();

					/* meta */
					$date    = $this->get_show_date( get_post_meta( $post_id, '_wolf_show_date', true ), true );
					$city    = get_post_meta( $post_id, '_wolf_show_city', true );
					$country = get_post_meta( $post_id, '_wolf_show_country_short', true );
					$state   = get_post_meta( $post_id, '_wolf_show_state', true );
					$place   = $city;

					if ( $country && ! $state ) {
						$place = $city . ', ' . $country;
					}

					if ( ! $country && $state ) {
						$place = $city . ', ' . $state;
					}

					if ( $country && $state ) {
						$place = $city . ', ' . $state . ' ( ' . $country . ' )';
					}
					
					$venue       = get_post_meta( $post_id, '_wolf_show_venue', true ) ? '<br>' . get_post_meta( $post_id, '_wolf_show_venue', true ) : '';
					$ticket      = get_post_meta( $post_id, '_wolf_show_ticket', true );
					$cancelled   = get_post_meta( $post_id, '_wolf_show_cancel', true );
					$soldout     = get_post_meta( $post_id, '_wolf_show_soldout', true );
					$free        = get_post_meta( $post_id, '_wolf_show_free', true );
					$price       = get_post_meta( $post_id, '_wolf_show_price', true );
					$ticket_text = __( 'Buy ticket', 'wolf' );
					$artist      = get_post_meta( $post_id, '_wolf_show_artist', true );
					$action      = '';

					if ( ! $cancelled && ! $soldout && $ticket ) {

						$action = '<a target="_blank" href="' . esc_url( $ticket ) . '" class="wolf-show-ticket-text">'. sanitize_text_field( $ticket_text ) .'</a>';
					}

					if ( $cancelled ) {

						$action = '<span class="wolf-show-label cancelled">' . __( 'cancelled', 'wolf' ) . '</span>';

					}

					if ( $soldout ) {
						$action = '<span class="wolf-show-label sold-out">' . __( 'sold out!', 'wolf' ) . '</span>';
					}	

					if ( $free ) {

						$action = '<span class="wolf-show-label">' . __( 'free', 'wolf' ) . '</span>';

					}

					if ( ! $cancelled && ! $soldout && ! $ticket && ! $free ) {

						$action = '';

					}

					$custom_class = '';

					if ( $cancelled ) {

						$custom_class = 'wolf-show-cancelled';
					}
					?>
					<tr class="wolf-single-date <?php echo sanitize_html_class( $custom_class );  ?>">
						<td class="wolf-show-date"><?php echo wp_kses_post( $date ); ?></td>
						<td class="wolf-show-infos">
							<?php if ( $artist ) : ?>
							<strong><?php echo sanitize_text_field( $artist ); ?></strong> - 
							<?php endif; ?>
							
							<?php if ( $display_link ) : ?>
							<a title="<?php _e( 'View details', 'wolf' ); ?>" class="wolf-show-entry-link" href="<?php echo get_permalink( $post_id ); ?>">
							<?php endif; ?>

							<strong><?php echo sanitize_text_field( $place ); ?></strong> &mdash; <?php echo sanitize_text_field( $venue ); ?>
							

							<?php if ( $display_link ) : ?>
							</a>
							<?php endif; ?>

							<?php if ( $action ) : ?>
							<br><?php echo wp_kses_post( $action ); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php 

				endwhile;
				echo '</table>';

				if ( $url ) {
					echo '<p><a href="' . esc_url( $url ) . '" class="wolf-more-dates">' . __( 'View more dates', 'wolf' ) . '</a></p>';
				}
			} else {
				
				?><p><?php _e( 'No upcoming shows scheduled.', 'wolf' ); ?></p><?php

			} // endif post
			wp_reset_postdata();
		}

		/**
		 * Returns post thumbnail URL
		 *
		 * @param string $format
		 * @param int $post_id
		 * @return string
		 */
		public function get_post_thumbnail_url( $format, $post_id ) {
			global $post;

			if ( is_object( $post ) && isset( $post->ID ) && $post_id == null )
				$ID = $post->ID;
			else
				$ID = $post_id;

			if ( $ID && has_post_thumbnail( $ID ) ) {

				$attachment_id = get_post_thumbnail_id( $ID );
				if ( $attachment_id ){
					$img_src = wp_get_attachment_image_src( $attachment_id, $format ); 
					
					if ( $img_src && isset( $img_src[0] ) )
						return $img_src[0];
				}
			}
		}

		/**
		 * Shortcode function
		 *
		 * @param array $atts
		 * @return string
		 */
		public function shortcode( $atts ) {

			extract(
				shortcode_atts(
					array(
						'count' => 100,
						'past' => null,
						'link' => null,
					), $atts
				)
			);

			ob_start(); 
			$this->tour_dates( $count, $past, $link );
			return ob_get_clean();
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			if ( $this->plugin_url ) return $this->plugin_url;
			return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			if ( $this->plugin_path ) return $this->plugin_path;
			return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

	} // end class

	/**
	 * Init Wolf_Tour_Dates class
	 */
	$GLOBALS['wolf_tour_dates'] = new Wolf_Tour_Dates();

} // end class exists check