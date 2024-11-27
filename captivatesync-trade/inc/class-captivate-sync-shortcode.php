<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'CFM_Hosting_Shortcode' ) ) :

class CFM_Hosting_Shortcode {

	/**
	 * Enqueue assets
	 *
	 * @since 2.0.1
	 */
	public static function assets() {

		$current_screen = get_current_screen();

		$generate_shortcode_screens = array(
			'toplevel_page_cfm-hosting-shortcode',
			'admin_page_cfm-hosting-shortcode',
			'captivate-sync_page_cfm-hosting-shortcode',
		);

		if ( in_array( $current_screen->id, $generate_shortcode_screens ) ) :

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker');

			wp_enqueue_script( 'cfmsync-generate-shortcode', CFMH_URL . 'captivate-sync-assets/js/dist/generate-shortcode-min.js', array(), CFMH_VERSION, true );

		endif;
	}

	/**
	 * Shortcode - episodes list
	 *
	 * @since 2.0.1
	 */
    public static function episodes_list( $atts ) {

		$output = '';
		static $i = 0; $i++;

		// attributes
		$a = shortcode_atts( array(
			'show_id' 				=> '',
			'episode_id' 			=> '',
			'layout' 				=> 'list',
			'columns' 				=> '3',
			'title' 				=> 'show',
			'title_tag' 			=> 'h2',
			'se_num' 				=> 'default',
			'title_color' 			=> '',
			'title_hover_color' 	=> '',
			'image' 				=> 'show',
			'image_size' 			=> 'large',
			'player' 				=> 'above_content',
			'link' 					=> 'show',
			'link_text' 			=> 'Listen to this episode',
			'link_text_color' 		=> '',
			'link_text_hover_color' => '',
			'content' 				=> 'show',
			'content_length' 		=> 55,
			'order' 				=> 'DESC',
			'items' 				=> 10,
			'pagination' 			=> 'numbers',
			'exclude' 				=> 'no',
			'load_more_text' 		=> 'Load More',
			'load_more_class' 		=> '',
		), $atts );

		$show_ids = ( '' != $a['show_id'] ) ? explode( ',', $a['show_id'] ) : array();
		$episode_ids = ( '' != $a['episode_id'] ) ? explode( ',', $a['episode_id'] ) : array();

		if ( ! empty( $show_ids ) || ! empty( $episode_ids ) ) :

			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			$podcasts = ( ! empty( $episode_ids ) ) ? cfm_get_show_ids() : $show_ids;
			$orderby = ( 'episodes' == $a['order'] ) ? 'post__in' : 'date';

			$get_episodes = array(
				'post_type'      => 'captivate_podcast',
				'posts_per_page' => (int) $a['items'],
				'orderby'		 => $orderby,
				'order'          => $a['order'],
				'post_status'    => array( 'publish' ),
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => 'cfm_show_id',
						'value'   => $podcasts,
						'compare' => 'IN',
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => 'cfm_episode_website_active',
							'value'   => '1',
							'compare' => '='
						),
						array(
							'key'     => 'cfm_episode_website_active',
							'compare' => 'NOT EXISTS',
						)
					),
					array(
						'key'     => 'cfm_episode_status',
						'value'   => array('Exclusive', 'Early Access', 'Expired'),
						'compare' => 'NOT IN',
					)
				),
				'paged' => $paged,
			);

			// display only selected episodes.
			if ( ! empty( $episode_ids ) ) {
				$get_episodes['post__in'] = $episode_ids;
			}

			// exclude first episode.
			if ( $a['exclude'] == 'yes' ) {
				$get_episodes['offset'] = 1;
			}

			$episodes = new WP_Query( $get_episodes );

			if ( $episodes->have_posts() ) :

				$cfm_general_settings = get_option( 'cfm_general_settings' );
				$season_episode_number_enable = isset( $cfm_general_settings['season_episode_number_enable'] ) ? $cfm_general_settings['season_episode_number_enable'] : '';

				if ( 'hide' == $a['se_num'] ) {
					remove_action( 'the_title', 'CFMH_Hosting_Front::title_filter' );
				}

				$output .= '<div id="cfm-shortcode-wrap-' . $i . '" class="cfm-shortcode-wrap">';

					$layout_class = $a['layout'] == 'grid' ? 'cfm-episodes-grid' : 'cfm-episodes-list';
					$column_class = $a['layout'] == 'grid' ? ' cfm-episodes-cols-' . $a['columns'] : '';

					// output style if at least one color is set.
					if ( '' != $a['title_color'] || $a['title_hover_color'] || $a['link_text_color'] || $a['link_text_hover_color'] ) {
						$output .= '<style>';
							$output .= ( '' != $a['title_color'] ) ? '#cfm-episodes-' . $i . ' .cfm-episode-title a{color:' . sanitize_hex_color( $a['title_color'] ) . ';}' : '';
							$output .= ( '' != $a['title_hover_color'] ) ? '#cfm-episodes-' . $i . ' .cfm-episode-title a:hover{color:' . sanitize_hex_color( $a['title_hover_color'] ) . ';}' : '';
							$output .= ( '' != $a['link_text_color'] ) ? '#cfm-episodes-' . $i . ' .cfm-episode-link a {color:' . sanitize_hex_color( $a['link_text_color'] ) . ';}' : '';
							$output .= ( '' != $a['link_text_hover_color'] ) ? '#cfm-episodes-' . $i . ' .cfm-episode-link a:hover{color:' . sanitize_hex_color( $a['link_text_hover_color'] ) . ';}' : '';
						$output .= '</style>';
					}

					$output .= '<div id="cfm-episodes-' . $i . '" class="' . esc_attr( $layout_class ) . esc_attr( $column_class ) . '">';

						while ( $episodes->have_posts() ) :

							$episodes->the_post();
							$post_id = get_the_ID();
							$episode_title = get_the_title();
							$featured_image_class = has_post_thumbnail( $post_id ) && ( $a['image'] == 'left' || $a['image'] == 'right' ) && $a['layout'] == 'list' ? ' cfm-has-image-beside' : '';
							$player = '<div class="cfm-episode-player">' . cfm_captivate_player( $post_id ) . '</div>';

							$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );
							$show_se_number_enable = cfm_get_show_info( $cfm_show_id, 'season_episode_number_enable' );

							// season and episode number.
							if ( 'show' == $a['se_num'] && '1' != $season_episode_number_enable && '1' != $show_se_number_enable ) {
								$episode_title = cfm_get_se_num_format( $post_id ) . get_the_title();
							}

							// title tag.
							$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p');
							$title_tag = in_array( $a['title_tag'],  $allowed_tags ) ? $a['title_tag'] : 'h2';

							$output .= '<div class="cfm-episode-wrap' . $featured_image_class . '">';

								// featured image left container start.
								if ( has_post_thumbnail( $post_id ) && $a['image'] == 'left' && $a['layout'] == 'list' )
									$output .= '<div class="cfm-episode-image-left"><div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, $a['image_size'] ) . '</a></div>';

								// featured image left container end, content right start.
								if ( has_post_thumbnail( $post_id ) && $a['image'] == 'left' && $a['layout'] == 'list' )
									$output .= '</div><div class="cfm-episode-content-right">';

								// content left start.
								if ( has_post_thumbnail( $post_id ) && $a['image'] == 'right' && $a['layout'] == 'list' )
									$output .= '<div class="cfm-episode-content-left">';

								// featured image above title.
								if ( has_post_thumbnail( $post_id ) && $a['image'] == 'above_title' )
									$output .= '<div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, $a['image_size'] ) . '</a></div>';

								// title.
								if ( $a['title'] == 'show' )
									$output .= '<div class="cfm-episode-title"><' . $title_tag . '><a href="' . get_permalink() . '">' . $episode_title . '</a></' . $title_tag . '></div>';

								// featured image below title.
								if ( has_post_thumbnail( $post_id ) && $a['image'] == 'below_title' )
									$output .= '<div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, $a['image_size'] ) . '</a></div>';

								// player above content.
								if ( $a['player'] == 'above_content' )
									$output .= $player;

								// content excerpt.
								if ( $a['content'] == 'excerpt' )
									$output .= '<div class="cfm-episode-content">' . wp_trim_words( get_the_excerpt(), $a['content_length'], '...' ) . '</div>';

								// content full text.
								if ( $a['content'] == 'fulltext' )
									$output .= '<div class="cfm-episode-content">' . get_the_content() . '</div>';

								// player below content.
								if ( $a['player'] == 'below_content' )
									$output .= $player;

								// permalink.
								if ( $a['link'] == 'show' )
									$output .= '<div class="cfm-episode-link"><a href="' . get_permalink() . '">' . esc_html( $a['link_text'] ) . '</a></div>';

								// content right end.
								if ( has_post_thumbnail( $post_id ) && $a['image'] == 'left' && $a['layout'] == 'list' )
									$output .= '</div>';

								// content left end, featured image right container start.
								if ( has_post_thumbnail( $post_id ) && $a['image'] == 'right' && $a['layout'] == 'list' )
									$output .= '</div><div class="cfm-episode-image-right"><div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, $a['image_size'] ) . '</a></div></div>';

							$output .= '</div>';

						endwhile;

					$output .= '</div>';

					// pagination.
					if ( $episodes->found_posts > (int) $a['items'] ) {

						// numbers - add "show" checking for those who uses the old shortcode.
						if ( $a['pagination'] == 'show' || $a['pagination'] == 'numbers' ) {

							$GLOBALS['wp_query']->max_num_pages = $episodes->max_num_pages;
							$pagination = get_the_posts_pagination( array(
							   'mid_size' => 1,
							   'prev_text' => __( 'Previous' ),
							   'next_text' => __( 'Next' ),
							   'screen_reader_text' => __( 'Episodes navigation' )
							) );

							$output .= '<div id="cfm-pagination-' . $i . '" class="cfm-episodes-pagination">' . $pagination . '</div>';
						}

						// load more.
						if ( $a['pagination'] == 'load_more' ) {
							$load_more_text = ( '' != $a['load_more_text'] ) ? $a['load_more_text'] : 'Load More';
							$load_more_class = ( '' != $a['load_more_class'] ) ? ' class="' . esc_attr( $a['load_more_class'] ) . '"' : '';
							$output .= '<div id="cfm-loadmore-' . $i . '" class="cfm-episodes-loadmore">';
								$output .= '<button
												data-shortcode-id="' . esc_attr( $i ) . '"
												data-shortcode-atts="' . esc_attr( base64_encode(serialize( $atts ) ) ) . '"
												data-max-page="' . esc_attr( $episodes->max_num_pages ) . '"
												data-current-page="' . esc_attr( $paged ) . '"
											' . $load_more_class . '>' . esc_html( $load_more_text ) . '</button>';
							$output .= '</div>';

							wp_enqueue_script( 'cfmsync-shortcode-loadmore' );
						}

					}

				$output .= '</div>';

				wp_reset_postdata();

			else :

				$output .= '<div><p>Nothing found. Please check your show/episode id.</p></div>';

			endif;

		else :
			$output .= '<div><p>Show doesn\'t exists. Please check your show/episode id.</p></div>';
		endif;

		return $output;

    }

	/**
	 * Shortcode - Load more
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function shortcode_loadmore() {

		$output = '';

		if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], '_cfm_front_nonce' ) ) {
			$output = '<p><strong>ERROR:</strong> Something went wrong! Please refresh the page and try again.</p>';
		}
		else {
			$a = unserialize( base64_decode( $_POST['shortcode_atts'] ) );
			$show_ids = ( '' != $a['show_id'] ) ? explode( ',', $a['show_id'] ) : array();
			$episode_ids = ( '' != $a['episode_id'] ) ? explode( ',', $a['episode_id'] ) : array();

			if ( ! empty( $show_ids ) || ! empty( $episode_ids ) ) {

				$paged = sanitize_text_field( wp_unslash( $_POST['current_page'] ) );
				$podcasts = ( ! empty( $episode_ids ) ) ? cfm_get_show_ids() : $show_ids;
				$orderby = ( 'episodes' == $a['order'] ) ? 'post__in' : 'date';

				$get_episodes = array(
					'post_type'      => 'captivate_podcast',
					'posts_per_page' => (int) $a['items'],
					'orderby'		 => $orderby,
					'order'          => $a['order'],
					'post_status'    => array( 'publish' ),
					'meta_query'     => array(
						array(
							'key'     => 'cfm_show_id',
							'value'   => $podcasts,
							'compare' => 'IN',
						),
						array(
							'relation' => 'OR',
							array(
								'key'     => 'cfm_episode_website_active',
								'value'   => '1',
								'compare' => '='
							),
							array(
								'key'     => 'cfm_episode_website_active',
								'compare' => 'NOT EXISTS',
							)
						)
					),
					'paged' => $paged,
				);

				if ( ! empty( $episode_ids ) ) {
					$get_episodes['post__in'] = $episode_ids;
				}

				$episodes = new WP_Query( $get_episodes );

				if ( $episodes->have_posts() ) {

					$cfm_general_settings = get_option( 'cfm_general_settings' );
					$season_episode_number_enable = isset( $cfm_general_settings['season_episode_number_enable'] ) ? $cfm_general_settings['season_episode_number_enable'] : '';

					$layout_class = $a['layout'] == 'grid' ? 'cfm-episodes-grid' : 'cfm-episodes-list';
					$column_class = $a['layout'] == 'grid' ? ' cfm-episodes-cols-' . $a['columns'] : '';

					// output style if at least one color is set.
					if ( '' != $a['title_color'] || $a['title_hover_color'] || $a['link_text_color'] || $a['link_text_hover_color'] ) {
						$output .= '<style>';
							$output .= ( '' != $a['title_color'] ) ? '#cfm-episodes-' . $i . ' .cfm-episode-title a{color:' . sanitize_hex_color( $a['title_color'] ) . ';}' : '';
							$output .= ( '' != $a['title_hover_color'] ) ? '#cfm-episodes-' . $i . ' .cfm-episode-title a:hover{color:' . sanitize_hex_color( $a['title_hover_color'] ) . ';}' : '';
							$output .= ( '' != $a['link_text_color'] ) ? '#cfm-episodes-' . $i . ' .cfm-episode-link a {color:' . sanitize_hex_color( $a['link_text_color'] ) . ';}' : '';
							$output .= ( '' != $a['link_text_hover_color'] ) ? '#cfm-episodes-' . $i . ' .cfm-episode-link a:hover{color:' . sanitize_hex_color( $a['link_text_hover_color'] ) . ';}' : '';
						$output .= '</style>';
					}

					while ( $episodes->have_posts() ) :

						$episodes->the_post();
						$post_id = get_the_ID();
						$episode_title = get_the_title();
						$featured_image_class = has_post_thumbnail( $post_id ) && ( $a['image'] == 'left' || $a['image'] == 'right' ) && $a['layout'] == 'list' ? ' cfm-has-image-beside' : '';
						$player = '<div class="cfm-episode-player">' . cfm_captivate_player( $post_id ) . '</div>';

						$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );
						$show_se_number_enable = cfm_get_show_info( $cfm_show_id, 'season_episode_number_enable' );

						// season and episode number.
						if ( 'show' == $a['se_num'] && '1' != $season_episode_number_enable && '1' != $show_se_number_enable ) {
							$episode_title = cfm_get_se_num_format( $post_id ) . get_the_title();
						}

						// title tag.
						$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p');
						$title_tag = in_array( $a['title_tag'],  $allowed_tags ) ? $a['title_tag'] : 'h2';

						$output .= '<div class="cfm-episode-wrap' . $featured_image_class . '">';

							// featured image left container start.
							if ( has_post_thumbnail( $post_id ) && $a['image'] == 'left' && $a['layout'] == 'list' )
								$output .= '<div class="cfm-episode-image-left"><div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, $a['image_size'] ) . '</a></div>';

							// featured image left container end, content right start.
							if ( has_post_thumbnail( $post_id ) && $a['image'] == 'left' && $a['layout'] == 'list' )
								$output .= '</div><div class="cfm-episode-content-right">';

							// content left start.
							if ( has_post_thumbnail( $post_id ) && $a['image'] == 'right' && $a['layout'] == 'list' )
								$output .= '<div class="cfm-episode-content-left">';

							// featured image above title.
							if ( has_post_thumbnail( $post_id ) && $a['image'] == 'above_title' )
								$output .= '<div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, $a['image_size'] ) . '</a></div>';

							// title.
							if ( $a['title'] == 'show' )
								$output .= '<div class="cfm-episode-title"><' . $title_tag . '><a href="' . get_permalink() . '">' . $episode_title . '</a></' . $title_tag . '></div>';

							// featured image below title.
							if ( has_post_thumbnail( $post_id ) && $a['image'] == 'below_title' )
								$output .= '<div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, $a['image_size'] ) . '</a></div>';

							// player above content.
							if ( $a['player'] == 'above_content' )
								$output .= $player;

							// content excerpt.
							if ( $a['content'] == 'excerpt' )
								$output .= '<div class="cfm-episode-content">' . wp_trim_words( get_the_excerpt(), $a['content_length'], '...' ) . '</div>';

							// content full text.
							if ( $a['content'] == 'fulltext' )
								$output .= '<div class="cfm-episode-content">' . get_the_content() . '</div>';

							// player below content.
							if ( $a['player'] == 'below_content' )
								$output .= $player;

							// permalink.
							if ( $a['link'] == 'show' )
								$output .= '<div class="cfm-episode-link"><a href="' . get_permalink() . '">' . esc_html( $a['link_text'] ) . '</a></div>';

							// content right end.
							if ( has_post_thumbnail( $post_id ) && $a['image'] == 'left' && $a['layout'] == 'list' )
								$output .= '</div>';

							// content left end, featured image right container start.
							if ( has_post_thumbnail( $post_id ) && $a['image'] == 'right' && $a['layout'] == 'list' )
								$output .= '</div><div class="cfm-episode-image-right"><div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, $a['image_size'] ) . '</a></div></div>';

						$output .= '</div>';

					endwhile;

					wp_reset_postdata();

				}
				else {
					$output = 'no_more';
				}

			}
			else {
				$output = 'nothing_found';
			}

		}

		echo $output;

		wp_die();

	}

	/**
	 * Load episodes - shortcode builder
	 *
	 * @since 3.0
	 * @return string
	 */
	public static function shortcode_load_episodes() {

		$output = '';

		if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

			$show_ids = $_POST['show_ids'];

			if ( ! empty( $show_ids ) ) {

				$shows_count = count( $show_ids );

				foreach ( $show_ids as $show_id ) {
					$output .= ( $shows_count > 1 ) ? '<div class="dropdown-row-group"><div class="dropdown-header">' . esc_html( cfm_get_show_info( $show_id, 'title' ) ) . '</div>' : '';

					$args = array(
						'post_type'      => 'captivate_podcast',
						'posts_per_page' => -1,
						'order'          => 'DESC',
						'meta_query'     => array(
							array(
								'key'     => 'cfm_show_id',
								'value'   => $show_id,
								'compare' => '=',
							),
						),
					);
					$episodes = new WP_Query( $args );

					if ( $episodes->have_posts() ) {
						while ( $episodes->have_posts() ) {
							$episodes->the_post();
							$post_status = get_post_status();

							if ( 'future' == $post_status ) {
								$episode_status = "scheduled";
							}
							elseif ( 'publish' == $post_status ) {
								$episode_status = "published";
							}
							else {
								$episode_status = $post_status;
							}

							$output .= '<a class="dropdown-item" data-show-id="' . esc_attr( $show_id ) . '" data-id="' . esc_attr( get_the_ID() ) . '">' . esc_html( get_the_title() ) . '<small class="status ' . esc_attr( $episode_status ) . '">' . esc_html( $episode_status ) . '</small></a>';
						}
						wp_reset_postdata();
					}
					else {
						$output .= '<span>No episodes found from this podcast.</span>';
					}

					$output .= ( $shows_count > 1 ) ? '</div>' : '';
				}

			}
			else {

				$shows = cfm_get_shows();
				$user_shows = get_user_meta( get_current_user_id(), 'cfm_user_shows', true );

				if ( ! empty( $shows ) ) {

					$shows_count = count( $shows );

					foreach ( $shows as $show ) {
						if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) ) {

							$output .= ( $shows_count > 1 ) ? '<div class="dropdown-row-group loded"><div class="dropdown-header">' . esc_html( $show['title'] ) . '</div>' : '';

								$args     = array(
									'post_type'      => 'captivate_podcast',
									'posts_per_page' => -1,
									'order'          => 'DESC',
									'meta_query'     => array(
										array(
											'key'     => 'cfm_show_id',
											'value'   => $show['id'],
											'compare' => '=',
										),
									),
								);
								$episodes = new WP_Query( $args );

								if ( $episodes->have_posts() ) {
									while ( $episodes->have_posts() ) {
										$episodes->the_post();
										$post_status = get_post_status();

										if ( 'future' == $post_status ) {
											$episode_status = "scheduled";
										}
										elseif ( 'publish' == $post_status ) {
											$episode_status = "published";
										}
										else {
											$episode_status = $post_status;
										}

										$output .= '<a class="dropdown-item" data-show-id="' . esc_attr( $show['id'] ) . '" data-id="' . esc_attr( get_the_ID() ) . '">' . esc_html( get_the_title() ) . '<small class="status ' . esc_attr( $episode_status ) . '">' . esc_html( $episode_status ) . '</small></a>';
									}
									wp_reset_postdata();
								}
								else {
									$output .= '<span>No episodes found from this podcast.</span>';
								}

							$output .= ( $shows_count > 1 ) ? '</div>' : '';
						}
					}
				}
				else {
					$output .= '<span>No episodes found.</span>';
				}

			}
		}
		else {
			$output = '<span>Something went wrong! Please refresh the page.</span>';
		}

		echo $output;

		wp_die();
	}

    /**
	 * Save shortcode
	 *
	 * @since 1.1.4
	 * @return string
	 */
	public static function save_shortcode() {

		$output = '<strong>ERROR:</strong> Something went wrong! Please refresh the page and try again.';

		if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

			if ( isset( $_POST['shortcode'] ) ) {

				$shortcode = sanitize_text_field( wp_unslash( $_POST['shortcode'] ) );
				$shortcode_preview = sanitize_text_field( wp_unslash( $_POST['shortcode_preview'] ) );

				update_option( 'cfm_shortcode_latest', $shortcode );
				update_option( 'cfm_shortcode_preview', $shortcode_preview );

				$output = do_shortcode( $shortcode_preview );

			}

		}

		echo $output;

		wp_die();

	}

}

endif;