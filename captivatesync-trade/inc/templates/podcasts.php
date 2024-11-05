<?php
/**
 * Template page for shows list
 */
?>

<div class="wrap cfmh cfm-hosting-podcasts">

	<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>

	<?php $shows = cfm_get_shows(); $user_shows = get_user_meta( get_current_user_id(), 'cfm_user_shows', true ); ?>

	<div class="cfm-page-content">

		<div class="row mb-4">
			<div class="col-lg-6">
				<div class="manage-podcasts">
					<?php
					if ( isset( $_GET['page'] ) && ( 'cfm-hosting-publish-episode' != sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) :

						if ( ! empty( $shows ) && current_user_can( 'manage_options' ) ) {
							echo '<button class="btn btn-primary me-4 mb-2" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Manually Sync Podcast Data" data-confirmation-content="Are you sure you want to sync all your podcasts and episodes on this website? Manual sync will pull all the data from Captivate and will update all the podcasts and episodes on this website." data-confirmation-button="cfm-manual-sync-data">Manually Sync Podcast Data</button>';
						}
						?>

						<?php if ( current_user_can( 'manage_options' ) ) : ?>
							<button id="cfm-manage-shows" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#cfm-captivate-shows-modal">Add/Remove Podcasts</button>
						<?php endif; ?>

					<?php endif; ?>
				</div>
			</div>
			<div class="col-lg-6">
				<?php if ( ! empty( $shows ) ) : ?>
					<div class="float-end">
						<?php $default_view = get_user_meta( get_current_user_id(), 'cfm_podcasts_default_view', true ); ?>
						<div class="btn-group cfm-content-switcher">
							<a aria-label="List View" id="cfm-list-view" <?php echo $default_view == 'list' ? 'active' : ''; ?>><i class="fa fa-list fa-fw"></i></a>
							<a aria-label="Grid View" id="cfm-grid-view" <?php echo $default_view == 'list' ? 'grid' : ''; ?>><i class="fa fa-th-large fa-fw"></i></a>
							<input type="hidden" name="data_content" value="<?php echo esc_attr( $default_view ); ?>">
						</div>
					</div>

					<?php $default_sort = get_user_meta( get_current_user_id(), 'cfm_podcasts_default_sort', true ); ?>
					<div id="cfm-dropdown-sort-podcasts" class="cfm-dropdown-menu dropdown-sort float-lg-end me-2" data-sort="<?php echo esc_attr( $default_sort ); ?>">
						<span>Sort By:</span>
						<button id="sort-shows-by" type="button" class="btn btn-border dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<?php
							if ( $default_sort == 'created' ) {
								echo 'Podcast Creation Date';
							}
							else if ( $default_sort == 'published_date' ) {
								echo 'Last Published Date';
							}
							else {
								echo 'Podcast Name';
							}
							?>
						</button>
						<div class="dropdown-menu" aria-labelledby="sortShowsBy">
						<a class="dropdown-item <?php echo $default_sort == 'title' ? 'active' : ''; ?>" data-sort="title">Podcast Name</a>
						<a class="dropdown-item <?php echo $default_sort == 'created' ? 'active' : ''; ?>" data-sort="created">Podcast Creation Date</a>
						<a class="dropdown-item <?php echo $default_sort == 'published_date' ? 'active' : ''; ?>" data-sort="published_date">Last Published Date</a>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div id="cfm-shows" class="cfm-shows"></div>

	</div><!--/ .cfm-page-content -->

	<?php require CFMH . 'inc/templates/template-parts/footer.php'; ?>

	<!-- Select shows modal -->
	<div id="cfm-captivate-shows-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">My Podcasts</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body">
					<div id="cfm-captivate-shows" class="cfm-captivate-shows"></div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-outline-primary me-auto" data-bs-dismiss="modal">Close</button>
					<button id="cfm-select-captivate-shows" type="button" class="btn btn-primary">Select &amp; Sync Podcasts <i class="fal fa-sync ms-2"></i></button>
				</div>
			</div>
		</div>
	</div>
	<!-- /Select shows modal -->

</div><!--/ .wrap -->
