<?php
/**
 * Template page for shortcode
 */
?>

<div class="wrap cfmh cfm-hosting-shortcode">

	<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>

	<?php $shows = cfm_get_shows(); $user_shows = get_user_meta( get_current_user_id(), 'cfm_user_shows', true ); ?>

	<div class="cfm-page-content">

		<div class="cfm-shortcode-builder">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Podcast</strong></div></div>
				<div class="col-lg-9">
					<div class="cfm-field cfm-podcasts-picker">
						<label for="select_shows">Select Podcast(s)</label>

						<div class="cfm-dropdown-picker">
							<input type="text" class="form-control form-control-sm dropdown-toggle" id="select_shows" name="select_shows" placeholder="Search for podcasts..." data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" autocomplete="off">

							<div class="dropdown-menu" aria-labelledby="select-shows">
								<?php
								if ( ! empty( $shows ) ) {
									foreach ( $shows as $show ) {
										if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) ) {
											echo '<a class="dropdown-item" data-id="' . esc_attr( $show['id'] ) . '">' . esc_html( $show['title'] ) . '</a>';
										}
									}
								}
								else {
									echo '<span>No podcasts found.</span>';
								}
								?>
							</div>

							<div id="cfm-podcasts-selected" class="cfm-dropdown-selected"></div>
						</div>

						<small>You can select multiple podcasts. Useful for displaying latest episodes across all your podcasts.</small>
					</div>

					<div class="cfm-field cfm-episodes-picker mt-4">
						<label for="select_episodes">Select Episode(s)</label>

						<div class="cfm-dropdown-picker">
							<input type="text" class="form-control form-control-sm dropdown-toggle" id="select_episodes" name="select_episodes" placeholder="Search for episodes..." data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" autocomplete="off">

							<div class="dropdown-menu" aria-labelledby="select-episodes" style="max-height: 200px; overflow: auto;">
								<?php
								if ( ! empty( $shows ) ) {

									$shows_count = count( $shows );

									foreach ( $shows as $show ) {
										if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) ) {

											echo ( $shows_count > 1 ) ? '<div class="dropdown-row-group"><div class="dropdown-header">' . esc_html( $show['title'] ) . '</div>' : '';

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

														echo '<a class="dropdown-item" data-show-id="' . esc_attr( $show['id'] ) . '" data-id="' . esc_attr( get_the_ID() ) . '">' . esc_html( get_the_title() ) . '<small class="status ' . esc_attr( $episode_status ) . '">' . esc_html( $episode_status ) . '</small></a>';
													}
													wp_reset_postdata();
												}
												else {
													echo '<span>No episodes found from this podcast.</span>';
												}

											echo ( $shows_count > 1 ) ? '</div>' : '';
										}
									}
								}
								else {
									echo '<span>No episodes found.</span>';
								}
								?>
							</div>

							<div id="cfm-episodes-selected" class="cfm-dropdown-selected"></div>
						</div>

						<small>You can select multiple episodes. Leave empty to display all from selected podcasts above.</small>
						<small><strong>Note:</strong> Scheduled or draft episodes can be selected, but they will not appear until they are published. Additionally, inactive episodes will not be displayed.</small>
					</div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Layout</strong></div></div>
				<div class="col-lg-9">

					<div class="row">

						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Layout</label>
								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_layout_grid" name="shortcode_layout" class="form-check-input" value="list" checked="checked">
										<label class="form-check-label" for="shortcode_layout_grid">List</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_layout_list" name="shortcode_layout" class="form-check-input" value="grid">
										<label class="form-check-label" for="shortcode_layout_list">Grid</label>
									</div>
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Grid Column</label>
								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_column_2" name="shortcode_column" class="form-check-input" value="2" checked="checked">
										<label class="form-check-label" for="shortcode_column_2">2</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_column_3" name="shortcode_column" class="form-check-input" value="3">
										<label class="form-check-label" for="shortcode_column_3">3</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_column_4" name="shortcode_column" class="form-check-input" value="4">
										<label class="form-check-label" for="shortcode_column_4">4</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_column_5" name="shortcode_column" class="form-check-input" value="5">
										<label class="form-check-label" for="shortcode_column_5">5</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_column_6" name="shortcode_column" class="form-check-input" value="6">
										<label class="form-check-label" for="shortcode_column_6">6</label>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Content</strong></div></div>
				<div class="col-lg-9">

					<div class="row">
						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Episode Title</label>

								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_title_show" name="shortcode_title" class="form-check-input" value="show" checked="checked">
										<label class="form-check-label" for="shortcode_title_show">Show</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_title_hide" name="shortcode_title" class="form-check-input" value="hide">
										<label class="form-check-label" for="shortcode_title_hide">Hide</label>
									</div>
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Season and Episode Number</label>

								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_se_num_default" name="shortcode_se_num" class="form-check-input" value="default" checked="checked">
										<label class="form-check-label" for="shortcode_se_num_default">Default <small class="d-inline">(in podcast settings)</small></label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_se_num_show" name="shortcode_se_num" class="form-check-input" value="show">
										<label class="form-check-label" for="shortcode_se_num_show">Show</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_se_num_hide" name="shortcode_se_num" class="form-check-input" value="hide">
										<label class="form-check-label" for="shortcode_se_num_hide">Hide</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Episode Title Tag</label>
								<input type="text" class="form-control form-control-sm" id="shortcode_title_tag" name="shortcode_title_tag" value="h2" placeholder="h1, h2, h3, h4, h5, h6, div, p">
							</div>
						</div>

						<div class="col-lg-3">
							<div class="cfm-field color-picker-sm">
								<label>Episode Title Color</label>
								<input name="shortcode_title_color" type="text" class="cfm-color-picker">
							</div>
						</div>

						<div class="col-lg-3">
							<div class="cfm-field">
								<label>Episode Title Hover Color</label>
								<input name="shortcode_title_hover_color" type="text" class="cfm-color-picker">
							</div>
						</div>
					</div>

					<div class="cfm-field mt-4">
						<div class="row">
							<div class="col-lg-6">
								<div class="cfm-field">
									<label>Featured Image</label>
									<div class="form-group">
										<div class="form-check form-check-inline">
											<input type="radio" id="shortcode_image_above_title" name="shortcode_image" class="form-check-input" value="above_title" checked="checked">
											<label class="form-check-label" for="shortcode_image_above_title">Above</label>
										</div>
										<div class="form-check form-check-inline">
											<input type="radio" id="shortcode_image_below_title" name="shortcode_image" class="form-check-input" value="below_title">
											<label class="form-check-label" for="shortcode_image_below_title">Below</label>
										</div>
										<div class="form-check form-check-inline">
											<input type="radio" id="shortcode_image_left" name="shortcode_image" class="form-check-input" value="left">
											<label class="form-check-label" for="shortcode_image_left">Left</label>
										</div>
										<div class="form-check form-check-inline">
											<input type="radio" id="shortcode_image_right" name="shortcode_image" class="form-check-input" value="right">
											<label class="form-check-label" for="shortcode_image_right">Right</label>
										</div>
										<div class="form-check form-check-inline">
											<input type="radio" id="shortcode_image_hide" name="shortcode_image" class="form-check-input" value="hide">
											<label class="form-check-label" for="shortcode_image_hide">Hide</label>
										</div>
									</div>
								</div>
							</div>

							<div class="col-lg-6">
								<div class="cfm-field">
									<label>Featured Image Size</label>
									<div class="form-group">
										<div class="form-check form-check-inline">
											<input type="radio" id="shortcode_image_size_thumbnail" name="shortcode_image_size" class="form-check-input" value="thumbnail">
											<label class="form-check-label" for="shortcode_image_size_thumbnail">Thumbnail</label>
										</div>
										<div class="form-check form-check-inline">
											<input type="radio" id="shortcode_image_size_medium" name="shortcode_image_size" class="form-check-input" value="medium">
											<label class="form-check-label" for="shortcode_image_size_medium">Medium</label>
										</div>
										<div class="form-check form-check-inline">
											<input type="radio" id="shortcode_image_size_large" name="shortcode_image_size" class="form-check-input" value="large" checked="checked">
											<label class="form-check-label" for="shortcode_image_size_large">Large</label>
										</div>
										<div class="form-check form-check-inline">
											<input type="radio" id="shortcode_image_size_full" name="shortcode_image_size" class="form-check-input" value="full">
											<label class="form-check-label" for="shortcode_image_size_full">Full</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Episode Content</label>

								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_content_excerpt" name="shortcode_content" class="form-check-input" value="excerpt" checked="checked">
										<label class="form-check-label" for="shortcode_content_excerpt">Excerpt</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_content_fulltext" name="shortcode_content" class="form-check-input" value="fulltext">
										<label class="form-check-label" for="shortcode_content_fulltext">Full Text</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_content_hide" name="shortcode_content" class="form-check-input" value="hide">
										<label class="form-check-label" for="shortcode_content_hide">Hide</label>
									</div>
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Episode Player</label>

								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_player_above" name="shortcode_player" class="form-check-input" value="above_content" checked="checked">
										<label class="form-check-label" for="shortcode_player_above">Above Content</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_player_below" name="shortcode_player" class="form-check-input" value="below_content">
										<label class="form-check-label" for="shortcode_player_below">Below Content</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_player_hide" name="shortcode_player" class="form-check-input" value="hide">
										<label class="form-check-label" for="shortcode_player_hide">Hide</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Excerpt Length <small class="d-inline">(in words)</small></label>
								<input type="number" class="form-control form-control-sm" name="shortcode_content_length" value="55">
							</div>
						</div>

						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Number of Episodes</label>
								<input type="number" class="form-control form-control-sm" name="shortcode_items" value="10">
							</div>
						</div>
					</div>

					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Link</label>

								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_link_show" name="shortcode_link" class="form-check-input" value="show" checked="checked">
										<label class="form-check-label" for="shortcode_link_show">Show</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_link_hide" name="shortcode_link" class="form-check-input" value="hide">
										<label class="form-check-label" for="shortcode_link_hide">Hide</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Link Text</label>
								<input type="text" class="form-control form-control-sm" name="shortcode_link_text" value="Listen to this episode">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="cfm-field">
								<label>Link Text Color</label>
								<input name="shortcode_link_text_color" type="text" class="cfm-color-picker">
							</div>
						</div>

						<div class="col-lg-3">
							<div class="cfm-field">
								<label>Link Text Hover Color</label>
								<input name="shortcode_link_text_hover_color" type="text" class="cfm-color-picker">
							</div>
						</div>
					</div>

					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Order</label>

								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_order_descending" name="shortcode_order" class="form-check-input" value="desc" checked="checked">
										<label class="form-check-label" for="shortcode_order_descending">Newest Episode First</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_order_ascending" name="shortcode_order" class="form-check-input" value="asc">
										<label class="form-check-label" for="shortcode_order_ascending">Oldest Episode First</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_order_episodes" name="shortcode_order" class="form-check-input" value="episodes">
										<label class="form-check-label" for="shortcode_order_episodes">Episodes Selection</label>
									</div>
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Pagination</label>

								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_pagination_numbers" name="shortcode_pagination" class="form-check-input" value="numbers" checked="checked">
										<label class="form-check-label" for="shortcode_pagination_numbers">Numbers</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_pagination_load_more" name="shortcode_pagination" class="form-check-input" value="load_more">
										<label class="form-check-label" for="shortcode_pagination_load_more">Load More</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_pagination_hide" name="shortcode_pagination" class="form-check-input" value="hide">
										<label class="form-check-label" for="shortcode_pagination_hide">Hide</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Exclude First Episode</label>

								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_exclude_no" name="shortcode_exclude" class="form-check-input" value="no" checked="checked">
										<label class="form-check-label" for="shortcode_exclude_no">No</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="shortcode_exclude_yes" name="shortcode_exclude" class="form-check-input" value="yes">
										<label class="form-check-label" for="shortcode_exclude_yes">Yes</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Load More Button Text</label>
								<input name="shortcode_pagination_load_more_text" type="text" class="form-control form-control-sm" value="Load More">
							</div>
						</div>

						<div class="col-lg-6">
							<div class="cfm-field">
								<label>Load More Button Class</label>
								<input name="shortcode_pagination_load_more_class" type="text" class="form-control form-control-sm">
							</div>
						</div>
					</div>

				</div>

			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Paste this shortcode into your website page</strong></div></div>
				<div class="col-lg-9">
					<div id="clipboard-shortcode" class="border p-3 mt-2 fw-light text-copy">
						<?php
						if ( get_option( 'cfm_shortcode_latest' ) ) {
							echo get_option( 'cfm_shortcode_latest' );
						}
						else {
							echo '[cfm_captivate_episodes show_id=""]';
						}
						?>
					</div>
					<div class="text-end mt-2">
						<a class="btn btn-outline-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#cfm-shortcode-preview-modal">Preview</a>
						<a class="btn btn-outline-primary btn-sm clipboard" data-clipboard-target="#clipboard-shortcode" data-clipboard-response="Shortcode has been copied.">Copy</a>
					</div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"></div>
				<div class="col-lg-9">
					<button type="button" id="generate_shortcode" name="generate_shortcode" class="btn btn-primary full-md-button">Update Shortcode <i class="fal fa-code ms-2"></i></button>
				</div>
			</div>

		</div>

	</div><!--/ .cfm-page-content -->

	<?php require CFMH . 'inc/templates/template-parts/footer.php'; ?>

	<!-- Shortcode preview modal -->
	<div id="cfm-shortcode-preview-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Shortcode Preview</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body">
					<div id="shortcode-preview" class="shortcode-preview"><?php echo get_option( 'cfm_shortcode_preview' ) ? do_shortcode( get_option( 'cfm_shortcode_preview' ) ) : ''; ?></div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-outline-primary me-auto" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- /Shortcode preview modal -->

</div><!--/ .wrap -->

