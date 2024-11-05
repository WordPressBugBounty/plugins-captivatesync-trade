<?php
/**
 * Header template
 */

$page_slug = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
$current_screen = get_current_screen();
$no_art_screens = array(
	'toplevel_page_cfm-hosting-podcast-episodes',
	'admin_page_cfm-hosting-podcast-episodes',
	'captivate-sync_page_cfm-hosting-podcast-episodes',
	'toplevel_page_cfm-hosting-publish-episode',
	'admin_page_cfm-hosting-publish-episode',
	'captivate-sync_page_cfm-hosting-publish-episode',
	'toplevel_page_cfm-hosting-edit-episode',
	'admin_page_cfm-hosting-edit-episode',
	'captivate-sync_page_cfm-hosting-edit-episode',
);
$no_art = ( ! in_array( $current_screen->id, $no_art_screens ) && strpos( $current_screen->id, 'captivate-sync_page_cfm-hosting-podcast-episodes_' ) !== 0 ) ? true : false;
?>

<div class="cfm-page-heading<?php echo $no_art ? ' no-art' : ''; ?>">

	<?php if ( $no_art == false ) : ?>
		<div class="cfm-page-artwork">
			<img class="img-fluid" src="<?php echo esc_url( cfm_get_show_artwork( cfm_get_show_id(), '200x200' ) ); ?>" alt="<?php echo esc_attr( cfm_get_show_info( cfm_get_show_id(),'title' ) ); ?>" width="160" height="160">
		</div>
	<?php endif; ?>

	<div class="cfm-page-title">

		<h1><?php echo esc_html( get_admin_page_title() ); ?></h2>

		<?php if ( $no_art == false ) : ?>
			<div class="podcast-settings">
				<a class="cfm-display-show-settings" data-bs-toggle="modal" data-bs-target="#cfm-show-settings-modal" data-reference="<?php echo esc_attr( cfm_get_show_id() ); ?>"><i class="fal fa-cog me-1"></i> Podcast Settings</a>

				<?php if ( '1' == cfm_get_show_info( cfm_get_show_id(), 'private' ) ) : ?>
					<span><i class="fal fa-lock ms-1 me-1"></i> Private Podcast</span>
				<?php else : ?>
					<a class="clipboard" data-clipboard-response="RSS Feed has been copied." data-clipboard-text="<?php echo esc_attr( cfm_get_show_info( cfm_get_show_id(), 'feed_url' ) ); ?>" data-original-title="Copy RSS Feed"><i class="fal fa-rss ms-1 me-1"></i> RSS Feed</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	</div>

	<div class="cfm-page-logo">

		<a target="_blank" href="<?php echo esc_url( CFMH_CAPTIVATE_URL ); ?>"><img src="<?php echo esc_url( CFMH_URL ); ?>assets/img/captivate-sync-black.svg"></a>

	</div>

</div>
