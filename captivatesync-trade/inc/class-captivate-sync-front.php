<?php
/**
 * User for front-end output/data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Front' ) ) :

	/**
	 * Hosting Front class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Front {

		static $page_id = 0;

		/**
		 * Enqueueu assets
		 *
		 * @since 1.1
		 */
		public static function assets() {

			wp_enqueue_script( 'cfmsync-player-api', CFMH_URL . 'captivate-sync-assets/js/dist/player-api-min.js', array(), CFMH_VERSION, true );

			if ( is_singular( 'captivate_podcast' ) ) {
				wp_enqueue_script( 'cfmsync-player-js', CFMH_URL . 'captivate-sync-assets/js/dist/player-js-min.js', array( 'jquery' ), CFMH_VERSION, true );
				wp_enqueue_style( 'cfmsync-front-style', CFMH_URL . 'captivate-sync-assets/css/dist/front-min.css' );
			}

			wp_enqueue_style( 'cfmsync-shortcode', CFMH_URL . 'captivate-sync-assets/css/dist/shortcode-min.css', array(), CFMH_VERSION );

			wp_register_script( 'cfmsync-shortcode-loadmore', 	CFMH_URL . 'captivate-sync-assets/js/dist/shortcode-loadmore-min.js', array( 'jquery' ), CFMH_VERSION, true );
			wp_localize_script(
				'cfmsync-shortcode-loadmore',
				'cfmsync_front',
				array(
					'ajaxurl'       				=> admin_url( 'admin-ajax.php' ),
					'ajaxnonce'     				=> wp_create_nonce( '_cfm_front_nonce' ),
				)
			);

		}

		/**
		 * Index page
		 *
		 * @since 1.0
		 * @param string $query  Query to search.
		 * @return query_set
		 */
		public static function index_page( $query ) {

			if ( empty( self::$page_id ) ) {
				self::$page_id = $query->queried_object_id;
			}

			$shows       = cfm_get_shows();
			$index_pages = array();

			if ( ! empty( $shows ) ) {
				foreach ( $shows as $show ) {
					if ( '' != $show['index_page'] ) {
						$index_pages[ $show['index_page'] ] = $show['id'];
					}
				}
			}

			if ( array_key_exists( self::$page_id, $index_pages ) && $query->is_main_query() && ! is_admin() ) {

				$theme = wp_get_theme();
				$show_id = $index_pages[ self::$page_id ];

				if ( cfm_get_show_info( $show_id, 'display_episodes' ) != '0' ) {

					// target Divi theme.
					if ( 'Divi' == $theme->name || 'Divi' == $theme->parent_theme ) {
						add_filter( 'template_include', 'cfm_index_page_template', 999 );

						/**
						 * Index page for divi
						 *
						 * @since 1.2.3
						 * @param string $template  Template for index.
						 * @return new template
						 */
						function cfm_index_page_template( $template ) {

							$index_page_template = locate_template( array( 'captivate.php', 'archive.php', 'index.php' ) );

							if ( '' != $index_page_template ) {
								return $index_page_template;
							}

							return $template;

						}

						$query->is_post_type_archive = true;

					}
					else {
						$query->is_archive	= true;
					}

					$query->is_page     = false;
					$query->is_singular	= false;
					$query->set( 'post_type', 'captivate_podcast' );
					$query->set( 'meta_key', 'cfm_show_id' );
					$query->set( 'meta_value', $show_id );

					add_filter( 'pre_option_page_for_posts', array( 'CFMH_Hosting_Front', 'pre_option_page_for_posts_function' ) );
					add_filter( 'pre_option_show_on_front', array( 'CFMH_Hosting_Front', 'pre_option_show_on_front_function' ) );

					/**
					 * Index page title
					 *
					 * @since 1.1.3
					 * @param array $title
					 * @return $title | $site_name
					 */
					add_filter( 'pre_get_document_title', 'index_page_title', 999 );
					function index_page_title( $title ) {

						return get_the_title( CFMH_Hosting_Front::$page_id ) . ' | ' . get_bloginfo( 'name' );

					}

					/**
					 * Archive page title
					 *
					 * @since 1.1.3
					 * @param array $title
					 * @return $title
					 */
					add_filter( 'get_the_archive_title', 'archive_page_title', 999 );
					function archive_page_title( $title ) {

						return get_the_title( CFMH_Hosting_Front::$page_id );

					}

				}

			}

		}

		/**
		 * Deactivate episodes
		 * Episodes that are inactivate, early access, and exclusive  will not appear on front-end and searches
		 * @since 3.0
		 * @param string $query  Query to search.
		 * @return query_set
		 */
		public static function deactivate_episodes( $query ) {

			if ( ! is_admin() && $query->is_main_query() ) {

				if ( $query->is_singular() || $query->is_post_type_archive() || $query->is_archive() || is_tax() || $query->is_search() || $query->is_home() || $query->is_feed() ) {

					$ids_array = array_unique( array_merge(
						cfm_get_inactive_episodes(),
						cfm_get_private_episodes(),
						cfm_get_episode_ids_by_status( array( 'Expired' ) ),
						cfm_get_episode_ids_by_type( array( 'exclusive', 'early' ) )
					) );

					$query->set( 'post__not_in', $ids_array );
				}
			}
		}
		public static function deactivate_episodes_robots($robots) {

			$ids_array = array_unique( array_merge(
				cfm_get_inactive_episodes(),
				cfm_get_private_episodes(),
				cfm_get_episode_ids_by_status(array('Expired')),
				cfm_get_episode_ids_by_type(array('exclusive', 'early'))
			) );

			if ( is_singular('captivate_podcast') && in_array(get_the_ID(), $ids_array) ) {
				$robots['noindex']  = true;
				$robots['nofollow'] = true;
			}

			return $robots;
		}

		/**
		 * Page for posts
		 *
		 * @since 1.0
		 * @return int
		 */
		public static function pre_option_page_for_posts_function() {
			return self::$page_id;
		}

		/**
		 * Page for front
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function pre_option_show_on_front_function() {
			return 'page';
		}

		/**
		 * Rewrite captivate_podcast slug
		 *
		 * @since 1.0
		 * @param array  $args  Arguements.
		 * @param string $post_type  Post type.
		 * @return array
		 */
		public static function register_post_type_args( $args, $post_type ) {

			if ( 'captivate_podcast' === $post_type ) {

				$post_slug = basename( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
				$post_id   = ( $post = get_page_by_path( $post_slug, OBJECT, 'captivate_podcast' ) ) ? $post->ID : 0;

				$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );

				$args['rewrite']['slug'] = cfm_get_show_page( $cfm_show_id, 'slug' );

				if ( ! is_admin() ) {
					flush_rewrite_rules();
				}
			}

			return $args;
		}

		/**
		 * Modify title output
		 *
		 * @since 3.0
		 */
		public static function title_filter( $title ) {

			global $post;

			if ( ! is_admin() && isset( $post ) && 'captivate_podcast' == get_post_type( $post->ID ) ) {

				$cfm_general_settings = get_option( 'cfm_general_settings' );
				$season_episode_number_enable = isset( $cfm_general_settings['season_episode_number_enable'] ) ? $cfm_general_settings['season_episode_number_enable'] : '';

				// per show.
				$cfm_show_id = get_post_meta( $post->ID, 'cfm_show_id', true );
				$show_se_number_enable = cfm_get_show_info( $cfm_show_id, 'season_episode_number_enable' );

				// output.
				if ( '1' == $show_se_number_enable || ( ( '1' != $show_se_number_enable && '0' != $show_se_number_enable ) && '1' == $season_episode_number_enable ) ) {
					$title = cfm_get_se_num_format( $post->ID ) . $title;
				}
			}

			return $title;
		}

		/**
		 * Modify content output
		 *
		 * @since 1.0
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function content_filter( $content ) {

			if ( ! class_exists( 'PWFT' ) ) {

				$output    = '';
				$post_id   = get_the_ID();
				$post_type = get_post_type( $post_id );

				if ( 'captivate_podcast' == $post_type ) {
					$output .= cfm_captivate_player( $post_id );
					$output .= $content;
				}
				else {
					$output .= $content;
				}
				return $output;
			}
			else {
				return $content;
			}

		}

		/**
		 * Show transcription.
		 *
		 * @since 2.0
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function content_transcript( $content ) {

			$output = $content;

			if ( is_singular( 'captivate_podcast' ) ) {

                $post_id   = get_the_ID();
                $transcript = get_post_meta( $post_id, 'cfm_episode_transcript', true );

                if ( is_array( $transcript ) && ! empty( $transcript ) ) {

                	if ( $transcript['transcription_text'] || $transcript['transcription_html'] ) {

	                    if ( $transcript['transcription_text'] ) {
							$array_of_lines = preg_split( '/\r\n|\r|\n/', $transcript['transcription_text'] );
	                        $transcript_content = '';

	                        foreach ( $array_of_lines as $line ) {
	                            preg_match( '/([a-zA-Z\W]{1,15}[a-zA-Z\W]{0,15})([0-9]{0,2}:?[0-9]{2}:?[0-9][0-9][ ]*)/', $line, $output_array );

	                            if ( $output_array ) {
	                                $transcript_content .= '<cite>'. trim( $output_array[1] ) . ':</cite><time> ' . $output_array[2] . '</time>';
	                            }
	                            else {
	                                $transcript_content .= '' != $line ? '<p>' . $line . '</p>' : '';
	                            }
	                        }
	                    }
						else {
	                        $html = curl_init( $transcript['transcription_html'] );
	                        curl_setopt( $html, CURLOPT_RETURNTRANSFER, TRUE );
	                        curl_setopt( $html, CURLOPT_FOLLOWLOCATION, TRUE );
	                        curl_setopt( $html, CURLOPT_AUTOREFERER, TRUE );
	                        $transcript_content = curl_exec( $html );
	                    }

	                    $output .= '<div class="cfm-transcript">';
	                        $output .= '<h5 class="cfm-transcript-title">Transcript</h5>';
	                        $output .= '<div class="cfm-transcript-content">' . $transcript_content . '</div>';
	                    $output .= '</div>';
	                }

                }

            }

			return $output;

		}

		/**
		 * Modify content output
		 *
		 * @since 2.0.2
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function pw_content_filter( $content ) {

			$output = $content;

			if ( class_exists( 'PWFT' ) && is_singular( 'captivate_podcast' ) ) {

				$cfm_episode_custom_field = get_post_meta( get_the_ID(), 'cfm_episode_custom_field', true );

				if ( $cfm_episode_custom_field ) {
					$output .= '<div id="cfm-custom-field" class="cfm-custom-field">' . $cfm_episode_custom_field . '</div>';
				}
			}

			return $output;

		}

		/**
		 * Modify content output to add the auto-timestamp
		 *
		 * @since 1.0
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function content_auto_timestamp( $content ) {

			$output = '';

			if ( is_singular( 'captivate_podcast' ) ) {

				// auto-timestamp pattern.
                $pattern = '/(?:[0-5]\d|2[0-3]):(?:[0-5]\d):?(?:[0-5]\d)?/';

				$found_timestamp = preg_replace_callback(
					$pattern,
					function ($m) {
						  return empty($m[1]) ? '<a href="javascript: void(0);" class="cp-timestamp" data-timestamp="'. $m[0] . '">'. $m[0] . '</a>' : $m[0];
					},
					$content
				);

				if ( $found_timestamp ) {
					$output = $found_timestamp;
				}

			}
			else {
				$output .= $content;
			}

			return $output;

		}

		/**
		 * Modify content output to translate dynamic text
		 *
		 * @since 1.0
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function content_dynamic_text( $content ) {

			$output    = '';
			$post_id   = get_the_ID();
			$post_type = get_post_type( $post_id );
			$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );

			if ( 'captivate_podcast' == $post_type ) {
				$output .= cfm_translate_dynamic_text( $cfm_show_id, $post_id, $content );
			}
			else {
				$output .= $content;
			}

			return $output;
		}

		/**
		 * Use artwork as featured image
		 *
		 * @since 3.0
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function use_artwork( $image, $attachment_id, $size, $icon ) {

			$post_id = get_the_ID();

			if ( ! is_admin() && 'captivate_podcast' == get_post_type( $post_id ) ) {

				$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );
				$cfm_episode_artwork = get_post_meta( $post_id, 'cfm_episode_artwork', true );
				$cfm_episode_artwork = ( $cfm_episode_artwork ) ? $cfm_episode_artwork : cfm_get_show_artwork( $cfm_show_id, $size = 'full' );
				$use_artwork = cfm_get_show_info( $cfm_show_id, 'use_artwork_as_featured_image' );

				if ( '1' == $use_artwork ) {
					$image[0] = $cfm_episode_artwork;
					$image[1] = 1400;
					$image[2] = 1400;
				}
			}

			return $image;
		}
		public static function filter_has_post_thumbnail() {

			global $post;
			$post_id = $post->ID;
			$thumbnail_id  = get_post_thumbnail_id( $post );
    		$has_thumbnail = (bool) $thumbnail_id;

			if ( ! is_admin() && 'captivate_podcast' == get_post_type( $post_id ) ) {

				$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );

				$use_artwork = cfm_get_show_info( $cfm_show_id, 'use_artwork_as_featured_image' );

				if ( '1' == $use_artwork || 'if_empty' == $use_artwork ) {
					return true;
				}
			}

			return ( $has_thumbnail ) ? true : false;
		}
		public static function default_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {

			if ( ! is_admin() && 'captivate_podcast' == get_post_type( $post_id ) ) {

				$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );
				$cfm_episode_artwork = get_post_meta( $post_id, 'cfm_episode_artwork', true );
				$cfm_episode_artwork = ( $cfm_episode_artwork ) ? $cfm_episode_artwork : cfm_get_show_artwork( $cfm_show_id, $size = 'full' );
				$use_artwork = cfm_get_show_info( $cfm_show_id, 'use_artwork_as_featured_image' );

				if ( '1' == $use_artwork || 'if_empty' == $use_artwork ) {
					if ( $html == '' ) {
						return sprintf("<img src=\"%s\" alt=\"%s\" />", esc_url( $cfm_episode_artwork ), esc_url( get_the_title() ) );
					}
				}
			}

			return $html;
		}

		/**
		 * Edit post link
		 *
		 * @since 1.0
		 * @param string $link Link for episode.
		 * @return string
		 */
		public static function edit_post_link( $link ) {
			global $post;
			$post_id     = $post->ID;
			$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );

			$captivate_edit_link = '<a class="post-edit-link" href="' . esc_url( admin_url( 'admin.php?page=cfm-hosting-edit-episode&show_id=' . $cfm_show_id . '&eid=' . $post_id ) ) . '">Edit <span class="screen-reader-text">' . $post->post_title . '</span></a>';

			return ( 'captivate_podcast' === get_post_type() ) ? $captivate_edit_link : $link;
		}

		/**
		 * Add meta tags
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function add_meta_data() {

			if ( is_singular( 'captivate_podcast' ) ) {

				global $post;
				$post_id = $post->ID;

				$cfm_show_id 			= get_post_meta( $post_id, 'cfm_show_id', true );
				$cfm_episode_id        	= get_post_meta( $post_id, 'cfm_episode_id', true );
				$cfm_episode_title     	= get_the_title( $post_id );
				$cfm_episode_shownotes 	= cfm_limit_characters( get_the_excerpt(), 140, '' );
				$cfm_episode_content   	= cfm_limit_characters( get_the_excerpt(), 152, '' );
				$cfm_episode_artwork   	= get_post_meta( $post_id, 'cfm_episode_artwork', true );
				$cfm_episode_artwork   	= ( $cfm_episode_artwork ) ? $cfm_episode_artwork : cfm_get_show_artwork( $cfm_show_id, $size = 'full' );

				$og_image 						= ( has_post_thumbnail( $post_id ) ) ? get_the_post_thumbnail_url( $post_id,  'full' ) : $cfm_episode_artwork;
				$cfm_episode_seo_title   		= get_post_meta( $post_id, 'cfm_episode_seo_title', true );
				$cfm_episode_seo_description   	= get_post_meta( $post_id, 'cfm_episode_seo_description', true );

				$cfm_episode_media_url = get_post_meta( $post_id, 'cfm_episode_media_url', true );

				// twitter data.
				echo '	<meta property="twitter:card" content="player" />' . "\n";
				echo '	<meta property="twitter:player" content="' . CFMH_PLAYER_URL . '/episode/' . esc_attr( $cfm_episode_id ) . '/twitter/">' . "\n";
				echo '	<meta name="twitter:player:width" content="540">' . "\n";
				echo '	<meta name="twitter:player:height" content="177">' . "\n";
				echo '	<meta property="twitter:title" content="' . esc_attr( $cfm_episode_seo_title ? $cfm_episode_seo_title : $cfm_episode_title ) . '">' . "\n";
				echo '	<meta property="twitter:description" content="' . esc_attr( $cfm_episode_seo_description ? $cfm_episode_seo_description : $cfm_episode_shownotes ) . '">' . "\n";
				echo '	<meta property="twitter:site" content="@CaptivateAudio">' . "\n";
				echo '	<meta property="twitter:image" content="' . esc_attr( $og_image ) . '" />' . "\n";

				// og data.
				if ( $cfm_episode_seo_title || $cfm_episode_title ) {
					echo '	<meta property="og:title" content="' . esc_attr( $cfm_episode_seo_title ? $cfm_episode_seo_title : $cfm_episode_title ) . '">' . "\n";
				}
				echo '	<meta property="og:description" content="' . esc_attr( $cfm_episode_seo_description ? $cfm_episode_seo_description : $cfm_episode_content . '...' ) . '">' . "\n";
				echo '	<meta property="description" content="' . esc_attr( $cfm_episode_seo_description ? $cfm_episode_seo_description : $cfm_episode_content . '...' ) . '">' . "\n";
				echo '	<meta property="og:image" content="' . esc_attr( $og_image ) . '" />' . "\n";

				// og audio.
				if ( $cfm_episode_media_url ) {
					echo '	<meta property="og:audio" content="' . esc_attr( cfm_add_media_prefixes ( $cfm_show_id, $cfm_episode_media_url ) ) . '" />' . "\n";
					echo '	<meta property="og:audio:type" content="audio/mpeg">' . "\n";
				}

			}

		}

		/**
		 * Redirect old slug - default redirect not working due to page mapping flush_rewrite_rules()
		 * See method register_post_type_args() above
		 *
		 * @since 3.0
		 * @return string
		 */
		public static function redirect_old_slug() {

			if ( is_404() && '' !== get_query_var( 'name' ) ) {


				$post_type = 'captivate_podcast';
				// Do not attempt redirect for hierarchical post types.
				if ( is_post_type_hierarchical( $post_type ) ) {
					return;
				}

				$id = _find_post_by_old_slug( $post_type );

				if ( ! $id ) {
					$id = _find_post_by_old_date( $post_type );
				}

				/**
				 * Filters the old slug redirect post ID.
				 *
				 * @param int $id The redirect post ID.
				 */
				$id = apply_filters( 'old_slug_redirect_post_id', $id );

				if ( ! $id ) {
					return;
				}

				// do the redirect only for podcasts with index page.
				$cfm_show_id = get_post_meta( $id, 'cfm_show_id', true );
				$index_page = cfm_get_show_info( $cfm_show_id, 'index_page' );

				if ( $index_page ) {

					$link = get_permalink( $id );

					if ( get_query_var( 'paged' ) > 1 ) {
						$link = user_trailingslashit( trailingslashit( $link ) . 'page/' . get_query_var( 'paged' ) );
					} elseif ( is_embed() ) {
						$link = user_trailingslashit( trailingslashit( $link ) . 'embed' );
					}

					/**
					 * Filters the old slug redirect URL.
					 *
					 * @param string $link The redirect URL.
					 */
					$link = apply_filters( 'old_slug_redirect_url', $link );

					if ( ! $link ) {
						return;
					}

					wp_redirect( $link, 301 ); // Permanent redirect.
					exit;

				}
			}

		}

		/**
		 * Add show feed rss
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function add_show_feed_rss() {
			$shows = cfm_get_shows();

			if ( ! empty( $shows ) ) {

				$queried_object = get_queried_object();
				$queried_object_id = $queried_object->ID ?? 'CFM_NULL';

				foreach ( $shows as $show ) {
					if ( $queried_object_id == $show['index_page'] ) {
						echo '<link rel="alternate" type="application/rss+xml" title="RSS feed for ' . esc_attr( $show['title'] ) . '" href="' . esc_url( $show['feed_url'] ) . '" />' . "\n";
					}
				}

			}
		}

		/**
		 * Display ACF fields
		 *
		 * @since 3.0.1
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function acf_fields_on_content($content) {

			if ( class_exists('ACF') && is_singular('captivate_podcast') ) {

				$output    = '';
				$acf_output = '';
				$post_id   = get_the_ID();
				$post_type = get_post_type($post_id);
				$acf_option_field_value = get_post_meta($post_id, 'acf_option_field_value', true);
				$acf_option_field_label = get_post_meta( $post_id, 'acf_option_field_label', true );
				$acf_option_field_group_label = get_post_meta( $post_id, 'acf_option_field_group_label', true );
				$field_groups = acf_get_field_groups(array('post_type' => $post_type));

				if ( ! empty($field_groups) && in_array($acf_option_field_value, array('above', 'below') ) ) {

					$acf_output .= '<div class="cfm-acf-container cfm-acf-' . esc_attr($acf_option_field_value) . '-content">';
					foreach ( $field_groups as $field_group ) {
						$fields = acf_get_fields($field_group);

						if ( $fields ) {
							$acf_output .= '<div class="cfm-acf-field-group">';

								if ( $acf_option_field_group_label == 'yes' ) {
									$acf_output .= '<div class="cfm-acf-field-group-name">' . esc_html($field_group['title']) . '</div>';
								}

								foreach ($fields as $field) {

									$field_name = CFMH_ACF_FIELD_PREFIX . $field['key'];
									$field_value = get_field($field_name, $post_id);
									$field_object = get_field_object($field['key'], $post_id);

									if ( ! empty($field_value) ) {

										if ( in_array( $field['type'], CFMH_ACF_FIELDS_ALLOWED ) ) {

											$acf_output .= '<div class="cfm-acf-field ' . $field_name . '">';

											if ( $acf_option_field_label == 'yes' ) {
												$acf_output .= '<div class="cfm-acf-field-label">' . esc_html($field['label']) . '</div>';
											}

											switch ( $field['type'] ) {
												case 'text':
													$acf_output .= '<div class="cfm-acf-field-value">' . esc_html($field_value) . '</div>';
													break;
												case 'email':
													$acf_output .= '<div class="cfm-acf-field-value"><a href="mailto:' . esc_attr($field_value) . '">' . esc_html($field_value) . '</a></div>';
													break;
												case 'textarea':
													$textarea = $field_object['new_lines'] == 'br' ? nl2br($field_value) : ($field_object['new_lines'] == 'wpautop' ? wpautop($field_value) : esc_textarea($field_value));
													$acf_output .= '<div class="cfm-acf-field-value">' . wp_kses_post($textarea) . '</div>';
													break;
												case 'select':
													if ( isset($field_object['choices']) ) {
														$selected_label = isset($field_object['choices'][$field_value]) ? $field_object['choices'][$field_value] : 'No choice selected';
														$acf_output .= '<div class="cfm-acf-field-value">' . esc_html($selected_label) . '</div>';
													}
													break;
												case 'wysiwyg':
													$acf_output .= '<div class="cfm-acf-field-value">' . wp_kses_post(wpautop($field_value)) . '</div>';
													break;
												case 'url':
													$acf_output .= '<div class="cfm-acf-field-value"><a href="' . esc_url($field_value) . '" target="_blank">' . esc_html($field_value) . '</a></div>';
													break;
												case 'oembed':
													$oembed   = new WP_oEmbed();
													$provider = $oembed->get_provider( $field_value, [ 'discover' => false ] );

													if ( false !== $provider ) {
														$acf_output .= '<div class="cfm-acf-field-value">' . wp_oembed_get($field_value) . '</div>';
													}
													else {
														$acf_output .= '<div class="cfm-acf-field-value"><a href="' . esc_url($field_value) . '" target="_blank">' . esc_html($field_value) . '</a></div>';
													}
													break;
												default:
													$acf_output .= '<div class="cfm-acf-field-value">' . esc_html($field_value) . '</div>';
													break;
											}

											$acf_output .= '</div>';

										}

									}
								}

							$acf_output .= '</div>';
						}
					}
					$acf_output .= '</div>';

				}

				if ( 'above' == $acf_option_field_value ) {
					$output .= $acf_output;
					$output .= $content;
				} else if ( 'below' == $acf_option_field_value ) {
					$output .= $content;
					$output .= $acf_output;
				}
				else {
					$output .= $content;
				}

				return $output;
			}
			else {
				return $content;
			}

		}

	}

endif;
