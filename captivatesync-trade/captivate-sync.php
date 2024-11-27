<?php
 /**
 Plugin Name:  Captivate Sync&trade;
 Plugin URI:   https://captivate.fm/sync
 Description:  Captivate Sync&trade; is the WordPress podcasting plugin from Captivate.fm. Publish directly from your WordPress site or your Captivate podcast hosting account and stay in-sync wherever you are!
 Version:      3.0.3
 Author:       Captivate Audio Ltd
 Author URI:   https://www.captivate.fm
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'CFMH' ) ) {
	define( 'CFMH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'CFMH_URL' ) ) {
	define( 'CFMH_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'CFMH_VERSION' ) ) {
	define( 'CFMH_VERSION', '3.0.3' );
}

if ( ! defined( 'CFMH_API_URL' ) ) {
	define( 'CFMH_API_URL', 'https://api.captivate.fm' );
}

if ( ! defined( 'CFMH_CAPTIVATE_URL' ) ) {
	define( 'CFMH_CAPTIVATE_URL', 'https://my.captivate.fm' );
}

if ( ! defined( 'CFMH_PLAYER_URL' ) ) {
	define( 'CFMH_PLAYER_URL', 'https://player.captivate.fm' );
}

if ( ! defined( 'CFMH_MSP_INTERVAL' ) ) {
	define( 'CFMH_MSP_INTERVAL', 15 * MINUTE_IN_SECONDS );
}

if ( ! defined( 'CFMH_WP_ERROR' ) ) {
	define( 'CFMH_WP_ERROR', 'wp_error' );
}

if ( ! defined( 'CFMH_ACF_FIELD_PREFIX' ) ) {
	define( 'CFMH_ACF_FIELD_PREFIX', 'cfm_acf_' );
}

if ( ! defined( 'CFMH_ACF_FIELDS_ALLOWED' ) ) {
	define( 'CFMH_ACF_FIELDS_ALLOWED', ['text', 'textarea', 'select', 'radio', 'wysiwyg', 'number', 'range', 'email', 'url', 'oembed'] );
}

// Check if CFM_Hosting class already exists.
if ( ! class_exists( 'CFM_Hosting' ) ) :

	/**
	 * Main sync class
	 *
	 * @since 1.0
	 */
	class CFM_Hosting {

		/**
		 * Construct
		 *
		 * @since 1.0
		 */
		public function __construct() {
			$this->_init();
		}

		/**
		 * Initialize hooks and includes
		 *
		 * @since 1.0
		 */
		public function _init() {

			// Insert initial data.
			register_activation_hook( __FILE__, array( $this, '_install' ) );

			// Scheduler
			register_activation_hook( __FILE__, array( $this, '_set_scheduler' ) );
			register_deactivation_hook( __FILE__, array( $this, '_clear_scheduler' ) );

			// Hooks, includes and authentication.
			$this->_load_includes();
			$this->_load_hooks();
			$this->_authentication();

			add_action( 'rest_api_init', function() {
				register_rest_route( 'captivate-sync/v1', '/sync', array(
					'methods'  				=> 'POST',
					'callback' 				=> '_captivate_sync',
					'permission_callback' 	=> function() { return ''; }
				) );
			} );

			function _captivate_sync( $request ) {

				$data     = $request->get_params();
				$sync_key = $data['sync_key'];
				$show_id  = $data['show_id'];
				$episode_id = $data['episode_id'];
				$event_operation = $data['event_operation'];

				if ( $sync_key && $show_id ) {

					$current_shows = cfm_get_show_ids();

					if ( in_array( $show_id, $current_shows ) ) {

						if ( cfm_get_show_info( $show_id, 'sync_key' ) == $sync_key ) {

							if ( $episode_id ) {
							    // sync episodes.
							    switch ( $event_operation ) {
    								case 'CREATE':
    									cfm_sync_episodes( $show_id, array( 'create' ), array( 'all' ) );
    									break;
    								case 'UPDATE':
    									cfm_sync_episodes( $show_id, array( 'update' ), array( $episode_id ) );
    									break;
    								case 'DELETE':
    								    cfm_sync_episodes( $show_id, array( 'delete' ), array( 'all' ) );
    								    break;
    								default:
    									break;
    							}
							}
							else {
							    if ( 'UPDATE' == $event_operation ) {
							        // sync show information.
							        $sync_shows = cfm_sync_shows( $show_id );
							    }
							}
						}
					}
				}
			}
		}

		/**
		 * Create database table for shows
		 *
		 * @since 1.0
		 */
		public static function _install() {

			global $wpdb;

			// cfm_shows table.
			$cfm_shows           = $wpdb->prefix . 'cfm_shows';
			$cfm_shows_structure = "
				CREATE TABLE IF NOT EXISTS $cfm_shows(
					id bigint(20) NOT NULL AUTO_INCREMENT,
					show_id varchar(40) NOT NULL,
					cfm_option varchar(100) NOT NULL,
					cfm_value longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT '',
					PRIMARY KEY (id)
				) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
			";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			dbDelta( $cfm_shows_structure );

			// change table collation - for existing users.
			$wpdb->query( "ALTER TABLE $cfm_shows CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci" );

			// clear plugin update notice.
			update_option( 'cfm_plugin_updated', '1' );

		}

		/**
		 * Set scheduler
		 *
		 * @since 1.0
		 */
		public static function _set_scheduler() {

			// Set schedule to get new episodes from captivate and insert to WP.
			if ( ! wp_next_scheduled( 'cfm_sync_new_episodes' ) ) {
				wp_schedule_event( time(), 'hourly', 'cfm_sync_new_episodes' );
			}

		}

		/**
		 * Clear scheduler
		 *
		 * @since 1.0
		 */
		public static function _clear_scheduler() {

			// Clear schedule to get new episodes from captivate and insert to WP.
			wp_clear_scheduled_hook( 'cfm_sync_new_episodes' );

		}

		/**
		 * Load includes
		 *
		 * @since 1.0
		 */
		private function _load_includes() {

			include_once CFMH . 'inc/functions.php';
			include_once CFMH . 'inc/class-captivate-sync-data.php';
			include_once CFMH . 'inc/class-captivate-sync-front.php';
			include_once CFMH . 'inc/class-captivate-sync-shortcode.php';

			if ( is_admin() ) :
				include_once CFMH . 'inc/class-captivate-sync-admin.php';
				include_once CFMH . 'inc/class-captivate-sync-authentication.php';
				include_once CFMH . 'inc/class-captivate-sync-manage-shows.php';
				include_once CFMH . 'inc/class-captivate-sync-manage-episodes.php';
				include_once CFMH . 'inc/class-captivate-sync-publish-episode.php';
			endif;
		}

		/**
		 * Load hooks
		 *
		 * @since 1.0
		 */
		private function _load_hooks() {

			add_action( 'init', array( 'CFMH_Hosting_Data', 'register' ) );
			add_action( 'init', array( 'CFMH_Hosting_Data', 'unregister' ), 100 );

			// publish missed scheduled episodes
			add_action( 'init', array( $this, 'publish_missed_scheduled' ), 0 );

			// set show page.
			add_action( 'pre_get_posts', array( 'CFMH_Hosting_Front', 'index_page' ), 100 );

			// deactivate episodes.
			add_action( 'pre_get_posts', array( 'CFMH_Hosting_Front', 'deactivate_episodes' ), 100 );
			add_filter( 'wp_robots', array( 'CFMH_Hosting_Front', 'deactivate_episodes_robots' ), 100 );

			// captivate_podcast rewrite slug.
			add_filter( 'register_post_type_args', array( 'CFMH_Hosting_Front', 'register_post_type_args' ), 10, 2 );

			add_filter( 'the_title', array( 'CFMH_Hosting_Front', 'title_filter' ), 10, 1 );

			// add player to episodes.
			add_filter( 'the_excerpt', array( 'CFMH_Hosting_Front', 'content_filter' ), 11 );
			add_filter( 'the_content', array( 'CFMH_Hosting_Front', 'content_filter' ), 11 );

			// remove captivate_podcast edit link.
			add_filter( 'edit_post_link', array( 'CFMH_Hosting_Front', 'edit_post_link' ) );

			// meta data.
			add_action( 'wp_head', array( 'CFMH_Hosting_Front', 'add_meta_data' ), 5 );

			// rss feed.
			add_action( 'wp_head', array( 'CFMH_Hosting_Front', 'add_show_feed_rss' ), 1 );

			// player api.
			add_action( 'wp_enqueue_scripts', array( 'CFMH_Hosting_Front', 'assets' ) );

			// transcription.
			add_filter( 'the_content', array( 'CFMH_Hosting_Front', 'content_transcript' ), 11 );

			// add custom field to episodes.
			add_filter( 'the_content', array( 'CFMH_Hosting_Front', 'pw_content_filter' ), 11 );

			// auto-timestamp.
			add_filter( 'the_content', array( 'CFMH_Hosting_Front', 'content_auto_timestamp' ), 12 );

			// dynamic text.
			add_filter( 'the_excerpt', array( 'CFMH_Hosting_Front', 'content_dynamic_text' ), 13 );
			add_filter( 'the_content', array( 'CFMH_Hosting_Front', 'content_dynamic_text' ), 13 );

			// use artwork as featured image.
			add_filter( 'wp_get_attachment_image_src', array( 'CFMH_Hosting_Front', 'use_artwork' ), 10, 4 );
			add_filter( 'has_post_thumbnail', array( 'CFMH_Hosting_Front', 'filter_has_post_thumbnail' ) );
			add_filter( 'post_thumbnail_html', array( 'CFMH_Hosting_Front', 'default_post_thumbnail_html' ), 10, 5 );

			// redirect old slug to new slug - for podcasts with selected page mapping.
			add_filter( 'template_redirect', array( 'CFMH_Hosting_Front', 'redirect_old_slug' ) );

			// shortcode.
			add_action( 'admin_enqueue_scripts', array( 'CFM_Hosting_Shortcode', 'assets' ) );
			add_shortcode( 'cfm_captivate_episodes', array( 'CFM_Hosting_Shortcode', 'episodes_list' ) );

			add_action('wp_ajax_shortcode-loadmore', array( 'CFM_Hosting_Shortcode', 'shortcode_loadmore' ) );
			add_action('wp_ajax_nopriv_shortcode-loadmore', array( 'CFM_Hosting_Shortcode', 'shortcode_loadmore' ) );

			// Get new episodes from captivate and insert to WP
			add_action( 'cfm_sync_new_episodes', array( $this, 'get_new_episodes' ) );

			// ACF
			add_filter( 'the_content', array( 'CFMH_Hosting_Front', 'acf_fields_on_content' ), 11 );

			if ( is_admin() ) :

				// restrictions.
				add_action( 'current_screen', array( 'CFMH_Hosting_Admin', 'restrict_other_admin_pages' ) );

				// show settings.
				add_action( 'admin_enqueue_scripts', array( 'CFMH_Hosting_Admin', 'assets' ), 20 );
				add_action( 'admin_menu', array( 'CFMH_Hosting_Admin', 'menus' ) );

				// user authentication.
				add_action( 'admin_enqueue_scripts', array( 'CFMH_Hosting_Authentication', 'assets' ), 20 );
				add_action( 'admin_init', array( 'CFMH_Hosting_Authentication', 'redirect_to_authentication' ) );
				add_action( 'wp_ajax_create-authentication', array( 'CFMH_Hosting_Authentication', 'create_authentication' ) );
				add_action( 'wp_ajax_remove-authentication', array( 'CFMH_Hosting_Authentication', 'remove_authentication' ) );

				// my podcasts.
				add_action( 'admin_enqueue_scripts', array( 'CFMH_Hosting_Manage_Shows', 'assets' ), 20 );
				add_action( 'wp_ajax_manage-captivate-shows', array( 'CFMH_Hosting_Manage_Shows', 'manage_captivate_shows' ) );
				add_action( 'wp_ajax_select-captivate-shows', array( 'CFMH_Hosting_Manage_Shows', 'select_captivate_shows' ) );
				add_action( 'wp_ajax_load-shows', array( 'CFMH_Hosting_Manage_Shows', 'load_shows' ) );
				add_action( 'wp_ajax_sync-shows', array( 'CFMH_Hosting_Manage_Shows', 'sync_shows' ) );
				add_action( 'wp_ajax_sync-show', array( 'CFMH_Hosting_Manage_Shows', 'sync_show' ) );
				add_action( 'wp_ajax_load-show-settings', array( 'CFMH_Hosting_Manage_Shows', 'load_show_settings' ) );
				add_action( 'wp_ajax_save-show-settings', array( 'CFMH_Hosting_Manage_Shows', 'save_show_settings' ) );

				add_action( 'wp_ajax_set-show-page', array( 'CFMH_Hosting_Manage_Shows', 'set_show_page' ) );
				add_action( 'wp_ajax_set-show-author', array( 'CFMH_Hosting_Manage_Shows', 'set_show_author' ) );
				add_action( 'wp_ajax_set-display-episodes', array( 'CFMH_Hosting_Manage_Shows', 'set_display_episodes' ) );

				// my episodes.
				add_action( 'admin_enqueue_scripts', array( 'CFMH_Hosting_Manage_Episodes', 'assets' ), 20 );
				add_action( 'wp_ajax_share-episode', array( 'CFMH_Hosting_Manage_Episodes', 'share_episode' ) );
				add_action( 'wp_ajax_toggle-episode', array( 'CFMH_Hosting_Manage_Episodes', 'toggle_episode' ) );
				add_action( 'wp_ajax_trash-episode', array( 'CFMH_Hosting_Manage_Episodes', 'delete_episode' ) );

				// publish episode.
				add_action( 'admin_enqueue_scripts', array( 'CFMH_Hosting_Publish_Episode', 'assets' ), 20 );
				add_action( 'admin_post_form_publish_episode', array( 'CFMH_Hosting_Publish_Episode', 'publish_episode_save' ) );
				add_action( 'wp_ajax_add-webcategory', array( 'CFMH_Hosting_Publish_Episode', 'add_webcategory' ) );
				add_action( 'wp_ajax_add-webtags', array( 'CFMH_Hosting_Publish_Episode', 'add_webtags' ) );

				add_action( 'wp_ajax_duplicate-episode', array( 'CFMH_Hosting_Publish_Episode', 'duplicate_episode' ) );
				add_action( 'wp_ajax_save-acf-fields', array( 'CFMH_Hosting_Publish_Episode', 'save_acf_fields' ) );

				add_action( 'wp_ajax_change-shownotes-template', array( 'CFMH_Hosting_Publish_Episode', 'change_shownotes_template' ) );
				add_action( 'wp_ajax_insert-static-block', array( 'CFMH_Hosting_Publish_Episode', 'insert_static_block' ) );
				add_action( 'wp_ajax_insert-static-shortcode', array( 'CFMH_Hosting_Publish_Episode', 'insert_static_shortcode' ) );

				add_action( 'wp_ajax_render-dt-variables', array( 'CFMH_Hosting_Publish_Episode', 'render_dt_variables' ) );

				// settings.
				add_action( 'wp_ajax_save-settings', array( 'CFMH_Hosting_Admin', 'save_settings' ) );

				// shortcode.
				add_action( 'wp_ajax_shortcode-load-episodes', array( 'CFM_Hosting_Shortcode', 'shortcode_load_episodes' ) );
				add_action( 'wp_ajax_save-shortcode', array( 'CFM_Hosting_Shortcode', 'save_shortcode' ) );

				// user podcast management.
				add_action( 'edit_user_profile', array( 'CFMH_Hosting_Admin', 'add_user_podcast_management' ) );
				add_action( 'edit_user_profile_update', array( 'CFMH_Hosting_Admin', 'update_user_podcast_management' ) );

				// admin footer.
				add_action( 'admin_footer', array( 'CFMH_Hosting_Admin', 'admin_footer' ) );

				// body class.
				add_filter( 'admin_body_class', array( 'CFMH_Hosting_Admin', 'body_class' ) );

				// extend timeout.
				add_filter( 'http_request_timeout', 'CFMH_timeout_extend' );
				function CFMH_timeout_extend( $time ) {
					// Default timeout is 5.
					return 500;
				}

			endif;

		}

		/**
		 * Generate user authentication
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function _authentication() {

			if ( is_admin() ) :

				if ( ! get_transient( 'cfm_authentication_token' ) && get_option( 'cfm_authentication_id' ) ) {

					$request = wp_remote_post( CFMH_API_URL . '/authenticate/pw', array(
						'timeout' => 500,
						'body' => array(
							'username' => get_option( 'cfm_authentication_id' ),
							'token'    => get_option( 'cfm_authentication_key' ),
						),
					) );

					// Debugging.
					cfm_generate_log( 'GENERATE AUTHENTICATION', $request );

					if ( ! is_wp_error( $request ) && 'Unauthorized' != $request['body'] && is_array( $request ) ) {

						$request = json_decode( $request['body'] );
						$user_name = $request->user->first_name . ' ' . $request->user->last_name;

						set_transient( 'cfm_authentication_token', $request->user->token, 3600 * 24 * 7 );
						update_option( 'cfm_authentication_name', $user_name );

						return $request->user->token;

					}
					else {
						set_transient( 'cfm_authentication_token', 'FAILED', 3600 );
					}
				}

			endif;
		}

		/**
		 * Check timestamp from transient and publish all missed scheduled episodes
		 *
		 * @since 1.1.0
		 * @return none
		 */
		public static function publish_missed_scheduled() {

			$last_scheduled_missed_time = get_transient( 'wp_scheduled_missed_time' );

			$time = current_time( 'timestamp', 0 );

			if ( false !== $last_scheduled_missed_time && absint( $last_scheduled_missed_time ) > ( $time - CFMH_MSP_INTERVAL ) ) {
				return;
			}

			set_transient( 'wp_scheduled_missed_time', $time, CFMH_MSP_INTERVAL );

			global $wpdb;

			$sql_query = "
				SELECT
				ID
				FROM {$wpdb->posts}
				WHERE ( ( post_date > 0 && post_date <= %s ) )
				AND post_status = 'future'
				AND post_type = 'captivate_podcast'
				LIMIT 0, %d
			";

			$sql = $wpdb->prepare( $sql_query, current_time( 'mysql', 0 ), 5 );

			$scheduled_post_ids = $wpdb->get_col( $sql );

			if ( ! count( $scheduled_post_ids ) ) {
				return;
			}

			foreach ( $scheduled_post_ids as $scheduled_post_id ) {
				if ( ! $scheduled_post_id ) {
					continue;
				}

				wp_publish_post( $scheduled_post_id );
			}
		}

		/**
		 * Get new episodes
		 *
		 * @since 1.0
		 * @return void
		 */
		public static function get_new_episodes() {

			// sync only if authorized.
			if ( true === cfm_user_authentication() ) {

				$current_shows = cfm_get_show_ids();

				if ( ! empty( $current_shows ) ) {

					foreach ( $current_shows as $show_id ) {
						cfm_sync_shows( $show_id );
						cfm_sync_plugin_version( $show_id );
						cfm_sync_episodes( $show_id, array( 'all' ), array( 'all' ) );
					}
				}
			}

		}

	}

	new CFM_Hosting();

endif;
