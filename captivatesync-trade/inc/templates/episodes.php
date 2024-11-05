<?php
/**
 * Template page for episodes list
 */
?>

<div class="wrap cfmh cfm-hosting-podcast-episodes">

	<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>

	<div class="cfm-page-content">

		<?php
		$show_id = cfm_get_show_id();

		$args     = array(
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
		?>

		<div class="row">
			<div class="col-12">

				<div class="cfm-table cfm-data-table filter-enabled">

					<?php if ( ! $episodes->have_posts() ) : ?>

						<div id="cfm-datatable-episodes_filter" class="dataTables_filter mb-4 text-end"><div class="filter-actions"><a href="<?php echo esc_url( admin_url( 'admin.php?page=cfm-hosting-publish-episode&show_id=' . $show_id ) ); ?>" class="btn btn-primary">Publish New Episode <i class="fal fa-podcast ms-lg-2"></i></a></div></div>

					<?php endif; ?>

					<table id="cfm-datatable-episodes" class="table">
						<thead>
							<tr>
								<th class="cfm-th-num" width="50">#</th>
								<th class="cfm-th-title">Episode</th>
								<th class="cfm-th-date">Date</th>
								<th class="cfm-th-status">Publish Status</th>
								<th class="cfm-th-actions"></th>
							</tr>
						</thead>

						<tbody>
							<?php
							if ( $episodes->have_posts() ) {

								while ( $episodes->have_posts() ) {

									$episodes->the_post();
									$pid            			= get_the_ID();
									$cfm_episode_id  			= get_post_meta( $pid, 'cfm_episode_id', true );
									$post_status    			= get_post_status();
									$nonce_trash 				= wp_create_nonce( 'trash_post_' . $pid );
									$nonce_toggle 				= wp_create_nonce( 'toggle_post_' . $pid );
									$btn_disabled 				= ( 'future' != $post_status && 'publish' != $post_status ) ? ' disabled' : '';
									$cfm_episode_itunes_type 	= get_post_meta( $pid, 'cfm_episode_itunes_type', true );
									$cfm_episode_itunes_number 	= get_post_meta( $pid, 'cfm_episode_itunes_number', true );
									$cfm_episode_expiration 	= get_post_meta( $pid, 'cfm_episode_expiration', true );
									$cfm_episode_early_access_end_date 	= get_post_meta( $pid, 'cfm_episode_early_access_end_date', true );
									$early_access = ( $cfm_episode_early_access_end_date ) ? '<i class="fal fa-calendar-clock me-1 cfmsync-tooltip" data-bs-placement="top" data-bs-html="true" title="This episode will release to your public feed on<br>' . date( 'F j, Y G:i', strtotime( $cfm_episode_early_access_end_date ) ) . '"></i>' : '';
									$episode_private 			= ( '1' == get_post_meta( $pid, 'cfm_episode_private', true ) ) ? '<i class="fa-light fa-dot-circle cfmsync-tooltip" data-bs-placement="top" title="This episode will not display in your RSS feed, Captivate Sites, and Captivate Sync."></i>' : '';

									$permalink = ( 'future' == $post_status || 'publish' == $post_status ) ? get_permalink( $pid ) : get_bloginfo( 'url' ) . '/?post_type=captivate_podcast&p=' . $pid . '&preview=true';
									$cfm_episode_amie_status 	= get_post_meta( $pid, 'cfm_episode_amie_status', true );
									$cfm_episode_website_active = get_post_meta( $pid, 'cfm_episode_website_active', true );
									$cfm_episode_status = get_post_meta( $pid, 'cfm_episode_status', true );

									// set the expiration tooltip.
									if ( $cfm_episode_expiration ) {
										$episode_expiration_date = date( 'F j, Y G:i', strtotime( $cfm_episode_expiration ) );
										$episode_expiration = cfm_is_datetime_passed( $cfm_episode_expiration ) ? "<i class='fa-light fa-stopwatch cfmsync-tooltip' data-bs-placement='top' title='This episode expired on {$episode_expiration_date}'></i>" : "<i class='fa-light fa-stopwatch cfmsync-tooltip' data-bs-placement='top' data-bs-html='true' title='This episode will expire on<br> {$episode_expiration_date}'></i>";
									}
									else {
										$episode_expiration = '';
									}

									// set amie status.
									switch ( $cfm_episode_amie_status ) {
										case 'complete':
											$cfm_episode_amie_status_icon = ' <img class="amie-icon cfmsync-tooltip" data-bs-placement="top" data-bs-html="true" src="' . esc_url( CFMH_URL ) . 'assets/img/amie-complete.svg" title="AMIE processing is done. If this episode is published, the processed file will be live.">';
											break;
										case 'processing':
											$cfm_episode_amie_status_icon = ' <img class="amie-icon cfmsync-tooltip" data-bs-placement="top" data-bs-html="true" src="' . esc_url( CFMH_URL ) . 'assets/img/amie-processing.svg" title="AMIE processing in progress. If this episode is published, the original file will still be live.">';
											break;
										case 'failed':
											$cfm_episode_amie_status_icon = ' <img class="amie-icon cfmsync-tooltip" data-bs-placement="top" data-bs-html="true" src="' . esc_url( CFMH_URL ) . 'assets/img/amie-failed.svg" title="AMIE processing failed. If this episode is published, the original file will still be live.">';
											break;
										default:
										$cfm_episode_amie_status_icon = '';
									}

									// set the complete status.
									if ( 'Expired' == $cfm_episode_status ) {
										$episode_status = "<span class='status expired'>{$episode_expiration}{$episode_private} Expired " . $cfm_episode_amie_status_icon . "</span>";
									}
									elseif ( 'Early Access' == $cfm_episode_status ) {
										$episode_status = "<span class='status early-access'>{$early_access}{$episode_expiration}{$episode_private} Early Access " . $cfm_episode_amie_status_icon . "</span>";
									}
									elseif ( 'Exclusive' == $cfm_episode_status ) {
										$episode_status = "<span class='status exclusive'><i class='fal fa-circle-star me-1'></i>{$episode_expiration}{$episode_private} Exclusive" . $cfm_episode_amie_status_icon . "</span>";
									}
									else {
										if ( 'future' == $post_status ) {
											$episode_status = "<span class='status scheduled'><i class='far fa-clock'></i>{$episode_expiration}{$episode_private} Scheduled" . $cfm_episode_amie_status_icon . "</span>";
										}
										elseif ( 'publish' == $post_status ) {
											$episode_status = "<span class='status published'><i class='fa-light fa-circle-check'></i> {$episode_expiration}{$episode_private} Published" . $cfm_episode_amie_status_icon . "</span>";
										}
										else {
											$episode_status = "<span class='status " . esc_attr( $post_status ) . " text-capitalize'>{$episode_expiration}{$episode_private} " . esc_html( $post_status ) . $cfm_episode_amie_status_icon . "</span>";
										}
									}
									?>

									<tr id="post-<?php echo esc_attr($pid); ?>">
										<td class="cfm-td-num">
											<?php
											if ( 'trailer' == $cfm_episode_itunes_type || 'bonus' == $cfm_episode_itunes_type ) {
												echo '<span class="text-capitalize">' . esc_html( $cfm_episode_itunes_type ) . '</span>';
											}
											else {
												echo '<span>' . esc_html( $cfm_episode_itunes_number ) . '</span>';
											}
											?>
										</td>
										<td class="cfm-td-title">
											<span><?php echo esc_html( get_the_title() ); ?></span>
											<p class="hidden">
												<span><?php echo esc_html( get_the_date( 'jS F Y', $pid ) ); ?> <?php echo $episode_status; ?></span>
												<span>
													<a aria-label="Share" class="btn<?php echo $btn_disabled; ?>" data-bs-toggle="modal" data-bs-target="#cfm-episode-share-modal"><i class="fal fa-share-alt"></i></a>
													<a aria-label="Analytics" class="btn<?php echo $btn_disabled; ?>" href="<?php echo esc_url( CFMH_CAPTIVATE_URL . "/dashboard/podcast/{$show_id}/analytics/{$cfm_episode_id}" ); ?>" target="_blank"><i class="fal fa-analytics"></i></a>
													<a aria-label="Edit" class="btn" href="<?php echo esc_url( admin_url( 'admin.php?page=cfm-hosting-edit-episode&show_id=' . $show_id . '&eid=' . $pid ) ); ?>"><i class="fal fa-edit"></i></a>

													<?php if ( '0' == $cfm_episode_website_active ) : ?>
														<a aria-label="Toggle" class="btn btn-toggle" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Activate Episode" data-confirmation-content="Are you sure you want to activate '<?php echo esc_attr( get_the_title() ); ?>' episode? This episode will be activated and will be available publicly on this website." data-confirmation-button="cfm-toggle-episode" data-confirmation-button-text="Activate Episode" data-confirmation-reference="<?php echo esc_attr( $pid ); ?>" data-confirmation-nonce="<?php echo esc_attr( $nonce_toggle ); ?>"><i class="fal fa-play"></i></a>
													<?php else : ?>
														<a aria-label="Toggle" class="btn btn-toggle" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Deactivate Episode" data-confirmation-content="Are you sure you want to deactivate '<?php echo esc_attr( get_the_title() ); ?>' episode? This episode will be deactivated and will not be available publicly on this website. This action will not change the episode status and will not affect the episode in Captivate." data-confirmation-button="cfm-toggle-episode" data-confirmation-button-text="Deactivate Episode" data-confirmation-reference="<?php echo esc_attr( $pid ); ?>" data-confirmation-nonce="<?php echo esc_attr( $nonce_toggle ); ?>"><i class="fal fa-pause"></i></a>
													<?php endif; ?>

													<?php if ( current_user_can( 'delete_others_posts' ) ) : ?>
														<a aria-label="Trash" class="btn btn-trash" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Delete Episode" data-confirmation-content="Are you sure you want to delete this episode? This episode will be removed from your Captivate account too." data-confirmation-button="cfm-trash-episode" data-confirmation-button-text="Delete Episode" data-confirmation-reference="<?php echo esc_attr( $pid ); ?>" data-confirmation-nonce="<?php echo esc_attr( $nonce_trash ); ?>"><i class="fal fa-trash-alt"></i></a>
													<?php endif; ?>
												</span>
											</p>
										</td>

										<td class="cfm-td-date" data-sort="<?php echo esc_attr( get_the_date( 'Y-m-d H:i:s', $pid ) ); ?>"><?php echo esc_html( get_the_date( 'jS M Y', $pid ) ); ?></td>

										<td class="cfm-td-status <?php echo esc_attr( 'cfm-td-status-' . $post_status ); ?>"><?php echo $episode_status; ?></td>

										<td class="cfm-td-actions">

											<a aria-label="Share" class="btn<?php echo $btn_disabled; ?> cfmsync-tooltip" title="Share episode" data-bs-toggle="modal" data-bs-target="#cfm-episode-share-modal"><i class="fal fa-share-alt"></i></a>
											<a aria-label="Analytics" class="btn<?php echo $btn_disabled; ?> cfmsync-tooltip" title="View episode analytics" href="<?php echo esc_url( CFMH_CAPTIVATE_URL . "/dashboard/podcast/{$show_id}/analytics/{$cfm_episode_id}" ); ?>" target="_blank"><i class="fal fa-analytics"></i></a>
											<a aria-label="Edit" class="btn cfmsync-tooltip" title="Edit episode" href="<?php echo esc_url(admin_url( 'admin.php?page=cfm-hosting-edit-episode&show_id=' . $show_id . '&eid=' . $pid ) ); ?>"><i class="fal fa-edit"></i></a>

											<?php if ( '0' == $cfm_episode_website_active ) : ?>
												<a aria-label="Toggle" class="btn btn-toggle cfmsync-tooltip" title="Activate episode" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Activate Episode" data-confirmation-content="Are you sure you want to activate '<?php echo esc_attr( get_the_title() ); ?>' episode? This episode will be activated and will be available publicly on this website." data-confirmation-button="cfm-toggle-episode" data-confirmation-button-text="Activate Episode" data-confirmation-reference="<?php echo esc_attr( $pid ); ?>" data-confirmation-nonce="<?php echo esc_attr( $nonce_toggle ); ?>"><i class="fal fa-play"></i></a>
											<?php else : ?>
												<a aria-label="Toggle" class="btn btn-toggle cfmsync-tooltip" title="Deactivate episode" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Deactivate Episode" data-confirmation-content="Are you sure you want to deactivate '<?php echo esc_attr( get_the_title() ); ?>' episode? This episode will be deactivated and will not be available publicly on this website. This action will not change the episode status and will not affect the episode in Captivate." data-confirmation-button="cfm-toggle-episode" data-confirmation-button-text="Deactivate Episode" data-confirmation-reference="<?php echo esc_attr( $pid ); ?>" data-confirmation-nonce="<?php echo esc_attr( $nonce_toggle ); ?>"><i class="fal fa-pause"></i></a>
											<?php endif; ?>

											<?php if ( current_user_can( 'delete_others_posts' ) ) : ?>
												<a aria-label="Trash" class="btn btn-trash cfmsync-tooltip" title="Delete episode" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Delete Episode" data-confirmation-content="Are you sure you want to delete '<?php echo esc_attr( get_the_title() ); ?>' episode? This episode will be removed from your Captivate account too." data-confirmation-button="cfm-trash-episode" data-confirmation-button-text="Delete Episode" data-confirmation-reference="<?php echo esc_attr( $pid ); ?>" data-confirmation-nonce="<?php echo esc_attr( $nonce_trash ); ?>"><i class="fal fa-trash-alt"></i></a>
											<?php endif; ?>
										</td>
									</tr>
									<?php
								}

								wp_reset_postdata();
							}
							else {
								$colspan = current_user_can( 'delete_others_posts' ) ? '8' : '7';
								echo '<tr><td colspan="' . $colspan . '">0 episodes found.</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>

			</div>
		</div>

	</div><!--/ .cfm-page-content -->

	<?php require CFMH . 'inc/templates/template-parts/footer.php'; ?>

	<!-- Share modal -->
	<div id="cfm-episode-share-modal" class="modal fade modal-slideout" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-slideout" role="document">
			<div class="modal-content">
				<div class="offcanvas-header flex-column align-items-end mb-4">
					<button type="button" aria-label="Close" data-bs-dismiss="modal" class="close-btn"> Close <i class="fas fa-arrow-right"></i></button>
				</div>

				<div class="modal-header">
					<h4 class="modal-title">Share this episode</h4>
				</div>

				<div class="modal-body">
					<div id="cfm-episode-share" class="cfm-episode-share"></div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-outline-primary me-auto" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- /Share modal -->

</div><!--/ .wrap -->
