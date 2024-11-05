<?php
/**
 * User authentication template
 */
?>

<div class="wrap cfmh cfm-hosting-credentials">

	<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>

	<div class="cfm-page-content">

		<?php if ( cfm_user_authentication() ) : ?>

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>User Authentication</strong></div></div>
				<div class="col-lg-9">
					<div class="authentication-name"><?php echo esc_html( get_option( 'cfm_authentication_name' ) ); ?></div>
					<div class="authentication-date"><?php echo ( true === cfm_user_authentication() ) ? '<i class="fas fa-check me-2"></i> Authenticated on' : '<i class="fas fa-times me-2 text-danger"></i> Authentication FAILED on'; ?> <span><?php echo esc_html( gmdate( 'F j, Y H:ia', strtotime( get_option( 'cfm_authentication_date_added' ) ) ) ); ?></span></div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"></div>
				<div class="col-lg-9">
					<button type="submit" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Remove Authentication" data-confirmation-content="Are you sure you want to remove authentication on this website? User credentials, shows, and episodes will be removed from this site." data-confirmation-button="remove-authentication" class="btn btn-primary">Remove Authentication <i class="fal fa-user-times ms-2"></i></button>
				</div>
			</div>

		<?php else : ?>

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>User Authentication</strong></div></div>
				<div class="col-lg-9">
					<div class="cfm-field">
						<label for="auth_id">User ID</label>
						<input type="text" class="form-control" id="auth_id" name="auth_id" autocomplete="off">
					</div>

					<div class="cfm-field mt-4">
						<label for="auth_key">API Key</label>
						<input type="text" class="form-control" id="auth_key" name="auth_key" autocomplete="off">
					</div>

					<div class="mt-4">
						<a class="text-decoration-none" href="https://help.captivate.fm/en/articles/3440133-how-to-find-your-captivate-api-details" target="_blank">How to find your API details</a>
					</div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"></div>
				<div class="col-lg-9">
					<button type="submit" id="create-authentication" class="btn btn-primary">Authenticate User <i class="fal fa-user-check ms-2"></i></button>
				</div>
			</div>

		<?php endif; ?>

	</div><!--/ .cfm-page-content -->

	<?php require CFMH . 'inc/templates/template-parts/footer.php'; ?>

</div><!--/ .wrap -->
