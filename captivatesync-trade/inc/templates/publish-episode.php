<?php
/**
 * Template page for publish/edit episode
 */

$show_id 		= cfm_get_show_id();
$post_id 		= isset( $_GET['eid'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['eid'] ) ) : 0;
$cfm_episode_id = get_post_meta( $post_id, 'cfm_episode_id', true );
$is_edit 		= 0 != $post_id ? true : false;
$post_status 	= get_post_status( $post_id );
$user_shows 	= get_user_meta( get_current_user_id(), 'cfm_user_shows', true );
$response 		= isset( $_GET['response'] ) ? sanitize_text_field( wp_unslash( $_GET['response'] ) ) : 0;

if ( ! cfm_is_show_exists( $show_id ) ) {
	wp_die( '<p>Show does not exists.</p>', '', array('link_url' => esc_url(admin_url()), 'link_text' => 'Return to Dashboard'));
}

if ( $is_edit && ( 'trash' == $post_status || false === $post_status ) ) {
	wp_die( '<p>Episode does not exists.</p>', '', array('link_url' => esc_url(admin_url()), 'link_text' => 'Return to Dashboard'));
}

if ( ! current_user_can( 'manage_options' ) && (  empty( $user_shows ) || ( ! empty( $user_shows ) && ! in_array( $show_id, $user_shows ) ) ) ) {
	wp_die( '<p>Sorry, you are not allowed to access this page.</p>', '', array( 'link_url' => esc_url(admin_url()), 'link_text' => 'Return to Dashboard' ) );
}
?>

