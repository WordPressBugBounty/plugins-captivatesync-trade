<?php
/**
 * Used for general settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Settings' ) ) :

	/**
	 * Hosting Dashboard Settings class
	 *
	 * @since 3.2.0
	 */
	class CFMH_Hosting_Settings {

		/**
		 * Save settings
		 *
		 * @since 3.0
		 * @return string
		 */
		public static function save_settings() {

			$output = 'Something went wrong! Please refresh the page and try again.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				$form_data = $_POST['form_data'];
				$general_settings = array();
				$shows = cfm_get_shows();
				$index_page_info = array();

				if ( is_array( $form_data ) && ! empty( $form_data ) ) {
					foreach ( $form_data as $row ) {
						$name = sanitize_text_field( wp_unslash( $row['name'] ) );
						$value = $row['value'];

						// make sure 'slugs' will be saved as slug.
						if ( 'archive_slug' == $name || 'category_archive_slug' == $name || 'tag_archive_slug' == $name ) {
							$general_settings[$name] = sanitize_title( wp_unslash( $value ) );
						}
						else if ( 'single_slug' == $name ) {
							$general_settings[$name] = sanitize_title( wp_unslash( $value ) );

							// get shows with 0 index_page.
							if ( ! empty( $shows ) ) {
								foreach ( $shows as $show ) {
									// update captivate shows URL that isn't mapped.
									if ( '0' == $show['index_page'] || '' == $show['index_page'] ) {
										$index_page_info['captivate_sync_url'] = get_bloginfo( 'url' ) . '/' . sanitize_title( wp_unslash( $value ) ) . '/';

										$update_index_page = wp_remote_request( CFMH_API_URL . '/shows/' . $show['id'] . '/sync/url', array(
											'timeout' => 500,
											'body'    => $index_page_info,
											'method'  => 'PUT',
											'headers' => array(
												'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
											),
										) );

										// Debugging.
										cfm_generate_log( 'SETTINGS - UPDATE INDEX PAGE', $update_index_page );
									}
								}
							}
						}
						// accept extra whitespace for season and episode number text format.
						else if ( 'season_episode_number_text' == $name ) {
							$general_settings[$name] = wp_unslash( wp_filter_kses( $value ) );
						}
						else if ( 'bonus_trailer_text' == $name ) {
							$general_settings[$name] = wp_unslash( wp_filter_kses( $value ) );
						}
						else {
							$general_settings[$name] = sanitize_text_field( wp_unslash( $value ) );
						}
					}

					update_option( 'cfm_general_settings', $general_settings );

					$output = 'success';
				}
			}

			echo $output;

			wp_die();
		}

		/**
		 * Get settings
		 *
		 * @since 3.2.0
		 * @return string
		 */
		public static function get_settings( $key, $default = '' ) {
			$settings = get_option( 'cfm_general_settings', array() );
			return ( isset( $settings[ $key ] ) && $settings[ $key ] !== '' ) ? $settings[ $key ] : $default;
		}
	}

endif;
