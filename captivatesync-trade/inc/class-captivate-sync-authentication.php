<?php
/**
 * Used for admin data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Authentication' ) ) :

	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}

	/**
	 * Hosting Dashboard Admin class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Authentication {

		/**
		 * Enqueueu assets
		 *
		 * @since 1.0
		 */
		public static function assets() {

			$current_screen = get_current_screen();

			$allowed_screens = array(
				'toplevel_page_cfm-hosting-credentials',
				'admin_page_cfm-hosting-credentials',
				'captivate-sync_page_cfm-hosting-credentials',
			);

			if ( in_array( $current_screen->id, $allowed_screens ) ) :
				wp_enqueue_script( 'cfm-authentication', CFMH_URL . 'assets/js/dist/authentication-min.js', array( 'jquery' ), CFMH_VERSION, true );
			endif;

		}

		/**
		 * Redirect if unauthorized.
		 *
		 * Redirect my shows to authentication page if not authorized.
		 *
		 * @return redirect
		 * @since 1.0
		 */
		public static function redirect_to_authentication() {

		    global $pagenow;

		    # Check current admin page.
		    if ( $pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'cfm-hosting-podcasts' ) {

		    	if ( true !== cfm_user_authentication() ) {

			        wp_redirect( admin_url( 'admin.php?page=cfm-hosting-credentials' ) );
			        exit;

			    }

		    }

		}

		/**
		 * Create authentication
		 *
		 * @since 1.0
		 * @return redirect
		 */
		public static function create_authentication() {

			$output = 'Something went wrong! Please refresh the page and try again.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				$auth_id  = sanitize_text_field( $_POST['auth_id'] );
				$auth_key = sanitize_text_field( $_POST['auth_key'] );

				if ( empty( $auth_id ) || empty( $auth_key ) ) {
					$output = 'Please fill in the required fields.';
				}
				else {

					if ( get_transient( 'cfm_authentication_token' ) ) {
						$output = 'Authentication token already exists.';
					}
					else {
						$request = wp_remote_post( CFMH_API_URL . '/authenticate/pw', array(
							'timeout' => 500,
							'body' => array(
								'username' => $auth_id,
								'token'    => $auth_key,
							),
						) );

						// Debugging.
						cfm_generate_log( 'CREATE AUTHENTICATION', $request );

						if ( ! is_wp_error( $request ) && 'Unauthorized' != $request['body'] && is_array( $request ) ) {

							$request = json_decode( $request['body'] );
							$user_name = $request->user->first_name . ' ' . $request->user->last_name;

							set_transient( 'cfm_authentication_token', $request->user->token, 3600 * 24 * 7 );

							update_option( 'cfm_authentication_id', $auth_id );
							update_option( 'cfm_authentication_key', $auth_key );
							update_option( 'cfm_authentication_name', $user_name );
							update_option( 'cfm_authentication_date_added', current_time( 'mysql' ) );

							// create own db table for subsite.
							if ( is_multisite() ) {
							    global $wpdb;

                    			// cfm_shows table.
                    			$cfm_shows           = $wpdb->prefix . 'cfm_shows';
                    			$cfm_shows_structure = "
                    				CREATE TABLE IF NOT EXISTS $cfm_shows(
                    					id bigint(20) NOT NULL AUTO_INCREMENT,
                    					show_id varchar(40) NOT NULL,
                    					cfm_option varchar(100) NOT NULL,
                    					cfm_value longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
                    					PRIMARY KEY (id)
                    				) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
                    			";

                    			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

                    			dbDelta( $cfm_shows_structure );
							}

							$output = 'success';
						}
						else {
							set_transient( 'cfm_authentication_token', 'FAILED', 3600 );

							$output = 'Authentication failed.';
						}
					}
				}
			}

			echo $output;

			wp_die();

		}

		/**
		 * Remove authentication
		 *
		 * @since 1.0
		 * @return array | string
		 */
		public static function remove_authentication() {

			$output = 'Something went wrong! Please refresh the page and try again.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				// delete all shows.
				$current_shows = cfm_get_show_ids();

				if ( ! empty( $current_shows ) ) {

					foreach ( $current_shows as $show_id ) {
						$remove_shows = wp_remote_request( CFMH_API_URL . '/shows/' . $show_id . '/sync',array(
							'timeout' => 500,
							'method'  => 'DELETE',
							'headers' => array(
								'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
							),
						) );

						// Debugging.
						cfm_generate_log( 'REMOVE AUTHENTICATION - REMOVE SHOWS', $remove_shows );

						if ( ! is_wp_error( $remove_shows ) && 'Unauthorized' !== $remove_shows['body'] && is_array( $remove_shows ) ) {

							$remove_shows = json_decode( $remove_shows['body'] );

							if ( $remove_shows->success ) {
								// success.
							}
						}
					}

					global $wpdb;
					$table_name = $wpdb->prefix . 'cfm_shows';
					$delete     = $wpdb->query( "TRUNCATE TABLE $table_name" );
				}

				// delete user credentials.
				delete_option( 'cfm_authentication_id' );
				delete_option( 'cfm_authentication_key' );
				delete_transient( 'cfm_authentication_token' );
				update_option( 'cfm_authentication_date_removed', current_time( 'mysql' ) );

				$output = 'success';

			}

			echo $output;

			wp_die();

		}

	}

endif;
