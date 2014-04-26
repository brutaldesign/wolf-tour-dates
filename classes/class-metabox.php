<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Wolf_Tour_Dates_Metabox' ) ) {
	/**
	 * Wolf Tour Dates Metabox Helper
	 *
	 * Helper class to create tour dates metaboxes
	 *
	 * @author WpWolf
	 * @package WolfTourDates/Classes
	 * @since 1.0.0
	 */
	class Wolf_Tour_Dates_Metabox {

		var $meta = array();

		/**
		 * Wolf_Tour_Dates_Metabox Constructor.
		 *
		 */
		public function __construct( $meta = array() ) {

			$this->meta = $meta + $this->meta;
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'save_post', array( $this, 'save' ) );
		}


		/**
		 * Add metaboxes in admin
		 */
		public function add_meta_boxes() {

			foreach ( $this->meta as $k => $v ) {
	
				if ( is_array( $v['page'] ) ) {
					foreach ( $v['page'] as $p ) {
						add_meta_box(
							sanitize_title( $k ) . '_wolf_meta_box', // $id
							$v['title'], // $title 
							array( $this, 'render' ), // $callback
							$p, // $page
							'normal', // $context
							'high'
						); // $priority	
					}
				} else {

					add_meta_box(
						sanitize_title( $k ) . '_wolf_meta_box',
						$v['title'],
						array( $this, 'render' ),
						$v['page'],
						'normal',
						'high'
					);

				}	
			}
		}

		/**
		 * Enqueue admin scripts
		 */
		public function admin_scripts() {

			wp_enqueue_script( 'wolf-tour-dates-datepicker', WOLF_TOUR_DATES_URL . '/assets/admin/js/datepicker.min.js', array( 'jquery-ui-datepicker' ), false, true );		
		}

		/**
		 * Enqueue admin styles
		 */
		public function admin_styles() {
			
			/* Datepicker CSS */
			wp_enqueue_style( 'jquery-ui-custom', WOLF_TOUR_DATES_URL . '/assets/admin/css/jquery-ui-custom.min.css', array(), WOLF_TOUR_DATES_VERSION, 'all' );
		}

		/**
		 * Display fields
		 *
		 * @return string
		 */
		public function render() {

			global $post;
			$meta_fields = array();
			
			$current_post_type = get_post_type( $post->ID );

			foreach ( $this->meta as $k => $v ) {
				if ( is_array( $v['page'] ) ) {
					if ( in_array( $current_post_type, $v['page'] ) ) {
						$meta_fields = $v['metafields'];
					}
				} else {
					if ( $current_post_type == $v['page'] ) {
						$meta_fields = $v['metafields'];
					}
				}
			}

			// Use nonce for verification
			echo '<input type="hidden" name="custom_meta_box_nonce" value="' . wp_create_nonce( basename( __FILE__ ) ) . '">';
			
			// Begin the field table and loop
			echo '<table class="form-table wolf-metabox-table">';

			foreach ( $this->meta as $k => $v ) {

				if ( isset( $v['help'] ) ) {
					echo '<div class="wolf-metabox-help">' . $v['help'] . '</div>';
				}
			}

			foreach ( $meta_fields as $field ) {
				
				if ( ! isset( $field['desc'] ) ) $field['desc'] = '';
				if ( ! isset( $field['def'] ) ) $field['def']   = '';
				
				// get value of this field if it exists for this post
				$meta = get_post_meta( $post->ID, $field['id'], true );
				
				if ( ! $meta )
					$meta = $field['def'];
				
				// begin a table row with
				echo '<tr>
				
				<th style="width:20%"><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
				
				<td>';

				// editor
				if ( $field['type'] == 'editor' ) {
					
					wp_editor( $meta, $field['id'], $settings = array() );

					// text
				} elseif ( $field['type'] == 'text' || $field['type'] == 'url' || $field['type'] == 'email' ) {
				
					echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="30">
					<br><span class="description">' . $field['desc'] . '</span>';
				
					// textarea
				} elseif ( $field['type'] == 'textarea' ) {
					echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="60" rows="4">' . $meta . '</textarea>
					<br><span class="description">' . $field['desc'] . '</span>';
				
					// checkbox
				} elseif ( $field['type'] == 'checkbox' ) {
					echo '<input type="checkbox" name="' . $field['id'] . '" id="' . $field['id'] . '" ', $meta ? ' checked="checked"' : '','>
					<span class="description">' . $field['desc'] . '</span>';
				
					// select
				} elseif ( $field['type'] == 'select' ) {
					
					echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">';
				
					if ( array_keys( $field['options'] ) != array_keys( array_keys( $field['options'] ) ) ) {
					
						foreach ( $field['options'] as $k => $option ) {
							echo '<option', $meta == $k ? ' selected="selected"' : '', ' value="' . $k . '">' . $option . '</option>';
						}
					} else {
						foreach ( $field['options'] as $option ) {
							echo '<option', $meta == $option ? ' selected="selected"' : '', ' value="' . $option . '">' . $option . '</option>';
						}
					}
					
					echo '</select><br><span class="description">' . $field['desc'] . '</span>';
				
					// datepicker
				} elseif ( $field['type'] == 'datepicker' ) {
					echo '<input type="text" class="wolf-metabox-datepicker" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="30">
					<br><span class="description">' . $field['desc'] . '</span>';
				
				}
				
				echo '</td>';
			
				'</tr>';
			} // end foreach
			echo '</table>'; // end table
		}

		/**
		 * Save post meta data
		 *
		 * @param int $post_id
		 */
		public function save( $post_id ) {
			global $post;

			$meta_fields = '';
			
			// verify nonce
			if ( ( isset( $_POST['wolf_meta_box_nonce'] ) ) && ( ! wp_verify_nonce( $_POST['wolf_meta_box_nonce'], basename( __FILE__ ) ) ) )
				return $post_id;
			
			// check autosave
			if (  defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return $post_id;
			
			// check permissions
			if ( isset( $_POST['post_type'] ) && is_object( $post ) ) {
				
				$current_post_type = get_post_type( $post->ID );
				
				if ( 'page' == $_POST['post_type'] ) {
					
					if ( ! current_user_can( 'edit_page', $post_id ) )
						return $post_id;
					
				} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
						return $post_id;
				}
			
				foreach ( $this->meta as $k => $v ) {

					if ( is_array( $v['page'] ) )
						$condition = isset( $_POST['post_type'] ) && in_array( $_POST['post_type'], $v['page'] );
					else
						$condition = isset( $_POST['post_type'] ) && $_POST['post_type'] == $v['page'];

					if ( $condition ) {
						$meta_fields = $v['metafields'];
						
						// loop through fields and save the data
						foreach ( $meta_fields as $field ) {
							

							if ( $field['type'] == 'tax_select' ) continue;

							if ( $field['type'] == 'background' ) {

								$meta = get_post_meta( $post_id, $field['id'], true );
								
								$bg_settings = array( 'color', 'position', 'repeat', 'attachment', 'size', 'img' );

								foreach ( $bg_settings as $s ) {

									$o = $field['id'].'_'.$s;
									
									if ( isset( $_POST[$o] ) ) {
										
										update_post_meta( $post_id, $o , $_POST[$o] );
									}
								}
							} // end background

							else {
								$old = get_post_meta( $post_id, $field['id'], true );
								$new = '';
								
								if ( isset( $_POST[ $field['id'] ] ) ) {

									if ( $field['type'] == 'editor' )
										$new = wpautop( wptexturize( $_POST[ $field['id'] ] ) );
									
									elseif ( $field['type'] == 'url' )
										$new = esc_url( $_POST[ $field['id'] ] );

									elseif ( $field['type'] == 'url' )
										$new = sanitize_email( $_POST[ $field['id'] ] );
									
									else
										$new = $_POST[ $field['id'] ];
								}
									
								if ( $new && $new != $old ) {

									update_post_meta( $post_id, $field['id'], $new );
								
								} elseif ( '' == $new && $old ) {
									
									delete_post_meta( $post_id, $field['id'], $old );
								}
							}
						} // end foreach
					}
				}					
			}
		} // end save function

	} // end class

} // end class check