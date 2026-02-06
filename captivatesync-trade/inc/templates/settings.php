<?php
/**
 * Settings template
 */
?>

<div class="wrap cfmh cfm-hosting-settings">

	<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>

	<?php
	$archive_enable = CFMH_Hosting_Settings::get_settings( 'archive_enable', '1' );
	$archive_title = CFMH_Hosting_Settings::get_settings( 'archive_title', '' );
	$archive_slug = CFMH_Hosting_Settings::get_settings( 'archive_slug', '' );
	$single_slug = CFMH_Hosting_Settings::get_settings( 'single_slug', '' );
	$category_archive_slug = CFMH_Hosting_Settings::get_settings( 'category_archive_slug', '' );
	$tag_archive_slug = CFMH_Hosting_Settings::get_settings( 'tag_archive_slug', '' );

	$season_episode_number_enable = CFMH_Hosting_Settings::get_settings( 'season_episode_number_enable', '0' );
	$season_episode_number_text = CFMH_Hosting_Settings::get_settings( 'season_episode_number_text', 'S{snum} E{enum}: ' );
	$bonus_trailer_text = CFMH_Hosting_Settings::get_settings( 'bonus_trailer_text', 'S{snum} {enum} Episode: ' );

	$captivate_shownotes_enable = CFMH_Hosting_Settings::get_settings( 'captivate_shownotes_enable', '0' );
	$timestamp_shownotes_enable = CFMH_Hosting_Settings::get_settings( 'timestamp_shownotes_enable', '1' );
	$transcript_shownotes_enable = CFMH_Hosting_Settings::get_settings( 'transcript_shownotes_enable', '1' );

	$autosync_show_information = CFMH_Hosting_Settings::get_settings( 'autosync_show_information', '1' );
	$autosync_new_episodes = CFMH_Hosting_Settings::get_settings( 'autosync_new_episodes', '1' );
	$autosync_existing_episodes = CFMH_Hosting_Settings::get_settings( 'autosync_existing_episodes', '1' );

	$episode_video_enable = CFMH_Hosting_Settings::get_settings( 'episode_video_enable', '0' );
	?>

	<div class="cfm-page-content">
		<form id="cfm-form-settings" name="cfm-form-settings" method="post">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Episode Archive</strong></div></div>
				<div class="col-lg-9">
					<div class="cfm-field">
						<label>Enable archive page?</label>
						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="archive_yes" name="archive_enable" class="form-check-input" value="1" <?php checked( $archive_enable, '1' ); ?>>
								<label class="form-check-label" for="archive_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="archive_no" name="archive_enable" class="form-check-input" value="0" <?php checked( $archive_enable, '0' ); ?>>
								<label class="form-check-label" for="archive_no">No</label>
							</div>
						</div>
						<small>If <strong>disabled</strong>, the Captivate Podcasts archive page (<strong><?php echo get_bloginfo( 'url' ); ?>/captivate-podcasts</strong>) will be removed. It will return a 404 error and won't appear in search results.</small>
					</div>

					<div class="cfm-field mt-4">
						<label for="archive_title">Archive page title</label>
						<input type="text" class="form-control" id="archive_title" name="archive_title" value="<?php echo esc_attr( $archive_title ); ?>" placeholder="Captivate Podcasts">
					</div>

					<div class="cfm-field mt-4">
						<label for="archive_slug">Archive page slug</label>
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
						<label for="single_slug">Single episode slug</label>
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
						<label for="archive_slug">Category archive slug</label>
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
						<label for="archive_slug">Tag archive slug</label>
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
								<input type="radio" id="season_episode_number_no" name="season_episode_number_enable" class="form-check-input" value="0" <?php checked( $season_episode_number_enable, '0' ); ?>>
								<label class="form-check-label" for="season_episode_number_no">No</label>
							</div>
						</div>
						<small>If set to <strong>Yes</strong>, your season and episode number will be prepended to the episode title, following the format <strong>S1 E1: Your Episode Title Here</strong>. This can be customized per shortcode, but cannot be overridden on individual episode pages.</small>
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
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Show Notes</strong></div></div>
				<div class="col-lg-9">
					<div class="cfm-field">
						<label>Use Captivate-generated show notes as the individual episode content in WordPress? <i>(available soon)</i></label>
						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="captivate_shownotes_enable_yes" name="captivate_shownotes_enable" class="form-check-input" value="1" <?php checked( $captivate_shownotes_enable, '1' ); ?>>
								<label class="form-check-label" for="captivate_shownotes_enable_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="captivate_shownotes_enable_no" name="captivate_shownotes_enable" class="form-check-input" value="0" <?php checked( $captivate_shownotes_enable, '0' ); ?>>
								<label class="form-check-label" for="captivate_shownotes_enable_no">No</label>
							</div>
						</div>
						<small>If set to <strong>Yes</strong>, the Captivate-generated show notes (including rendered dynamic show notes, AMIE show notes, etc.) will be displayed on the single episode page in WordPress. These will be the exact rendered show notes from Captivate.</small>
					</div>

					<div class="cfm-field mt-4">
						<label>Make timestamps in show notes clickable</label>
						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="timestamp_shownotes_enable_yes" name="timestamp_shownotes_enable" class="form-check-input" value="1" <?php checked( $timestamp_shownotes_enable, '1' ); ?>>
								<label class="form-check-label" for="timestamp_shownotes_enable_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="timestamp_shownotes_enable_no" name="timestamp_shownotes_enable" class="form-check-input" value="0" <?php checked( $timestamp_shownotes_enable, '0' ); ?>>
								<label class="form-check-label" for="timestamp_shownotes_enable_no">No</label>
							</div>
						</div>
						<small>If set to <strong>Yes</strong>, timestamps in your show notes will be turned into clickable links on the single episode page.</small>
					</div>

					<div class="cfm-field mt-4">
						<label>Show transcript section on episode pages</label>
						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="transcript_shownotes_enable_yes" name="transcript_shownotes_enable" class="form-check-input" value="1" <?php checked( $transcript_shownotes_enable, '1' ); ?>>
								<label class="form-check-label" for="transcript_shownotes_enable_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="transcript_shownotes_enable_no" name="transcript_shownotes_enable" class="form-check-input" value="0" <?php checked( $transcript_shownotes_enable, '0' ); ?>>
								<label class="form-check-label" for="transcript_shownotes_enable_no">No</label>
							</div>
						</div>
						<small>If set to <strong>Yes</strong>, the transcript (if available) will be displayed on the single episode page.</small>
					</div>

					<div class="cfm-field mt-4">
						<label>Show episode video on episode pages</label>
						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="episode_video_enable_yes" name="episode_video_enable" class="form-check-input" value="1" <?php checked( $episode_video_enable, '1' ); ?>>
								<label class="form-check-label" for="episode_video_enable_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="episode_video_enable_no" name="episode_video_enable" class="form-check-input" value="0" <?php checked( $episode_video_enable, '0' ); ?>>
								<label class="form-check-label" for="episode_video_enable_no">No</label>
							</div>
						</div>
						<small>If set to <strong>Yes</strong>, the episode video (if available) will be displayed on the single episode page.</small>
					</div>
				</div>
			</div>

			<hr class="mt-5 mb-5 mt-lg-7 mb-lg-7">

			<div class="row">
				<div class="col-lg-3 mb-3 mb-lg-0"><div class="cfm-field-heading"><strong>Auto-Sync (Webhook Fallback)</strong></div></div>
				<div class="col-lg-9">
					<div class="cfm-field">
						<label>Enable automatic syncing of show information</label>
						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="autosync_show_information_yes" name="autosync_show_information" class="form-check-input" value="1" <?php checked( $autosync_show_information, '1' ); ?>>
								<label class="form-check-label" for="autosync_show_information_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="autosync_show_information_no" name="autosync_show_information" class="form-check-input" value="0" <?php checked( $autosync_show_information, '0' ); ?>>
								<label class="form-check-label" for="autosync_show_information_no">No</label>
							</div>
						</div>
						<small>If <strong>enabled</strong>, your show information will be updated every 150 minutes in case the Captivate webhook doesn't go through. You can turn this off and sync manually as needed.</small>
					</div>

					<div class="cfm-field mt-4">
						<label>Enable automatic syncing of new episodes</label>
						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="autosync_new_episodes_yes" name="autosync_new_episodes" class="form-check-input" value="1" <?php checked( $autosync_new_episodes, '1' ); ?>>
								<label class="form-check-label" for="autosync_new_episodes_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="autosync_new_episodes_no" name="autosync_new_episodes" class="form-check-input" value="0" <?php checked( $autosync_new_episodes, '0' ); ?>>
								<label class="form-check-label" for="autosync_new_episodes_no">No</label>
							</div>
						</div>
						<small>If <strong>enabled</strong>, we'll check for new episodes every 60 minutes in case the Captivate webhook doesn't go through. You can turn this off and sync manually as needed.</small>
					</div>

					<div class="cfm-field mt-4">
						<label>Enable automatic syncing of existing episodes</label>
						<div class="form-group">
							<div class="form-check form-check-inline">
								<input type="radio" id="autosync_existing_episodes_yes" name="autosync_existing_episodes" class="form-check-input" value="1" <?php checked( $autosync_existing_episodes, '1' ); ?>>
								<label class="form-check-label" for="autosync_existing_episodess_yes">Yes</label>
							</div>
							<div class="form-check form-check-inline">
								<input type="radio" id="autosync_existing_episodes_no" name="autosync_existing_episodes" class="form-check-input" value="0" <?php checked( $autosync_existing_episodes, '0' ); ?>>
								<label class="form-check-label" for="autosync_existing_episodes_no">No</label>
							</div>
						</div>
						<small>If <strong>enabled</strong>, your existing episodes will be updated every 90 minutes in case the Captivate webhook doesn't go through. You can turn this off and sync manually as needed.</small>
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
