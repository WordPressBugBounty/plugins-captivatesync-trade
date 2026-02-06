<?php
/**
 * Used to process shows and episodes sync
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Manage_Episodes' ) ) :

	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}

	/**
	 * Manage Episodes class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Manage_Episodes {

		/**
		 * Enqueue JS
		 *
		 * @since 1.0
		 */
		public static function assets() {

			$current_screen = get_current_screen();

			$allowed_screens = array(
				'toplevel_page_cfm-hosting-podcast-episodes',
				'admin_page_cfm-hosting-podcast-episodes',
				'captivate-sync_page_cfm-hosting-podcast-episodes',
			);

			if ( in_array( $current_screen->id, $allowed_screens ) || ( strpos( $current_screen->id, 'captivate-sync_page_cfm-hosting-podcast-episodes_' ) === 0 ) ) :
				wp_enqueue_script( 'cfm-manage-episodes', CFMH_URL . 'captivate-sync-assets/js/dist/manage-episodes-min.js', array( 'jquery' ), CFMH_VERSION, true );
			endif;

		}

		/**
		 * Episode share modal
		 *
		 * @since 3.0
		 * @return string
		 */
		public static function share_episode() {
			if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {
				echo '<div class="cfm-alert cfm-alert-error"><span class="alert-icon"></span> <span class="alert-text">Something went wrong! Please refresh the page and try again.</span></div>';
				wp_die();
			}

			$pid = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

			if ( ! $pid || ! get_post( $pid ) ) {
				echo '<div class="cfm-alert cfm-alert-error"><span class="alert-icon"></span> <span class="alert-text">Invalid episode.</span></div>';
				wp_die();
			}

			$cfm_episode_id = get_post_meta( $pid, 'cfm_episode_id', true );
			?>
				<div class="row">
					<div class="col-sm-12">
						<label>Paste this link into your social posts</label>
						<div id="clipboard-ep-link" class="text-copy">
							<?php echo esc_url( CFMH_PLAYER_URL . '/episode/' . $cfm_episode_id ); ?>
						</div>
						<div class="text-end mt-2">
							<a class="clipboard btn btn-outline-primary btn-sm" data-clipboard-target="#clipboard-ep-link" data-clipboard-response="Your player URL has been copied to your clipboard.">Copy</a>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12">
						<label>Episode Web Page URL</label>
						<div id="clipboard-ep-web" class="text-copy">
							<?php echo esc_url( get_permalink( $pid ) ); ?>
						</div>
						<div class="text-end mt-2">
							<a class="btn btn-outline-primary btn-sm mr-2" href="<?php echo esc_url( get_permalink( $pid ) ); ?>" target="_blank">View</a>
							<a class="clipboard btn btn-outline-primary btn-sm" data-clipboard-target="#clipboard-ep-web" data-clipboard-response="Your website URL has been copied to your clipboard.">Copy</a>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12">
						<label>Embed on another website</label>
						<div id="clipboard-ep-embed" class="text-copy">
							<?php echo esc_html( '<div style="width: 100%; height: 200px; margin-bottom: 20px; border-radius: 6px; overflow:hidden;"><iframe style="width: 100%; height: 200px;" frameborder="no" scrolling="no" seamless src="' . CFMH_PLAYER_URL . '/episode/' . $cfm_episode_id . '"></iframe></div>' ); ?>
						</div>
						<div class="text-end mt-2">
							<a class="clipboard btn btn-outline-primary btn-sm" data-clipboard-target="#clipboard-ep-embed" data-clipboard-response="Your website embed code has been copied to your clipboard.">Copy</a>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12">
						<label>Direct audio file URL</label>
						<div id="clipboard-ep-audio" class="text-copy">
							<?php echo esc_html( get_post_meta( $pid, 'cfm_episode_media_url', true ) ); ?>
						</div>
						<div class="text-end mt-2">
							<a class="clipboard btn btn-outline-primary btn-sm" data-clipboard-target="#clipboard-ep-audio" data-clipboard-response="Your file URL has been copied to your clipboard.">Copy</a>
						</div>
					</div>
				</div>
			<?php
			wp_die();
		}

		/**
		 * Toggle episode
		 *
		 * @since 3.0
		 * @return string
		 */
		public static function toggle_episode() {

			$output = 'error';

			if ( current_user_can( 'edit_posts' ) ) {

				$pid = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

				if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], 'toggle_post_' . $pid ) ) {

					$cfm_episode_website_active = get_post_meta( $pid, 'cfm_episode_website_active', true );

					if ( '0' == $cfm_episode_website_active ) {
						update_post_meta( $pid, 'cfm_episode_website_active', '1' );
						$output = 'episode_activated';
					}
					else {
						update_post_meta( $pid, 'cfm_episode_website_active', '0' );
						$output = 'episode_deactivated';
					}
				}
			}

			echo $output;
			wp_die();
		}

		/**
		 * Delete episode
		 *
		 * If episode_id exists in Captivate and if slug matches.
		 * Episode will be permanently deleted on Captivate and will be moved to trash in WordPress.
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function delete_episode() {

			$output = 'error';

			if ( current_user_can( 'delete_others_posts' ) ) {

				$pid = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

				if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], 'trash_post_' . $pid ) ) {

					$cfm_episode_id = get_post_meta( $pid, 'cfm_episode_id', true );
					$post_name = get_post_field( 'post_name', $pid );

					if ( $cfm_episode_id ) {

						$captivate_episode = cfm_get_captivate_episode( $cfm_episode_id );

						if ( $captivate_episode ) {

							$captivate_episode_data = cfm_episodes_data_array( $captivate_episode, $cfm_episode_id );
							$captivate_slug = $captivate_episode_data['slug'];

							if ( $post_name == $captivate_slug ) {
								// Check if $cfm_episode_id is more than 1 in WordPress, this means there's a duplicate episode, notify the user to contact support.
								$get_episode = array(
									'post_type'      => 'captivate_podcast',
									'posts_per_page' => -1,
									'post_status'    => array( 'publish', 'draft', 'future', 'private' ),
									'meta_query'     => array(
										array(
											'key'     => 'cfm_episode_id',
											'value'   => $cfm_episode_id,
											'compare' => '=',
										),
									),
								);

								$episode = new WP_Query( $get_episode );
								$count_episode = $episode->post_count;

								if ( $count_episode > 1 ) {
									// do nothing and inform the user.
									$output = 'duplicate_episode';
								}
								else {
									// delete episode in Captivate.
									$remove_episode = wp_remote_request(
										CFMH_API_URL . '/episodes/' . $cfm_episode_id,
										array(
											'timeout' => 500,
											'method'  => 'DELETE',
											'headers' => array(
												'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
											),
										)
									);

									// Debugging.
									cfm_generate_log( 'CAPTIVATE DELETE EPISODE', $remove_episode );

									// trash episode in WordPress once deleted in Captivate.
									if ( !is_wp_error($remove_episode) ) {
										$response_data = json_decode(wp_remote_retrieve_body($remove_episode));

										if ( isset( $response_data->success ) && $response_data->success === true ) {
											wp_trash_post( $pid );
											$output = 'success';
										}
									}
								}

							}
							else {
								// Trash episode in WordPress only since it's a duplicate episode.
								wp_trash_post( $pid );
								$output = 'success_wp';
							}

						}

					}
					else {
						// Trash episode in WordPress only since it doesn't exists in Captivate.
						wp_trash_post( $pid );
						$output = 'success_wp';
					}

				}
			}

			echo $output;

			wp_die();

		}

	}

endif;
