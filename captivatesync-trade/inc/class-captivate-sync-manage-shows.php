<?php
/**
 * Used to process shows and episodes sync
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Manage_Shows' ) ) :

	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}

	/**
	 * Manage Shows class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Manage_Shows {

		/**
		 * Enqueue sync JS
		 *
		 * @since 1.0
		 */
		public static function assets() {

			$current_screen = get_current_screen();

			$allowed_screens = array(
				'toplevel_page_cfm-hosting-podcasts',
				'admin_page_cfm-hosting-podcasts',
				'captivate-sync_page_cfm-hosting-podcasts',
			);

			if ( in_array( $current_screen->id, $allowed_screens ) ) :
				wp_enqueue_script( 'cfm-manage-shows', CFMH_URL . 'captivate-sync-assets/js/dist/manage-shows-min.js', array( 'jquery' ), CFMH_VERSION, true );
			endif;

		}

		/**
		 * Manage shows
		 *
		 * @since 2.0.5
		 * @return string
		 */
		public static function manage_captivate_shows() {

			$output = '';

			if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {
				$output .= 'Something went wrong! Please refresh the page and try again.';
			}
			else {

				if ( ! get_transient( 'cfm_authentication_token' ) ) {

					$output .= 'No authorization.';

				} else {

					$captivate_shows = cfm_get_captivate_shows();

					if ( is_array( $captivate_shows ) ) {

						if ( count( $captivate_shows ) >= 1 ) {

							$current_shows = cfm_get_show_ids();

							$output .= '<ul>';

								$output .= '<li class="d-none d-md-block pt-2 pb-2"><div class="row align-items-center">';
									$output .= '<div class="col-md-7 mb-lg-0"><strong>Podcast name</strong></div>';
									$output .= '<div class="col-md-5 mb-2 mb-lg-0"><strong>Default author</strong></div>';
								$output .= '</div></li>';

								$query_users_ids_by_role = [
									'fields' => ['id'],
									'role__in' => ['administrator', 'editor', 'author'],
								];
								$array_of_users = get_users( $query_users_ids_by_role );
								$array_of_users_ids = array_map( function ( $user ) {
									return $user->id;
								}, $array_of_users );
								$users_ids_list = implode( ',', $array_of_users_ids );

								foreach ( $captivate_shows as $id => $show ) {

									$author_id = cfm_get_show_info( $show->id, 'wp_author_id' );

									ob_start();
									wp_dropdown_users( array(
										'name' => 'author_for_show_' . $show->id,
										'id'   => 'author_for_show_' . $show->id,
										'show'   => 'display_name_with_login',
										'class' => 'form-control form-control-sm',
										'include' => $users_ids_list,
										'selected' => ( $author_id ) ? $author_id : get_current_user_id(),
									) );
									$user_dropdown_html = ob_get_clean();

									$checked = in_array( $show->id, $current_shows ) ? ' checked="checked"' : '';
									$private = '1' == $show->private ? '<i class="fal fa-lock me-2" aria-label="Private Podcast"></i>' : '';
									$output .= '<li id="show-' . $show->id . '">';

										$output .= '<div class="row align-items-center">';
											$output .= '<div class="col-md-7 mb-lg-0">';
											$output .= '<label class="mb-0"><input type="checkbox" value="' . esc_attr( $show->id ) . '" name="shows_to_sync"' . $checked .'> ' . $private . esc_html( $show->title ) . '</label>';
											$output .= '</div>';
											$output .= '<div class="col-md-5 mb-2 mb-lg-0">' . $user_dropdown_html . '</div>';
										$output .= '</div>';

									$output .= '</li>';
								}

							$output .= '</ul>';

						}
						else {
							$output .= '<div class="cfm-shows-empty text-center pt-5 pb-5 mt-5 mb-5"><p>You haven’t created any podcasts with Captivate yet.</p>';
							$output .= '<a class="btn btn-outline-primary" href="' . esc_url( CFMH_CAPTIVATE_URL . '/dashboard/new-podcast' ) . '" target="_blank">Create Your First</a></div>';
						}
					}
					else {
						$output .= 'Cannot get shows. Please contact support.';
					}
				}
			}

			echo $output;

			wp_die();
		}

		/**
		 * Select shows
		 *
		 * @since 1.0
		 * @return void
		 */
		public static function select_captivate_shows() {

			$errors = array();
			$output = array();
			$success = array();
			$output['return'] = false;

			if ( !isset($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_cfm_nonce') ) {
				$output['message'] = 'Something went wrong! Please refresh the page and try again.';
			}
			else {
				$current_shows 	= cfm_get_show_ids();
				$selected_shows = isset($_POST['shows']) ? array_map('sanitize_text_field', wp_unslash($_POST['shows'])) : array();
				$show_authors = isset($_POST['authors']) ? array_map('sanitize_text_field', wp_unslash($_POST['authors'])) : array();

				if ( ! empty($selected_shows) ) {
					foreach ($selected_shows as $show_id) {

						if ( isset($show_authors['author_for_show_' . $show_id]) ) {
							cfm_update_show_info($show_id, 'wp_author_id', $show_authors['author_for_show_' . $show_id]);
						}

						if ( in_array($show_id, $current_shows) ) {
							try {
								cfm_sync_episodes($show_id, array('create'));
							} catch (Exception $e) {
								cfm_generate_log("SELECTEXISTINGSHOWS_CREATEPISODES ({$show_id})", $e->getMessage());
								continue;
							}
						}
						else {
							$webhook            = array();
							$webhook['webhook'] = get_site_url(null, '/wp-json/captivate-sync/v1/sync', null);

							$sync_shows = wp_remote_request(
								CFMH_API_URL . '/shows/' . $show_id . '/sync',
								array(
									'timeout' => 500,
									'method'  => 'PUT',
									'body'    => $webhook,
									'headers' => array(
										'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
									),
								)
							);

							// Debugging.
							cfm_generate_log( 'SYNC SELECT SHOWS', $sync_shows );

							if ( !is_wp_error($sync_shows) && 'Unauthorized' !== $sync_shows['body'] && is_array($sync_shows) ) {

								$sync_shows = json_decode($sync_shows['body']);

								$success[] = array(
									'id'      => $show_id,
									'success' => $sync_shows->success,
									'error'   => false == $sync_shows->success ? $sync_shows->errors[0] : false,
								);

								if ( $sync_shows->success ) {
									cfm_sync_shows($show_id, $sync_shows->sync_key);
									cfm_sync_episodes($show_id, array('create'));
								}
							}
							else {
								$errors = "Can't connect to Captivate Sync.";
							}
						}

						cfm_sync_plugin_version($show_id);
					}
				}

				$to_remove = array_diff($current_shows, $selected_shows);

				if ( !empty($to_remove) ) {
					foreach ( $to_remove as $show_id ) {
						$disconnect_show = cfm_disconnect_captivate_show($show_id);
						if ( $disconnect_show ) {
							cfm_remove_show($show_id);
						}
					}
				}

				if ( !empty($success) ) {
					$output['return'] = $success;
				}
			}

			echo json_encode($output);

			wp_die();
		}

		/**
		 * Sync shows
		 *
		 * @since 1.0
		 * @return void
		 */
		public static function sync_shows() {
			$output = 'Something went wrong! Please refresh the page and try again.';

			if ( isset($_POST['_nonce']) && wp_verify_nonce($_POST['_nonce'], '_cfm_nonce') ) {
				$current_shows = cfm_get_show_ids();
				foreach ( $current_shows as $index => $show_id ) {
					try {
						$sync_shows = cfm_sync_shows($show_id);
						cfm_sync_plugin_version($show_id);
					} catch (Exception $e) {
						cfm_generate_log("MANUALSYNC-SHOWINFO ({$show_id})", $e->getMessage());
						continue;
					}

					try {
						$sync_episodes = cfm_sync_episodes($show_id, array('all'));
					} catch (Exception $e) {
						cfm_generate_log("MANUALSYNC-EPISODES ({$show_id})", $e->getMessage());
						continue;
					}
				}
				$output = 'success';
			}
			echo $output;
			wp_die();
		}

		/**
		 * Sync show
		 *
		 * @since 3.0
		 * @return void
		 */
		public static function sync_show() {
			$output = 'Something went wrong! Please refresh the page and try again.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {
				$show_id = isset( $_POST['show_id'] ) ? sanitize_text_field( wp_unslash( $_POST['show_id'] ) ) : '';
				if ( cfm_is_show_exists($show_id) ) {
					$sync_shows = cfm_sync_shows($show_id);
					$sync_episodes = cfm_sync_episodes($show_id, array('all'));
				}
				$output = 'success';
			}
			echo $output;
			wp_die();
		}

		/**
		 * Shows list/grid view
		 *
		 * @since 3.0.0
		 * @return string
		 */
		public static function load_shows() {

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) :

				$shows = cfm_get_shows();
				$user_shows = get_user_meta( get_current_user_id(), 'cfm_user_shows', true );
				$layout = isset( $_POST['layout'] ) ? sanitize_text_field( wp_unslash( $_POST['layout'] ) ) : 'grid';
				$sort_by = isset( $_POST['this_action'] ) ? sanitize_text_field( wp_unslash( $_POST['this_action'] ) ) : '';
				$sort_by = ( $sort_by == 'content_view' ) ? get_user_meta( get_current_user_id(), 'cfm_podcasts_default_sort', true ) : $sort_by;
				$sort_by = ( $sort_by ) ? $sort_by : 'title';

				update_user_meta( get_current_user_id(), 'cfm_podcasts_default_view', $layout );
				update_user_meta( get_current_user_id(), 'cfm_podcasts_default_sort', $sort_by );

				$sort_shows = array_column( $shows, $sort_by );
				array_multisort( $sort_shows, SORT_ASC, $shows );
				?>

				<?php if ( ! empty( $shows ) ) : ?>

					<?php if ( 'grid' == $layout ) : ?>

						<div class="row cfm-shows-grid">

						<?php foreach ( $shows as $show ) : ?>

							<?php if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) ) : ?>

								<div class="data-item col-xl-6 mb-4">

									<div id="show_<?php echo esc_attr( $show['id'] ); ?>" class="cfm-show-wrap">

										<div class="media show-object">

											<div class="media-body">

												<div class="row">
													<div class="col-sm-4 mb-4 mb-sm-0">
														<img class="img-fluid mb-2" src="<?php echo esc_attr( cfm_get_show_artwork( $show['id'], '800x800' ) ); ?>" alt="<?php echo esc_attr( $show['title'] ); ?>" width="160" height="160">
													</div>

													<div class="col-sm-8">
														<h5 class="mt-0 mb-1"><?php echo ( '1' == $show['private'] ) ? '<i class="fal fa-lock me-2 cfmsync-tooltip" data-bs-placement="bottom" title="Private Podcast"></i>' : ''; ?> <?php echo esc_html( cfm_limit_characters( $show['title'], 30 ) ); ?></h5>
														<div class="small last-sync">
															<strong>Last Sync:</strong> <?php echo esc_html( gmdate( 'Y-m-d h:ia', strtotime( $show['last_synchronised'] ) ) ); ?>
														</div>

														<div class="row mt-2">
															<div class="col-md-6 mb-2 mb-lg-0">
																<?php
																wp_dropdown_pages(
																	array(
																		'name' => 'page_for_show',
																		'id'   => 'page_for_show',
																		'show_option_none' => __( 'Page Mapping' ),
																		'option_none_value' => '0',
																		'class' => 'form-control form-control-sm',
																		'selected' => cfm_get_show_info( $show['id'], 'index_page' ),
																	)
																);
																?>
															</div>

															<div class="col-md-6 mb-2 mb-lg-0">
																<?php
																$query_users_ids_by_role = [
																	'fields' => ['id'],
																	'role__in' => ['administrator', 'editor', 'author'],
																];
																$array_of_users = get_users( $query_users_ids_by_role );
																$array_of_users_ids = array_map( function ( $user ) {
																	return $user->id;
																}, $array_of_users );
																$users_ids_list = implode( ',', $array_of_users_ids );

																wp_dropdown_users( array(
																	'name' => 'author_for_show',
																	'id'   => 'author_for_show',
																	'show'   => 'display_name_with_login',
																	'show_option_none' => __( 'Author' ),
																	'option_none_value' => '0',
																	'class' => 'form-control form-control-sm',
																	'include' => $users_ids_list,
																	'selected' => cfm_get_show_info( $show['id'], 'wp_author_id' ),
																) );
																?>
															</div>
														</div>

														<div class="mt-3">
															<?php
															// always '1' if not exists/empty or checked.
															$display_episodes = cfm_get_show_info( $show['id'], 'display_episodes' ) == '0' ? '0' : '1';
															?>

															<label><input type="checkbox" name="display_episodes" id="display_episodes" value="1" <?php checked( $display_episodes, '1' ); ?>> Display episodes?</label>
														</div>

														<hr>

													</div>
												</div>

												<div class="row">
													<div class="col-sm-4 mb-4 mb-sm-0">
														<div class="cfm-show-actions d-flex justify-content-between">
															<button class="btn btn-secondary cfmsync-tooltip clipboard<?php echo ( '1' == $show['private'] ) ? ' disabled': ''; ?>" data-bs-placement="bottom" data-clipboard-response="RSS Feed has been copied" data-clipboard-text="<?php echo ( '1' == $show['private'] ) ? 'Private Podcast' : esc_attr( $show['feed_url'] ); ?>" title="Copy RSS Feed" aria-label="Copy RSS Feed"><i class="fal fa-rss"></i></button>

															<button class="btn btn-secondary cfmsync-tooltip" data-bs-placement="bottom" title="Sync Podcast and Episodes" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Sync Podcast And Episodes" data-confirmation-content="Are you sure you want to sync this show's information and episodes? This will pull all the data from Captivate and will update the show information and episodes on this website." data-confirmation-button="cfm-sync-show-and-episodes" data-confirmation-reference="<?php echo esc_attr( $show['id'] ); ?>" aria-label="Sync Podcast and Episodes"><i class="fal fa-sync"></i></button>

															<button class="btn btn-secondary cfmsync-tooltip" data-bs-placement="bottom" title="Clear Publish Saved Data" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Clear Publish Saved Data" data-confirmation-content="Are you sure you want to clear the publish episode auto-save data on this show? All fields on publish episode screen for this show will be emptied." data-confirmation-button="cfm-clear-publish-data" data-confirmation-reference="<?php echo esc_attr( $show['id'] ); ?>" aria-label="Clear Publish Saved Data"><i class="fal fa-eraser"></i></button>
														</div>
													</div>

													<div class="col-sm-8">
														<div class="d-flex justify-content-between">
															<button class="cfm-display-show-settings btn btn-secondary" data-bs-toggle="modal" data-bs-target="#cfm-show-settings-modal" data-reference="<?php echo esc_attr( $show['id'] ); ?>">Podcast Settings <i class="fal fa-cog ms-lg-2"></i></button>

															<a href="<?php echo esc_url( admin_url( 'admin.php?page=cfm-hosting-publish-episode&show_id=' . $show['id'] ) ); ?>" class="btn btn-primary">Publish Episode <i class="fal fa-podcast ms-lg-2"></i></a>
														</div>
													</div>
												</div>

											</div>

										</div>
									</div>
								</div>

							<?php endif; ?>

						<?php endforeach; ?>

						</div><!-- /row -->

					<?php else : ?>

						<div class="row cfm-datatable-list cfm-shows-list">

							<div class="col-12">

								<div class="datatable-head">
									<div class="datatable-row">
										<div class="datatable-cell datatable-toggle"></div>
										<div class="datatable-cell datatable-cover">Cover</div>
										<div class="datatable-cell datatable-title">Podcast name</div>
										<div class="datatable-cell datatable-actions"></div>
									</div>
								</div>

								<div class="datatable-body">

									<?php foreach ( $shows as $show ) : ?>

										<?php if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) ) : ?>
											<div id="show_<?php echo esc_attr( $show['id'] ); ?>" class="datatable-row cfm-show-wrap">


												<div class="datatable-row-data">
													<div class="datatable-cell datatable-toggle"><a class="toggle-row" data-show-id="<?php echo esc_attr( $show['id'] ); ?>"><i class="fas fa-chevron-right"></i></a></div>

													<div class="datatable-cell datatable-cover">
														<img class="img-fluid" src="<?php echo esc_attr( cfm_get_show_artwork( $show['id'], '200x200' ) ); ?>" alt="<?php echo esc_attr( $show['title'] ); ?>" width="69" height="69">
													</div>

													<div class="datatable-cell datatable-title">
														<a target="_blank" href="<?php echo esc_url( CFMH_CAPTIVATE_URL . '/dashboard/podcast/' . $show['id'] . '/settings' ); ?>"><?php echo ( '1' == $show['private'] ) ? '<i class="fal fa-lock me-2 cfmsync-tooltip" data-bs-placement="bottom" title="Private Podcast"></i>' : ''; ?> <?php echo esc_html( $show['title'] ); ?></a>
														<span>Last Published: <?php echo esc_html( date( 'j M Y', strtotime( cfm_get_show_info( $show['id'], 'published_date' ) ) ) ); ?></span>
													</div>

													<div class="datatable-cell datatable-actions">

														<div class="icon-actions">
															<button class="cfm-display-show-settings btn cfmsync-tooltip" data-placement="top" title="Podcast Settings" aria-label="Podcast Settings" data-bs-toggle="modal" data-bs-target="#cfm-show-settings-modal" data-reference="<?php echo esc_attr( $show['id'] ); ?>"><i class="fal fa-cog"></i></button>
															<a class="btn cfmsync-tooltip clipboard<?php echo ( '1' == $show['private'] ) ? ' disabled': ''; ?>" data-placement="top" data-clipboard-response="RSS Feed has been copied." data-clipboard-text="<?php echo ( '1' == $show['private'] ) ? 'Private Podcast' : esc_attr( $show['feed_url'] ); ?>" title="Copy RSS Feed" aria-label="Copy RSS Feed"><i class="fal fa-rss"></i></a>
															<a class="btn cfmsync-tooltip" data-placement="top" title="Sync Podcast and Episodes" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Sync Podcast And Episodes" data-confirmation-content="Are you sure you want to sync this podcast information and episodes? This will pull all the data from Captivate and will update the show information and episodes on this website." data-confirmation-button="cfm-sync-show-and-episodes" data-confirmation-reference="<?php echo esc_attr( $show['id'] ); ?>" aria-label="Sync Podcast and Episodes"><i class="fal fa-sync"></i></a>
															<a class="btn cfmsync-tooltip" data-placement="top" title="Clear Publish Saved Data" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Clear Publish Saved Data" data-confirmation-content="Are you sure you want to clear the publish episode auto-save data on this podcast? All fields on publish episode screen for this podcast will be emptied." data-confirmation-button="cfm-clear-publish-data" data-confirmation-reference="<?php echo esc_attr( $show['id'] ); ?>" aria-label="Clear Publish Saved Data"><i class="fal fa-eraser"></i></a>
															<a class="btn cfmsync-tooltip" data-placement="top" title="Publish New Episode" href="<?php echo esc_url( admin_url( 'admin.php?page=cfm-hosting-publish-episode&show_id=' . $show['id'] ) ); ?>" aria-label="Publish New Episode"><i class="fal fa-podcast"></i></a>
														</div>
													</div>
												</div>

												<div class="datatable-row-detail" data-show-id="<?php echo esc_attr( $show['id'] ); ?>">

													<div class="d-lg-flex gap-4">

														<div>
															<div class="text-muted text-uppercase extra-small">Number of Episodes</div>
															<div class="fw-bold mt-2 mb-2 mb-lg-0"><?php echo cfm_get_published_episodes( $show['id'] ); ?></div>
														</div>
														<div class="separator"></div>
														<div>
															<div class="text-muted text-uppercase extra-small">Page Mapping <i class="fal fa-info-circle ms-2 cfmsync-tooltip" aria-hidden="true" data-bs-placement="top" aria-label="Page Mapping" data-bs-original-title="Select a page to use for your episode individual pages. This page's URL slug will be used to generate links for each episode. Additionally, if the 'Show episodes' option is set to 'Yes', a list of episodes will be displayed."></i></div>
															<div class="page-mapping mt-2 mb-2 mb-lg-0">
																<?php
																wp_dropdown_pages(
																	array(
																		'name' => 'page_for_show',
																		'id'   => 'page_for_show',
																		'show_option_none' => __( 'Page Mapping' ),
																		'option_none_value' => '0',
																		'class' => 'form-control form-control-sm',
																		'selected' => cfm_get_show_info(
																			$show['id'],
																			'index_page'
																		),
																	)
																);
																?>
															</div>
														</div>
														<div class="separator"></div>
														<div>
															<div class="text-muted text-uppercase extra-small">Show episodes <i class="fal fa-info-circle ms-2 cfmsync-tooltip" aria-hidden="true" data-bs-placement="top" aria-label="Show Episodes" data-bs-original-title="Display the list of episodes on the page you selected from the Page Mapping settings."></i></div>
															<div class="display-episodes mt-2 mb-2 mb-lg-0">
																<?php
																// always '1' if not exists/empty or checked.
																$display_episodes = cfm_get_show_info( $show['id'], 'display_episodes' ) == '0' ? '0' : '1';
																?>
																<select name="display_episodes" class="form-control form-control-sm" id="display_episodes">
																	<option value="1" <?php selected( $display_episodes, '1' ); ?>>Show episodes?</option>
																	<option value="1" <?php selected( $display_episodes, '1' ); ?>>Yes</option>
																	<option value="0" <?php selected( $display_episodes, '0' ); ?>>No</option>
																</select>
															</div>
														</div>
														<div class="separator"></div>
														<div>
															<div class="text-muted text-uppercase extra-small">Default author <i class="fal fa-info-circle ms-2 cfmsync-tooltip" aria-hidden="true" data-bs-placement="top" aria-label="Show Episodes" data-bs-original-title="Configure a default author for all new episodes. This setting will only apply to episodes created after the default author is set. Existing episodes will retain their current authors and will not be affected."></i></div>
															<div class="default-author mt-2 mb-2 mb-lg-0">
																<?php
																$query_users_ids_by_role = [
																	'fields' => ['id'],
																	'role__in' => ['administrator', 'editor', 'author'],
																];
																$array_of_users = get_users( $query_users_ids_by_role );
																$array_of_users_ids = array_map( function ( $user ) {
																	return $user->id;
																}, $array_of_users );
																$users_ids_list = implode( ',', $array_of_users_ids );

																wp_dropdown_users( array(
																	'name' => 'author_for_show',
																	'id'   => 'author_for_show',
																	'show'   => 'display_name_with_login',
																	'show_option_none' => __( 'Author' ),
																	'option_none_value' => '0',
																	'class' => 'form-control form-control-sm',
																	'include' => $users_ids_list,
																	'selected' => cfm_get_show_info( $show['id'], 'wp_author_id' ),
																) );
																?>
															</div>
														</div>
													</div>

												</div>


											</div>

										<?php endif; ?>

									<?php endforeach; ?>

								</div>

							</div>

						</div>

					<?php endif; ?>

				<?php else : ?>

					<div class="cfm-alert cfm-alert-warning">
						<span class="alert-icon"></span> <span class="alert-text">No podcasts synchronized to this website, yet.</span>
					</div>

				<?php endif; ?>

			<?php else : ?>
				<div class="cfm-alert cfm-alert-error">
					<span class="alert-icon"></span> <span class="alert-text"><strong>ERROR:</strong> Something went wrong! Please refresh the page and try again.</span>
				</div>
			<?php endif; ?>

			<?php
			wp_die();
		}

		/**
		 * Set show page
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function set_show_page() {

			$output = 'Something went wrong! Please refresh the page and try again.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				if ( isset( $_POST['show_id'] ) && isset( $_POST['page_id'] ) ) {

					$page_id =  sanitize_text_field( wp_unslash( $_POST['page_id'] ) );

					global $wpdb;
					$table_name = $wpdb->prefix . 'cfm_shows';

					$count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE cfm_option = 'index_page' AND cfm_value = '$page_id' AND cfm_value <> '0'");

					if ( $count > 0 ) {
						$output = 'already_exists';
					}
					else {
						$single_slug = CFMH_Hosting_Settings::get_settings( 'single_slug', 'captivate-podcast' );

						$sync_slug = ( $page_id != '0' ) ? get_bloginfo( 'url' ) . '/' . get_post_field( 'post_name', $page_id ) . '/' : get_bloginfo( 'url' ) . '/' . $single_slug . '/';
						$index_page_info = array();
						$index_page_info['captivate_sync_url'] = $sync_slug;

						$update_index_page = wp_remote_request(
							CFMH_API_URL . '/shows/' . $_POST['show_id'] . '/sync/url',
							array(
								'timeout' => 500,
								'body'    => $index_page_info,
								'method'  => 'PUT',
								'headers' => array(
									'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
								),
							)
						);

						// Debugging.
						cfm_generate_log( 'SYNC INDEX PAGE URL', $update_index_page );

						if ( ! is_wp_error( $update_index_page ) && 'Unauthorized' != $update_index_page['body'] && is_array( $update_index_page ) ) {

							cfm_update_show_info( $_POST['show_id'], 'index_page', $page_id );

							$output = 'success';

						}

					}

				}

			}

			echo $output;

			wp_die();

		}

		/**
		 * Set show author
		 *
		 * @since 1.1.4
		 * @return string
		 */
		public static function set_show_author() {

			$output = 'Something went wrong! Please refresh the page and try again.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				if ( isset( $_POST['show_id'] ) && isset( $_POST['author_id'] ) ) {

					cfm_update_show_info( $_POST['show_id'], 'wp_author_id', $_POST['author_id'] );

					$output = 'success';

				}

			}

			echo $output;

			wp_die();

		}

		/**
		 * Set display episodes
		 *
		 * @since 1.1.4
		 * @return string
		 */
		public static function set_display_episodes() {

			$output = 'Something went wrong! Please refresh the page and try again.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				if ( isset( $_POST['show_id'] ) && isset( $_POST['display_episodes'] ) ) {

					cfm_update_show_info( $_POST['show_id'], 'display_episodes', $_POST['display_episodes'] );

					$output = 'success';

				}

			}

			echo $output;

			wp_die();

		}

		/**
		 * Show settings
		 *
		 * @since 3.0.0
		 * @return string
		 */
		public static function load_show_settings() {

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) :

				$show_id = isset( $_POST['show_id'] ) ? sanitize_text_field( wp_unslash( $_POST['show_id'] ) ) : '';
				$use_artwork = cfm_get_show_info( $show_id, 'use_artwork_as_featured_image' );
				$season_episode_number_enable = cfm_get_show_info( $show_id, 'season_episode_number_enable' );
				$season_episode_number_text = cfm_get_show_info( $show_id, 'season_episode_number_text' );
				$bonus_trailer_text = cfm_get_show_info( $show_id, 'bonus_trailer_text' );
				?>
					<div class="row">
						<div class="col-lg-6">
							<p><strong><?php echo cfm_get_show_info( $show_id, 'title' ); ?></strong></p>
						</div>
						<div class="col-lg-6 text-end">
							<?php
							$index_page = cfm_get_show_info( $show_id, 'index_page' );
							if ( '0' != $index_page && '' != $index_page ) {
								echo '<a class="btn btn-outline-primary btn-sm" href="' . esc_url( get_permalink( $index_page ) ) . '" target="_blank">View Podcast Page <i class="fal fa-eye ms-2"></i></a>';
							}
							?>
							<a class="btn btn-outline-primary btn-sm" href="<?php echo esc_url( CFMH_CAPTIVATE_URL . '/dashboard/podcast/' . $show_id . '/episodes' ); ?>" target="_blank">View Podcast in Captivate <i class="fal fa-external-link ms-2"></i></a>
						</div>
					</div>

					<hr class="mt-2 mb-5 mt-lg-7 mb-lg-7">

					<div class="row">
						<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Featured Image</strong></div></div>
						<div class="col-lg-9">
							<div class="cfm-field">
								<label>Use podcast artwork as featured image?</label>
								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="use_artwork_if_empty" name="use_artwork" class="form-check-input" value="if_empty" <?php checked( $use_artwork, 'if_empty' ); ?>>
										<label class="form-check-label" for="use_artwork_if_empty">If no featured image</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="use_artwork_yes" name="use_artwork" class="form-check-input" value="1" <?php checked( $use_artwork, '1' ); ?>>
										<label class="form-check-label" for="use_artwork_yes">Yes</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="use_artwork_no" name="use_artwork" class="form-check-input" value="0" <?php echo ( '1' != $use_artwork && 'if_empty' != $use_artwork ) ? 'checked="checked"' : '' ; ?>>
										<label class="form-check-label" for="use_artwork_no">No</label>
									</div>
								</div>
								<small>If set to <strong>If no featured image</strong>, your episode-specific artwork (if any) or podcast artwork will be used as the featured image for episodes that has no featured image uploaded under this podcast.</small>

								<small>If set to <strong>Yes</strong>, your episode-specific artwork (if any) or podcast artwork will be used as the featured image for all episodes under this podcast.</small>

								<small><strong>Note: </strong> This will not replace the actual uploaded featured images.</small>
							</div>
						</div>
					</div>

					<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

					<div class="row">
						<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Season and Episode Number</strong></div></div>
						<div class="col-lg-9">
							<div class="cfm-field">
								<label>Show season and episode number before episode title?</label>
								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="se_num_default" name="se_num" class="form-check-input" value="default" <?php echo ( '1' != $season_episode_number_enable && '0' != $season_episode_number_enable ) ? 'checked="checked"' : '' ; ?>>
										<label class="form-check-label" for="se_num_default">Default <small class="d-inline">(in settings)</small></label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="se_num_yes" name="se_num" class="form-check-input" value="1" <?php checked( $season_episode_number_enable, '1' ); ?>>
										<label class="form-check-label" for="se_num_yes">Yes</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="se_num_no" name="se_num" class="form-check-input" value="0" <?php checked( $season_episode_number_enable, '0' ); ?>>
										<label class="form-check-label" for="se_num_no">No</label>
									</div>
								</div>
							</div>

							<div class="cfm-field mt-4">
								<label for="se_num_text">Season and Episode number text format</label>
								<input type="text" class="form-control" id="se_num_text" name="se_num_text" value="<?php echo esc_attr( $season_episode_number_text ); ?>" placeholder="S{snum} E{enum}: ">
								<small>Season and episode number text format where {snum} is the season number and {enum} is the episode number.</small>
							</div>

							<div class="cfm-field mt-4">
								<label for="bonus_trailer_text">Bonus and Trailer text format</label>
								<input type="text" class="form-control" id="bonus_trailer_text" name="bonus_trailer_text" value="<?php echo esc_attr( $bonus_trailer_text ); ?>" placeholder="S{snum} {enum} Episode: ">
								<small>If the episode is a bonus or trailer, this formatting will be applied where {snum} is the season number and {enum} is the "Bonus" or "Trailer" text.</small>
							</div>
						</div>
					</div>

				<?php

			?>
			<?php else : ?>
				<div class="cfm-alert cfm-alert-error">
					<span class="alert-icon"></span> <span class="alert-text"><strong>ERROR:</strong> Something went wrong! Please refresh the page and try again.</span>
				</div>
			<?php endif; ?>
			<?php
			wp_die();
		}

		/**
		 * Save show settings
		 *
		 * @since 3.0.0
		 * @return string
		 */
		public static function save_show_settings() {

			$output = 'Something went wrong! Please refresh the page and try again.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				if ( isset( $_POST['show_id'] ) ) {

					if ( isset( $_POST['use_artwork'] ) ) {
						cfm_update_show_info( $_POST['show_id'], 'use_artwork_as_featured_image', sanitize_text_field( wp_unslash( $_POST['use_artwork'] ) ) );
					}

					if ( isset( $_POST['se_num'] ) ) {
						cfm_update_show_info( $_POST['show_id'], 'season_episode_number_enable', sanitize_text_field( wp_unslash( $_POST['se_num'] ) ) );
					}

					if ( isset( $_POST['se_num_text'] ) ) {
						cfm_update_show_info( $_POST['show_id'], 'season_episode_number_text', wp_unslash( wp_filter_kses( $_POST['se_num_text'] ) ) );
					}

					if ( isset( $_POST['bonus_trailer_text'] ) ) {
						cfm_update_show_info( $_POST['show_id'], 'bonus_trailer_text', wp_unslash( wp_filter_kses( $_POST['bonus_trailer_text'] ) ) );
					}

					$output = 'success';

				}

			}

			echo $output;

			wp_die();

		}

	}

endif;