<div class="wrap cfmh cfm-hosting-publish-episode">

	<?php
	$artwork_id      = get_post_meta( $post_id, 'cfm_episode_artwork_id', true );
	$artwork_url     = get_post_meta( $post_id, 'cfm_episode_artwork', true );
	$featured_image  = get_the_post_thumbnail_url( $post_id, 'medium' );

	$artwork_width   	 = get_post_meta( $post_id, 'cfm_episode_artwork_width', true );
	$artwork_height  	 = get_post_meta( $post_id, 'cfm_episode_artwork_height', true );
	$artwork_type    	 = get_post_meta( $post_id, 'cfm_episode_artwork_type', true );
	$artwork_filesize    = get_post_meta( $post_id, 'cfm_episode_artwork_filesize', true );

	$post_title      = get_the_title( $post_id );
	$post_name       = get_post_field( 'post_name', $post_id );
	$post_author     = get_post_field( 'post_author', $post_id );
	$post_excerpt 	 = get_post_field( 'post_excerpt', $post_id );
	$comment_status	 = get_post_field( 'comment_status', $post_id );
	$ping_status 	 = get_post_field( 'ping_status', $post_id );
	$wp_editor 	 	 = get_post_meta( $post_id, 'cfm_enable_wordpress_editor', true );
	$custom_field 	 = get_post_meta( $post_id, 'cfm_episode_custom_field', true );
	$itunes_title    = get_post_meta( $post_id, 'cfm_episode_itunes_title', true );
	$itunes_season   = get_post_meta( $post_id, 'cfm_episode_itunes_season', true );
	$itunes_number   = get_post_meta( $post_id, 'cfm_episode_itunes_number', true );
	$itunes_type     = get_post_meta( $post_id, 'cfm_episode_itunes_type', true );
	$itunes_explicit = get_post_meta( $post_id, 'cfm_episode_itunes_explicit', true );
	$donation_link   = get_post_meta( $post_id, 'cfm_episode_donation_link', true );
	$donation_label  = get_post_meta( $post_id, 'cfm_episode_donation_label', true );
	$seo_title       = get_post_meta( $post_id, 'cfm_episode_seo_title', true );
	$seo_description = get_post_meta( $post_id, 'cfm_episode_seo_description', true );

	$media_created_at       = get_post_meta( $post_id, 'cfm_episode_media_created_at', true );
	$media_id        		= get_post_meta( $post_id, 'cfm_episode_media_id', true );
	$media_bit_rate        	= get_post_meta( $post_id, 'cfm_episode_media_bit_rate', true );
	$media_bit_rate_str     = get_post_meta( $post_id, 'cfm_episode_media_bit_rate_str', true );
	$media_duration        	= get_post_meta( $post_id, 'cfm_episode_media_duration', true );
	$media_duration_str     = get_post_meta( $post_id, 'cfm_episode_media_duration_str', true );
	$media_id3_size        	= get_post_meta( $post_id, 'cfm_episode_media_id3_size', true );
	$media_name        		= get_post_meta( $post_id, 'cfm_episode_media_name', true );
	$media_size        		= get_post_meta( $post_id, 'cfm_episode_media_size', true );
	$media_type      		= get_post_meta( $post_id, 'cfm_episode_media_type', true );
	$media_url       		= get_post_meta( $post_id, 'cfm_episode_media_url', true );
	$media_shows_id        	= get_post_meta( $post_id, 'cfm_episode_media_shows_id', true );
	$media_updated_at       = get_post_meta( $post_id, 'cfm_episode_media_updated_at', true );
	$media_users_id        	= get_post_meta( $post_id, 'cfm_episode_media_users_id', true );

	$image_id 				= get_post_meta( $post_id, '_thumbnail_id', true );
	$seo_description_width  = $seo_description ? (strlen($seo_description) / 155 * 100) : 0;
	$seo_description_width  = $seo_description_width >= 100 ? 100 : $seo_description_width;
	$seo_description_color  = "orange";

	if ( $seo_description_width >= 50 && $seo_description_width <= 99 ) {
		$seo_description_color = "#29ab57";
	} else if ( $seo_description_width >= 100 ) {
		$seo_description_color = "#dc3545";
	}

	$transcript 	= get_post_meta( $post_id, 'cfm_episode_transcript', true);
	$is_transcript  = is_array( $transcript ) && ( ( null != $transcript['transcription_file'] && '' != $transcript['transcription_file'] ) || ( null != $transcript['transcription_text'] && '' != $transcript['transcription_text'] ) ) ? true : false;
	$permalink = ( 'future' == $post_status || 'publish' == $post_status ) ? get_permalink( $post_id ) : get_bloginfo( 'url' ) . '/?post_type=captivate_podcast&p=' . $post_id . '&preview=true';

	$bookings = cfm_get_episode_bookings( $post_id );
	$research_links = cfm_get_episode_research_links( $show_id, $post_id );

	$episode_duplicate = get_post_meta( $post_id, 'cfm_episode_duplicate', true );
	$show_timezone = cfm_get_show_info( $show_id, 'time_zone' );
	$episode_status = get_post_meta( $post_id, 'cfm_episode_status', true );
	$episode_website_active = get_post_meta( $post_id, 'cfm_episode_website_active', true );

	$exclusivity_date = get_post_meta( $post_id, 'cfm_episode_exclusivity_date', true );

	$youtube_video_id = get_post_meta( $post_id, 'cfm_episode_youtube_video_id', true );
	$youtube_video_title = get_post_meta( $post_id, 'cfm_episode_youtube_video_title', true );

	$acf_option_field_value = get_post_meta( $post_id, 'acf_option_field_value', true );
	$acf_option_field_label = get_post_meta( $post_id, 'acf_option_field_label', true );
	$acf_option_field_group_label = get_post_meta( $post_id, 'acf_option_field_group_label', true );

	$social_media_image_id = get_post_meta( $post_id, 'cfm_episode_social_media_image_id', true );
	$social_media_image_url = get_post_meta( $post_id, 'cfm_episode_social_media_image_url', true );
	$social_media_title = get_post_meta( $post_id, 'cfm_episode_social_media_title', true );
	$social_media_description = get_post_meta( $post_id, 'cfm_episode_social_media_description', true );

	$x_image_id = get_post_meta( $post_id, 'cfm_episode_x_image_id', true );
	$x_image_url = get_post_meta( $post_id, 'cfm_episode_x_image_url', true );
	$x_title = get_post_meta( $post_id, 'cfm_episode_x_title', true );
	$x_description = get_post_meta( $post_id, 'cfm_episode_x_description', true );
	?>

	<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>

	<div class="cfm-page-content">

		<div class="sub-title"><h2><?php echo $is_edit ? "Edit Episode" : "Publish New Episode" ?></h2></div>

		<form id="cfm-form-publish-episode" name="cfm-form-publish-episode" enctype="multipart/form-data" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

			<?php wp_nonce_field( '_sec_action_' . $post_id, '_sec' ); ?>

			<input type="hidden" name="action" value="form_publish_episode">
			<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
			<input type="hidden" name="show_id" value="<?php echo esc_attr( $show_id ); ?>">
			<input type="hidden" name="submit_action" value="draft">

			<input type="hidden" name="media_created_at" value="<?php echo esc_attr( $media_created_at ); ?>">
			<input type="hidden" name="media_id" value="<?php echo esc_attr( $media_id ); ?>">
			<input type="hidden" name="media_bit_rate" value="<?php echo esc_attr( $media_bit_rate ); ?>">
			<input type="hidden" name="media_bit_rate_str" value="<?php echo esc_attr( $media_bit_rate_str ); ?>">
			<input type="hidden" name="media_duration" value="<?php echo esc_attr( $media_duration ); ?>">
			<input type="hidden" name="media_duration_str" value="<?php echo esc_attr( $media_duration_str ); ?>">
			<input type="hidden" name="media_id3_size" value="<?php echo esc_attr( $media_id3_size ); ?>">
			<input type="hidden" name="media_name" value="<?php echo esc_attr( $media_name ); ?>">
			<input type="hidden" name="media_size" value="<?php echo esc_attr( $media_size ); ?>">
			<input type="hidden" name="media_type" value="<?php echo esc_attr( $media_type ); ?>">
			<input type="hidden" name="media_url" value="<?php echo esc_attr( $media_url ); ?>">
			<input type="hidden" name="media_shows_id" value="<?php echo esc_attr( $media_shows_id ); ?>">
			<input type="hidden" name="media_updated_at" value="<?php echo esc_attr( $media_updated_at ); ?>">
			<input type="hidden" name="media_users_id" value="<?php echo esc_attr( $media_users_id ); ?>">

			<?php
			if ( 1 == $response ) {
				echo '<div class="cfm-alert cfm-alert-error mb-4"><span class="alert-icon"></span> <span class="alert-text">Please fill in the required fields.</span></div>';}

			if ( 2 == $response ) {
				echo '<div class="cfm-alert cfm-alert-success mb-4"><span class="alert-icon"></span> <span class="alert-text">Episode created and synchronized to your Captivate account, too. <a class="text-decoration-none" href="' . esc_url( $permalink ).'" target="_blank">View Episode</a></span></div>';}

			if ( 3 == $response ) {
				echo '<div class="cfm-alert cfm-alert-success mb-4"><span class="alert-icon"></span> <span class="alert-text">Episode updated and synchronized to your Captivate account, too. <a class="text-decoration-none" href="' . esc_url( $permalink ).'" target="_blank">View Episode</a></span></div>';}

			if ( 4 == $response ) {
				echo '<div class="cfm-alert cfm-alert-error mb-4"><span class="alert-icon"></span> <span class="alert-text">You haven\'t got the right access to this show.</span></div>';}

			if ( 5 == $response ) {
				echo '<div class="cfm-alert cfm-alert-error mb-4"><span class="alert-icon"></span> <span class="alert-text">There\'s no selected show.</span></div>';}

			if ( 6 == $response ) {
				echo '<div class="cfm-alert cfm-alert-error mb-4"><span class="alert-icon"></span> <span class="alert-text">API error code 12, please contact support.</span></div>';}

			if ( $is_edit && ! $cfm_episode_id && '1' != $episode_duplicate ) {
				echo '<div class="cfm-alert cfm-alert-warning mb-4"><span class="alert-icon"></span> <span class="alert-text">This episode has not been published on Captivate yet due to API error code 12. Please try saving the episode again to resolve the issue.</span></div>';}

			if ( $is_edit && ! $cfm_episode_id && '1' == $episode_duplicate ) {
				echo '<div class="cfm-alert cfm-alert-warning mb-4"><span class="alert-icon"></span> <span class="alert-text">This episode has not been published on Captivate yet. Please update and sync it accordingly.</span></div>';}

			if ( $is_edit && '0' == $episode_website_active ) {
				echo '<div class="cfm-alert cfm-alert-warning mb-4"><span class="alert-icon"></span> <span class="alert-text">This episode is marked as inactive and will not appear on the website or in search results until it is reactivated.</span></div>';}
			?>

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Upload Your File</strong></div></div>
				<div class="col-lg-9">
					<div id="cfm-audio-uploader" class="cfm-dropzone">
						<div class="dropzone-result">
							<?php if ( $is_edit && $media_url ) : ?>
								<audio controls="controls" preload="none"><source type="audio/mpeg" src="<?php echo esc_attr( $media_url ); ?>"> Your browser does not support the audio element. </audio>
								<div class="dropzone-result-info d-flex justify-content-between">
									<div class="result-info">
										<?php if ( $media_name && $media_bit_rate_str && $media_duration_str ) : ?>
											<strong><?php echo esc_html( $media_name ) ; ?></strong> <br><?php echo esc_html( $media_bit_rate_str ); ?> | <?php echo esc_html( $media_duration_str ); ?>
										<?php else : ?>
											<strong><?php echo esc_html( basename( $media_url ) ) ; ?></strong>
										<?php endif; ?>
									</div>
									<div class="result-actions"><button type="button" class="replace-audio btn btn-outline-dark">Replace audio file</button></div>
								</div>
							<?php endif; ?>
						</div>

						<div class="dropzone-preloader">
							<div class="dropzone-progress"><div class="progress-bar"></div></div>
							<div class="dropzone-progress-info d-flex justify-content-between">
								<div class="progress-info"></div>
								<div class="progress-actions"><a class="cancel-upload text-decoration-none">Cancel upload</a></div>
							</div>
						</div>

						<div class="dropzone-uploader"<?php echo ( ! $is_edit || ( $is_edit && ! $media_url ) ) ? ' style="display: block";' : ''; ?>>
							<div id="podcast-dropzone" class="dropzone podcast-dropzone">
								<div class="fallback hidden"><input name="file" type="file" /></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Episode Details</strong></div></div>
				<div class="col-lg-9">

					<!-- Episode Title -->
					<div class="cfm-field cfm-episode-title">
						<label for="post_title">Episode Title</label>
						<input type="text" class="form-control<?php echo ( '' == $post_title ) ? ' post-title-empty' : ''; ?>" id="post_title" name="post_title" value="<?php echo esc_attr( $post_title ); ?>" placeholder="Your catchy episode title">
					</div>

					<!-- iTunes Title -->
					<div class="cfm-field cfm-itunes-title-check mt-4">
						<div class="form-check">
							<input class="form-check-input" type="checkbox" id="post_title_check" name="post_title_check" value="" <?php echo ( $is_edit && '' != $itunes_title ) ? 'checked="checked"' : ''; ?>>
							<label class="form-check-label" for="post_title_check">Display a different episode title on Apple Podcasts?</label>
						</div>
					</div>

					<div class="cfm-field cfm-itunes-title mt-2<?php echo ( $is_edit && '' != $itunes_title ) ? '' : ' hidden'; ?>">
						<input type="text" class="form-control" id="itunes_title" name="itunes_title" value="<?php echo esc_attr( $itunes_title ); ?>">
					</div>

					<!-- Episode Show Notes -->
					<div class="cfm-field cfm-episode-shownotes mt-4">

						<div class="row align-items-center">
							<div class="col-sm-4">
								<label for="post_content">Episode Show Notes</label>
							</div>

							<div class="col-sm-8 justify-content-end">
								<?php
								$shownotes_templates = cfm_get_dynamic_text( $show_id, array( 'shownotes_template' ), array( 'all' ) );

								if ( is_array( $shownotes_templates ) && ! empty( $shownotes_templates ) ) :
									?>
									<div id="cfm-dropdown-dt-templates" class="cfm-dropdown-menu dropdown-dt-templates mb-2 ms-4 float-lg-end">
										<button type="button" class="btn btn-outline-primary btn-md dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Show Notes Templates</button>
										<div class="dropdown-menu">
											<div class="dropdown-search"><i class="fal fa-search"></i><input type="search" class="form-control search" placeholder="Search Show Notes Templates"></div>
											<div class="dropdown-contents">
												<?php
												foreach ( $shownotes_templates as $template ) {
													echo '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Change Show Notes Template" data-confirmation-content="Changing to a different template will mean you\'ll lose your current content and it will reset." data-confirmation-button="cfm-change-shownotes-template" data-confirmation-button-text="Change Template" data-confirmation-reference="' . esc_attr( $template['name'] ) . '">' . esc_html( $template['name_human'] ) . '</a>';
												}
												?>
											</div>
										</div>
									</div>
								<?php endif; ?>

								<div class="form-check mb-2 mt-2 float-lg-end">
									<input type="checkbox" id="enable_wordpress_editor" name="enable_wordpress_editor" class="form-check-input" <?php echo $wp_editor == 'on' ? 'checked' : ''; ?>>
									<label class="form-check-label" for="enable_wordpress_editor">Use WordPress Editor</label>
								</div>
							</div>
						</div>

						<?php
						$content = '';
						if ( $is_edit ) {
							$post    = get_post( $post_id, OBJECT, 'edit' );
							$content = $post->post_content;
						}
						?>

						<div class="cfm-shownotes-editor">

							<div class="cfm-captivate-editor<?php echo $wp_editor == 'on' ? ' hidden' : ''; ?>">

								<?php require CFMH . 'inc/templates/template-parts/ql-toolbar.php'; ?>

								<div id="cfm-field-wpeditor"><?php echo $content; ?></div>

								<textarea name="post_content" id="post_content" class="hidden" data-gramm="false"><?php echo $content; ?></textarea>

								<small class="mt-2 text-end">
									<a class="expand text-decoration-none">Expand Writing Area <i class="fa-regular ms-1 fa-expand"></i></a>
								</small>

							</div>

							<div class="cfm-wordpress-editor<?php echo $wp_editor != 'on' ? ' hidden' : ''; ?>">
								<?php
								$settings = array( 'editor_height' => 250 );
								$editor_id = 'post_content_wp';
								wp_editor( $content, $editor_id, $settings );
								?>
							</div>

						</div>

					</div>

					<!-- Episode Number and Season Number -->
					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field cfm-episode-number">
								<label for="episode_number">Episode Number</label>
								<input type="number" id="episode_number" name="episode_number" min="0" value="<?php echo esc_attr( $itunes_number ); ?>" class="form-control" placeholder="0">
							</div>
						</div>

						<div class="col-lg-6">
							<div class="cfm-field cfm-season-number">
								<label for="season_number">Season Number</label>
								<input type="number" id="season_number" name="season_number" min="0" value="<?php echo esc_attr( $itunes_season ); ?>" class="form-control" placeholder="0">
							</div>
						</div>
					</div>

					<!-- Episode Type -->
					<div class="cfm-field cfm-episode-type mt-4">
						<label>Episode Type</label>

						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="episode_normal" name="episode_type" class="form-check-input" value="full" <?php checked( $itunes_type, 'full' ); ?> <?php echo ( ! $is_edit ) ? 'checked="checked"' : ''; ?>>
								<label class="form-check-label" for="episode_normal">Normal</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="episode_trailer" name="episode_type" class="form-check-input" value="trailer" <?php checked( $itunes_type, 'trailer' ); ?>>
								<label class="form-check-label" for="episode_trailer">Trailer</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="episode_bonus" name="episode_type" class="form-check-input" value="bonus" <?php checked( $itunes_type, 'bonus' ); ?>>
								<label class="form-check-label" for="episode_bonus">Bonus</label>
							</div>
						</div>

						<small>Usually your normal show type but you can also publish a trailer or bonus episode that, depending on the player, will display differently.</small>
					</div>

					<!-- Episode Explicit -->
					<div class="cfm-field cfm-episode-explicit mt-4">
						<label for="episode_explicit">Mark as Explicit?</label>

						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="explicit_default" name="episode_explicit" class="form-check-input" data-explicit-default="<?php echo esc_attr( cfm_get_show_info( $show_id, 'explicit' ) ); ?>" value="default" <?php checked( $itunes_explicit, 'default' ); ?> <?php echo ( ! $is_edit ) ? 'checked="checked"' : ''; ?>>
								<label class="form-check-label" for="explicit_default">Use show default</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="explicit_yes" name="episode_explicit" class="form-check-input" value="explicit" <?php checked( $itunes_explicit, 'explicit' ); ?>>
								<label class="form-check-label" for="explicit_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="explicit_no" name="episode_explicit" class="form-check-input" value="clean" <?php checked( $itunes_explicit, 'clean' ); ?>>
								<label class="form-check-label" for="explicit_no">No</label>
							</div>
						</div>

						<small>If many of your episodes contain explicit language, set this to 'yes' in show settings. It's vital that you make sure this is right!</small>
					</div>

					<!-- Transcription -->
					<div class="cfm-field cfm-episode-transcription mt-4">
						<label>Transcription</label>

						<div class="cmf-transcript-wrap">
							<?php
							$transcript_content = '';
							if ( $is_transcript ) {
								$transcript_content = '';
								if ( 'file' == $transcript['transcription_uploaded'] ) {
									$transcript_content = basename( $transcript['transcription_file'] );
								}
								else {
									$transcript_content = $transcript['transcription_text'];
								}

								echo '<strong>' . esc_html( cfm_limit_characters( $transcript_content, $limit = 20, $readmore = '...' ) ) . '</strong> <a id="transcript-edit" class="float-end" data-bs-toggle="modal" data-bs-target="#transcript-modal" href="#"><i class="fal fa-edit"></i> Edit</a><div class="mt-2"><a id="transcript-remove" class="transcript-remove text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a></div>';
							}
							else {
								echo '<a id="transcript-add" data-bs-toggle="modal" data-bs-target="#transcript-modal" href="#"><i class="fal fa-file-alt me-2"></i> Add a transcript to this episode </a>';
							}
							?>
						</div>

						<textarea name="transcript_current" id="transcript_current" class="hidden"><?php echo esc_textarea( $transcript_content ); ?></textarea>
						<input type="hidden" name="transcript_type" id="transcript_type" value="<?php echo $is_transcript ? esc_attr( $transcript['transcription_uploaded'] ) : 'text'; ?>" />
						<input type="hidden" name="transcript_updated" id="transcript_updated" value="0" />

						<!-- Transcription Modal -->
						<div class="modal fade modal-slideout" id="transcript-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
							<div class="modal-dialog modal-dialog-slideout" role="document">
								<div class="modal-content">
									<div class="offcanvas-header flex-column align-items-end mb-4">
										<button type="button" id="close-transcript" aria-label="Close" data-bs-dismiss="modal" class="close-btn"> Close <i class="fas fa-arrow-right"></i></button>
									</div>

									<div class="modal-header">
										<h4 class="modal-title">Transcription</h4>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">
										<div class="mb-4 fw-light"><strong>Tip:</strong> make sure you follow the sample format below, otherwise your transcription may not appear properly in podcast apps that support this feature.</div>

										<textarea name="transcript_text" id="transcript_text" rows="14" placeholder="Alfred 00:00&#10;Will you be wanting the Batpod, sir?&#10;&#10;Bruce 00:20&#10;In the middle of the day, Alfred? Not very subtle.&#10;&#10;Alfred 00:30&#10;The Lamborghini, then." class="form-control"<?php echo ( $is_transcript && 'file' == $transcript['transcription_uploaded'] ) ? ' disabled="disabled"' : ''; ?>><?php echo ( $is_transcript && 'text' == $transcript['transcription_uploaded'] ) ? esc_textarea( $transcript['transcription_text'] ) : ''; ?></textarea>

										<div class="transcript-upload-box<?php echo ( $is_transcript && 'text' == $transcript['transcription_uploaded'] ) ? ' disabled' : ''; ?>">
											<?php
											if ( $is_transcript && 'file' == $transcript['transcription_uploaded'] ) {
												echo '<div class="transcript-text">File uploaded: <strong>' . basename( $transcript['transcription_file'] ) . '</strong></div><a id="remove-transcript-file" class="text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a>';
											}
											else {
												echo '<div class="transcript-text">Have a transcript file? Upload it directly... </div><a id="upload-transcript" href="javascript: void(0);"><i class="fal fa-cloud-upload" aria-hidden="true"></i> Upload File</a>';
											}
											?>
										</div>
										<input class="hidden" name="transcript_file" id="transcript_file" type="file" onclick="this.value=null;" accept=".srt" />
									</div>
									<div class="modal-footer">
										<button type="button" id="cancel-transcript" class="btn btn-outline-primary me-auto" data-bs-dismiss="modal">Cancel</button>
										<button type="button" id="update-transcript" class="btn btn-primary" disabled="disabled">Update Transcript</button>
									</div>
								</div>
							</div>
						</div>
						<!-- /Transcription Modal -->
					</div>

				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<?php if ( $is_edit ) : ?>

				<div class="row">
					<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Guest Bookings</strong></div></div>
					<div class="col-lg-9">
						<?php
						if ( is_array( $bookings ) && ! empty( $bookings ) ) {

							echo '<label class="mb-3">Guest bookings assigned to this episode.</label>';

							echo '<div class="cfm-datatable-list"><div class="row"><div class="col-12">';

								echo '<div class="datatable-head d-none d-sm-block"><div class="datatable-row">';
									echo '<div class="datatable-cell datatable-guest-name">Guest Name</div>';
									echo '<div class="datatable-cell datatable-booking">Booking</div>';
								echo '</div></div>';

								echo '<div class="datatable-body">';
									foreach ( $bookings as $b ) {
										$g_name = $b->guest_first_name . ' ' . $b->guest_last_name;
										$g_booking = new DateTime( $b->start) ;
										$g_booking->setTimezone( new DateTimeZone( $show_timezone ) );
										$g_booking = $g_booking->format('F j, Y \a\t g:i A T');

										echo '<div class="datatable-row"><div class="datatable-row-data d-block d-sm-flex">';
											echo '<div class="datatable-cell datatable-guest-name">' . esc_html( $g_name ) . '</div>';
											echo '<div class="datatable-cell datatable-booking">' . esc_html( $g_booking ) . '</div>';
										echo '</div></div>';
									}
								echo '</div>';

							echo '</div></div></div>';
						}
						else {
							echo '<label class="mb-3">No guest bookings assigned to this episode.</label>';
						}
						?>
					</div>
				</div>

				<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

				<div class="row">
					<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Research Links</strong></div></div>
					<div class="col-lg-9">
						<?php
						if ( ! empty( $research_links ) ) {

							echo '<label class="mb-3">Research links assigned to this episode.</label>';

							echo '<div class="cfm-datatable-list"><div class="row"><div class="col-12">';

								echo '<div class="datatable-head d-none d-sm-block"><div class="datatable-row">';
									echo '<div class="datatable-cell datatable-rl-label">Label</div>';
									echo '<div class="datatable-cell datatable-rl-url">URL</div>';
								echo '</div></div>';

								echo '<div class="datatable-body">';
									foreach ( $research_links as $link ) {
										echo '<div class="datatable-row"><div class="datatable-row-data d-block d-sm-flex">';
											echo '<div class="datatable-cell datatable-rl-label">' . esc_html( $link['title'] ) . '</div>';
											echo '<div class="datatable-cell rl-url">' . esc_html( $link['url'] ) . '</div>';
										echo '</div></div>';
									}
								echo '</div>';

							echo '</div></div></div>';
						}
						else {
							echo '<label class="mb-3">No research links assigned to this episode.</label>';
						}
						?>
					</div>
				</div>

				<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<?php endif; ?>

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Episode Artwork (optional)</strong></div></div>
				<div class="col-lg-9">
					<label class="mb-3">Upload an episode specific image to replace your podcast’s usual cover art.</label>

					<div id="cfm-artwork-uploader" class="cfm-dropzone fake-dropzone">
						<div class="fd-wrap">
							<div class="fd-col-image">
								<div class="fd-result">
									<?php if ( $is_edit && $artwork_url ) : ?>
										<img src="<?php echo esc_attr( $artwork_url . '?width=400&height=400' ); ?>" width="200" height="200" class="img-fluid">
									<?php endif; ?>

									<?php if ( ! $is_edit || ( $is_edit && ! $artwork_url ) ) : ?>
										<i class="fal fa-image"></i>
									<?php endif; ?>
								</div>
							</div>

							<div class="fd-col-browse">
								<div class="fd-uploader"<?php echo ( ! $is_edit || ( $is_edit && ! $artwork_url ) ) ? ' style="display: block";' : ''; ?>>
									<div id="artwork-dropzone" class="dropzone artwork-dropzone">
										<div class="dz-default">
											<i class="fal fa-image"></i>
											<strong>Browse media library</strong>
										</div>
									</div>
								</div>

								<div class="fd-replace"<?php echo ( $is_edit && $artwork_url ) ? ' style="display: block";' : ''; ?>>
									<button type="button" class="btn btn-primary mb-md-4 d-md-block mr-3 mr-md-0 upload-new-image">Upload New Image</button>
									<button type="button" class="btn btn-outline-primary remove-image">Remove Image</button>
								</div>

								<input type="hidden" name="episode_artwork" id="episode_artwork" value="<?php echo esc_attr( $artwork_url ); ?>" />
								<input type="hidden" name="episode_artwork_id" id="episode_artwork_id" value="<?php echo esc_attr( $artwork_id ); ?>" />
								<input type="hidden" name="episode_artwork_width" id="episode_artwork_width" value="<?php echo esc_attr( $artwork_width ); ?>" />
								<input type="hidden" name="episode_artwork_height" id="episode_artwork_height" value="<?php echo esc_attr( $artwork_height ); ?>" />
								<input type="hidden" name="episode_artwork_type" id="episode_artwork_type" value="<?php echo esc_attr( $artwork_type ); ?>" />
								<input type="hidden" name="episode_artwork_filesize" id="episode_artwork_filesize" value="<?php echo esc_attr( $artwork_filesize ); ?>" />
							</div>
						</div>
					</div>

					<small class="d-block pt-3">Your artwork should be 3000px x 3000px, PNG or JPEG, and under 2MB in size. Please <a class="text-decoration-none" href="https://help.captivate.fm/en/articles/3315645-podcast-artwork-specifications" target="_blank">check out our help article for more details.</a></small>

					<small class="d-block pt-3"><strong>Important:</strong> WordPress may automatically scale large images down to 2,560 pixels. To preserve the full 3,000 x 3,000 px image, ensure that large image scaling is disabled in WordPress.</small>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<?php if ( ! empty($youtube_video_id) ) : ?>
				<div class="row">
					<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Episode Video</strong></div></div>
					<div class="col-lg-9">
						<?php if ( ! empty($youtube_video_title) ) : ?>
							<label class="mb-3"><?php echo esc_attr($youtube_video_title); ?></label>
						<?php endif; ?>

						<?php if ( ! empty($youtube_video_id) ) :
							$youtube_url = 'https://youtu.be/' . $youtube_video_id;
							?>
							<div class="cfm-field cfm-episode-video">
								<a href="<?php echo esc_url($youtube_url); ?>" target="_blank"><?php echo esc_html($youtube_url); ?></a>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">
			<?php endif; ?>

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Episode SEO</strong></div></div>
				<div class="col-lg-9">

					<!-- SEO Title -->
					<div class="cfm-field cfm-seo-title">
						<label for="seo_title">SEO Title</label>
						<input type="text" class="form-control" id="seo_title" name="seo_title" value="<?php echo esc_attr( $seo_title ); ?>" placeholder="SEO friendly title">

						<small>The title shown in search engine results and social shares (defaults to your episode title if empty).</small>
					</div>

					<!-- SEO Description -->
					<div class="cfm-field cfm-seo-description mt-4">
						<label for="seo_description">SEO Description</label>

						<textarea class="form-control" id="seo_description" name="seo_description" rows="4" placeholder="Short, to the point SEO friendly description"><?php echo esc_textarea( $seo_description ); ?></textarea>

						<div class="cfm-seo-description-count">
							<div class="cfm-seo-description-progress" style="width: <?php echo $seo_description_width; ?>%; background: <?php echo $seo_description_color; ?>"></div>
						</div>

						<small>The description shown in search engine results and social shares.</small>
					</div>

					<div class="cfm-field cfm-episode-slug mt-4">
						<label for="post_name">Episode Slug</label>

						<div class="input-group">
							<span class="input-group-text"><?php echo esc_url( get_bloginfo( 'url' ) . '/' . cfm_get_show_page( $show_id , 'slug' ) . '/' ); ?></span>
							<input type="text" class="form-control" id="post_name" name="post_name" value="<?php echo esc_attr( $post_name ); ?>">
						</div>

						<small>The URL of your episode, you can change it here for SEO purposes or leave it set to what we generated from your episode title.</small>
					</div>

				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Website Details</strong></div></div>
				<div class="col-lg-9">
					<label class="mb-3">Upload a featured image for your website.</label>

					<div id="cfm-featured-image-uploader" class="cfm-dropzone fake-dropzone cfm-image-uploader" data-uploader-title="Select Website Featured Image">
						<div class="fd-wrap">
							<div class="fd-col-image">
								<div class="fd-result">
									<?php if ( $is_edit && $featured_image ) : ?>
										<img src="<?php echo esc_attr( $featured_image ); ?>" width="200" height="200" class="img-fluid">
									<?php endif; ?>

									<?php if ( ! $is_edit || ( $is_edit && ! $featured_image ) ) : ?>
										<i class="fal fa-image"></i>
									<?php endif; ?>
								</div>
							</div>

							<div class="fd-col-browse">
								<div class="fd-uploader"<?php echo ( ! $is_edit || ( $is_edit && ! $featured_image ) ) ? ' style="display: block";' : ''; ?>>
									<div class="dropzone featured-image-dropzone">
										<div class="dz-default">
											<i class="fal fa-image"></i>
											<strong>Browse media library</strong>
										</div>
									</div>
								</div>

								<div class="fd-replace"<?php echo ( $is_edit && $featured_image ) ? ' style="display: block";' : ''; ?>>
									<button type="button" class="btn btn-primary mb-md-4 d-md-block mr-3 mr-md-0 upload-new-image">Upload New Image</button>
									<button type="button" class="btn btn-outline-primary remove-image">Remove Image</button>
								</div>

								<input type="hidden" name="featured_image_id" class="fd-input-image-id" value="<?php echo esc_attr( $image_id ); ?>" />
								<input type="hidden" name="featured_image_url" class="fd-input-image-url" value="<?php echo esc_attr( $featured_image ); ?>" />
							</div>
						</div>
					</div>

					<?php if ( current_user_can( 'edit_others_posts' ) ) : ?>
						<div class="cfm-field cfm-website-author mt-4">
							<label for="post_author">Author</label>
							<?php
							$author_id = ( $is_edit ) ? (int) $post_author : cfm_get_show_author( $show_id );

							wp_dropdown_users( array(
								'name'	 				=> 'post_author',
								'class' 				=> 'form-control',
								'show'   				=> 'display_name_with_login',
								'selected' 				=> $author_id,
								'include_selected' 		=> true
							) );
							?>
						</div>
					<?php endif; ?>

					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field cfm-field-list-check cfm-website-categories">
								<label for="website_category">Categories</label>

								<div class="cfm-website-categories-wrap form-control">
									<?php
									$cat_post_id = $post_id;
									$args        = array(
										'descendants_and_self'  => 0,
										'selected_cats'         => false,
										'popular_cats'          => false,
										'walker'                => null,
										'taxonomy'              => 'captivate_category',
										'checked_ontop'         => false,
									);
									echo '<ul>';
										wp_terms_checklist( $cat_post_id, $args );
									echo '</ul>';
									?>
								</div>

								<div class="row mt-2">
									<div class="col-lg-6">
										<div class="cfm-category-parent mb-2">
											<?php
											$args = array(
												'show_option_all' => '',
												'show_option_none' => 'Parent Category',
												'option_none_value' => '-1',
												'orderby' => 'name',
												'order' => 'ASC',
												'show_count' => 0,
												'hide_empty' => 0,
												'child_of' => 0,
												'exclude' => '',
												'include' => '',
												'echo' => 1,
												'selected' => 0,
												'hierarchical' => 1,
												'name' => 'category_parent',
												'id'   => '',
												'class' => 'form-control',
												'depth' => 0,
												'tab_index' => 0,
												'taxonomy' => 'captivate_category',
												'hide_if_empty' => false,
												'value_field' => 'term_id',
											);
											wp_dropdown_categories( $args );
											?>
										</div>
									</div>

									<div class="col-lg-6">
										<div class="input-group">
											<input type="text" class="form-control" id="website_category" name="website_category" placeholder="New category">
											<button type="button" id="add-website-category" class="btn btn-outline-secondary input-group-button"><i class="fal fa-plus"></i></button>
										</div>
									</div>
								</div>

							</div>
						</div>

						<div class="col-lg-6">
							<div class="cfm-field cfm-field-list-check cfm-website-tags">
								<label for="website_tags">Tags</label>
								<div class="cfm-website-tags-wrap form-control">
									<?php
									$tag_post_id = $post_id;
									$args        = array(
										'descendants_and_self'  => 0,
										'selected_cats'         => false,
										'popular_cats'          => false,
										'walker'                => null,
										'taxonomy'              => 'captivate_tag',
										'checked_ontop'         => true,
									);
									echo '<ul>';
										wp_terms_checklist( $tag_post_id, $args );
									echo '</ul>';
									?>
								</div>

								<div class="input-group mt-2">
									<input type="text" class="form-control" id="website_tags" name="website_tags" placeholder="Separate tags with commas">
									<button type="button" id="add-website-tags" class="btn btn-outline-secondary input-group-button"><i class="fal fa-plus"></i></button>
								</div>
							</div>
						</div>
					</div>

					<?php
					/** CUSTOM TAXONOMIES */
					$tax_exclude = [ 'captivate_category', 'captivate_tag' ];
					$taxonomies = get_object_taxonomies( 'captivate_podcast', 'objects' );

					// Filter only taxonomies that have at least one term and are not excluded
					$custom_taxonomies = array_filter( $taxonomies, function( $taxonomy ) use ( $tax_exclude ) {
						if ( in_array( $taxonomy->name, $tax_exclude, true ) ) {
							return false;
						}

						$terms = get_terms([
							'taxonomy'   => $taxonomy->name,
							'hide_empty' => false,
						]);

						return ! empty( $terms );
					});

					// Only display the row if at least one taxonomy exists
					if ( ! empty( $custom_taxonomies ) ) :
					?>

					<div class="row">
						<?php
							foreach ( $custom_taxonomies as $taxonomy ) {
								// Skip excluded taxonomies
								if ( in_array( $taxonomy->name, [ 'captivate_tag', 'captivate_category' ], true ) ) {
									continue;
								}

								// Skip taxonomy with no terms
								if ( ! get_terms( [ 'taxonomy' => $taxonomy->name, 'hide_empty' => false ] ) ) {
									continue;
								}

								$is_hierarchical = $taxonomy->hierarchical;
								?>
								<div class="col-lg-6 mt-4">
									<div class="cfm-field cfm-field-list-check cfm-website-taxonomy-<?php echo esc_attr( $taxonomy->name ); ?>">

										<label><?php echo esc_html( $taxonomy->label ); ?> <i>(Taxonomy)</i></label>

										<?php if ( $is_hierarchical ) : ?>
											<div class="cfm-website-taxonomy-wrap form-control">
												<ul>
													<?php
													wp_terms_checklist( $post_id, [
														'taxonomy'      => $taxonomy->name,
														'checked_ontop' => false,
													] );
													?>
												</ul>
											</div>

										<?php else : ?>
											<div class="cfm-website-taxonomy-wrap form-control">
												<ul>
													<?php
													$terms     = get_terms([
														'taxonomy'   => $taxonomy->name,
														'hide_empty' => false,
													]);

													$assigned  = wp_get_object_terms( $post_id, $taxonomy->name, [ 'fields' => 'ids' ] );

													foreach ( $terms as $term ) :
														$checked = in_array( $term->term_id, $assigned ) ? 'checked' : '';
													?>
														<li>
															<label>
																<input type="checkbox"
																	name="tax_input[<?php echo esc_attr( $taxonomy->name ); ?>][]"
																	value="<?php echo esc_attr( $term->term_id ); ?>"
																	<?php echo $checked; ?>>
																<?php echo esc_html( $term->name ); ?>
															</label>
														</li>
													<?php endforeach; ?>
												</ul>
											</div>
										<?php endif; ?>
									</div>
								</div>
								<?php
							}
						?>
					</div>
					<?php endif; ?>

					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="cfm-field cfm-website-comments">
								<label>Allow comments</label>

								<?php $comment_status_check = $is_edit ? $comment_status : get_default_comment_status( 'post', 'comment' ); ?>

								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="website_comment_yes" name="website_comment" class="form-check-input" value="open" <?php checked( $comment_status_check, 'open' ); ?>>
										<label class="form-check-label" for="website_comment_yes">Yes</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="website_comment_no" name="website_comment" class="form-check-input" value="closed" <?php echo ( 'open' != $comment_status_check ) ? 'checked="checked"' : ''; ?>>
										<label class="form-check-label" for="website_comment_no">No</label>
									</div>
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="cfm-field cfm-website-pingbacks">
								<label>Allow pingbacks &amp; trackbacks</label>

								<?php $ping_status_check = $is_edit ? $ping_status : get_default_comment_status( 'post', 'pingback' ); ?>

								<div class="form-group">
									<div class="form-check form-check-inline">
										<input type="radio" id="website_ping_yes" name="website_ping" class="form-check-input" value="open" <?php checked( $ping_status_check, 'open' ); ?>>
										<label class="form-check-label" for="website_ping_yes">Yes</label>
									</div>
									<div class="form-check form-check-inline">
										<input type="radio" id="website_ping_no" name="website_ping" class="form-check-input" value="closed" <?php echo ( 'open' != $ping_status_check ) ? 'checked="checked"' : ''; ?>>
										<label class="form-check-label" for="website_ping_no">No</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="cfm-field cfm-website-excerpt mt-4">
						<label for="post_excerpt">Website Excerpt</label>

						<textarea class="form-control" id="post_excerpt" name="post_excerpt" rows="4" placeholder="Short description shown on your website"><?php echo esc_textarea( $post_excerpt ); ?></textarea>

						<small>Excerpts allow you to display short summaries of your show notes instead of the full text of each episode on your website.</small>
					</div>

					<?php if ( class_exists( 'PWFT' ) ) : ?>
						<div class="cfm-field cfm-website-custom-field mt-4">
							<label for="custom_field">Website Custom Field</label>

							<textarea class="form-control" id="custom_field" name="custom_field" rows="4"><?php echo esc_textarea( $custom_field ); ?></textarea>

							<small>Custom content for your website shown at the bottom of your episode show notes.</small>
						</div>
					<?php endif; ?>

					<?php
					if ( CFMH_Hosting_Publish_Episode::render_acf_field_groups('captivate_podcast', 'exists') ) :
						?>
						<div class="cfm-field cfm-website-acf mt-4">
							<label>Advanced Custom Fields</label>

							<div class="acf-fields-wrap">
								<a id="acf-fields" data-bs-toggle="modal" data-bs-target="#acf-modal" href="#" class="text-decoration-none"><i class="fal fa-cogs me-2"></i>Manage ACF Fields</a>
							</div>

							<small>ACF Field Groups created through the ACF plugin can be easily managed here. Customize your content with a variety of field types such as Text, Textarea, Select, Radio, WYSIWYG, Number, Range, Email, URL, and oEmbed to tailor your site's content precisely to your needs.</small>

							<!-- ACF Modal -->
							<div class="modal fade modal-slideout" id="acf-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
								<div class="modal-dialog modal-dialog-slideout" role="document">
									<div class="modal-content">
										<div class="offcanvas-header flex-column align-items-end mb-4">
											<button type="button" id="close-acf" aria-label="Close" data-bs-dismiss="modal" class="close-btn"> Close <i class="fas fa-arrow-right"></i></button>
										</div>

										<div class="modal-header">
											<h4 class="modal-title">Advanced Custom Fields</h4>
											<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
										</div>
										<div class="modal-body modal-body-acf">

											<div class="cfm-acf-options mb-2">
												<div class="row cfm-modal-field">
													<div class="col-sm-6 mb-2 mb-sm-0">
														<label class="mb-0 me-4">Display on Single Episode Pages?</label>
													</div>
													<div class="col-sm-6">
														<select name="acf_option_field_value">
															<option value="no" <?php selected($acf_option_field_value, 'no'); ?>>No</option>
															<option value="above" <?php selected($acf_option_field_value, 'above'); ?>>Above Content</option>
															<option value="below" <?php selected($acf_option_field_value, 'below'); ?>>Below Content</option>
														</select>
													</div>
												</div>
											</div>
											<div class="cfm-acf-options mb-2">
												<div class="row cfm-modal-field">
													<div class="col-sm-6 mb-2 mb-sm-0">
														<label class="mb-0 me-4">Display ACF field label?</label>
													</div>
													<div class="col-sm-6">
														<select name="acf_option_field_label">
															<option value="yes" <?php selected($acf_option_field_label, 'yes'); ?>>Yes</option>
															<option value="no" <?php selected($acf_option_field_label, 'no'); ?>>No</option>
														</select>
													</div>
												</div>
											</div>
											<div class="cfm-acf-options mb-4">
												<div class="row cfm-modal-field">
													<div class="col-sm-6 mb-2 mb-sm-0">
														<label class="mb-0 me-4">Display ACF field group label?</label>
													</div>
													<div class="col-sm-6">
														<select name="acf_option_field_group_label">
															<option value="yes" <?php selected($acf_option_field_group_label, 'yes'); ?>>Yes</option>
															<option value="no" <?php selected($acf_option_field_group_label, 'no'); ?>>No</option>
														</select>
													</div>
												</div>
											</div>

											<div class="cfm-field-groups modal-field-groups-wrap">
												<?php CFMH_Hosting_Publish_Episode::render_acf_field_groups('captivate_podcast', 'field_groups', $post_id); ?>
												<input type="hidden" name="acf_nonce" value="<?php echo wp_create_nonce('acf_save_nonce'); ?>" />
											</div>

										</div>
										<div class="modal-footer">
											<button type="button" id="close-acf" class="btn btn-outline-primary me-auto" data-bs-dismiss="modal">Close</button>
										</div>
									</div>
								</div>
							</div>
							<!-- /ACF Modal -->
						</div>
					<?php endif; ?>

					<div class="cfm-field cfm-website-social-media mt-4">
						<label>Social Media Appearance</label>

						<div class="social-media-wrap">
							<a id="social-media" data-bs-toggle="modal" data-bs-target="#social-media-modal" href="#" class="text-decoration-none"><i class="fal fa-share-nodes me-2"></i>Customize Social Media Appearance</a>
						</div>

						<small>Customize how your episode appears on social media platforms like Facebook, X, Instagram, WhatsApp, Threads, LinkedIn, Slack, and more.</small>

						<!-- Social Media Modal -->
						<div class="modal fade modal-slideout" id="social-media-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
							<div class="modal-dialog modal-dialog-slideout" role="document">
								<div class="modal-content">
									<div class="offcanvas-header flex-column align-items-end mb-4">
										<button type="button" aria-label="Close" data-bs-dismiss="modal" class="close-btn"> Close <i class="fas fa-arrow-right"></i></button>
									</div>

									<div class="modal-header">
										<h4 class="modal-title">Social Media Appearance</h4>
										<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
									</div>
									<div class="modal-body">

										<div class="cfm-modal-field mb-4">
											<label class="mb-2">Social image</label>

											<div id="cfm-social-media-image-uploader" class="cfm-dropzone fake-dropzone cfm-image-uploader" data-uploader-title="Select Social Media Image">
												<div class="fd-wrap">
													<div class="fd-col-image">
														<div class="fd-result">
															<?php if ( $is_edit && $social_media_image_url ) : ?>
																<img src="<?php echo esc_attr( $social_media_image_url ); ?>" width="200" height="200" class="img-fluid">
															<?php endif; ?>

															<?php if ( ! $is_edit || ( $is_edit && ! $social_media_image_url ) ) : ?>
																<i class="fal fa-image"></i>
															<?php endif; ?>
														</div>
													</div>

													<div class="fd-col-browse">
														<div class="fd-uploader"<?php echo ( ! $is_edit || ( $is_edit && ! $social_media_image_url ) ) ? ' style="display: block";' : ''; ?>>
															<div class="dropzone social-image-dropzone">
																<div class="dz-default">
																	<i class="fal fa-image"></i>
																	<strong>Browse media library</strong>
																</div>
															</div>
														</div>

														<div class="fd-replace"<?php echo ( $is_edit && $social_media_image_url ) ? ' style="display: block";' : ''; ?>>
															<button type="button" class="btn btn-primary btn-md mb-md-2 d-md-block mr-3 mr-md-0 upload-new-image">Upload New Image</button>
															<button type="button" class="btn btn-outline-primary btn-md remove-image">Remove Image</button>
														</div>

														<input type="hidden" name="social_media_image_id" class="fd-input-image-id" value="<?php echo esc_attr( $social_media_image_id ); ?>" />
														<input type="hidden" name="social_media_image_url" class="fd-input-image-url" value="<?php echo esc_attr( $social_media_image_url ); ?>" />
													</div>
												</div>
											</div>

											<small>Defaults to your featured image if empty</a></small>
										</div>

										<div class="cfm-modal-field mb-4">
											<label class="mb-2">Social title</label>
											<input type="text" name="social_media_title" value="<?php echo esc_attr( $social_media_title ); ?>">
										</div>

										<div class="cfm-modal-field mb-4">
											<label class="mb-2">Social description</label>
											<textarea name="social_media_description" rows="3" maxlength="150"><?php echo esc_textarea( $social_media_description ); ?></textarea>
										</div>

										<div class="mb-4 fw-light">Customize how your post appears on X, fill out the 'X Appearance' settings below. If left empty, the general 'Social Media Appearance' settings above will be used for sharing on X.</div>

										<div class="cfm-modal-field-group-name mb-4">X Appearance</div>

										<div class="cfm-modal-field mb-4">
											<label class="mb-2">X image</label>

											<div id="cfm-x-image-uploader" class="cfm-dropzone fake-dropzone cfm-image-uploader" data-uploader-title="Select X Image">
												<div class="fd-wrap">
													<div class="fd-col-image">
														<div class="fd-result">
															<?php if ( $is_edit && $x_image_url ) : ?>
																<img src="<?php echo esc_attr( $x_image_url ); ?>" width="200" height="200" class="img-fluid">
															<?php endif; ?>

															<?php if ( ! $is_edit || ( $is_edit && ! $x_image_url ) ) : ?>
																<i class="fal fa-image"></i>
															<?php endif; ?>
														</div>
													</div>

													<div class="fd-col-browse">

														<div class="fd-uploader"<?php echo ( ! $is_edit || ( $is_edit && ! $x_image_url ) ) ? ' style="display: block";' : ''; ?>>
															<div class="dropzone social-image-dropzone">
																<div class="dz-default">
																	<i class="fal fa-image"></i>
																	<strong>Browse media library</strong>
																</div>
															</div>
														</div>

														<div class="fd-replace"<?php echo ( $is_edit && $x_image_url ) ? ' style="display: block";' : ''; ?>>
															<button type="button" class="btn btn-primary btn-md mb-md-2 d-md-block mr-3 mr-md-0 upload-new-image">Upload New Image</button>
															<button type="button" class="btn btn-outline-primary btn-md remove-image">Remove Image</button>
														</div>

														<input type="hidden" name="x_image_id" class="fd-input-image-id" value="<?php echo esc_attr( $x_image_id ); ?>" />
														<input type="hidden" name="x_image_url" class="fd-input-image-url" value="<?php echo esc_attr( $x_image_url ); ?>" />
													</div>
												</div>
											</div>
										</div>

										<small>Defaults to your social image if empty</a></small>

										<div class="cfm-modal-field mb-4">
											<label class="mb-2">X title</label>
											<input type="text" name="x_title" value="<?php echo esc_attr( $x_title ); ?>">
										</div>

										<div class="cfm-modal-field mb-4">
											<label class="mb-2">X description</label>
											<textarea name="x_description" rows="3" maxlength="150"><?php echo esc_textarea( $x_description ); ?></textarea>
										</div>

									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-outline-primary me-auto" data-bs-dismiss="modal">Close</button>
									</div>
								</div>
							</div>
						</div>
						<!-- /Social Media Modal -->
					</div>

				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Publish Details</strong></div></div>
				<div class="col-lg-9">

					<div class="row">
						<div class="col-lg-6">
							<div class="cfm-field cfm-episode-publish-date">
								<label for="publish_date">Publish date <i class="fal fa-info-circle ms-2 cfmsync-tooltip" aria-hidden="true" data-bs-placement="top" title="Changing the publish date will change the date shown in your feed and may affect the order of your episodes. If the episode is published in the past it will become a published episode."></i></label>

								<div class="cfm-datepicker">
									<?php
									$date_today = new DateTime( 'now', new DateTimeZone( $show_timezone ) );
									$publish_date = $date_today->format( 'm/d/Y' );

									if ( $is_edit ) {
										$publish_date = get_the_date( 'm/d/Y', $post_id );
									}
									?>

									<div class="input-group">
										<input type="text" class="form-control" id="publish_date" name="publish_date" value="<?php echo esc_attr( $publish_date ); ?>" autocomplete="off">
										<button type="button" class="btn btn-outline-secondary input-group-button"><i class="fal fa-calendar-alt"></i></button>
									</div>
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<div class="cfm-field cfm-episode-publish-time">
								<label for="publish_time">Publish time <i class="fal fa-info-circle ms-2 cfmsync-tooltip" aria-hidden="true" data-bs-placement="top" title="The time that you'd like this episode to publish on the date you have selected."></i></label>

								<div class="cfm-timepicker">
									<?php
									$default_publish_time = cfm_get_show_info( $show_id, 'default_time' );

									if ( $is_edit ) {
										$publish_time = get_the_date( 'H:i', $post_id );
									}
									else {
										$publish_time = ( $default_publish_time ) ? $default_publish_time : '09:00';
									}
									?>

									<input type="text" class="form-control dropdown-toggle" id="publish_time" name="publish_time" value="<?php echo esc_attr( $publish_time ); ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" autocomplete="off">

									<div class="dropdown-menu" aria-labelledby="publish_time">
										<?php
											$now = new DateTime();
											$end = clone $now;
											$end->modify( '+12 hours' );

											$timeframe = '00:00';

											echo '<a class="dropdown-item">00:00</a>';
											while ( $timeframe <= '23:30' ) {
												$timeframe = date( 'H:i', strtotime( '+15 minutes', strtotime( $timeframe ) ) );
												echo '<a class="dropdown-item">' . $timeframe . '</a>';
											}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row mt-5">
						<?php
						// Episode status notice.
						if ( $exclusivity_date ) : ?>
							<div class="col-12 text-end">
								<p>This is a <strong>Timed Exclusive</strong> episode. Updating it won't change its status, publish date, and publish time. </p>
							</div>
						<?php elseif ( in_array( $episode_status, array( 'Exclusive', 'Early Access', 'Expired' ) ) ) : ?>
							<div class="col-12 text-end">
								<p>This is an <strong><?php echo esc_html( $episode_status ); ?></strong> episode. Updating it won't change its status, publish date, and publish time. </p>
							</div>
						<?php endif; ?>

						<div class="col-3">
							<div class="text-left">
								<a id="episode-cancel" href="<?php echo esc_url( admin_url( 'admin.php?page=cfm-hosting-podcast-episodes_' . $show_id ) ); ?>" class="btn btn-outline-primary full-md-button">Cancel</a>

								<?php
								if ( $is_edit && current_user_can( 'edit_others_posts' ) ) :
									$duplicate_episode_nonce = wp_create_nonce( 'duplicate_post_' . $post_id );
									?>
									<a id="episode-duplicate" class="d-block mt-4 text-decoration-none" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Duplicate Episode" data-confirmation-content="Are you sure you want to duplicate this episode? A new draft of the same episode will be created without an audio file, artwork, and transcription." data-confirmation-button="cfm-duplicate-episode" data-confirmation-reference="<?php echo esc_attr( $post_id  ); ?>" data-confirmation-nonce="<?php echo esc_attr( $duplicate_episode_nonce ); ?>">Duplicate this episode</a>
								<?php endif; ?>
							</div>
						</div>

						<div class="col-9">

							<div id="cfm-episode-save" class="text-end">

								<?php
								if ( $is_edit ) {
									if ( 'future' == $post_status || 'publish' == $post_status ) {
										echo '<button type="submit" id="episode_draft" name="episode_draft" class="btn btn-outline-primary full-md-button me-3">Revert to Draft</button>';

										$update_episode_text = ( 'future' == $post_status ) ? 'Schedule Episode' : 'Update Episode';
										echo '<button type="submit" id="episode_update" name="episode_update" class="btn btn-primary full-md-button">' . $update_episode_text . '</button>';
									}
									else {
										if ( $media_url ) {
											echo '<button type="submit" id="episode_draft" name="episode_draft" class="btn btn-outline-primary full-md-button me-3">Update Draft Episode</button>';
											echo '<button type="submit" id="episode_update" name="episode_update" class="btn btn-primary full-md-button">Publish Episode</button>';
										}
										else {
											echo '<button type="submit" id="episode_draft" name="episode_draft" class="btn btn-primary full-md-button">Update Draft Episode</button>';
										}
									}
								}
								else {
									echo '<button type="submit" id="episode_draft" name="episode_draft" class="btn btn-primary full-md-button">Save As Draft</button>';
								}
								?>
							</div>

						</div>
					</div>

				</div>
			</div>

		</form>

	</div><!--/ .cfm-page-content -->

	<?php require CFMH . 'inc/templates/template-parts/footer.php'; ?>

	<!-- Block confirmation modal -->
	<div id="cfm-insert-block-modal" class="modal fade cfm-insert-variable-modal" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="confirm-changes" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header"><h4 class="modal-title">How do you want to add this Block?</h4></div>

				<div class="modal-body">
					<p class="fw-light">Adding this Block dynamically will enable it to be auto-updated later if the data is changed (recommended), or you can convert it to static text right now.</p>

					<div class="form-group">
						<div class="form-check form-check-inline">
							<input type="radio" id="block_dynamic" name="dt_type" class="form-check-input" value="dynamic" checked="checked">
							<label class="form-check-label" for="block_dynamic">Dynamic</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="block_static" name="dt_type" class="form-check-input" value="static">
							<label class="form-check-label" for="block_static">Static</label>
						</div>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-outline-primary me-auto" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary modal-confirm" id="cfm-insert-dt-block">Insert Block</button>
				</div>
			</div>
		</div>
	</div>
	<!-- /Block confirmation modal -->

	<!-- Shortcode confirmation modal -->
	<div id="cfm-insert-shortcode-modal" class="modal fade cfm-insert-variable-modal" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="confirm-changes" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header"><h4 class="modal-title">How do you want to add this Shortcode?</h4></div>

				<div class="modal-body">
					<p class="fw-light">Adding this Shortcode dynamically will enable it to be auto-updated later if the data is changed (recommended), or you can convert it to static text right now.</p>

					<div class="form-group">
						<div class="form-check form-check-inline">
							<input type="radio" id="shortcode_dynamic" name="dt_type" class="form-check-input" value="dynamic" checked="checked">
							<label class="form-check-label" for="shortcode_dynamic">Dynamic</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="shortcode_static" name="dt_type" class="form-check-input" value="static">
							<label class="form-check-label" for="shortcode_static">Static</label>
						</div>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-outline-primary me-auto" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary modal-confirm" id="cfm-insert-dt-shortcode">Insert Shortcode</button>
				</div>
			</div>
		</div>
	</div>
	<!-- /Shortcode confirmation modal -->

</div><!--/ .wrap -->
