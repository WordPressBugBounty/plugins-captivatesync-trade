<?php
/**
 * Used to power our CaptivateSync brain.
 */
if ( ! function_exists( 'cfm_get_captivate_shows' ) ) :
	/**
	 * Get user shows from Captivate.
	 *
	 * @since 3.0
	 *
	 * @return array | string
	 */
	function cfm_get_captivate_shows() {

		$response = wp_remote_request( CFMH_API_URL . '/users/' . get_option( 'cfm_authentication_id' ) . '/shows/', array(
			'timeout' => 500,
			'method'  => 'GET',
			'headers' => array(
				'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
			),
		) );

		// Debugging.
		cfm_generate_log( 'GET CAPTIVATE SHOWS', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? json_decode( $response['body'] )->shows : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_get_captivate_show' ) ) :
	/**
	 * Get captivate show.
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 *
	 * @return array | string
	 */
	function cfm_get_captivate_show( $show_id ) {

		$response = wp_remote_get( CFMH_API_URL . '/shows/' . $show_id, array(
			'timeout' => 500,
			'headers' => array(
				'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
			),
		) );

		// Debugging.
		cfm_generate_log( 'GET CAPTIVATE SHOW', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? json_decode( $response['body'] )->show : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_get_captivate_site_link' ) ) :
	/**
	 * Get captivate site link.
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 *
	 * @return string
	 */
	function cfm_get_captivate_site_link( $show_id ) {

		$custom_domain = cfm_get_show_info( $show_id, 'custom_domain' );
		$show_link = cfm_get_show_info( $show_id, 'show_link' );

		if ( '' != $custom_domain ) {
			$site_link = 'https://' . $custom_domain;
		}
		else if ( '' != $show_link ) {
			$site_link = 'https://' . $show_link . '.captivate.fm';
		}
		else {
			$site_link = '';
		}

		return $site_link;
    }
endif;

if ( ! function_exists( 'cfm_get_captivate_dynamic_text' ) ) :
	/**
	 * Get captivate dynamic-text.
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 *
	 * @return array
	 */
	function cfm_get_captivate_dynamic_text( $show_id ) {

		$response = wp_remote_get( CFMH_API_URL . '/shows/' . $show_id . '/dynamic-text', array(
			'timeout' => 500,
			'headers' => array(
				'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
			),
		) );

		// Debugging.
		cfm_generate_log( 'GET CAPTIVATE DYNAMIC TEXT', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? $response['body'] : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_get_captivate_guests' ) ) :
	/**
	 * Get captivate guests.
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 *
	 * @return array
	 */
	function cfm_get_captivate_guests( $show_id ) {

		$response = wp_remote_get( CFMH_API_URL . '/shows/' . $show_id . '/guests', array(
			'timeout' => 500,
			'headers' => array(
				'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
			),
		) );

		// Debugging.
		cfm_generate_log( 'GET CAPTIVATE GUESTS', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? $response['body'] : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_get_captivate_bookings' ) ) :
	/**
	 * Get captivate bookings.
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 *
	 * @return array
	 */
	function cfm_get_captivate_bookings( $show_id, $episode_id ) {

		$response = wp_remote_get( CFMH_API_URL . '/shows/' . $show_id . '/episodes/' . $episode_id .'/bookings', array(
			'timeout' => 500,
			'headers' => array(
				'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
			),
		) );

		// Debugging.
		cfm_generate_log( 'GET CAPTIVATE BOOKINGS', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? $response['body'] : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_get_captivate_attribution_links' ) ) :
	/**
	 * Get captivate attribution links.
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 *
	 * @return array
	 */
	function cfm_get_captivate_attribution_links( $show_id ) {

		$response = wp_remote_get( CFMH_API_URL . '/shows/' . $show_id . '/short-links', array(
			'timeout' => 500,
			'headers' => array(
				'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
			),
		) );

		// Debugging.
		cfm_generate_log( 'GET CAPTIVATE ATTRIBUTION LINKS', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? json_encode( json_decode( $response['body'] )->data ) : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_get_captivate_marketing_links' ) ) :
	/**
	 * Get captivate marketing links.
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 *
	 * @return array
	 */
	function cfm_get_captivate_marketing_links( $show_id ) {

		$response = wp_remote_get( CFMH_API_URL . '/shows/' . $show_id . '/marketing', array(
			'timeout' => 500,
			'headers' => array(
				'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
			),
		) );

		// Debugging.
		cfm_generate_log( 'GET CAPTIVATE MARKETING LINKS', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? $response['body'] : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_get_captivate_research_links' ) ) :
	/**
	 * Get captivate research links.
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 *
	 * @return array
	 */
	function cfm_get_captivate_research_links( $show_id ) {

		$response = wp_remote_get( CFMH_API_URL . '/shows/' . $show_id . '/research-links', array(
			'timeout' => 500,
			'headers' => array(
				'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
			),
		) );

		// Debugging.
		cfm_generate_log( 'GET CAPTIVATE RESEARCH LINKS', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? $response['body'] : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_get_captivate_episodes' ) ) :
    /**
	 * Get Captivate episodes.
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 *
	 * @return array | string
	 */
    function cfm_get_captivate_episodes( $show_id ) {

        $response = wp_remote_get(
            CFMH_API_URL . '/shows/' . $show_id . '/episodes',
            array(
                'timeout' => 500,
                'headers' => array(
                    'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
                ),
            )
        );

        // Debugging.
		cfm_generate_log( 'GET CAPTIVATE EPISODES', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? json_decode( $response['body'] )->episodes : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_get_captivate_episode' ) ) :
    /**
	 * Get Captivate episode.
	 *
	 * @since 3.0
	 * @param string  $episode_id  The episode ID.
	 *
	 * @return array | string
	 */
    function cfm_get_captivate_episode( $episode_id ) {
		$response = wp_remote_get( CFMH_API_URL . '/episodes/' . $episode_id, array(
			'timeout' => 500,
			'headers' => array(
				'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
			),
		));

		// Debugging.
		cfm_generate_log( 'GET CAPTIVATE EPISODE', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? json_decode( $response['body'] )->episode : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_episodes_data_array' ) ) :
	/**
	 * Store episodes data to array
	 *
	 * @since 3.0
	 * @param array  $data Captivate episode(s)
	 * @param string  $episode_id Captivate episode ID
	 *
	 * @return array
	 */
	function cfm_episodes_data_array( $data, $episode_id = false ) {

		$captivate_episodes_data = array();

		if ( ! empty( $data ) ) {
			if ( $episode_id ) {
				$captivate_episodes_data = array(
					'id' 						=> $episode_id,
					'shows_id' 					=> $data->shows_id,
					'media_id' 					=> $data->media_id,
					'title' 					=> $data->title,
					'itunes_title' 				=> $data->itunes_title,
					'published_date' 			=> $data->published_date,
					'status' 					=> $data->status,
					'episode_art' 				=> $data->episode_art,
					'shownotes' 				=> $data->shownotes,
					'episode_type' 				=> $data->episode_type,
					'episode_season' 			=> $data->episode_season,
					'episode_number' 			=> $data->episode_number,
					'author' 					=> $data->author,
					'link' 						=> $data->link,
					'explicit' 					=> $data->explicit,
					'itunes_block' 				=> $data->itunes_block,
					'google_block' 				=> $data->google_block,
					'google_description' 		=> $data->google_description,
					'donation_link' 			=> $data->donation_link,
					'donation_text' 			=> $data->donation_text,
					'website_title' 			=> $data->website_title,
					'slug'				 		=> $data->slug,
					'seo_title'			 		=> $data->seo_title,
					'seo_description'    		=> $data->seo_description,
					'episode_private'    		=> $data->episode_private,
					'episode_expiration'    	=> $data->episode_expiration,
					'transcription_html'    	=> $data->transcription_html,
					'transcription_file'    	=> $data->transcription_file,
					'transcription_json'    	=> $data->transcription_json,
					'transcription_text'    	=> $data->transcription_text,
					'idea_title'    			=> $data->idea_title,
					'idea_summary'    			=> $data->idea_summary,
					'idea_notes'    			=> $data->idea_notes,
					'idea_created_at'    		=> $data->idea_created_at,
					'media_name' 				=> $data->media_name,
					'media_size' 				=> $data->media_size,
					'media_bit_rate' 			=> $data->media_bit_rate,
					'media_id3_size' 			=> $data->media_id3_size,
					'media_type' 				=> $data->media_type,
					'media_url' 				=> $data->media_url,
					'media_duration' 			=> $data->media_duration,
					'amie_status' 				=> $data->amie_status,
					'idea_production_notes' 	=> $data->idea_production_notes,
					'early_access_end_date' 	=> $data->early_access_end_date,
					'captivate_episode_type' 	=> $data->captivate_episode_type,
					'exclusivity_date' 			=> $data->exclusivity_date
				);
			}
			else {

				foreach ( $data as $captivate_episode ) {
					$episode_id = $captivate_episode->id ? $captivate_episode->id : $capitvate_episode->episodes_id;
					$captivate_episodes_data[$episode_id] = array(
						'id'                 	=> $episode_id,
						'shows_id'           	=> $captivate_episode->shows_id,
						'media_id'           	=> $captivate_episode->media_id,
						'title'              	=> $captivate_episode->title,
						'itunes_title'       	=> $captivate_episode->itunes_title,
						'published_date'     	=> $captivate_episode->published_date,
						'status'             	=> $captivate_episode->status,
						'episode_art'        	=> $captivate_episode->episode_art,
						'shownotes'          	=> $captivate_episode->shownotes,
						'episode_type'       	=> $captivate_episode->episode_type,
						'episode_season'     	=> $captivate_episode->episode_season,
						'episode_number'     	=> $captivate_episode->episode_number,
						'author'             	=> $captivate_episode->author,
						'link'               	=> $captivate_episode->link,
						'explicit'           	=> $captivate_episode->explicit,
						'itunes_block'       	=> $captivate_episode->itunes_block,
						'google_block'       	=> $captivate_episode->google_block,
						'google_description' 	=> $captivate_episode->google_description,
						'donation_link'      	=> $captivate_episode->donation_link,
						'donation_text'      	=> $captivate_episode->donation_text,
						'website_title'      	=> $captivate_episode->website_title,
						'slug'				 	=> $captivate_episode->slug,
						'seo_title'			 	=> $captivate_episode->seo_title,
						'seo_description'    	=> $captivate_episode->seo_description,
						'episode_private'    	=> $captivate_episode->episode_private,
						'episode_expiration'    => $captivate_episode->episode_expiration,
						'transcription_html'    => $captivate_episode->transcription_html,
						'transcription_file'    => $captivate_episode->transcription_file,
						'transcription_json'    => $captivate_episode->transcription_json,
						'transcription_text'    => $captivate_episode->transcription_text,
						'idea_title'    		=> $captivate_episode->idea_title,
						'idea_summary'    		=> $captivate_episode->idea_summary,
						'idea_notes'    		=> $captivate_episode->idea_notes,
						'idea_created_at'    	=> $captivate_episode->idea_created_at,
						'media_name' 			=> $captivate_episode->media_name,
						'media_size' 			=> $captivate_episode->media_size,
						'media_bit_rate' 		=> $captivate_episode->media_bit_rate,
						'media_id3_size' 		=> $captivate_episode->media_id3_size,
						'media_type' 			=> $captivate_episode->media_type,
						'media_url' 			=> $captivate_episode->media_url,
						'media_duration' 		=> $captivate_episode->media_duration,
						'amie_status' 			=> $captivate_episode->amie_status,
						'idea_production_notes' => $captivate_episode->idea_production_notes,
						'early_access_end_date' => $captivate_episode->early_access_end_date,
						'captivate_episode_type' => $captivate_episode->captivate_episode_type,
						'exclusivity_date' 		=> $captivate_episode->exclusivity_date
					);
				}

			}
		}

		return $captivate_episodes_data;

    }
endif;

if ( ! function_exists( 'cfm_get_show_artwork' ) ) :
	/**
	 *
	 * @since 3.0
	 * @param string $show_id  The show id.
	 * @param size full or custom_size such as '800x800'
	 *
	 * @return artwork_url
	 */
	function cfm_get_show_artwork( $show_id, $size = 'full' ) {

		if ( cfm_is_show_exists( $show_id ) ) {

			$artwork_url = cfm_get_show_info( $show_id, 'artwork' );

			// Regular expression to match size in the format '800x800'.
			if ( preg_match( '/^(\d+)x(\d+)$/', $size, $matches ) ) {
				$width = $matches[1];
				$height = $matches[2];

				return $artwork_url . '?width=' . $width . '&height=' . $height;
			}

			return $artwork_url;
		}

	}
endif;

if ( ! function_exists( 'cfm_get_show_marketing_links' ) ) :
	/**
	 *
	 * @since 3.0
	 * @param string $show_id  The show id.
	 *
	 * @return array()
	 */
	function cfm_get_show_marketing_links( $show_id ) {

		if ( cfm_is_show_exists( $show_id ) ) {

			$marketing_links = cfm_get_show_info( $show_id, 'marketing_links' );
			$marketing_links = json_decode( $marketing_links );

			return isset( $marketing_links->marketing ) ? $marketing_links->marketing : array();
		}

	}
endif;

if ( ! function_exists( 'cfm_get_episode_research_links' ) ) :
	/**
	 *
	 * @since 3.0
	 * @param string $show_id  The show id.
	 * @param int $post_id  The post id.
	 *
	 * @return array()
	 */
	function cfm_get_episode_research_links( $show_id, $post_id ) {

		if ( cfm_is_show_exists( $show_id ) ) {

			$cfm_episode_id = get_post_meta( $post_id, 'cfm_episode_id', true );
			$research_links = cfm_get_show_info( $show_id, 'research_links' );
			$research_links = json_decode( $research_links );
			$research_links = isset( $research_links->research_links ) ? $research_links->research_links : array();
			$research_links_array = array();

			if ( is_array( $research_links ) && ! empty( $research_links ) ) {
				foreach ( $research_links as $rl ) {
					if ( in_array( $cfm_episode_id, $rl->episodeIds ) ) {
						$research_links_array[$rl->id] = array(
							'id' => $rl->id,
							'show_id' => $rl->show_id,
							'url' => $rl->url,
							'title' => $rl->title,
							'notes' => $rl->notes,
							'created_at' => $rl->created_at,
							'updated_at' => $rl->updated_at,
							'episodeIds' => $rl->episodeIds
						);
					}
				}
			}

			return $research_links_array;
		}

	}
endif;

if ( ! function_exists( 'cfm_get_episode_bookings' ) ) :
	/**
	 *
	 * @since 3.0
	 * @param int $post_id  The post id.
	 *
	 * @return array()
	 */
	function cfm_get_episode_bookings( $post_id ) {

		$bookings = get_post_meta( $post_id, 'cfm_episode_bookings', true );
		$bookings = json_decode( $bookings );

		return isset( $bookings->bookings ) ? $bookings->bookings : array();
	}
endif;

if ( ! function_exists( 'cfm_is_show_exists' ) ) :
	/**
	 * Check if show id exists
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 *
	 * @return boolean
	 */
	function cfm_is_show_exists( $show_id ) {
		return in_array( $show_id, cfm_get_show_ids() ) ? true : false;
	}
endif;

if ( ! function_exists( 'cfm_limit_characters' ) ) :
	/**
	 * Limit characters
	 *
	 * @since 1.0
	 * @param string  $characters  The entire string.
	 * @param int     $limit  Limit of characters.
	 * @param boolean $readmore  Elipsis needed.
	 *
	 * @return string
	 */
	function cfm_limit_characters( $characters, $limit = 150, $readmore = '...' ) {
		$characters = wp_strip_all_tags( $characters );
		$length     = strlen( $characters );
		if ( $length <= $limit ) {
			return $characters;
		} else {
			return substr( $characters, 0, $limit ) . $readmore;
		}
	}
endif;

if ( ! function_exists( 'cfm_get_show_page' ) ) :
	/**
	 * Get show
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 * @param string $option  Option.
	 *
	 * @return page
	 */
	function cfm_get_show_page( $show_id, $option ) {

		$shows    = cfm_get_shows();
		$show_ids = array();
		$cfm_general_settings = get_option( 'cfm_general_settings' );
		$single_slug = ( isset( $cfm_general_settings['single_slug'] ) && '' != $cfm_general_settings['single_slug'] ) ? $cfm_general_settings['single_slug'] : 'captivate-podcast';

		if ( ! empty( $shows ) ) {
			foreach ( $shows as $show ) {
				$show_ids[ $show['id'] ] = $show['index_page'];
			}
		}

		$index_page = ( cfm_is_show_exists( $show_id ) ) ? $show_ids[ $show_id ] : '0';

		if ( 'slug' === $option ) {
			$page = ( '0' == $index_page || '' == $index_page ) ? $single_slug : get_post_field( 'post_name', $index_page );
		}
		else {
			$page = $index_page;
		}

		return $page;
	}
endif;

if ( ! function_exists( 'cfm_get_shows' ) ) :
	/**
	 * Get shows
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	function cfm_get_shows() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'cfm_shows';
		$results    = $wpdb->get_results( "SELECT DISTINCT(show_id) FROM $table_name" );

		$shows = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$title             = cfm_get_show_info( $result->show_id, 'title' );
				$artwork           = cfm_get_show_info( $result->show_id, 'artwork' ) ? cfm_get_show_info( $result->show_id, 'artwork' ) : CFMH_URL . 'assets/img/captivate-default.jpg';
				$last_synchronised = cfm_get_show_info( $result->show_id, 'last_synchronised' );
				$index_page        = cfm_get_show_info( $result->show_id, 'index_page' );
				$author  		   = cfm_get_show_info( $result->show_id, 'author' );
				$feed_url          = cfm_get_show_info( $result->show_id, 'feed_url' );
				$published_date    = cfm_get_show_info( $result->show_id, 'published_date' );
				$status 		   = cfm_get_show_info( $result->show_id, 'status' );
				$private           = cfm_get_show_info( $result->show_id, 'private' );
				$role          	   = cfm_get_show_info( $result->show_id, 'role' );
				$team          	   = cfm_get_show_info( $result->show_id, 'team' );
				$created           = cfm_get_show_info( $result->show_id, 'created' );
				$wp_author_id  	   = cfm_get_show_info( $result->show_id, 'wp_author_id' );

				$shows[] = array(
					'id'                => $result->show_id,
					'title'             => $title,
					'artwork'           => $artwork,
					'last_synchronised' => $last_synchronised,
					'index_page'        => $index_page,
					'author'       	    => $author,
					'feed_url'          => $feed_url,
					'published_date'    => date( 'Y-m-d H:i:s', strtotime( $published_date ) ),
					'status'          	=> $status,
					'private'         	=> $private,
					'role'          	=> $role,
					'team'          	=> $team,
					'created'          	=> date( 'Y-m-d H:i:s', strtotime( $created ) ),
					'wp_author_id'      => $wp_author_id
				);
			}
		}

		return $shows;
	}
endif;

if ( ! function_exists( 'cfm_get_show_id' ) ) :
	/**
	 * Get show ID
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	function cfm_get_show_id() {

		$current_screen = get_current_screen();

		if ( isset( $_GET['show_id'] ) ) {
			$show_id = sanitize_text_field( wp_unslash( $_GET['show_id'] ) );
		}
		else {
			if ( null !== $current_screen && strpos( $current_screen->id, 'cfm-hosting-podcast-episodes_' ) !== false ) {
				$show_id = substr( $current_screen->id, 49 );
			}
			else {
				$shows		= cfm_get_shows();
				$show_id 	= ! empty( $shows ) ? $shows[0]['id'] : '';
			}
		}

		return $show_id;
	}
endif;

if ( ! function_exists( 'cfm_get_show_ids' ) ) :
	/**
	 * Get show IDs
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	function cfm_get_show_ids() {

		$shows    = cfm_get_shows();
		$show_ids = array();

		if ( ! empty( $shows ) ) {

			foreach ( $shows as $show ) {
				$show_ids[] = $show['id'];
			}
		}

		return $show_ids;
	}
endif;

if ( ! function_exists( 'cfm_update_show_info' ) ) :
	/**
	 * Update show information
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 * @param string $option  The option.
	 * @param string $value  The value.
	 *
	 * @return void
	 */
	function cfm_update_show_info( $show_id, $option, $value ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'cfm_shows';

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE show_id = %s AND cfm_option = %s", $show_id, $option ) );

		if ( ! empty( $results ) ) {

			$wpdb->update(
				$table_name,
				array(
					'cfm_option' => $option,
					'cfm_value'  => $value,
					'show_id'    => $show_id,
				),
				array(
					'cfm_option' => $option,
					'show_id'    => $show_id,
				)
			);
		} else {

			$wpdb->insert(
				$table_name,
				array(
					'cfm_option' => $option,
					'cfm_value'  => $value,
					'show_id'    => $show_id,
				)
			);
		}
	}
endif;

if ( ! function_exists( 'cfm_get_show_info' ) ) :
	/**
	 * Get show information
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 * @param string $option  The option.
	 *
	 * @return information
	 */
	function cfm_get_show_info( $show_id, $option ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'cfm_shows';

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT cfm_value FROM $table_name WHERE show_id = %s AND cfm_option = %s", $show_id, $option ) );

		return ! empty( $row ) ? $row->cfm_value : '';
	}
endif;

if ( ! function_exists( 'cfm_remove_show_info' ) ) :
	/**
	 * Remove show information
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 *
	 * @return info deleted
	 */
	function cfm_remove_show_info( $show_id ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'cfm_shows';

		$row = $wpdb->get_row( $wpdb->prepare( "DELETE FROM $table_name WHERE show_id = %s", $show_id ) );

		return ! empty( $row ) ? $row->cfm_value : '';
	}
endif;

if ( ! function_exists( 'cfm_upload_file' ) ) :
	/**
	 * Upload file to Captivate
	 *
	 * @since 1.0
	 * @param string $file_path  The file path.
	 * @param string $show_id  The show ID.
	 *
	 * @return artwork url
	 */
	function cfm_upload_file( $file_path, $show_id ) {

		$boundary = hash( 'sha256', uniqid( '', true ) );

		$payload = '';

		$file_contents = false;

		$file_contents = cfm_image_get_contents( $file_path );

		if ( function_exists( 'finfo' ) ) {
			$file_info = new finfo( FILEINFO_MIME_TYPE );
			$mime_type = $file_info->buffer( $file_contents );
		}
		else {
			$file_info = getimagesize( $file_path );
			$mime_type = $file_info['mime'];
		}

		$base_name = basename( $file_path );

		if ( false !== $file_contents ) {

			// Upload the file.
			if ( $file_path ) {
				$payload .= '--' . $boundary;
				$payload .= "\r\n";
				$payload .= 'Content-Disposition: form-data; name="file"; filename="' . $base_name . '"' . "\r\n";
				$payload .= 'Content-Type: ' . $mime_type . "\r\n";
				$payload .= "\r\n";
				$payload .= $file_contents;
				$payload .= "\r\n";
			}

			$payload .= '--' . $boundary . '--';

			$request = wp_remote_post( CFMH_API_URL . '/shows/' . $show_id . '/artwork',
				array(
					'timeout' => 500,
					'body'    => $payload,
					'headers' => array(
						'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
						'content-type'  => 'multipart/form-data; boundary=' . $boundary,
					),
				)
			);

			$body = json_decode( $request['body'] );

			// Returns the url of the uploaded file.
			return ! empty( $body->artwork ) ? $body->artwork->artwork_url : '';

		}

	}
endif;

if ( ! function_exists( 'cfm_remove_show' ) ) :
	/**
	 * Hopefully we don't need this one, remove show.
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 *
	 * @return void
	 */
	function cfm_remove_show( $show_id ) {

		cfm_remove_show_info( $show_id );

		// get WP episodes.
		$get_episodes = array(
			'post_type'      => 'captivate_podcast',
			'posts_per_page' => -1,
			'order'          => 'DESC',
			'post_status'    => array( 'publish', 'draft', 'future', 'private' ),
			'meta_query'     => array(
				array(
					'key'     => 'cfm_show_id',
					'value'   => $show_id,
					'compare' => '=',
				),
			),
		);

		$episodes = new WP_Query( $get_episodes );

		if ( $episodes->have_posts() ) :

			while ( $episodes->have_posts() ) :
				$episodes->the_post();
				wp_delete_post( get_the_ID(), false );
			endwhile;

		endif;

	}
endif;


if ( ! function_exists( 'cfm_sync_shows' ) ) :
	/**
	 * Sync up Captivate shows to Captivate Sync. Get it.
	 *
	 * @since 1.0
	 * @param string  $show_id  The show ID.
	 * @param boolean $sync_key  The sync key.
	 *
	 * @return boolean
	 */
	function cfm_sync_shows( $show_id, $sync_key = false ) {

		$captivate_show = cfm_get_captivate_show( $show_id );

		if ( ! empty( $captivate_show ) && 'api_error' != $captivate_show ) {

			cfm_update_show_info( $show_id, 'last_synchronised', current_time( 'mysql' ) );

			if ( $sync_key ) {
				cfm_update_show_info( $show_id, 'sync_key', $sync_key );
			}

			foreach( $captivate_show as $k => $v ) {
				cfm_update_show_info( $show_id, $k, $v );
			}

			// created.
			if ( isset( $captivate_show->created ) ) {
				cfm_update_show_info( $show_id, 'created', date( 'Y-m-d H:i:s', strtotime( $captivate_show->created ) ) );
			}
			// last feed generation.
			if ( isset( $captivate_show->last_feed_generation ) ) {
				cfm_update_show_info( $show_id, 'last_feed_generation', date( 'Y-m-d H:i:s', strtotime( $captivate_show->last_feed_generation ) ) );
			}

			// last published.
			$get_episodes = array(
				'post_type'      => 'captivate_podcast',
				'posts_per_page' => 1,
				'order'          => 'DESC',
				'post_status'    => array( 'publish' ),
				'meta_query'     => array(
					array(
						'key'     => 'cfm_show_id',
						'value'   => $show_id,
						'compare' => '=',
					),
				),
			);

			$episodes = new WP_Query( $get_episodes );

			if ( $episodes->have_posts() ) :
				while ( $episodes->have_posts() ) : $episodes->the_post();
					cfm_update_show_info( $show_id, 'published_date', get_the_date( 'Y-m-d H:i:s' ) );
				endwhile;
			endif;

			// feed url.
			$get_feed = wp_remote_get(
				CFMH_API_URL . '/shows/' . $show_id . '/feed',
				array(
					'timeout' => 500,
					'headers' => array(
						'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
					),
				)
			);

			// get feed url debugging.
			cfm_generate_log( 'GET CAPTIVATE FEED URL', $get_feed );

			if ( ! is_wp_error( $get_feed ) && 'Unauthorized' !== $get_feed['body'] && is_array( $get_feed ) ) {
				$feed_url = json_decode( $get_feed['body'] )->feed;

				cfm_update_show_info( $show_id, 'feed_url', $feed_url );
			}

			// dynamic text.
			$dynamic_text = cfm_get_captivate_dynamic_text( $show_id );
			if ( 'api_error' != $dynamic_text ) {
				cfm_update_show_info( $show_id, 'dynamic_text', $dynamic_text );
			}

			// guests.
			$guests = cfm_get_captivate_guests( $show_id );
			if ( 'api_error' != $guests ) {
				cfm_update_show_info( $show_id, 'guests', $guests );
			}

			// attribution links.
			$attribution_links = cfm_get_captivate_attribution_links( $show_id );
			if ( 'api_error' != $attribution_links ) {
				cfm_update_show_info( $show_id, 'attribution_links', $attribution_links );
			}

			// marketing links.
			$marketing_links = cfm_get_captivate_marketing_links( $show_id );
			if ( 'api_error' != $marketing_links ) {
				cfm_update_show_info( $show_id, 'marketing_links', $marketing_links );
			}

			// research links.
			$research_links = cfm_get_captivate_research_links( $show_id );
			if ( 'api_error' != $research_links ) {
				cfm_update_show_info( $show_id, 'research_links', $research_links );
			}

			return true;
        }
		else {
			return false;
		}
    }

endif;

if ( ! function_exists( 'cfm_sync_episodes' ) ) :
    /**
	 * Sync up Captivate episodes to Captivate Sync. Get it.
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 * @param array $do  all | update | delete | create
	 * @param array $episodes  All episodes or by ID
	 *
	 * @return boolean
	 */
    function cfm_sync_episodes( $show_id, $do = array('all'), $episode_ids = array('all') ) {

		$captivate_episodes = cfm_get_captivate_episodes( $show_id );

		if ( ! empty( $captivate_episodes ) && 'api_error' != $captivate_episodes ) {

			$captivate_episodes_data = cfm_episodes_data_array( $captivate_episodes );

			// get WP episodes.
			$get_episodes = array(
				'post_type'      => 'captivate_podcast',
				'posts_per_page' => -1,
				'order'          => 'DESC',
				'post_status'    => array( 'publish', 'draft', 'future', 'private' ),
				'meta_query'     => array(
					array(
						'key'     => 'cfm_show_id',
						'value'   => $show_id,
						'compare' => '=',
					),
				),
			);

			$episodes = new WP_Query( $get_episodes );

			$wp_episode_ids = array();

			if ( $episodes->have_posts() ) :

				while ( $episodes->have_posts() ) :
					$episodes->the_post();
					$pid = get_the_ID();

					// store WP data to array (for comparison).
					$cfm_episode_id                  = get_post_meta( $pid, 'cfm_episode_id', true );
					$wp_episode_ids[ $cfm_episode_id ] = get_the_title();

					// update WP episodes - ONLY if set to all or update && all | specific episodes
					if ( array_key_exists( $cfm_episode_id, $captivate_episodes_data ) ) {

						if ( count( array_intersect( $do, array( 'all', 'update' ) ) ) > 0 && ( in_array( 'all', $episode_ids ) || in_array( $cfm_episode_id, $episode_ids ) ) ) {

							// title.
							$title = $captivate_episodes_data[ $cfm_episode_id ]['title'];

							// published_date.
							$published_date = $captivate_episodes_data[ $cfm_episode_id ]['published_date'];
							$published_date = date( 'Y-m-d H:i:s', strtotime( $published_date ) );

							// post data.
							$update_post_data = array(
								'ID'           	=> $pid,
								'post_title'   	=> wp_encode_emoji( $title ),
								'post_date' 	=> $published_date,
								'post_date_gmt' => get_gmt_from_date( $published_date, 'Y-m-d H:i:s' ),
								'edit_date' 	=> true,
							);

							// show notes - sync only if not empty and more than 20 characters.
							$shownotes = $captivate_episodes_data[ $cfm_episode_id ]['shownotes'];
							$shownotes_length = strlen( $shownotes );
							if ( '' != $shownotes && '<p><br></p>' != $shownotes && $shownotes_length > 20 ) {
								$update_post_data['post_content'] = wp_encode_emoji( $shownotes );
							}

							// status.
							$status = $captivate_episodes_data[ $cfm_episode_id ]['status'];

							switch ( $status ) {
								case 'Published':
									$post_status = 'publish';
									break;
								case 'Scheduled':
									$post_status = 'future';
									break;
								case 'Expired':
									$post_status = 'publish';
									break;
								case 'Exclusive':
									$post_status = 'publish';
									break;
								case 'Early Access':
									$post_status = 'publish';
									break;
								default:
									$post_status = 'draft';
							}
							update_post_meta( $pid, 'cfm_episode_status', $status );

							$update_post_data['post_status'] = $post_status;

							// slug.
							if ( $captivate_episodes_data[ $cfm_episode_id ]['slug'] && $captivate_episodes_data[ $cfm_episode_id ]['slug'] !== null && $captivate_episodes_data[ $cfm_episode_id ]['slug'] !== '0' ) {
								$update_post_data['post_name'] = $captivate_episodes_data[ $cfm_episode_id ]['slug'];
							}

							// Update the post data.
							wp_update_post( $update_post_data );

							// media data.
							update_post_meta( $pid, 'cfm_episode_media_id', $captivate_episodes_data[ $cfm_episode_id ]['media_id'] );
							update_post_meta( $pid, 'cfm_episode_media_url', $captivate_episodes_data[ $cfm_episode_id ]['media_url'] );
							update_post_meta( $pid, 'cfm_episode_media_name', $captivate_episodes_data[ $cfm_episode_id ]['media_name'] );
							update_post_meta( $pid, 'cfm_episode_media_size', $captivate_episodes_data[ $cfm_episode_id ]['media_size'] );
							update_post_meta( $pid, 'cfm_episode_media_id3_size', $captivate_episodes_data[ $cfm_episode_id ]['media_id3_size'] );
							update_post_meta( $pid, 'cfm_episode_media_type', $captivate_episodes_data[ $cfm_episode_id ]['media_type'] );
							$media_bit_rate = $captivate_episodes_data[ $cfm_episode_id ]['media_bit_rate'];
							update_post_meta( $pid, 'cfm_episode_media_bit_rate', $media_bit_rate );
							$media_bit_rate_str = substr( $media_bit_rate, 0, -3 ) . 'kbps';
							update_post_meta( $pid, 'cfm_episode_media_bit_rate_str', $media_bit_rate_str );

							$media_duration = $captivate_episodes_data[ $cfm_episode_id ]['media_duration'];
							update_post_meta( $pid, 'cfm_episode_media_duration', $media_duration );
								$media_duration_str = cfm_seconds_to_str( $media_duration );
							update_post_meta( $pid, 'cfm_episode_media_duration_str', $media_duration_str );

							// episode_art.
							$episode_art = $captivate_episodes_data[ $cfm_episode_id ]['episode_art'];
							if ( get_post_meta( $pid, 'cfm_episode_artwork', true ) !== $episode_art ) {
								update_post_meta( $pid, 'cfm_episode_artwork', $episode_art );
								delete_post_meta( $pid, 'cfm_episode_artwork_id' );
								delete_post_meta( $pid, 'cfm_episode_artwork_width' );
								delete_post_meta( $pid, 'cfm_episode_artwork_height' );
								delete_post_meta( $pid, 'cfm_episode_artwork_type' );
								delete_post_meta( $pid, 'cfm_episode_artwork_filesize' );
							}

							// other post meta.
							update_post_meta( $pid, 'cfm_episode_author', $captivate_episodes_data[ $cfm_episode_id ]['author'] );
							update_post_meta( $pid, 'cfm_episode_itunes_title', $captivate_episodes_data[ $cfm_episode_id ]['itunes_title'] );
							update_post_meta( $pid, 'cfm_episode_itunes_season', $captivate_episodes_data[ $cfm_episode_id ]['episode_season'] );
							update_post_meta( $pid, 'cfm_episode_itunes_number', $captivate_episodes_data[ $cfm_episode_id ]['episode_number'] );
							update_post_meta( $pid, 'cfm_episode_itunes_type', $captivate_episodes_data[ $cfm_episode_id ]['episode_type'] );
							update_post_meta( $pid, 'cfm_episode_itunes_explicit', $captivate_episodes_data[ $cfm_episode_id ]['explicit'] );
							update_post_meta( $pid, 'cfm_episode_donation_link', $captivate_episodes_data[ $cfm_episode_id ]['donation_link'] );
							update_post_meta( $pid, 'cfm_episode_donation_label', $captivate_episodes_data[ $cfm_episode_id ]['donation_text'] );
							update_post_meta( $pid, 'cfm_episode_seo_title', $captivate_episodes_data[ $cfm_episode_id ]['seo_title'] );
							update_post_meta( $pid, 'cfm_episode_seo_description', $captivate_episodes_data[ $cfm_episode_id ]['seo_description'] );
							update_post_meta( $pid, 'cfm_episode_private', $captivate_episodes_data[ $cfm_episode_id ]['episode_private'] );
							update_post_meta( $pid, 'cfm_episode_expiration', $captivate_episodes_data[ $cfm_episode_id ]['episode_expiration'] );


							// transcriptions.
							$transcription_uploaded = ( null != $captivate_episodes_data[ $cfm_episode_id ]['transcription_file'] && '' != $captivate_episodes_data[ $cfm_episode_id ]['transcription_file'] ) ? 'file' : 'text';
							$transcriptions = array(
								'transcription_uploaded' => $transcription_uploaded,
								'transcription_html' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_html'],
								'transcription_file' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_file'],
								'transcription_json' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_json'],
								'transcription_text' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_text'],
							);
							update_post_meta( $pid, 'cfm_episode_transcript', $transcriptions );

							// idea data.
							update_post_meta( $pid, 'cfm_episode_idea_title', $captivate_episodes_data[ $cfm_episode_id ]['idea_title'] );
							update_post_meta( $pid, 'cfm_episode_idea_summary', $captivate_episodes_data[ $cfm_episode_id ]['idea_summary'] );
							update_post_meta( $pid, 'cfm_episode_idea_notes', $captivate_episodes_data[ $cfm_episode_id ]['idea_notes'] );
							update_post_meta( $pid, 'cfm_episode_idea_created_at', $captivate_episodes_data[ $cfm_episode_id ]['idea_created_at'] );
							update_post_meta( $pid, 'cfm_episode_idea_production_notes', $captivate_episodes_data[ $cfm_episode_id ]['idea_production_notes'] );

							// other post meta.
							update_post_meta( $pid, 'cfm_episode_amie_status', $captivate_episodes_data[ $cfm_episode_id ]['amie_status'] );
							update_post_meta( $pid, 'cfm_episode_early_access_end_date', $captivate_episodes_data[ $cfm_episode_id ]['early_access_end_date'] );
							update_post_meta( $pid, 'cfm_episode_captivate_episode_type', $captivate_episodes_data[ $cfm_episode_id ]['captivate_episode_type'] );
							update_post_meta( $pid, 'cfm_episode_exclusivity_date', $captivate_episodes_data[ $cfm_episode_id ]['exclusivity_date'] );

							// bookings.
							$bookings = cfm_get_captivate_bookings( $show_id, $cfm_episode_id );
							if ( 'api_error' != $bookings ) {
								update_post_meta( $pid, 'cfm_episode_bookings', $bookings );
							}

						}
					}

				endwhile;

			endif;

			// delete from WP - ONLY if set to all or delete && all | specific episodes
			if ( count( array_intersect( $do, array( 'all', 'delete' ) ) ) > 0 && ( in_array( 'all', $episode_ids ) || in_array( $cfm_episode_id, $episode_ids ) ) ) {
				$to_delete = array_diff_key( $wp_episode_ids, $captivate_episodes_data );

				if ( ! empty( $to_delete ) ) {

					foreach ( $to_delete as $delete_id => $episode_title ) {

						$get_episode = array(
							'post_type'      => 'captivate_podcast',
							'posts_per_page' => 1,
							'order'          => 'DESC',
							'post_status'    => array( 'publish', 'draft', 'future', 'private' ),
							'meta_query'     => array(
								array(
									'key'     => 'cfm_episode_id',
									'value'   => $delete_id,
									'compare' => '=',
								),
								array(
									'key'     => 'cfm_migrated_stats',
									'compare' => 'NOT EXISTS',
								),
							),
						);

						$episode = new WP_Query( $get_episode );

						if ( $episode->have_posts() ) :

							while ( $episode->have_posts() ) :
								$episode->the_post();

								wp_trash_post( get_the_ID() );

							endwhile;

						endif;

					}
				}
			}

			// insert to WP - ONLY if set to all or create && all
			if ( count( array_intersect( $do, array( 'all', 'create' ) ) ) > 0 && in_array( 'all', $episode_ids ) ) {
				$to_insert = array_diff_key( $captivate_episodes_data, $wp_episode_ids );

				if ( ! empty( $to_insert ) ) {

					foreach ( $to_insert as $result ) {

						if ( cfm_episode_exists( $result['id'] ) ) {
							continue;
						}

						$post_title = $result['title'];
						$published_date = date( 'Y-m-d H:i:s', strtotime( $result['published_date'] ) );

						$post_data = array(
							'post_title'   => wp_encode_emoji( $post_title ),
							'post_content' => wp_encode_emoji( $result['shownotes'] ),
							'post_author'  => cfm_get_show_author( $show_id ),
							'post_type'    => 'captivate_podcast',
							'post_date' 	=> $published_date,
							'post_date_gmt' => get_gmt_from_date( $published_date, 'Y-m-d H:i:s' ),
						);

						if ( $result['slug'] ) {
							$post_data['post_name'] = $result['slug'];
						}

						// status.
						switch ( $result['status'] ) {
							case 'Published':
								$post_status = 'publish';
								break;
							case 'Scheduled':
								$post_status = 'future';
								break;
							case 'Expired':
								$post_status = 'publish';
								break;
							case 'Exclusive':
								$post_status = 'publish';
								break;
							case 'Early Access':
								$post_status = 'publish';
								break;
							default:
								$post_status = 'draft';
						}

						$post_data['post_status'] = $post_status;
						$inserted_pid = wp_insert_post( $post_data );

						update_post_meta( $inserted_pid, 'cfm_episode_status', $result['status'] );
						update_post_meta( $inserted_pid, 'cfm_show_id', $result['shows_id'] );
						update_post_meta( $inserted_pid, 'cfm_episode_id', $result['id']);
						update_post_meta( $inserted_pid, 'cfm_episode_media_id', $result['media_id'] );
						update_post_meta( $inserted_pid, 'cfm_episode_media_url', $result['media_url'] );
						update_post_meta( $inserted_pid, 'cfm_episode_media_name', $result['media_name'] );
						update_post_meta( $inserted_pid, 'cfm_episode_media_size', $result['media_size'] );
						update_post_meta( $inserted_pid, 'cfm_episode_media_bit_rate', $result['media_bit_rate'] );
						$media_bit_rate_str = substr( $result['media_bit_rate'], 0, -3 ) . 'kbps';
						update_post_meta( $inserted_pid, 'cfm_episode_media_bit_rate_str', $media_bit_rate_str );
						update_post_meta( $inserted_pid, 'cfm_episode_media_id3_size', $result['media_id3_size'] );
						update_post_meta( $inserted_pid, 'cfm_episode_media_type', $result['media_type'] );
						update_post_meta( $inserted_pid, 'cfm_episode_media_duration', $result['media_duration'] );
						$media_duration_str = cfm_seconds_to_str( $result['media_duration'] );
						update_post_meta( $inserted_pid, 'cfm_episode_media_duration_str', $media_duration_str );
						update_post_meta( $inserted_pid, 'cfm_episode_artwork', $result['episode_art'] );
						update_post_meta( $inserted_pid, 'cfm_episode_itunes_title', $result['itunes_title'] );
						update_post_meta( $inserted_pid, 'cfm_episode_author', $result['author'] );
						update_post_meta( $inserted_pid, 'cfm_episode_itunes_season', $result['episode_season'] );
						update_post_meta( $inserted_pid, 'cfm_episode_itunes_number', $result['episode_number'] );
						update_post_meta( $inserted_pid, 'cfm_episode_itunes_type', $result['episode_type'] );
						update_post_meta( $inserted_pid, 'cfm_episode_itunes_explicit', $result['explicit'] );
						update_post_meta( $inserted_pid, 'cfm_episode_donation_link', $result['donation_link'] );
						update_post_meta( $inserted_pid, 'cfm_episode_donation_label', $result['donation_text'] );
						update_post_meta( $inserted_pid, 'cfm_episode_seo_title', $result['seo_title'] );
						update_post_meta( $inserted_pid, 'cfm_episode_seo_description', $result['seo_description'] );
						update_post_meta( $inserted_pid, 'cfm_episode_private', $result['episode_private'] );
						update_post_meta( $inserted_pid, 'cfm_episode_expiration', $result['episode_expiration'] );

						// transcriptions.
						$transcription_uploaded = ( null != $result['transcription_file'] && '' != $result['transcription_file'] ) ? 'file' : 'text';
						$transcriptions = array(
							'transcription_uploaded' => $transcription_uploaded,
							'transcription_html' 	 => $result['transcription_html'],
							'transcription_file' 	 => $result['transcription_file'],
							'transcription_json' 	 => $result['transcription_json'],
							'transcription_text' 	 => $result['transcription_text'],
						);
						update_post_meta( $inserted_pid, 'cfm_episode_transcript', $transcriptions );

						// idea data.
						update_post_meta( $inserted_pid, 'cfm_episode_idea_title', $result['idea_title'] );
						update_post_meta( $inserted_pid, 'cfm_episode_idea_summary', $result['idea_summary'] );
						update_post_meta( $inserted_pid, 'cfm_episode_idea_notes', $result['idea_notes'] );
						update_post_meta( $inserted_pid, 'cfm_episode_idea_created_at', $result['idea_created_at'] );
						update_post_meta( $inserted_pid, 'cfm_episode_idea_production_notes', $result['idea_production_notes'] );

						// other post meta.
						update_post_meta( $inserted_pid, 'cfm_episode_amie_status', $result['amie_status'] );
						update_post_meta( $inserted_pid, 'cfm_episode_early_access_end_date', $result['early_access_end_date'] );
						update_post_meta( $inserted_pid, 'cfm_episode_captivate_episode_type', $result['captivate_episode_type'] );
						update_post_meta( $inserted_pid, 'cfm_episode_exclusivity_date', $result['exclusivity_date'] );

						// bookings.
						$bookings = cfm_get_captivate_bookings( $show_id, $cfm_episode_id );
						if ( 'api_error' != $bookings ) {
							update_post_meta( $inserted_pid, 'cfm_episode_bookings', $bookings );
						}
					}
				}
			}

			return true;
		}
		else {
			return false;
		}
    }
endif;

if ( ! function_exists( 'cfm_user_authentication' ) ) :
	/**
	 * Check user authentication
	 *
	 * @since 1.0
	 *
	 * @return string | boolean
	 */
	function cfm_user_authentication() {
		if ( get_transient( 'cfm_authentication_token' ) ) {

			if ( 'FAILED' == get_transient( 'cfm_authentication_token' ) ) {
				return 'failed';
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}
	}
endif;

if ( ! function_exists( 'cfm_is_debugging_on' ) ) :
	/**
	 * Is the debugging on?
	 *
	 * @since 1.0
	 *
	 * @return boolean
	 */
	function cfm_is_debugging_on() {
		return ( '1' == get_option( 'cfm_debugging' ) ) ? true : false;
	}
endif;

if ( ! function_exists( 'cfm_generate_log' ) ) :
	/**
	 * Generate txt log
	 *
	 * @since 2.0.5
	 *
	 * @return
	 */
	function cfm_generate_log( $title = '', $log_data = '', $debug_on = false ) {
		if ( cfm_is_debugging_on() || true === $debug_on ) {
			$log_date = date('Y-m-d H:i:s', time());
			$txt = '**START ' . $title . ' - ' . $log_date . '** ' . PHP_EOL . print_r($log_data, true) . '**END ' . $title . '**';
			$log_file = CFMH . '/logs.txt';

			if (file_exists($log_file) && filesize($log_file) >= 5 * 1024 * 1024) { // 5 MB
				file_put_contents($log_file, ''); // Clear the log file
			}

			$write_log = file_put_contents($log_file, PHP_EOL . $txt . PHP_EOL, FILE_APPEND | LOCK_EX);
		}
	}
endif;

if ( ! function_exists( 'cfm_is_user_has_show' ) ) :
	/**
	 * Is the user show not empty and exists in cfm_shows?
	 *
	 * @since 1.3
	 *
	 * @return boolean
	 */
	function cfm_is_user_has_show() {

		$shows = cfm_get_shows();
		$user_shows = get_user_meta( get_current_user_id(), 'cfm_user_shows', true );

		$show_exists = array();
		if ( ! empty( $shows ) && ! empty( $user_shows ) ) {
			$show_exists = count( array_intersect_key( $shows, $user_shows ) );
		}

		if ( empty( $show_exists ) ) {
			return false;
		}
		else {
			return true;
		}
	}
endif;

if ( ! function_exists( 'cfm_get_show_author' ) ) :
	/**
	 * Get the author show set in cfm_shows
	 *
	 * @since 1.1.4
	 *
	 * @return int $user_id
	 */
	function cfm_get_show_author( $show_id ) {

		$shows    = cfm_get_shows();
		$show_ids = array();

		if ( ! empty( $shows ) ) {
			foreach ( $shows as $show ) {
				$show_ids[ $show['id'] ] = $show['wp_author_id'];
			}
		}

		$author = ( $show_id ) ? (int) $show_ids[ $show_id ] : 0;

		return ( $author != 0 ) ? $author : get_current_user_id();
	}
endif;

if ( ! function_exists( 'cfm_update_transcript' ) ) :
	/**
	 * Update transcript on Captivate
	 *
	 * @since 2.0
	 * @param string $transcript  The file path or textarea.
	 * @param string $episode_id  The episode ID.
	 * @param boolean $updated.
	 *
	 * @return array
	 */
	function cfm_update_transcript( $transcript, $episode_id ) {

		$payload = '';
		$boundary = hash( 'sha256', uniqid( '', true ) );
		$transcript_wp = array(
			'transcription_uploaded' => 'text',
			'transcription_html' 	 => null,
			'transcription_file' 	 => null,
			'transcription_json' 	 => null,
			'transcription_text' 	 => null,
		);

		if ( is_array( $transcript ) && ! empty( $transcript ) ) {

			$file_contents = false;
			$file_contents = file_get_contents( $transcript['tmp_name'] );
			$mime_type = $transcript['type'];
			$base_name = basename( $transcript['name'] );

			if ( false !== $file_contents ) {

				// Upload the file.
				if ( $transcript ) {
					$payload .= '--' . $boundary;
					$payload .= "\r\n";
					$payload .= 'Content-Disposition: form-data; name="file"; filename="' . $base_name . '"' . "\r\n";
					$payload .= 'Content-Type: ' . $mime_type . "\r\n";
					$payload .= "\r\n";
					$payload .= $file_contents;
					$payload .= "\r\n";
				}

				$payload .= '--' . $boundary . '--';

				$request = wp_remote_post( CFMH_API_URL . '/episodes/' . $episode_id . '/transcript',
					array(
						'timeout' => 500,
						'body'    => $payload,
						'headers' => array(
							'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
							'content-type'  => 'multipart/form-data; boundary=' . $boundary,
						),
					)
				);

				// Debugging.
				cfm_generate_log( '*UPDATE TRANSCRIPT FILE', $request );

				$body = json_decode( $request['body'] );

				if ( isset( $body->success ) ) {
					$transcript_wp['transcription_uploaded'] = 'file';
					$transcript_wp['transcription_html'] = $body->episode->transcription_html;
					$transcript_wp['transcription_file'] = $body->episode->transcription_file;
					$transcript_wp['transcription_json'] = $body->episode->transcription_json;
					$transcript_wp['transcription_text'] = $body->episode->transcription_text;
				}

			}
		}
		else {

			if ( $transcript ) {
				$payload .= '--' . $boundary;
				$payload .= "\r\n";
				$payload .= 'Content-Disposition: form-data; name="text"' . "\r\n";
				$payload .= "\r\n";
				$payload .= $transcript;
				$payload .= "\r\n";
			}

			$payload .= '--' . $boundary . '--';

			$request = wp_remote_post( CFMH_API_URL . '/episodes/' . $episode_id . '/transcript',
				array(
					'timeout' => 500,
					'body'    => $payload,
					'headers' => array(
						'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
						'content-type'  => 'multipart/form-data; boundary=' . $boundary,
					),
				)
			);

			// Debugging.
			cfm_generate_log( '*UPDATE TRANSCRIPT TEXT', $request );

			$body = json_decode( $request['body'] );

			if ( isset( $body->success ) ) {
				$transcript_wp['transcription_uploaded'] = 'text';
				$transcript_wp['transcription_html'] = $body->episode->transcription_html;
				$transcript_wp['transcription_file'] = $body->episode->transcription_file;
				$transcript_wp['transcription_json'] = $body->episode->transcription_json;
				$transcript_wp['transcription_text'] = $body->episode->transcription_text;
			}

		}

		return $transcript_wp;

	}
endif;

if ( ! function_exists( 'cfm_generate_random_string' ) ) :
	/**
	 * Generate random string
	 *
	 * @since 3.0
	 * @param int $length  The length of random string.
	 *
	 * @return string
	 */
	function cfm_generate_random_string( $length = 29 ) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$characters_length = strlen( $characters );
		$random_string = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$random_string .= $characters[rand( 0, $characters_length - 1 )];
		}
		return $random_string;
	}
endif;

if ( ! function_exists( 'cfm_seconds_to_str' ) ) :
	/**
	 * Convert seconds to string
	 *
	 * @since 3.0
	 * @param int $input_seconds  The duration in seconds.
	 *
	 * @return string
	 */
	function cfm_number_ending( $number ) {
		return ( $number > 1 ) ? 's' : '';
	}
	function cfm_seconds_to_str( $input_seconds ) {
		$hours = floor( $input_seconds / 3600 );
		$minutes = floor( ( $input_seconds / 60 ) % 60 );
		$seconds = $input_seconds % 60;

		if ( $hours ) {
			return $hours . ' hour' . cfm_number_ending( $hours ) . ', ' . $minutes . ' minute' . cfm_number_ending( $minutes ) . ', ' . $seconds . ' second' . cfm_number_ending( $seconds );
		}
		if ( $minutes ) {
			return $minutes . ' minute' . cfm_number_ending( $minutes ) . ', ' . $seconds . ' second' . cfm_number_ending( $seconds );
		}
		if ( $seconds ) {
			return $seconds . ' second' . cfm_number_ending( $seconds );
		}
		return 'less than a second'; //'just now'
	}
endif;

if ( ! function_exists( 'cfm_captivate_player' ) ) :
	/**
	 * Captivate Player
	 *
	 * @since 3.0
	 * @param string $post_id  The post ID
	 * @param string $classes  Optional div classes separated by space
	 *
	 * @return HTML
	 */
	function cfm_captivate_player( $post_id, $classes = 'cfm-player-iframe' ) {

		$post_status = get_post_status( $post_id );

		if ( 'trash' == $post_status || false === $post_status ) {
			return;
		}

		$cfm_episode_id = get_post_meta( $post_id, 'cfm_episode_id', true );
		$cfm_episode_media_id = get_post_meta( $post_id, 'cfm_episode_media_id', true );

		if ( $cfm_episode_media_id ) {
			$output = '<div class="' . $classes . '" style="width: 100%; height: 200px; margin-bottom: 20px; border-radius: 6px; overflow:hidden;"><iframe style="width: 100%; height: 200px;" frameborder="no" scrolling="no" seamless allow="autoplay" src="' . CFMH_PLAYER_URL . '/episode/' . $cfm_episode_id . '"></iframe></div>';
		}
		else {
			if ( is_user_logged_in() ) {
				$output = '<div class="' . $classes . '" style="width: 100%; margin-bottom: 20px; border-radius: 6px; overflow:hidden; border: 1px solid #d6d6d6;"><div class="cfm-sorry-text">Sorry, there\'s no audio file uploaded to this episode yet.</div></div>';
			}
		}

		return $output;
	}
endif;

if ( ! function_exists( 'cfm_get_inactive_episodes' ) ) :
	/**
	 * Get inactive episodes
	 *
	 * @since 3.0
	 *
	 * @return array (post ids)
	 */
	function cfm_get_inactive_episodes() {

		$q = new WP_Query();
		$episode_ids = $q->query(
			array(
				'post_type'      => 'captivate_podcast',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'cfm_episode_website_active',
						'value'   => '0',
						'compare' => '=',
					),
				),
				'fields'     => 'ids',
			)
		);

		return $episode_ids;
	}
endif;

if ( ! function_exists( 'cfm_get_private_episodes' ) ) :
	/**
	 * Get private episodes
	 *
	 * @since 3.0
	 *
	 * @return array (post ids)
	 */
	function cfm_get_private_episodes() {

		$q = new WP_Query();
		$episode_ids = $q->query(
			array(
				'post_type'      => 'captivate_podcast',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'cfm_episode_private',
						'value'   => '1',
						'compare' => '=',
					),
				),
				'fields'     => 'ids',
			)
		);

		return $episode_ids;
	}
endif;

if ( ! function_exists( 'cfm_get_episode_ids_by_status' ) ) :
	/**
	 * Get episode ids by status
	 *
	 * @since 3.0
	 *
	 * @param array $status array( 'all', 'Expired', 'Published', 'Draft', 'Scheduled', 'Exclusive', 'Early Access')
	 * @return array (post ids)
	 */
	function cfm_get_episode_ids_by_status( $status = array( 'all' ) ) {

		$q = new WP_Query();
		$args = array(
			'post_type'      => 'captivate_podcast',
			'posts_per_page' => -1,
			'fields'     => 'ids',
		);

		if ( ! in_array( 'all', $status ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'cfm_episode_status',
					'value'   => $status,
					'compare' => 'IN',
				)
			);
		}
		$episode_ids = $q->query( $args );

		return $episode_ids;
	}
endif;

if ( ! function_exists( 'cfm_get_episode_ids_by_type' ) ) :
	/**
	 * Get episode ids by type
	 *
	 * @since 3.0
	 *
	 * @param array $types array( 'all', 'standard', 'exclusive', 'early')
	 * @return array (post ids)
	 */
	function cfm_get_episode_ids_by_type( $types = array( 'all' ) ) {

		$q = new WP_Query();
		$args = array(
			'post_type'      => 'captivate_podcast',
			'posts_per_page' => -1,
			'fields'     => 'ids',
		);

		if ( ! in_array( 'all', $types ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'cfm_episode_captivate_episode_type',
					'value'   => $types,
					'compare' => 'IN',
				)
			);
		}
		$episode_ids = $q->query( $args );

		return $episode_ids;
	}
endif;

if ( ! function_exists( 'cfm_get_dynamic_text' ) ) :
	/**
	 * Get dynamic text
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	function cfm_get_dynamic_text( $show_id, $type = array( 'all' ), $name = array( 'all' ) ) {

		$dynamic_text = cfm_get_show_info( $show_id, 'dynamic_text' );
		$dynamic_text = json_decode( $dynamic_text );
		$dynamic_text = isset( $dynamic_text->dynamic_text ) ? $dynamic_text->dynamic_text : array();

		// store show dynamic text in array.
		$dynamic_text_array = array();
		if ( is_array( $dynamic_text ) && ! empty( $dynamic_text ) ) {
			foreach ( $dynamic_text as $dt ) {

				if ( count( array_intersect( $type, array( 'all', 'snippet' ) ) ) > 0 && ( in_array( 'all', $name ) || in_array( $dt->name, $name ) ) ) {
					if ( 'snippet' == $dt->type ) {
						$dynamic_text_array[$dt->name] = array(
							'type' => $dt->type,
							'name' => $dt->name,
							'name_human' => $dt->name_human,
							'value' => $dt->value,
							'created_at' => $dt->created_at,
							'updated_at' => $dt->updated_at
						);
					}
				}

				if ( count( array_intersect( $type, array( 'all', 'variable' ) ) ) > 0 && ( in_array( 'all', $name ) || in_array( $dt->name, $name ) ) ) {
					if ( 'variable' == $dt->type ) {
						$dynamic_text_array[$dt->name] = array(
							'type' => $dt->type,
							'name' => $dt->name,
							'name_human' => $dt->name_human,
							'value' => $dt->value,
							'created_at' => $dt->created_at,
							'updated_at' => $dt->updated_at
						);
					}
				}

				if ( count( array_intersect( $type, array( 'all', 'shownotes_template' ) ) ) > 0 && ( in_array( 'all', $name ) || in_array( $dt->name, $name ) ) ) {
					if ( 'shownotes_template' == $dt->type ) {
						$dynamic_text_array[$dt->name] = array(
							'type' => $dt->type,
							'name' => $dt->name,
							'name_human' => $dt->name_human,
							'value' => $dt->value,
							'created_at' => $dt->created_at,
							'updated_at' => $dt->updated_at
						);
					}
				}

			}
		}

		return $dynamic_text_array;

	}
endif;

if ( ! function_exists( 'cfm_translate_dynamic_text' ) ) :
	/**
	 * Translate dynamic text
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	function cfm_translate_dynamic_text( $show_id, $post_id = '0', $content = '', $exclude_var = array() ) {

		$output = '';

		if ( $show_id && ( ! empty( $content ) || '' != $content ) ) {

			// loop through dynamic text vars from content.
			preg_match_all('/{{([^{}]*)}}/', $content, $matches);
			$first_vars = cfm_dynamic_text_vars( $show_id, $post_id, $content, $matches[1], $exclude_var );

			// 2nd loop - for blocks that contains dynamic shortcodes.
			preg_match_all('/{{([^{}]*)}}/', $first_vars, $matches);
			$second_vars = cfm_dynamic_text_vars( $show_id, $post_id, $first_vars, $matches[1], $exclude_var );

			$output = $second_vars;
		}

		return $output;
	}

	function cfm_dynamic_text_vars( $show_id, $post_id, $content, $matches, $exclude_var ) {

		$output = '';
		$dynamic_text = cfm_get_dynamic_text( $show_id, array( 'all' ), array( 'all' ) );

		// store show dynamic text in array.
		$dynamic_text_array = array();
		if ( is_array( $dynamic_text ) && ! empty( $dynamic_text ) ) {
			foreach ( $dynamic_text as $dt ) {
				$dynamic_text_array[$dt['name']] = $dt['value'];
			}
		}

		foreach ( $matches as $dt_var ) {

			// translate only valid slug.
			if ( preg_match( '/^[A-Za-z0-9]+(?:[_-][A-Za-z0-9]+)*$/', $dt_var ) ) {

				if ( strpos( $dt_var, 'd-show' ) !== false ) {
					$dt_show_selector = explode( 'd-show-', $dt_var );
					$dt_show_selector = str_replace( '-', '_', $dt_show_selector[1] );
					$dt_show_info = cfm_get_show_dynamic_text( $show_id, $dt_show_selector );
					$content = str_replace( '{{' . $dt_var . '}}', $dt_show_info, $content );
				}
				else if ( strpos( $dt_var, 'd-episode' ) !== false ) {

					if ( strpos( $dt_var, 'd-episode-idea' ) !== false ) {
						if ( ! in_array( 'd-episode-idea', $exclude_var ) ) {
							$dt_episode_selector = explode( 'd-episode-', $dt_var );
							$dt_episode_selector = str_replace( '-', '_', $dt_episode_selector[1] );
							$dt_episode_info = cfm_get_episode_dynamic_text( $show_id, $post_id, $dt_episode_selector );
							$content = str_replace( '{{' . $dt_var . '}}', $dt_episode_info, $content );
						}
					}
					else if ( strpos( $dt_var, 'd-episode-title' ) !== false ) {
						if ( ! in_array( 'd-episode-title', $exclude_var ) ) {
							$dt_episode_info = cfm_get_episode_dynamic_text( $show_id, $post_id, 'title' );
							$content = str_replace( '{{' . $dt_var . '}}', $dt_episode_info, $content );
						}
					}
					else if ( strpos( $dt_var, 'd-episode-type' ) !== false ) {
						if ( ! in_array( 'd-episode-type', $exclude_var ) ) {
							$dt_episode_info = cfm_get_episode_dynamic_text( $show_id, $post_id, 'type' );
							$content = str_replace( '{{' . $dt_var . '}}', $dt_episode_info, $content );
						}
					}
					else if ( strpos( $dt_var, 'd-episode-season' ) !== false ) {
						if ( ! in_array( 'd-episode-season', $exclude_var ) ) {
							$dt_episode_info = cfm_get_episode_dynamic_text( $show_id, $post_id, 'season' );
							$content = str_replace( '{{' . $dt_var . '}}', $dt_episode_info, $content );
						}
					}
					else if ( strpos( $dt_var, 'd-episode-number' ) !== false ) {
						if ( ! in_array( 'd-episode-number', $exclude_var ) ) {
							$dt_episode_info = cfm_get_episode_dynamic_text( $show_id, $post_id, 'number' );
							$content = str_replace( '{{' . $dt_var . '}}', $dt_episode_info, $content );
						}
					}
					else if ( strpos( $dt_var, 'd-episode-explicit' ) !== false ) {
						if ( ! in_array( 'd-episode-explicit', $exclude_var ) ) {
							$dt_episode_info = cfm_get_episode_dynamic_text( $show_id, $post_id, 'explicit' );
							$content = str_replace( '{{' . $dt_var . '}}', $dt_episode_info, $content );
						}
					}
					else {
						if ( ! in_array( 'd-episode', $exclude_var ) ) {
							$dt_episode_selector = explode( 'd-episode-', $dt_var );
							$dt_episode_selector = str_replace( '-', '_', $dt_episode_selector[1] );
							$dt_episode_info = cfm_get_episode_dynamic_text( $show_id, $post_id, $dt_episode_selector );
							$content = str_replace( '{{' . $dt_var . '}}', $dt_episode_info, $content );
						}
					}
				}
				else if ( strpos( $dt_var, 'd-guest' ) !== false ) {
					if ( ! in_array( 'd-guest', $exclude_var ) ) {
						$dt_guest_selector = explode( 'd-guest-', $dt_var );
						$dt_guest_info = cfm_get_guest_dynamic_text( $show_id, $post_id, $dt_guest_selector[1] );
						$content = str_replace( '{{' . $dt_var . '}}', $dt_guest_info, $content );
					}
				}
				else if ( strpos( $dt_var, 'd-short-link' ) !== false ) {
					if ( ! in_array( 'd-short-link', $exclude_var ) ) {
						$dt_short_link_selector = explode( 'd-short-link-', $dt_var );
						$dt_short_link_info = cfm_get_attribution_link_dynamic_text( $show_id, $dt_short_link_selector[1] );
						$content = str_replace( '{{' . $dt_var . '}}', $dt_short_link_info, $content );
					}
				}
				else if ( strpos( $dt_var, 'd-research-links-list' ) !== false ) {
					if ( ! in_array( 'd-research-links-list', $exclude_var ) ) {
						$dt_research_link_info = cfm_get_research_link_dynamic_text( $post_id, 'd-research-links-list' );
						$content = str_replace( '{{' . $dt_var . '}}', $dt_research_link_info, $content );
					}
				}
				else if ( strpos( $dt_var, 'd-condition' ) !== false ) {

					$itunes_type = get_post_meta( $post_id, 'cfm_episode_itunes_type', true );

					$parsed_trailer = cfm_get_string_between( $content, '{{d-condition-ep-type-trailer}}', '{{d-condition-end}}' );
					$parsed_bonus = cfm_get_string_between( $content, '{{d-condition-ep-type-bonus}}', '{{d-condition-end}}' );
					$parsed_full = cfm_get_string_between( $content, '{{d-condition-ep-type-full}}', '{{d-condition-end}}' );
					$parsed_has_guests = cfm_get_string_between( $content, '{{d-condition-ep-has-guests}}', '{{d-condition-end}}' );

					$bookings = get_post_meta( $post_id, 'cfm_episode_bookings', true );
					$bookings = json_decode( $bookings );
					$bookings = isset( $bookings->bookings ) ? $bookings->bookings : array();

					if ( 'trailer' == $itunes_type )  {
						$content = str_replace( '{{d-condition-ep-type-trailer}}' . $parsed_trailer . '{{d-condition-end}}', $parsed_trailer, $content );
					}
					else {
						$content = str_replace( '{{d-condition-ep-type-trailer}}' . $parsed_trailer . '{{d-condition-end}}', '', $content );
					}

					if ( 'bonus' == $itunes_type )  {
						$content = str_replace( '{{d-condition-ep-type-bonus}}' . $parsed_bonus . '{{d-condition-end}}', $parsed_bonus, $content );
					}
					else {
						$content = str_replace( '{{d-condition-ep-type-bonus}}' . $parsed_bonus.  '{{d-condition-end}}', '', $content );
					}

					if ( 'full' == $itunes_type )  {
						$content = str_replace( '{{d-condition-ep-type-full}}' . $parsed_full . '{{d-condition-end}}', $parsed_full, $content );
					}
					else {
						$content = str_replace( '{{d-condition-ep-type-full}}' . $parsed_full . '{{d-condition-end}}', '', $content );
					}

					if ( is_array( $bookings ) && ! empty( $bookings ) ) {
						$content = str_replace( '{{d-condition-ep-has-guests}}' . $parsed_has_guests . '{{d-condition-end}}', $parsed_has_guests, $content );
					}
					else {
						$content = str_replace( '{{d-condition-ep-has-guests}}' . $parsed_has_guests . '{{d-condition-end}}', '', $content );
					}

				}
				else {
					if ( ! in_array( $dt_var, $exclude_var ) ) {
						if ( array_key_exists( $dt_var, $dynamic_text_array ) ) {
							$content = str_replace( '{{' . $dt_var . '}}', $dynamic_text_array[$dt_var], $content );
						}
						else {
							$content = str_replace( '{{' . $dt_var . '}}', '', $content );
						}
					}
				}
			}
		}

		// remove excess end condition vars.
		$content = str_replace( '{{d-condition-end}}', '', $content );

		$output = $content;

		return $output;
	}

	function cfm_get_string_between( $string, $start, $end ) {
		$string = ' ' . $string;
		$ini = strpos($string, $start);
		if ($ini == 0) return '';
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len);
	}
endif;


if ( ! function_exists( 'cfm_get_show_dynamic_text' ) ) :
	/**
	 * Get show dynamic text
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	function cfm_get_show_dynamic_text( $show_id, $selector ) {

		$output = '';

		if ( $show_id && $selector ) {

			$site_link = cfm_get_captivate_site_link( $show_id );
			$marketing_links = cfm_get_show_marketing_links( $show_id );

			switch ( $selector ) {
				case "copyright":
					$copyright = cfm_get_show_info( $show_id, 'copyright' );
					$author = cfm_get_show_info( $show_id, 'author' );
					$output = ( '' != $copyright ) ? $copyright : 'Copyright ' . date( 'Y' ) . ' ' . $author;
					break;
				case "donation_link":
					$donation_text = cfm_get_show_info( $show_id, 'donation_text' );
					$donation_text = ( '' != $donation_text ) ? $donation_text : 'Support this Podcast';
					$donation_link = cfm_get_show_info( $show_id, 'donation_link' );
					$output = ( '' != $donation_link ) ? '<a href="' . esc_url( $donation_link ) . '" target="_blank">' . esc_html( $donation_text ) . '</a>' : '';
					break;
				case "listen_link":
					$listen_link = ( '' != $site_link ) ? $site_link . '/listen' : '';
					$output = ( '' != $listen_link ) ? '<a href="' . esc_url( $listen_link ) . '" target="_blank">Listen to ' . esc_html( cfm_get_show_info( $show_id, 'title' ) ) . '</a>' : '';
					break;
				case "site_link":
					$output = ( '' != $site_link ) ? '<a href="' . esc_url( $site_link ) . '" target="_blank">' . esc_html( cfm_get_show_info( $show_id, 'title' ) ) . ' website</a>' : '';
					break;
				case "affiliate_link":
					if ( ! empty( $marketing_links ) && $marketing_links->affiliate ) {
						$output = '<a href="' . esc_url( $marketing_links->affiliate ) . '" target="_blank">This podcast is hosted by Captivate, try it yourself for free.</a>';
					}
					else {
						$output = '';
					}
					break;
				case "support_link":
					$output = ( '' != $site_link ) ? '<a href="' . esc_url( $site_link ) . '/support" target="_blank">Support ' . esc_html( cfm_get_show_info( $show_id, 'title' ) ) . '</a>' : '';
					break;
				default:
					$output = cfm_get_show_info( $show_id, $selector );
			}
		}

		return $output;
	}
endif;

if ( ! function_exists( 'cfm_get_episode_dynamic_text' ) ) :
	/**
	 * Get episode dynamic text
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	function cfm_get_episode_dynamic_text( $show_id, $post_id, $selector ) {

		$output = '';

		if ( $selector ) {
			switch ( $selector ) {
				case "title":
					$output = ( '0' != $post_id ) ? get_the_title( $post_id ) : 'Untitled Episode';
					break;
				case "author":
					$output = ( '0' != $post_id ) ? get_post_meta( $post_id, 'cfm_episode_author', true ) : cfm_get_show_info( $show_id, 'author' );
					break;
				case "link":
					$output = ( '0' != $post_id ) ? '<a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a>' : '';
					break;
				case "type":
					$output = ( '0' != $post_id ) ? ucwords( get_post_meta( $post_id, 'cfm_episode_itunes_type', true ) ) : '';
					break;
				case "explicit":
					$explicit = ( '0' != $post_id ) ? get_post_meta( $post_id, 'cfm_episode_itunes_explicit', true ) : '';
					$output = ( '0' == $explicit ) ? ucwords( cfm_get_show_info( $show_id, 'explicit' ) ) : ucwords( $explicit );
					break;
				case "number":
					$output = ( '0' != $post_id ) ? get_post_meta( $post_id, 'cfm_episode_itunes_number', true ) : '';
					break;
				case "season":
					$output = ( '0' != $post_id ) ? get_post_meta( $post_id, 'cfm_episode_itunes_season', true ) : '';
					break;
				default:
					$output = ( '0' != $post_id ) ? get_post_meta( $post_id, 'cfm_episode_' . $selector, true ) : '';
			}
		}

		return $output;
	}
endif;

if ( ! function_exists( 'cfm_get_attribution_link_dynamic_text' ) ) :
	/**
	 * Get attribution dynamic text
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	function cfm_get_attribution_link_dynamic_text( $show_id, $selector, $return = 'dynamic_text' ) {

		$output = '';

		if ( $show_id && $selector ) {

			$attribution_links = cfm_get_show_info( $show_id, 'attribution_links' );
			$attribution_links = json_decode( $attribution_links );
			$attribution_links_array = array();

			// store attribution links in array.
			if ( is_array( $attribution_links ) && ! empty( $attribution_links ) ) {

				foreach ( $attribution_links as $al ) {
					$attribution_links_array[$al->slug] = array(
						'id' => $al->id,
						'show_id' => $al->show_id,
						'type' => $al->type,
						'label' => $al->label,
						'target_url' => $al->target_url,
						'slug' => $al->slug,
						'active' => $al->active,
					);
				}

				$active = $attribution_links_array[$selector]['active'];
				$label = $attribution_links_array[$selector]['label'];
				$slug = $attribution_links_array[$selector]['slug'];

				// clickable link output if active.
				if ( 1 == $active ) {
					if ( 'label' == $return ) {
						$output = 'Attribution Link: ' . esc_html( $label );
					}
					else {
						$output = '<a href="' . esc_url( cfm_get_captivate_site_link( $show_id ) . '/' . $slug ) . '" rel="noopener noreferrer" target="_blank">' . esc_html( $label ) . '</a>';
					}
				}

			}
		}

		return $output;
	}
endif;

if ( ! function_exists( 'cfm_get_guest_dynamic_text' ) ) :
	/**
	 * Get guest dynamic text
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	function cfm_get_guest_dynamic_text( $show_id, $post_id, $selector, $return = 'dynamic_text' ) {

		$output = '';

		if ( $show_id && $selector ) {

			$selector = explode( '-', $selector );
			$selector_array = array();

			if ( is_array( $selector ) && ! empty ( $selector ) ) {
				foreach ( $selector as $s ) {
					$selector_array[] = $s;
				}
			}
			$selector_id = array_slice( $selector_array, -5, 5, true );
			$selector_id = implode( '-', $selector_id );
			$selector_name = array_slice( $selector_array, 0, -5, true );
			$selector_name = implode( '-', $selector_name );

			$bookings = get_post_meta( $post_id, 'cfm_episode_bookings', true );
			$bookings = json_decode( $bookings );
			$bookings = isset( $bookings->bookings ) ? $bookings->bookings : array();
			$bookings_array = array();

			// store bookings in array.
			if ( is_array( $bookings ) && ! empty( $bookings ) ) {

				foreach ( $bookings as $b ) {
					$bookings_array[$b->show_guest_id] = array(
						'id' => $b->show_guest_id,
						'show_id' => $b->show_id,
						'first_name' => $b->guest_first_name,
						'last_name' => $b->guest_last_name,
						'email' => $b->guest_email,
						'biography' => $b->guest_biography,
						'fb_page_url' => $b->guest_fb_page_url,
						'fb_group_url' => $b->guest_fb_group_url,
						'twitter_username' => $b->guest_twitter_username,
						'insta_username' => $b->guest_insta_username,
						'linkedin_url' => $b->guest_linkedin_url,
						'youtube_url' => $b->guest_youtube_url,
						'additional_url_1' => $b->guest_additional_url_1,
						'created_at' => $b->created_at,
						'updated_at' => $b->updated_at
					);
				}

				if ( array_key_exists( $selector_id, $bookings_array ) ) {

					$biography 			= $bookings_array[$selector_id]['biography'];
					$first_name 		= $bookings_array[$selector_id]['first_name'];
					$last_name 			= $bookings_array[$selector_id]['last_name'];
					$full_name 			= $first_name . ' ' . $last_name;
					$fb_page_url 		= $bookings_array[$selector_id]['fb_page_url'];
					$fb_group_url 		= $bookings_array[$selector_id]['fb_group_url'];
					$insta_username 	= $bookings_array[$selector_id]['insta_username'];
					$twitter_username 	= $bookings_array[$selector_id]['twitter_username'];
					$youtube_url 		= $bookings_array[$selector_id]['youtube_url'];
					$additional_url_1 	= $bookings_array[$selector_id]['additional_url_1'];

					if ( 'label' == $return ) {
						switch ( $selector_name ) {
							case "bio":
								$output = 'Guest bio: ' . esc_html( $full_name );
								break;
							case "name":
								$output = 'Guest name: ' . esc_html( $full_name );
								break;
							case "fb-page":
								$output = 'Guest Facebook Page: ' . esc_html( $full_name );
								break;
							case "fb-group":
								$output = 'Guest Facebook Group: ' . esc_html( $full_name );
								break;
							case "instagram":
								$output = 'Guest Instagram: ' . esc_html( $full_name );
								break;
							case "twitter":
								$output = 'Guest Twitter: ' . esc_html( $full_name );
								break;
							case "youtube":
								$output = 'Guest YouTube: ' . esc_html( $full_name );
								break;
							case "url":
								$output = 'Guest URL: ' . esc_html( $full_name );
								break;
							default:
								$output = '';
						}
					}
					else {
						switch ( $selector_name ) {
							case "bio":
								$output = $biography;
								break;
							case "name":
								$output = $first_name . ' ' . $last_name;
								break;
							case "fb-page":
								$output = ( '' != $fb_page_url ) ? '<a href="' . esc_url( $fb_page_url ) . '" rel="noopener noreferrer" target="_blank">' . esc_html( $first_name ) . '\'s' . ' Facebook page</a>' : '';
								break;
							case "fb-group":
								$output = ( '' != $fb_group_url ) ? '<a href="' . esc_url( $fb_group_url ) . '" rel="noopener noreferrer" target="_blank">' . esc_html( $first_name ) . '\'s' . ' Facebook group</a>' : '';
								break;
							case "instagram":
								$output = ( '' != $insta_username ) ? '<a href="' . esc_url ( 'https://instagram.com/' . $insta_username ) . '" rel="noopener noreferrer" target="_blank">' . esc_html( '@' . $insta_username ) . ' on Instagram</a>' : '';
								break;
							case "twitter":
								$output = ( '' != $twitter_username ) ? '<a href="' . esc_url ('https://twitter.com/' . $twitter_username ) . '" rel="noopener noreferrer" target="_blank">' . esc_html( '@' . $twitter_username ) . ' on Twitter</a>' : '';
								break;
							case "youtube":
								$output = ( '' != $youtube_url ) ? '<a href="' . esc_url( $youtube_url ) . '" rel="noopener noreferrer" target="_blank">' . esc_html( $first_name ) . ' on YouTube</a>' : '';
								break;
							case "url":
								$output = ( '' != $additional_url_1 ) ? '<a href="' . esc_attr( $additional_url_1 ) . '" rel="noopener noreferrer" target="_blank">' . esc_html( $first_name ) . '\'s' . ' Website</a>' : '';
								break;
							default:
								$output = '';
						}
					}
				}

			}
		}

		return $output;
	}
endif;

if ( ! function_exists( 'cfm_get_research_link_dynamic_text' ) ) :
	/**
	 * Get research links dynamic text
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	function cfm_get_research_link_dynamic_text( $post_id = 0, $selector = '' ) {

		$output = '';

		if ( 0 != $post_id && $selector ) {

			$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );
			$cfm_episode_id = get_post_meta( $post_id, 'cfm_episode_id', true );

			$research_links = cfm_get_show_info( $cfm_show_id, 'research_links' );
			$research_links = json_decode( $research_links );
			$research_links = isset( $research_links->research_links ) ? $research_links->research_links : array();
			$research_links_array = array();

			// store research links in array if assigned to episode.
			if ( is_array( $research_links ) && ! empty( $research_links ) ) {
				foreach ( $research_links as $rl ) {
					if ( in_array( $cfm_episode_id, $rl->episodeIds ) ) {
						$research_links_array[$rl->id] = array(
							'id' => $rl->id,
							'show_id' => $rl->show_id,
							'url' => $rl->url,
							'title' => $rl->title,
							'notes' => $rl->notes,
							'created_at' => $rl->created_at,
							'updated_at' => $rl->updated_at,
							'episodeIds' => $rl->episodeIds
						);
					}
				}
			}

			// output list.
			if ( ! empty( $research_links_array ) ) {
				$output .= '<p>' . cfm_get_show_info( $cfm_show_id, 'research_links_header' ) . '</p>';
				$output .= '<ul class="cfm-research-links">';
				foreach ( $research_links_array as $link ) {
					$notes = ( '' != $link['notes'] ) ? ' - ' . $link['notes'] : '';
					$notes = str_replace( ['<p>', '</p>'], '', $notes );
					$output .= '<li><a href="' . esc_url( $link['url'] ) . '" rel="noopener noreferrer" target="_blank">' . esc_html( $link['title'] ) . '</a>' . $notes . '</li>';
				}
				$output .= '</ul>';
			}

		}

		return $output;
	}
endif;

if ( ! function_exists( 'cfm_get_se_num_format' ) ) :
	/**
	 * Get season and episode number format
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	function cfm_get_se_num_format( $post_id ) {

		$output = '';

		if ( 0 != $post_id ) {

			$cfm_episode_itunes_type = get_post_meta( $post_id, 'cfm_episode_itunes_type', true );
			$cfm_episode_itunes_season = get_post_meta( $post_id, 'cfm_episode_itunes_season', true );
			$cfm_episode_itunes_number = get_post_meta( $post_id, 'cfm_episode_itunes_number', true );

			$cfm_general_settings = get_option( 'cfm_general_settings' );
			$season_episode_number_text = isset( $cfm_general_settings['season_episode_number_text'] ) ? $cfm_general_settings['season_episode_number_text'] : 'S{snum} E{enum}: ';
			$bonus_trailer_text = isset( $cfm_general_settings['bonus_trailer_text'] ) ? $cfm_general_settings['bonus_trailer_text'] : 'S{snum} {enum} Episode: ';

			// per show.
			$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );
			$show_se_number_text = cfm_get_show_info( $cfm_show_id, 'season_episode_number_text' );
			$show_bt_text = cfm_get_show_info( $cfm_show_id, 'bonus_trailer_text' );

			if ( $show_se_number_text ) {
				$season_episode_number_text = $show_se_number_text;
			}
			if ( $show_bt_text ) {
				$bonus_trailer_text = $show_bt_text;
			}

			// text format.
			$se_num_text_format = in_array( $cfm_episode_itunes_type, ['bonus', 'trailer'] ) ? $bonus_trailer_text : $season_episode_number_text;

			// replace snum and enum with value.
			if ( stripos( strtolower( $se_num_text_format), '{snum}' ) !== false ) {
				$se_num_text_format = str_replace( '{snum}', $cfm_episode_itunes_season, $se_num_text_format );
			}
			if ( stripos( strtolower( $se_num_text_format ), '{enum}') !== false ) {
				if ( in_array( $cfm_episode_itunes_type, ['bonus', 'trailer'] ) ) {
					$cfm_episode_itunes_number = ucwords( $cfm_episode_itunes_type );
				}
				$se_num_text_format = str_replace( '{enum}', $cfm_episode_itunes_number, $se_num_text_format );
			}

			// output.
			$output = $se_num_text_format;
		}

		return $output;
	}
endif;

if ( ! function_exists( 'cfm_image_get_contents' ) ) :
	/**
	 * file_get_contents replacement for image upload.
	 *
	 * @since 2.0.22
	 *
	 * @return string
	 */
	function cfm_image_get_contents( $url ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_URL, $url );
		$data = curl_exec( $ch );
		curl_close( $ch );

		return $data;
	}
endif;

/**
 * Modify episodes permalink depending on index page
 */
add_filter( 'post_type_link', function ( $post_link, $post, $leavename, $sample ) {
	if ( $post->post_type == 'captivate_podcast' ) {

		$cfm_show_id = get_post_meta( $post->ID, 'cfm_show_id', true );
		$cfm_show_page = cfm_get_show_page( $cfm_show_id, 'slug' );

		$post_link = get_bloginfo( 'url' ) . '/' . $cfm_show_page . '/' . $post->post_name;
		$post_link = user_trailingslashit( $post_link );
	}

	return $post_link;
}, 999, 4 );

/**
 * Allow custom tags for shownotes content editor (dynamic text)
 */
add_filter('wp_kses_allowed_html', 'cfm_kses_allowed_tags');
function cfm_kses_allowed_tags( $allowed_tags ) {

	if (
        ( is_admin() && isset($_GET['page']) && ( $_GET['page'] === 'cfm-hosting-publish-episode' || $_GET['page'] === 'cfm-hosting-edit-episode' ) && isset($_GET['show_id']) )
        ||
        ( !is_admin() && ( is_post_type_archive('captivate_podcast') || is_singular('captivate_podcast') || is_tax('captivate_podcast') ) )
    ) {
        $allowed_tags['dt-variable'] = array(
            'data-dt-name' => true,
            'data-conditional-depth' => true,
        );
        $allowed_tags['span'] = array(
            'contenteditable' => true,
        );
    }

    return $allowed_tags;
}

if ( ! function_exists( 'cfm_sync_plugin_version' ) ) :
	/**
	 * Update sync_version on Captivate
	 *
	 * @since 3.0
	 * @param string  $show_id  The show ID.
	 *
	 * @return string sync_version
	 */
	function cfm_sync_plugin_version( $show_id ) {

		$show_info = array();

		$show_info['sync_version'] = CFMH_VERSION;

		$response = wp_remote_request( CFMH_API_URL . '/shows/' . $show_id . '/sync/version', array(
			'timeout' => 500,
			'body'    => $show_info,
			'method'  => 'PUT',
			'headers' => array(
				'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
			),
		) );

		// Debugging.
		cfm_generate_log( 'SYNC VERSION (' . CFMH_VERSION . ')', $response );

		return ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ? json_decode( $response['body'] )->sync_version : 'api_error';
    }
endif;

if ( ! function_exists( 'cfm_add_media_prefixes' ) ) :
	/**
	 * Add third-party analytics prefixes to the media url
	 *
	 * @since 2.0.24
	 * @param string  $show_id  The show ID.
	 * @param string  $media_url  media file URL.
	 *
	 * @return string media url
	 */
	function cfm_add_media_prefixes( $show_id, $media_url ) {

		$prefixes = cfm_get_show_info( $show_id, 'prefixes' );
		$prefixes = ! empty( $prefixes ) ? json_decode( $prefixes ) : [];
		$chain_of_prefixes = false;

		if ( count( $prefixes ) > 0 ) {
			$last_char_orig = substr($prefixes[0]->prefixUrl, -1 );
			if ( $last_char_orig != '/' ) {
				$chain_of_prefixes = $prefixes[0]->prefixUrl . '/';
			}
			else {
				$chain_of_prefixes = $prefixes[0]->prefixUrl;
			}
		}

		if ( count( $prefixes ) > 1 ) {
			foreach ( $prefixes as $index => $prefix ) {
				if ( $index != 0 ) {
					$prefix->prefixUrl = str_replace( 'https://', '', $prefix->prefixUrl );
					$prefix->prefixUrl = str_replace( 'http://', '', $prefix->prefixUrl );
					$last_char = substr( $prefix->prefixUrl, -1 );
					if ( $last_char != '/' ) {
						$chain_of_prefixes = $chain_of_prefixes . $prefix->prefixUrl . '/';
					}
					else {
						$chain_of_prefixes = $chain_of_prefixes . $prefix->prefixUrl;
					}
				}
			}
		}

		$result = $media_url;
		if ( $chain_of_prefixes ) {
			$result = str_replace( 'https://', $chain_of_prefixes, $result );
		}

		return $result;
	}
endif;

if ( ! function_exists( 'cfm_get_published_episodes' ) ) :
	/**
	 * Get the total number of published episodes
	 * Excluding expired
	 *
	 * @since 3.0
	 *
	 * @return int
	 */
	function cfm_get_published_episodes( $show_id ) {

		if ( ! cfm_is_valid_uuid( $show_id ) ) {
			return 0;
		}

		$args = array(
			'post_type'  => 'captivate_podcast',
			'post_status' => 'publish',
			'meta_query' => array(
            'relation' => 'AND',
				array(
					'key'   => 'cfm_show_id',
					'value' => $show_id,
					'compare' => '=',
				),
				array(
					'key'   => 'cfm_episode_status',
					'value' => array( 'Exclusive', 'Early Access', 'Expired' ),
					'compare' => 'NOT IN'
				)
			),
			'fields' => 'ids',
			'posts_per_page' => -1
		);

		$query = new WP_Query($args);

		// Get the number of posts.
		return $query->post_count;
	}
endif;

if ( ! function_exists( 'cfm_is_valid_uuid' ) ) :
	/**
	 * Validate uuid
	 *
	 * @since 3.0
	 *
	 * @param string $uuid The UUID string to check.
	 * @return boolean
	 */
	function cfm_is_valid_uuid( $uuid ) {
		$pattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-4[0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/';
		return preg_match($pattern, $uuid) === 1;
	}
endif;

if ( ! function_exists( 'cfm_is_datetime_passed' ) ) :
	/**
	 * Check if datetime has passed
	 *
	 * @since 3.0
	 *
	 * @param string $datetime.
	 * @return boolean
	 */
	function cfm_is_datetime_passed( $datetime ) {
		if ( !$datetime ) {
			return false;
		}
		$given_datetime = new DateTime( $datetime );
		$current_datetime = new DateTime();

		return ( $given_datetime < $current_datetime ) ? true : false;
	}
endif;

if ( ! function_exists( 'cfm_episode_exists' ) ) :
	/**
	 * Check if episode already exists
	 *
	 * @since 3.0
	 *
	 * @param string $episode_id.
	 * @return boolean
	 */
	function cfm_episode_exists($episode_id) {
		$query = new WP_Query(array(
			'post_type'  => 'captivate_podcast',
			'meta_key'   => 'cfm_episode_id',
			'meta_value' => $episode_id,
			'post_status'=> 'any',
			'posts_per_page' => 1,
		));

		return $query->have_posts();
	}
endif;