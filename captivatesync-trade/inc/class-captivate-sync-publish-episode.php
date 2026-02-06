<?php
/**
 * Used to process publish and edit episode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Publish_Episode' ) ) :

	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}

	/**
	 * Hosting Publish Episode class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Publish_Episode {

		/**
		 * Enqueue assets
		 *
		 * @since 1.0
		 */
		public static function assets() {

			$current_screen = get_current_screen();

			$publish_episode_screens = array(
				'toplevel_page_cfm-hosting-publish-episode',
				'admin_page_cfm-hosting-publish-episode',
				'captivate-sync_page_cfm-hosting-publish-episode',

				'toplevel_page_cfm-hosting-edit-episode',
				'admin_page_cfm-hosting-edit-episode',
				'captivate-sync_page_cfm-hosting-edit-episode',
			);

			if ( in_array( $current_screen->id, $publish_episode_screens ) ) :

				wp_enqueue_media();
				wp_enqueue_script( 'quilljs', CFMH_URL . 'vendor/quill/quill.min.js', array(), '1.3.7' );
				wp_enqueue_style( 'quilljs', CFMH_URL . 'vendor/quill/quill.snow.css', array(), '2.0.0' );
				wp_enqueue_script( 'quilljs-script', CFMH_URL . 'captivate-sync-assets/js/dist/quilljs-min.js', array(), '1.3.7' );

				wp_enqueue_style( 'jquery-ui-theme', CFMH_URL . 'vendor/jquery-ui/jquery-ui.min.css', array(), '1.12.1' );
				wp_enqueue_script( 'jquery-ui-datepicker' );

				wp_enqueue_script( 'dropzone', CFMH_URL . 'vendor/dropzone/dropzone.min.js', array(), '5.7.0' );
				wp_enqueue_style( 'dropzone', CFMH_URL . 'vendor/dropzone/dropzone.min.css' );

				wp_enqueue_script( 'savestorage', CFMH_URL . 'captivate-sync-assets/js/dist/local-storage-min.js', array(), CFMH_VERSION );

				wp_register_script( 'cfm_script', CFMH_URL . 'captivate-sync-assets/js/dist/publish-episode-min.js', array( 'jquery' ), CFMH_VERSION );
				wp_localize_script( 'cfm_script', 'cfm_script', array(
					'cfm_url'   => CFMH_API_URL,
					'xfNr5Wsp' => cfm_generate_random_string() . get_transient( 'cfm_authentication_token' ) . cfm_generate_random_string(),
				) );

				wp_enqueue_script( 'cfm_script' );

			endif;

		}

		/**
		 * Save episode
		 *
		 * @since 1.0
		 * @return void
		 */
		public static function publish_episode_save() {

			$show_id = isset( $_POST['show_id'] ) ? sanitize_text_field( wp_unslash( $_POST['show_id'] ) ) : '';
			$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0;
			$post_id = (int) $post_id;

			if ( ! isset( $_POST['_sec'] ) || ! wp_verify_nonce( $_POST['_sec'], '_sec_action_' . $post_id ) ) {
				wp_die( __( "Cheatin' uh?" ) ); exit;
			}
			else {

				if ( ! cfm_is_show_exists( $show_id ) ) {
					wp_die( __( "Show does not exists." ) ); exit;
				}
				else {

					$episode_info = array();
					$errors      = 0;
					$submit_action = 'draft';

					if ( isset( $_POST['submit_action'] ) && 'draft' == $_POST['submit_action'] ) {
						$submit_action = 'draft';
					}
					if ( isset( $_POST['submit_action'] ) && 'update' == $_POST['submit_action'] ) {
						$submit_action = 'update';
					}
					if ( isset( $_POST['submit_action'] ) && 'publish' == $_POST['submit_action'] ) {
						$submit_action = 'publish';
					}

					$post_title   = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
					$itunes_title = ( isset( $_POST['post_title_check'] ) && isset( $_POST['itunes_title'] ) ) ? sanitize_text_field( wp_unslash( $_POST['itunes_title'] ) ) : '';
					$enable_wordpress_editor = isset( $_POST['enable_wordpress_editor'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_wordpress_editor'] ) ) : 'off';
					$shownotes = $enable_wordpress_editor == 'on' ? wp_filter_post_kses( $_POST['post_content_wp'] ) : wp_filter_post_kses( $_POST['post_content'] );
					$shownotes = wp_unslash( $shownotes );

					// required fields if they pass the Js validation for some reason.
					if ( '' == $post_title ) {
						++$errors; }
					if ( 'draft' != $submit_action ) {
						if ( ! isset( $_POST['media_id'] ) || ( isset( $_POST['media_id'] ) && '' == $_POST['media_id'] ) ) {
							++$errors; }
						if ( '' == $shownotes || '<p><br></p>' == $shownotes ) {
							++$errors; }
					}

					// Post data.
					$post_author = isset( $_POST['post_author'] ) ? sanitize_text_field( wp_unslash( $_POST['post_author'] ) ) : get_current_user_id();

					$post_data = array(
						'post_title'   => sanitize_text_field( wp_unslash( $post_title ) ),
						'post_content' => $shownotes,
						'post_author'  => (int) $post_author,
						'post_excerpt'  => sanitize_textarea_field(wp_unslash($_POST['post_excerpt'])),
						'post_type'    => 'captivate_podcast',
					);

					// Post date and status.
					$post_datetime = sanitize_text_field( wp_unslash( $_POST['publish_date'] ) ) . ' ' . sanitize_text_field( wp_unslash( $_POST['publish_time'] ) );
					$post_datetime = date( 'Y-m-d H:i:s', strtotime( $post_datetime ) );

					$current_date      			= new DateTime();
					$post_date_publish 			= new DateTime( $post_datetime );
					$post_status 				= get_post_status( $post_id );
					$episode_status 			= get_post_meta( $post_id, 'cfm_episode_status', true );
					$episode_expiration 		= get_post_meta( $post_id, 'cfm_episode_expiration', true );
					$early_access_end_date 		= get_post_meta( $post_id, 'cfm_episode_early_access_end_date', true );
					$exclusivity_date 			= get_post_meta( $post_id, 'cfm_episode_exclusivity_date', true );
					$captivate_episode_type 	= get_post_meta( $post_id, 'cfm_episode_captivate_episode_type', true );
					$captivate_episode_type		= $captivate_episode_type ? $captivate_episode_type : 'standard';

					if ( $post_date_publish > $current_date ) {
						$post_data['post_status'] = 'future';
						$episode_info['status']  = 'Scheduled';
					}
					else {
						$post_data['post_status'] = 'publish';
						$episode_info['status']  = 'Published';
					}
					if ( 'draft' == $submit_action ) {
						$post_data['post_status'] = 'draft';
						$episode_info['status']  = 'Draft';
					}

					$post_data['post_date']     = $post_datetime;
					$post_data['post_date_gmt'] = get_gmt_from_date( $post_datetime, 'Y-m-d H:i:s' );

					// Post slug.
					$post_name = ( isset( $_POST['post_name'] ) && '' != $_POST['post_name'] ) ? $_POST['post_name'] : $_POST['post_title'];
					$post_data['post_name'] = sanitize_title( wp_unslash( $post_name ) );
					$episode_info['slug'] = sanitize_title( wp_unslash( $post_name ) );

					// Discussion.
					$website_comment = isset( $_POST['website_comment'] ) ? $_POST['website_comment'] : 'closed';
					$post_data['comment_status'] = sanitize_title( wp_unslash( $website_comment ) );

					$website_ping = isset( $_POST['website_ping'] ) ? $_POST['website_ping'] : 'closed';
					$post_data['ping_status'] = sanitize_title( wp_unslash( $website_ping ) );

					// Insert the post into the database if no error.
					if ( $errors > 0 ) {
						if ( ( 'update' == $submit_action || 'draft' == $submit_action ) && 0 != $post_id ) {
							wp_redirect( admin_url( "admin.php?page=cfm-hosting-edit-episode&show_id={$show_id}&eid={$post_id}&response=1" ) );
						}
						else {
							wp_redirect( admin_url( "admin.php?page=cfm-hosting-publish-episode&show_id={$show_id}&response=1" ) );
						}
					}
					else {

						$cfm_episode_id = get_post_meta( $post_id, 'cfm_episode_id', true );
						$auth_token = get_transient( 'cfm_authentication_token' );

						if ( 0 != $post_id ) {
							$post_data['ID'] = $post_id;
							$post_data['edit_date'] = true;

							// Make sure that exclusive, expired, and early access episodes stays the same on update.
							if ( in_array( $episode_status, array( 'Exclusive', 'Early Access', 'Expired' ) ) || $exclusivity_date || $early_access_end_date ) {
								$current_post_date = get_the_date( 'Y-m-d H:i:s', $post_id );
								$post_data['post_status'] 	= $post_status;
								$post_data['post_date']     = $current_post_date;
								$post_data['post_date_gmt'] = get_gmt_from_date( $current_post_date, 'Y-m-d H:i:s' );
							}

							wp_update_post( $post_data );
							$episode_info['episodes_id'] = $cfm_episode_id;
						}
						else {
							if ( !cfm_episode_exists( $cfm_episode_id ) ) {
								$post_id = wp_insert_post( $post_data );
							}
						}

						// episode categories.
						if ( isset( $_POST['tax_input']['captivate_category'] ) ) {
							$captivate_categories 	= wp_unslash( $_POST['tax_input']['captivate_category'] );
							$selected_categories	= array();

							if ( is_array( $captivate_categories ) && ! empty( $captivate_categories ) ) {
								foreach ( $captivate_categories as $id ) {
									$selected_categories[] = sanitize_text_field( $id );
								}
							}

							if ( ! empty( $selected_categories ) ) {
								wp_set_post_terms( $post_id, $selected_categories, 'captivate_category', false );
							}
						}

						// episode tags.
						if ( isset( $_POST['tax_input']['captivate_tag'] ) ) {
							$captivate_tags = wp_unslash( $_POST['tax_input']['captivate_tag'] );
							if ( ! empty( $captivate_tags ) ) {
								$tags = array();
								foreach ( $captivate_tags as $tag ) {
									$tags[] = (int) sanitize_text_field( $tag );
								}
								wp_set_post_terms( $post_id, $tags, 'captivate_tag', false );
							}
						}

						// cutom taxonomies.
						$tax_exclude = [ 'captivate_category', 'captivate_tag' ];
						$taxonomies = get_object_taxonomies( 'captivate_podcast', 'objects' );
						foreach ( $taxonomies as $taxonomy ) {
							if ( in_array( $taxonomy->name, $tax_exclude, true ) ) continue;

							if ( isset( $_POST['tax_input'][ $taxonomy->name ] ) ) {

								$submitted_terms = wp_unslash( $_POST['tax_input'][ $taxonomy->name ] );
								$sanitized_terms = [];

								if ( is_array( $submitted_terms ) && ! empty( $submitted_terms ) ) {

									// For hierarchical taxonomies: use IDs
									if ( $taxonomy->hierarchical ) {
										foreach ( $submitted_terms as $term_id ) {
											$sanitized_terms[] = intval( $term_id );
										}
									}
									// For non-hierarchical: convert IDs to term names
									else {
										foreach ( $submitted_terms as $term_id ) {
											$term_obj = get_term( intval($term_id), $taxonomy->name );
											if ( $term_obj && ! is_wp_error( $term_obj ) ) {
												$sanitized_terms[] = $term_obj->name;
											}
										}
									}
								}

								wp_set_post_terms( $post_id, $sanitized_terms, $taxonomy->name, false );
							}
						}

						// show id.
						$episode_info['shows_id'] = $show_id;
						update_post_meta( $post_id, 'cfm_show_id', $show_id );

						// use wordpress editor.
						update_post_meta( $post_id, 'cfm_enable_wordpress_editor', sanitize_text_field( wp_unslash( $enable_wordpress_editor ) ) );

						// iTunes title.
						$episode_info['itunes_title'] = $itunes_title;
						update_post_meta( $post_id, 'cfm_episode_itunes_title', $itunes_title );

						// Artwork, select new, do nothing if it's just the same.
						$uploaded_artwork = '';
						$artwork_id = sanitize_text_field( wp_unslash( $_POST['episode_artwork_id'] ) );
						$artwork_url = sanitize_text_field( wp_unslash( $_POST['episode_artwork'] ) );
						$artwork_width = sanitize_text_field( wp_unslash( $_POST['episode_artwork_width'] ) );
						$artwork_height = sanitize_text_field( wp_unslash( $_POST['episode_artwork_height'] ) );
						$artwork_type = sanitize_text_field( wp_unslash( $_POST['episode_artwork_type'] ) );
						$artwork_filesize = sanitize_text_field( wp_unslash( $_POST['episode_artwork_filesize'] ) );

						if ( '' != $artwork_id && get_post_meta( $post_id, 'cfm_episode_artwork_id', true ) != $artwork_id ) {
							// Upload selected artwork to Captivate.
							$uploaded_artwork = cfm_upload_file( $artwork_url, $show_id );
						}
						else {
							// get existing artwork.
							$uploaded_artwork = $artwork_url;
						}

						update_post_meta( $post_id, 'cfm_episode_artwork_id', $artwork_id );
						update_post_meta( $post_id, 'cfm_episode_artwork_width', $artwork_width );
						update_post_meta( $post_id, 'cfm_episode_artwork_height', $artwork_height );
						update_post_meta( $post_id, 'cfm_episode_artwork_type', $artwork_type );
						update_post_meta( $post_id, 'cfm_episode_artwork_filesize', $artwork_filesize );
						update_post_meta( $post_id, 'cfm_episode_artwork', $uploaded_artwork );
						$episode_info['episode_art'] = $uploaded_artwork;

						// Featured image.
						if ( isset( $_POST['featured_image_id'] ) && '' != $_POST['featured_image_id'] ) {
							$image_id = sanitize_text_field( wp_unslash( $_POST['featured_image_id'] ) );

							// set as featured image.
							update_post_meta( $post_id, '_thumbnail_id', $image_id );
						}

						// remove featured image.
						if ( '0' == $_POST['featured_image_id'] ) {
							delete_post_meta( $post_id, '_thumbnail_id' );
						}

						// Episode season.
						$episode_info['episode_season'] = sanitize_text_field( wp_unslash( $_POST['season_number'] ) );
						update_post_meta( $post_id, 'cfm_episode_itunes_season', sanitize_text_field( wp_unslash( $_POST['season_number'] ) ) );

						// Episode number.
						$episode_info['episode_number'] = sanitize_text_field( wp_unslash( $_POST['episode_number'] ) );
						update_post_meta( $post_id, 'cfm_episode_itunes_number', sanitize_text_field( wp_unslash( $_POST['episode_number'] ) ) );

						// Episode type.
						$episode_info['episode_type'] = sanitize_text_field( wp_unslash( $_POST['episode_type'] ) );
						update_post_meta( $post_id, 'cfm_episode_itunes_type', sanitize_text_field( wp_unslash( $_POST['episode_type'] ) ) );

						// Episode explicit.
						$episode_info['explicit'] = sanitize_text_field( wp_unslash( $_POST['episode_explicit'] ) );
						update_post_meta( $post_id, 'cfm_episode_itunes_explicit', sanitize_text_field( wp_unslash( $_POST['episode_explicit'] ) ) );

						// SEO title.
						$episode_info['seo_title'] = sanitize_text_field( wp_unslash( $_POST['seo_title'] ) );
						update_post_meta( $post_id, 'cfm_episode_seo_title', sanitize_text_field( wp_unslash( $_POST['seo_title'] ) ) );

						// SEO description.
						$episode_info['seo_description'] = sanitize_text_field( wp_unslash( $_POST['seo_description'] ) );
						update_post_meta( $post_id, 'cfm_episode_seo_description', sanitize_text_field( wp_unslash( $_POST['seo_description'] ) ) );

						// Audio file.
						if ( isset( $_POST['media_id'] ) ) {
							$episode_info['media_id'] = sanitize_text_field( wp_unslash( $_POST['media_id'] ) );
							update_post_meta( $post_id, 'cfm_episode_media_id', sanitize_text_field( wp_unslash( $_POST['media_id'] ) ) );
						}
						if ( isset( $_POST['media_url'] ) ) {
							$enclosure = sanitize_text_field( $_POST['media_url'] ) . "\n" . sanitize_text_field( wp_unslash( $_POST['media_size'] ) ) . "\n" . sanitize_text_field( wp_unslash( $_POST['media_type'] ) ) . "\n" . serialize( array( 'duration' => sanitize_text_field( wp_unslash( $_POST['media_duration'] ) ) ) );
							update_post_meta( $post_id, 'enclosure', $enclosure );
							update_post_meta( $post_id, 'cfm_episode_media_url', sanitize_text_field( wp_unslash( $_POST['media_url'] ) ) );
						}
						else {
							update_post_meta( $post_id, 'cfm_episode_media_url', '' );
						}
						if ( isset( $_POST['media_created_at'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_created_at', sanitize_text_field( wp_unslash( $_POST['media_created_at'] ) ) );
						}
						if ( isset( $_POST['media_bit_rate'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_bit_rate', sanitize_text_field( wp_unslash( $_POST['media_bit_rate'] ) ) );
						}
						if ( isset( $_POST['media_bit_rate_str'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_bit_rate_str', sanitize_text_field( wp_unslash( $_POST['media_bit_rate_str'] ) ) );
						}
						if ( isset( $_POST['media_duration'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_duration', sanitize_text_field( wp_unslash( $_POST['media_duration'] ) ) );
						}
						if ( isset( $_POST['media_duration_str'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_duration_str', sanitize_text_field( wp_unslash( $_POST['media_duration_str'] ) ) );
						}
						if ( isset( $_POST['media_id3_size'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_id3_size', sanitize_text_field( wp_unslash( $_POST['media_id3_size'] ) ) );
						}
						if ( isset( $_POST['media_name'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_name', sanitize_text_field( wp_unslash( $_POST['media_name'] ) ) );
						}
						if ( isset( $_POST['media_size'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_size', sanitize_text_field( wp_unslash( $_POST['media_size'] ) ) );
						}
						if ( isset( $_POST['media_type'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_type', sanitize_text_field( wp_unslash( $_POST['media_type'] ) ) );
						}
						if ( isset( $_POST['media_shows_id'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_shows_id', sanitize_text_field( wp_unslash( $_POST['media_shows_id'] ) ) );
						}
						if ( isset( $_POST['media_updated_at'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_updated_at', sanitize_text_field( wp_unslash( $_POST['media_updated_at'] ) ) );
						}
						if ( isset( $_POST['media_users_id'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_media_users_id', sanitize_text_field( wp_unslash( $_POST['media_users_id'] ) ) );
						}

						// cfm_episode_status
						if ( !in_array( $episode_status, array( 'Exclusive', 'Early Access', 'Expired' ) ) ) {
							$post_status = get_post_status($post_id);
							switch ( $post_status ) {
								case 'publish':
									$cfm_episode_status = 'Published';
									break;
								case 'future':
									$cfm_episode_status = 'Scheduled';
									break;
								case 'draft':
									$cfm_episode_status = 'Draft';
									break;
								default:
									$cfm_episode_status = $episode_status;
									break;
							}
							update_post_meta($post_id, 'cfm_episode_status', $cfm_episode_status);
						}

						// cfm_episode_website_active
						$cfm_episode_website_active = get_post_meta($post_id, 'cfm_episode_website_active', true);
						if ( empty($cfm_episode_website_active) && $cfm_episode_website_active !== '0' ) {
							update_post_meta( $post_id, 'cfm_episode_website_active', '1' );
						}

						// episode_private
						$cfm_episode_private = get_post_meta($post_id, 'cfm_episode_private', true);
						if ( empty($cfm_episode_private) && $cfm_episode_private !== '1' ) {
							update_post_meta( $post_id, 'cfm_episode_private', '0' );
						}

						// Transcript.
						if ( (isset( $_POST['transcript_updated'] ) && '1' == $_POST['transcript_updated']) || isset( $_POST['transcript_current'] ) ) {
							if ( isset( $_FILES['transcript_file'] ) && $_FILES['transcript_file']['size'] != 0 ) {

								$transcript_allowed = array( 'srt' );
								$transcript_filename = $_FILES['transcript_file']['name'];
								$transcript_ext = pathinfo( $transcript_filename, PATHINFO_EXTENSION );

								if ( ! in_array( $transcript_ext, $transcript_allowed ) ) {
									$transcript = array();
								}
								else {
									$transcript = $_FILES['transcript_file'];
								}
							}
							else {
								if ( isset( $_POST['transcript_text'] ) ) {
								    $transcript = wp_unslash( wp_filter_kses( $_POST['transcript_text'] ) );
							    }
							}
						}

						// Custom field.
						if ( isset( $_POST['custom_field'] ) ) {
							update_post_meta( $post_id, 'cfm_episode_custom_field', wp_filter_post_kses( $_POST['custom_field'] ) );
						}

						// ACF
						if ( class_exists('ACF') ) {

							if ( isset( $_POST['acf_option_field_value'] ) ) {
								update_post_meta( $post_id, 'acf_option_field_value', sanitize_text_field( wp_unslash( $_POST['acf_option_field_value'] ) ) );
							}
							if ( isset( $_POST['acf_option_field_label'] ) ) {
								update_post_meta( $post_id, 'acf_option_field_label', sanitize_text_field( wp_unslash( $_POST['acf_option_field_label'] ) ) );
							}
							if ( isset( $_POST['acf_option_field_group_label'] ) ) {
								update_post_meta( $post_id, 'acf_option_field_group_label', sanitize_text_field( wp_unslash( $_POST['acf_option_field_group_label'] ) ) );
							}

							foreach ($_POST as $field_name => $field_value) {
								if ($field_name === 'submit') continue;

								if (strpos($field_name, CFMH_ACF_FIELD_PREFIX) === 0) {

									$original_field_name = str_replace(CFMH_ACF_FIELD_PREFIX, '', $field_name);
									$field_object = get_field_object($original_field_name, $post_id);

									switch ($field_object['type']) {
										case 'textarea':
											$textarea = $field_object['new_lines'] == 'br' ? nl2br($field_value) : ($field_object['new_lines'] == 'wpautop' ? wpautop($field_value) : esc_textarea($field_value));
											update_field($field_name, wp_filter_post_kses($textarea), $post_id);
										case 'wysiwyg':
											update_field($field_name, wp_filter_post_kses($field_value), $post_id);
											break;
										default:
											$sanitized_value = sanitize_text_field($field_value);
											update_field($field_name, $sanitized_value, $post_id);
											break;
									}

								}
							}

						}

						// Social Media
						update_post_meta( $post_id, 'cfm_episode_social_media_image_id', sanitize_text_field(wp_unslash($_POST['social_media_image_id'])));
						update_post_meta( $post_id, 'cfm_episode_social_media_image_url', sanitize_url($_POST['social_media_image_url']));
						update_post_meta( $post_id, 'cfm_episode_social_media_title', sanitize_text_field(wp_unslash($_POST['social_media_title'])));
						update_post_meta( $post_id, 'cfm_episode_social_media_description', sanitize_text_field(wp_unslash($_POST['social_media_description'])));

						update_post_meta( $post_id, 'cfm_episode_x_image_id', sanitize_text_field(wp_unslash($_POST['x_image_id'])));
						update_post_meta( $post_id, 'cfm_episode_x_image_url', sanitize_url($_POST['x_image_url']));
						update_post_meta( $post_id, 'cfm_episode_x_title', sanitize_text_field(wp_unslash($_POST['x_title'])));
						update_post_meta( $post_id, 'cfm_episode_x_description', sanitize_text_field(wp_unslash($_POST['x_description'])));

						$episode_info['title']     		= $post_title;
						$episode_info['shownotes'] 		= cfm_trim_lists_for_quill($shownotes);
						$episode_info['date']      		= date( 'Y/m/d H:i:s', strtotime( $post_datetime ) );
						$episode_info['via_sync']  		= true;
						$episode_info['amie_status']    = 'processing';
						$episode_info['captivate_episode_type'] = $captivate_episode_type;
						$episode_info['episode_private'] = (int) $cfm_episode_private;

						// Make sure that exclusive, expired, and early access episodes stays the same on update.
						if ( in_array( $episode_status, array( 'Exclusive', 'Early Access', 'Expired' ) ) || $exclusivity_date || $early_access_end_date ) {
							$episode_info['status'] = $episode_status;
							$episode_info['date'] = get_the_date( 'Y/m/d H:i:s', $post_id );
						}

						if ( $early_access_end_date ) {
							// early_access_end_date is expecting a field "early_access_end_date"
							$episode_info['early_access_end_date'] = date( 'Y/m/d H:i:s', strtotime( $early_access_end_date ) );
						}

						if ( $exclusivity_date ) {
							// exclusivity_date is expecting a field "exclusivity_date"
							$episode_info['exclusivity_date'] = date( 'Y/m/d H:i:s', strtotime( $exclusivity_date ) );
						}

						if ( $episode_expiration ) {
							// episode_expiration is expecting a field "episode_expiration_date"
							$episode_info['episode_expiration_date'] = date( 'Y/m/d H:i:s', strtotime( $episode_expiration ) );
						}

						// Make sure youtube video id and title are still there if it exists.
						$youtube_video_id = get_post_meta( $post_id, 'cfm_episode_youtube_video_id', true );
						$youtube_video_title = get_post_meta( $post_id, 'cfm_episode_youtube_video_title', true );
						if ( ! empty( $youtube_video_id ) ) {
							$episode_info['youtube_video_id'] = $youtube_video_id;
						}
						if ( ! empty( $youtube_video_title ) ) {
							$episode_info['youtube_video_title'] = $youtube_video_title;
						}

						if ( $cfm_episode_id && ( 'update' == $submit_action || 'draft' == $submit_action ) ) {

							$response = wp_remote_request(
								CFMH_API_URL . '/episodes/' . $cfm_episode_id,
								array(
									'timeout' => 500,
									'body'    => $episode_info,
									'method'  => 'PUT',
									'headers' => array(
										'Authorization' => 'Bearer ' . $auth_token,
									),
								)
							);

							// Debugging.
							cfm_generate_log( 'EDIT EPISODE (ID ' . $post_id . ')', $response );

							if ( ! is_wp_error( $response ) && 'Unauthorized' !== $response['body'] && is_array( $response ) ) {

								$body = json_decode( $response['body'] );

								if ( 403 == $response['response']['code'] ) {
									wp_redirect( admin_url( "admin.php?page=cfm-hosting-edit-episode&show_id={$show_id}&eid={$post_id}&response=4" ) );
								}

								if ( isset( $body->success ) && $body->success == true ) {

									// transcriptions.
									if ( isset( $_POST['transcript_updated'] ) && '1' == $_POST['transcript_updated'] ) {
										$update_transcript = cfm_update_transcript( $transcript, $cfm_episode_id );
										update_post_meta( $post_id, 'cfm_episode_transcript', $update_transcript );
									}

									// Rendered shownotes.
									$get_updated_captivate_episode = cfm_get_captivate_episode($cfm_episode_id);
									if ( $get_updated_captivate_episode ) {
										$captivate_episode_data = cfm_episodes_data_array( $get_updated_captivate_episode, $cfm_episode_id );
										$captivate_episode_shownotes_rendered = $captivate_episode_data['shownotes_rendered'];
										update_post_meta($post_id, 'cfm_episode_shownotes_rendered', $captivate_episode_shownotes_rendered);
									}

									wp_redirect( admin_url( "admin.php?page=cfm-hosting-edit-episode&show_id={$show_id}&eid={$post_id}&response=3" ) );
								}
								else {
									wp_redirect( admin_url( "admin.php?page=cfm-hosting-edit-episode&show_id={$show_id}&eid={$post_id}&response=5" ) );
								}

							}
							else {
								// api error.
								wp_redirect( admin_url( "admin.php?page=cfm-hosting-edit-episode&show_id={$show_id}&eid={$post_id}&response=6" ) );
							}

						}
						else {

							$response = wp_remote_post(
								CFMH_API_URL . '/episodes',
								array(
									'timeout' => 500,
									'body'    => $episode_info,
									'headers' => array(
										'Authorization' => 'Bearer ' . $auth_token,
									),
								)
							);

							// Debugging.
							cfm_generate_log( 'PUBLISH EPISODE (ID ' . $post_id . ')', $response );

							if ( ! is_wp_error( $response ) && 'Unauthorized' !== $response['body'] && is_array( $response ) ) {
								if ( 403 == $response['response']['code'] ) {
									wp_redirect( admin_url( 'admin.php?page=cfm-hosting-publish-episode&response=4' ) );
								}

								$body = json_decode( $response['body'] );

								if ( isset( $body->success ) && $body->episode ) {

									$captivate_episode_id = $body->episode->id ? $body->episode->id : $body->episode->episodes_id;

									update_post_meta( $post_id, 'cfm_episode_id', $captivate_episode_id );

									// transcriptions.
									if ( isset( $_POST['transcript_updated'] ) && '1' == $_POST['transcript_updated'] ) {

										// add only if transcript exists.
										if ( isset( $_POST['transcript_current'] ) ) {
											$update_transcript = cfm_update_transcript( $transcript, $captivate_episode_id );
											update_post_meta( $post_id, 'cfm_episode_transcript', $update_transcript );
										}
									}
									wp_redirect( admin_url( "admin.php?page=cfm-hosting-edit-episode&show_id={$show_id}&eid={$post_id}&response=2&action=published" ) );
								}
								else {
									wp_redirect( admin_url( "admin.php?page=cfm-hosting-edit-episode&show_id={$show_id}&eid={$post_id}&response=6&action=failed" ) );
								}
							}
							else {
								wp_redirect( admin_url( "admin.php?page=cfm-hosting-edit-episode&show_id={$show_id}&eid={$post_id}&response=6&action=failed" ) );
							}
						}
					}
				}
			}
		}

		/**
		 * Add category
		 *
		 * @since 1.0
		 * @return json
		 */
		public static function add_webcategory() {

			$output = '';

			if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {
				$output = 'error';
			}
			else {

				$parent   = isset( $_POST['category_parent'] ) ? sanitize_text_field( wp_unslash( $_POST['category_parent'] ) ) : '';
				$category = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';

				if ( ! empty( $category ) ) :

					$json = array();

					$inserted_cat = wp_insert_term( $category, 'captivate_category', array( 'parent' => $parent ) );

					$term = get_term_by( 'id', $inserted_cat['term_id'], 'captivate_category' );

					$json['cat_checklist'] = '<li id="captivate_category-' . esc_attr( $inserted_cat['term_id'] ) . '"><label class="selectit"><input value="' . esc_attr( $inserted_cat['term_id'] ) . '" type="checkbox" name="tax_input[captivate_category][]" id="in-captivate_category-' . esc_attr( $inserted_cat['term_id'] ) . '" checked="checked">' . esc_html( $term->name ) . '</label></li>';

					$args = array(
						'show_option_all'   => '',
						'show_option_none'  => '— Parent Category —',
						'option_none_value' => '-1',
						'orderby'           => 'name',
						'order'             => 'ASC',
						'show_count'        => 0,
						'hide_empty'        => 0,
						'child_of'          => 0,
						'exclude'           => '',
						'include'           => '',
						'echo'              => 0,
						'selected'          => 0,
						'hierarchical'      => 1,
						'name'              => 'category_parent',
						'id'                => '',
						'class'             => 'form-control',
						'depth'             => 0,
						'tab_index'         => 0,
						'taxonomy'          => 'captivate_category',
						'hide_if_empty'     => false,
						'value_field'       => 'term_id',
					);

					$json['cat_parent'] = wp_dropdown_categories( $args );

					if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
						$output = json_encode( $json );
					} else {
						$output = 'error';
					}

				endif;

			}

			echo $output;

			wp_die();
		}

		/**
		 * Add tags
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function add_webtags() {

			if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {
				echo 'error';
			}
			else {

				$tags = isset( $_POST['tags'] ) ? sanitize_text_field( wp_unslash( $_POST['tags'] ) ) : array();

				if ( ! empty( $tags ) ) :

					$separated_tags = explode( ',', $tags );

					foreach ( $separated_tags as $tag ) {
						$inserted_tag = wp_insert_term( $tag, 'captivate_tag' ); // optional insert without saving the post.

						$term = get_term_by( 'id', $inserted_tag['term_id'], 'captivate_tag' );

						echo '<li id="captivate_tag-' . esc_attr( $inserted_tag['term_id'] ) . '"><label class="selectit"><input value="' . esc_attr( $inserted_tag['term_id'] ) . '" type="checkbox" name="tax_input[captivate_tag][]" id="in-captivate_tag-' . esc_attr( $inserted_tag['term_id'] ) . '" checked="checked">' . esc_html( $term->name ) . '</label></li>';
					}

				endif;

			}

			wp_die();
		}

		/**
		 * Duplicate episode as draft
		 *
		 * @since 3.0.0
		 * @return string
		 */
		public static function duplicate_episode() {

			$json['output'] = 'error';
			$json['message'] = '<strong>ERROR:</strong> Something went wrong! Please refresh the page and try again.';

			if ( current_user_can( 'edit_others_posts' ) ) {

				$pid = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
				$cfm_show_id = get_post_meta( $pid, 'cfm_show_id', true );

				if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], 'duplicate_post_' . $pid ) ) {

					global $wpdb;

					// get post data.
					$post = get_post( $pid );

					// if post data exists, create the post duplicate.
					if ( isset( $post ) && $post != null ) {

						if ( 'captivate_podcast' == $post->post_type ) {

							$args = array(
								'comment_status' => $post->comment_status,
								'ping_status'    => $post->ping_status,
								'post_author'    => $post->post_author,
								'post_content'   => $post->post_content,
								'post_excerpt'   => $post->post_excerpt,
								'post_name'      => $post->post_name,
								'post_parent'    => $post->post_parent,
								'post_password'  => $post->post_password,
								'post_status'    => 'draft',
								'post_title'     => $post->post_title,
								'post_type'      => $post->post_type,
								'to_ping'        => $post->to_ping,
								'menu_order'     => $post->menu_order
							);

							// insert the post.
							$new_post_id = wp_insert_post( $args );

							// get all current post terms ad set them to the new post draft.
							$taxonomies = get_object_taxonomies( $post->post_type ); // returns array of taxonomy names for post type, ex array("category", "post_tag");
							foreach ( $taxonomies as $taxonomy ) {
								$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
								wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
							}

							// duplicate all post meta just in two SQL queries except some data.
							$post_meta_infos = $wpdb->get_results("
								SELECT meta_key, meta_value
								FROM $wpdb->postmeta
								WHERE post_id = $pid
								AND (
									meta_key <> 'enclosure'
									AND meta_key <> '_useraudiourl'

									AND meta_key <> 'cfm_episode_id'
									AND meta_key <> 'cfm_episode_status'
									AND meta_key <> 'cfm_episode_amie_status'

									AND meta_key <> 'cfm_episode_media_created_at'
									AND meta_key <> 'cfm_episode_media_id'
									AND meta_key <> 'cfm_episode_media_bit_rate'
									AND meta_key <> 'cfm_episode_media_bit_rate_str'
									AND meta_key <> 'cfm_episode_media_id3_size'
									AND meta_key <> 'cfm_episode_media_name'
									AND meta_key <> 'cfm_episode_media_size'
									AND meta_key <> 'cfm_episode_media_type'
									AND meta_key <> 'cfm_episode_media_url'
									AND meta_key <> 'cfm_episode_media_shows_id'
									AND meta_key <> 'cfm_episode_media_updated_at'
									AND meta_key <> 'cfm_episode_media_users_id'
									AND meta_key <> 'cfm_episode_media_duration'
									AND meta_key <> 'cfm_episode_media_duration_str'

									AND meta_key <> 'cfm_episode_artwork_id'
									AND meta_key <> 'cfm_episode_artwork'
									AND meta_key <> 'cfm_episode_artwork_width'
									AND meta_key <> 'cfm_episode_artwork_height'
									AND meta_key <> 'cfm_episode_artwork_type'
									AND meta_key <> 'cfm_episode_artwork_filesize'

									AND meta_key <> 'cfm_episode_transcript'

									AND meta_key <> 'cfm_episode_expiration'
									AND meta_key <> 'cfm_episode_early_access_end_date'
									AND meta_key <> 'cfm_episode_exclusivity_date'
								)
							");

							if ( count( $post_meta_infos ) !=0 ) {
								$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
								foreach ( $post_meta_infos as $meta_info ) {
									$meta_key = $meta_info->meta_key;
									$meta_value = addslashes($meta_info->meta_value);
									$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
								}
								$sql_query.= implode( " UNION ALL ", $sql_query_sel );
								$wpdb->query( $sql_query );
							}

							update_post_meta( $new_post_id, 'cfm_episode_duplicate', '1' );

							$json['output'] = 'success';
							$json['message'] = 'Draft episode created successfully.';
							$json['redirect_url'] = admin_url( 'admin.php?page=cfm-hosting-edit-episode&show_id=' . $cfm_show_id . '&eid=' . $new_post_id );
						}
						else {
							$json['message'] = 'Episode creation failed, invalid post type: ' . $post->post_type;
						}
					}
					else {
						$json['message'] = 'Episode creation failed, could not find original episode: ' . $pid;
					}

				}
			}

			if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
				$output = json_encode( $json );
			}

			echo $output;

			wp_die();
		}

		/**
		 * Change shownotes template
		 *
		 * @since 3.0.0
		 * @return string
		 */
		public static function change_shownotes_template() {

			$output = 'error';

			$show_id = isset( $_POST['show_id'] ) ? sanitize_text_field( wp_unslash( $_POST['show_id'] ) ) : '';
			$template_name = isset( $_POST['template_name'] ) ? sanitize_text_field( wp_unslash( $_POST['template_name'] ) ) : '';

			if ( '' != $show_id && '' != $template_name ) {
				$shownotes_template = cfm_get_dynamic_text( $show_id, array( 'shownotes_template' ), array( $template_name ) );

				$template_value = $shownotes_template[$template_name]['value'];

				$output = $template_value;
			}

			echo $output;

			wp_die();
		}

		/**
		 * Insert block
		 *
		 * @since 3.0.0
		 * @return string
		 */
		public static function insert_static_block() {

			$output = 'error';

			$show_id = isset( $_POST['show_id'] ) ? sanitize_text_field( wp_unslash( $_POST['show_id'] ) ) : '';
			$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
			$data_reference = isset( $_POST['data_reference'] ) ? sanitize_text_field( wp_unslash( $_POST['data_reference'] ) ) : '';

			if ( '' != $show_id && '' != $data_reference ) {
				$block = cfm_get_dynamic_text( $show_id, array( 'snippet' ), array( $data_reference ) );
				$exclude = array( 'd-episode-title', 'd-episode-number', 'd-episode-season', 'd-episode-type', 'd-episode-explicit' );
				$output = cfm_translate_dynamic_text( $show_id, $post_id, $block[$data_reference]['value'], $exclude );
			}

			echo $output;

			wp_die();
		}

		/**
		 * Insert shortcode
		 *
		 * @since 3.0.0
		 * @return string
		 */
		public static function insert_static_shortcode() {

			$output = 'error';

			$show_id = isset( $_POST['show_id'] ) ? sanitize_text_field( wp_unslash( $_POST['show_id'] ) ) : '';
			$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
			$data_reference = isset( $_POST['data_reference'] ) ? sanitize_text_field( wp_unslash( $_POST['data_reference'] ) ) : '';
			$data_type = isset( $_POST['data_type'] ) ? sanitize_text_field( wp_unslash( $_POST['data_type'] ) ) : 'custom';

			if ( '' != $show_id && '' != $data_reference ) {
				$exclude = array( 'd-episode-title', 'd-episode-number', 'd-episode-season', 'd-episode-type', 'd-episode-explicit' );
				if ( 'custom' == $data_type ) {
					$shortcode = cfm_get_dynamic_text( $show_id, array( 'variable' ), array( $data_reference ) );
					$output = cfm_translate_dynamic_text( $show_id, $post_id, $shortcode[$data_reference]['value'], $exclude );
				}
				else {
					$output = cfm_translate_dynamic_text( $show_id, $post_id, '{{' . $data_reference . '}}', $exclude );
				}
			}

			echo $output;

			wp_die();
		}

		/**
		 * Render dynamic text variables
		 *
		 * @since 3.0.0
		 * @return string
		 */
		public static function render_dt_variables() {

			$output = 'error';

			$show_id = isset( $_POST['show_id'] ) ? sanitize_text_field( wp_unslash( $_POST['show_id'] ) ) : '';
			$content = isset( $_POST['content'] ) ? wp_filter_post_kses( $_POST['content'] ) : '';
			$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0;
			$post_id = (int) $post_id;

			$dt = cfm_get_dynamic_text( $show_id, array( 'snippet', 'variable' ), array( 'all' ) );

			preg_match_all( '/{{([^{}]*)}}/', $content, $matches );
			$names_array = array();

			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $dt_var ) {

					// translate only valid slug.
					if ( preg_match( '/^[A-Za-z0-9]+(?:[_-][A-Za-z0-9]+)*$/', $dt_var ) ) {

						if ( strpos( $dt_var, 'd-show' ) !== false ) {
							$dt_show_selector = explode( 'd-show-', $dt_var );
							$dt_show_label = str_replace( '-', ' ', $dt_show_selector[1] );
							$marketing_links = cfm_get_show_marketing_links( $show_id );

							$name_human = 'Podcast ' . ucwords( $dt_show_label );

							// affiliate link.
							if ( 'd-show-affiliate-link' == $dt_var ) {
								if ( ! empty( $marketing_links ) && $marketing_links->affiliate ) {
									$name_human = 'Captivate Affiliate Link';
								}
								else {
									$name_human = 'Unrecognized Variable';
								}
							}

							// Tipping/Membership link.
							if ( 'd-show-support-link' == $dt_var ) {
								$name_human = 'Tipping/Membership Link';
							}

						}
						else if ( strpos( $dt_var, 'd-episode' ) !== false ) {
							$dt_episode_selector = explode( 'd-episode-', $dt_var );
							$dt_episode_label = str_replace( '-', ' ', $dt_episode_selector[1] );
							$name_human = 'Episode ' . ucwords( $dt_episode_label );
						}
						else if ( strpos( $dt_var, 'd-guest' ) !== false ) {
							$dt_guest_selector = explode( 'd-guest-', $dt_var );
							$name_human = ucwords( cfm_get_guest_dynamic_text( $show_id, $post_id, $dt_guest_selector[1], 'label' ) );
						}
						else if ( strpos( $dt_var, 'd-short-link' ) !== false ) {
							$dt_short_link_selector = explode( 'd-short-link-', $dt_var );
							$name_human = ucwords( cfm_get_attribution_link_dynamic_text( $show_id, $dt_short_link_selector[1], 'label' ) );
						}
						else if ( strpos( $dt_var, 'd-research-links-list' ) !== false ) {
							$name_human = 'Research Links';
						}
						else if ( strpos( $dt_var, 'd-condition-ep' ) !== false ) {
							$dt_condition_ep_selector = explode( 'd-condition-ep-', $dt_var );

							switch ( $dt_condition_ep_selector[1] ) {
								case "type-full":
									$ep_name_human = 'If (Episode is Full) {';
									break;
								case "type-trailer":
									$ep_name_human = 'If (Episode is Trailer) {';
									break;
								case "type-bonus":
									$ep_name_human = 'If (Episode is Bonus) {';
									break;
								case "has-guests":
									$ep_name_human = 'If (Episode has Guests) {';
									break;
								default:
									$ep_name_human = 'If (Episode is ' . str_replace( '-', ' ', $dt_condition_ep_selector[1] ) . ') {';
							}
							$name_human = $ep_name_human;
						}
						else if ( strpos( $dt_var, 'd-condition-end' ) !== false ) {
							$name_human = '} end if';
						}
						else {
							$name_human = ucwords( $dt[$dt_var]['name_human'] );
						}

						$names_array[$dt_var] = $name_human;

					}
				}
			}

			$output = json_encode( $names_array );

			echo $output;

			wp_die();
		}

		/**
		 * Render ACF field groups
		 *
		 * @since 3.0.1
		 * @return string
		 */
		public static function render_acf_field_groups($post_type ='captivate_podcast', $return = 'field_groups', $post_id = 0) {

			if ( class_exists('ACF') ) {
				$field_groups = acf_get_field_groups(array('post_type' => $post_type));

				switch ($return) {
					case 'exists':
						return ! empty($field_groups);

					case 'field_groups':

						if ( ! empty($field_groups) ) {

							foreach ( $field_groups as $field_group ) {
								$fields = acf_get_fields($field_group);

								if ( $fields ) {
									echo '<div class="acf-field-group acf-field-' . $field_group['key'] . '">';
									echo '<div class="acf-field-group-name">' . esc_html($field_group['title']) . '</div>';

									foreach ($fields as $field) {
										$field_name = CFMH_ACF_FIELD_PREFIX . $field['key'];
										$field_value = get_field($field_name, $post_id);
										$default_value = isset($field['default_value']) ? $field['default_value'] : '';
										$final_value = $field_value ?: $default_value;
										$field_required = isset($field['required']) && $field['required'] == 1;
										$required_class = $field_required ? ' required' : '';
										$required_label = $field_required ? ' <span>*</span>' : '';
										$field_label = $field['label'] ? $field['label'] : '(no label)';
										$placeholder = isset($field['placeholder']) && $field['placeholder'] ? ' placeholder="' . $field['placeholder'] . '"' : '';
										$maxlength = isset($field['maxlength']) && $field['maxlength'] ? ' maxlength="' . $field['maxlength'] . '"' : '';

										echo '<div class="acf-field acf-field-key' . $field['key'] . ' acf-field-type-' . $field['type'] . $required_class . '">';

											if ( in_array( $field['type'], CFMH_ACF_FIELDS_ALLOWED ) ) {
												echo '<label for="' . esc_attr($field_name) . '">' . esc_html($field_label) . $required_label . '</label>';

												switch ($field['type']) {
													case 'text':
														echo '<input type="text" name="' . esc_attr($field_name) . '" value="' . esc_attr($final_value) . '"' . $maxlength . $placeholder . ' />';
														break;
													case 'textarea':

														$rows = $field['rows'] ? ' rows="' . $field['rows'] . '"' : '';
														echo '<textarea name="' . esc_attr($field_name) . '" rows="' . esc_attr($field['rows']) . '" ' . $maxlength . $rows . $placeholder . '>' . esc_textarea($final_value) . '</textarea>';
														break;
													case 'select':
														echo '<select name="' . esc_attr($field_name) . '">';
														foreach ($field['choices'] as $value => $label) {
															$selected = ($value == $field_value || $value == $final_value) ? ' selected' : '';
															echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
														}
														echo '</select>';
														break;
													case 'radio':
														foreach ($field['choices'] as $value => $label) {
															$checked = ($value == $field_value || $value == $final_value) ? ' checked' : '';
															echo '<label><input type="radio" name="' . esc_attr($field_name) . '" value="' . esc_attr($value) . '" ' . $checked . ' /> ' . esc_html($label) . '</label>';
														}
														break;
													case 'wysiwyg':
														$media_upload = $field['media_upload'] ? true : false;
														$toolbar = $field['toolbar'] == 'full' ? false : true;
														$editor_settings = array(
															'media_buttons' => $media_upload,
															'textarea_name' => $field_name,
															'teeny' => $toolbar,
														);
														echo '<div class="acf-wysiwyg-container">';
															wp_editor($final_value, $field_name, $editor_settings);
														echo '</div>';
														break;
													case 'number':
														echo '<input type="number" name="' . esc_attr($field_name) . '" value="' . esc_attr($final_value) . '" min="' . esc_attr($field['min']) . '" max="' . esc_attr($field['max']) . '" step="' . esc_attr($field['step']) . '"' . $placeholder . ' />';
														break;
													case 'range':
														echo '<input type="range" name="' . esc_attr($field_name) . '" value="' . esc_attr($final_value) . '" min="' . esc_attr($field['min']) . '" max="' . esc_attr($field['max']) . '" step="' . esc_attr($field['step']) . '" />';
														break;
													case 'email':
														echo '<input type="email" name="' . esc_attr($field_name) . '" value="' . esc_attr($final_value) . '"' . $placeholder . ' />';
														break;
													case 'url':
														echo '<input type="url" name="' . esc_attr($field_name) . '" value="' . esc_attr($final_value) . '"' . $placeholder . ' />';
														break;
													case 'oembed':
														echo '<input type="text" name="' . esc_attr($field_name) . '" value="' . esc_attr($final_value) . '"' . $placeholder . ' />';
														break;
													default:
														break;
												}
											}

											if ( $field['instructions'] ) {
												echo '<div class="acf-field-instructions">' . esc_html($field['instructions']) . '</div>';
											}
										echo '</div>';
									}

									echo '</div>';
								}
							}
						}

						return $field_groups;

					default:
						return [];
				}
			}
			else {
				return [];
			}
		}

	}

endif;
