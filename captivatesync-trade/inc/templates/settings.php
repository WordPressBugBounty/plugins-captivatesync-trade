<?php
/**
 * Settings template
 */
?>

<div class="wrap cfmh cfm-hosting-settings">

	<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>

	<?php
	$cfm_general_settings = get_option( 'cfm_general_settings' );
	$archive_enable = isset( $cfm_general_settings['archive_enable'] ) ? $cfm_general_settings['archive_enable'] : '';
	$archive_title = isset( $cfm_general_settings['archive_title'] ) ? $cfm_general_settings['archive_title'] : '';
	$archive_slug = isset( $cfm_general_settings['archive_slug'] ) ? $cfm_general_settings['archive_slug'] : '';
	$single_slug = isset( $cfm_general_settings['single_slug'] ) ? $cfm_general_settings['single_slug'] : '';
	$category_archive_slug = isset( $cfm_general_settings['category_archive_slug'] ) ? $cfm_general_settings['category_archive_slug'] : '';
	$tag_archive_slug = isset( $cfm_general_settings['tag_archive_slug'] ) ? $cfm_general_settings['tag_archive_slug'] : '';
	$season_episode_number_enable = isset( $cfm_general_settings['season_episode_number_enable'] ) ? $cfm_general_settings['season_episode_number_enable'] : '';
	$season_episode_number_text = isset( $cfm_general_settings['season_episode_number_text'] ) ? $cfm_general_settings['season_episode_number_text'] : 'S{snum} E{enum}: ';
	$bonus_trailer_text = isset( $cfm_general_settings['bonus_trailer_text'] ) ? $cfm_general_settings['bonus_trailer_text'] : 'S{snum} {enum} Episode: ';
	?>

	<div class="cfm-page-content">
		<form id="cfm-form-settings" name="cfm-form-settings" method="post">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Episode Archive</strong></div></div>
				<div class="col-lg-9">
					<div class="cfm-field">
						<label>Enable Archive Page?</label>
						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="archive_yes" name="archive_enable" class="form-check-input" value="1" <?php echo ( '1' == $archive_enable || '' == $archive_enable ) ? 'checked="checked"' : '' ; ?>>
								<label class="form-check-label" for="archive_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="archive_no" name="archive_enable" class="form-check-input" value="0" <?php checked( $archive_enable, '0' ); ?>>
								<label class="form-check-label" for="archive_no">No</label>
							</div>
						</div>
						<small>Setting this to <strong>No</strong> will disable the default Captivate Podcasts archive page that displays all the episodes from all podcasts. The archive page <strong><?php echo get_bloginfo( 'url' ); ?>/captivate-podcasts</strong> will return a 404 error and will not appear on searches.</small>
					</div>

					<div class="cfm-field mt-4">
						<label for="archive_title">Archive Page Title</label>
						<input type="text" class="form-control" id="archive_title" name="archive_title" value="<?php echo esc_attr( $archive_title ); ?>" placeholder="Captivate Podcasts">
					</div>

					<div class="cfm-field mt-4">
						<label for="archive_slug">Archive Page Slug</label>
						<input type="text" class="form-control" id="archive_slug" name="archive_slug" value="<?php echo esc_attr( $archive_slug ); ?>" placeholder="captivate-podcast">
						<small>Changing this with an existing page slug will make that page display all the Captivate episodes as the archive page. Please avoid changing this frequently.</small>
					</div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Episode Single</strong></div></div>
				<div class="col-lg-9">
					<div class="cfm-field">
						<label for="single_slug">Single Episodes Slug</label>
						<input type="text" class="form-control" id="single_slug" name="single_slug" value="<?php echo esc_attr( $single_slug ); ?>" placeholder="captivate-podcast">
						<small>Slug for your captivate episodes individual pages. All your podcasts that aren't mapped to a page will use this slug. Please avoid changing this frequently.</small>
					</div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Episode Category Archive</strong></div></div>
				<div class="col-lg-9">
					<div class="cfm-field">
						<label for="archive_slug">Category Archive Slug</label>
						<input type="text" class="form-control" id="category_archive_slug" name="category_archive_slug" value="<?php echo esc_attr( $category_archive_slug ); ?>" placeholder="captivate-category">
						<small>Slug for your captivate category archive pages. Please avoid changing this frequently.</small>
					</div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Episode Tag Archive</strong></div></div>
				<div class="col-lg-9">
					<div class="cfm-field">
						<label for="archive_slug">Tag Archive Slug</label>
						<input type="text" class="form-control" id="tag_archive_slug" name="tag_archive_slug" value="<?php echo esc_attr( $tag_archive_slug ); ?>" placeholder="captivate-tag">
						<small>Slug for your captivate tag archive pages. Please avoid changing this frequently.</small>
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
								<input type="radio" id="season_episode_number_yes" name="season_episode_number_enable" class="form-check-input" value="1" <?php checked( $season_episode_number_enable, '1' ); ?>>
								<label class="form-check-label" for="season_episode_number_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="season_episode_number_no" name="season_episode_number_enable" class="form-check-input" value="0" <?php echo ( '1' != $season_episode_number_enable ) ? 'checked="checked"' : '' ; ?>>
								<label class="form-check-label" for="season_episode_number_no">No</label>
							</div>
						</div>
						<small>Setting this to <strong>Yes</strong> will display your season and episode number before the episode title depending on the text format below like so <strong>S1 E1: Your Episode Title Here</strong>. This can be overridden per shortcode but not on individual episode pages.</small>
					</div>

					<div class="cfm-field mt-4">
						<label for="season_episode_number_text">Season and Episode number text format</label>
						<input type="text" class="form-control" id="season_episode_number_text" name="season_episode_number_text" value="<?php echo esc_attr( $season_episode_number_text ); ?>" placeholder="S{snum} E{enum}: ">
						<small>Season and episode number text format where {snum} is the season number and {enum} is the episode number.</small>
					</div>

					<div class="cfm-field mt-4">
						<label for="bonus_trailer_text">Bonus and Trailer text format</label>
						<input type="text" class="form-control" id="bonus_trailer_text" name="bonus_trailer_text" value="<?php echo esc_attr( $bonus_trailer_text ); ?>" placeholder="S{snum} {enum} Episode: ">
						<small>If the episode is a bonus or trailer, this formatting will be applied where {snum} is the season number and {enum} is the "Bonus" or "Trailer" text.</small>
					</div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"></div>
				<div class="col-lg-9">
					<button id="cfm-save-settings" class="btn btn-primary">Save Settings <i class="fal fa-cog ms-2"></i></button>
				</div>
			</div>

	</form>

	</div><!--/ .cfm-page-content -->

	<?php require CFMH . 'inc/templates/template-parts/footer.php'; ?>

</div><!--/ .wrap -->
