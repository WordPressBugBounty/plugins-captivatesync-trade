<!-- Create toolbar container -->
<div id="quilljs-toolbar">

	<!-- basic buttons -->
	<span class="ql-formats">
		<button class="ql-bold"></button>
		<button class="ql-italic"></button>
		<button class="ql-underline"></button>
		<button class="ql-strike"></button>
	</span>
	<span class="ql-formats">
		<button class="ql-blockquote"></button>
	</span>

	<!-- font size dropdown -->
	<span class="ql-formats">
		<select class="ql-size">
			<option value="small"></option>
			<option selected></option>
			<option value="large"></option>
			<option value="huge"></option>
		</select>
	</span>

	<!-- heading buttons -->
	<span class="ql-formats">
		<button class="ql-header" value="1"></button>
		<button class="ql-header" value="2"></button>
	</span>

	<!-- list buttons and align dropdown -->
	<span class="ql-formats">
		<button class="ql-list" value="ordered"></button>
		<button class="ql-list" value="bullet"></button>
	</span>
	<span class="ql-formats">
		<select class="ql-align">
			<option selected></option>
			<option value="center"></option>
			<option value="right"></option>
			<option value="justify"></option>
		</select>
	</span>

	<!-- link button -->
	<span class="ql-formats">
		<button class="ql-link"></button>
	</span>
	<span class="ql-formats">
		<button class="ql-clean"></button>
	</span>

	<!-- blocks button -->
	<?php
	$shownotes_blocks = cfm_get_dynamic_text( $show_id, array( 'snippet' ), array( 'all' ) );

	if ( is_array( $shownotes_blocks ) && ! empty( $shownotes_blocks ) ) : ?>
	<span class="ql-formats ql-formats-custom">
		<div id="cfm-dropdown-dt-blocks" class="cfm-dropdown-menu dropdown-ql-variable dropdown-dt-blocks">
			<button type="button" class="btn btn-border dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Blocks</button>
			<div class="dropdown-menu">
				<form><div class="dropdown-search"><i class="fal fa-search"></i><input type="search" class="form-control search" placeholder="Search Blocks"></div>
				<div class="dropdown-contents">
					<?php
					foreach ( $shownotes_blocks as $block ) {
						echo '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-block-modal" data-confirmation-reference="' . esc_attr( $block['name'] ) . '">' . esc_html( $block['name_human'] ) . '</a>';
					}
					?>
				</div>
			</div>
		</div>
		</span>
	<?php endif; ?>

	<!-- shortcodes button -->
	<span class="ql-formats ql-formats-custom">
		<div id="cfm-dropdown-dt-shortcodes" class="cfm-dropdown-menu dropdown-ql-variable dropdown-dt-shortcodes">
			<button type="button" class="btn btn-border dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Shortcodes</button>
			<div class="dropdown-menu">
				<div class="dropdown-search"><i class="fal fa-search"></i><input type="search" class="form-control search" placeholder="Search Shortcodes"></div>
				<div class="dropdown-checkbox">
					<label class="custom-control-label dt-show-custom-shortcodes"><input type="checkbox"> Show custom shortcodes only</label>
				</div>

				<div class="dropdown-contents">

					<?php
					$attribution_links = cfm_get_show_info( $show_id, 'attribution_links' );
					$attribution_links = json_decode( $attribution_links );

					if ( is_array( $attribution_links ) && ! empty( $attribution_links ) ) {
						foreach ( $attribution_links as $al ) {
							echo ( 1 == $al->active ) ? '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-short-link-' . esc_attr( $al->slug ) . '" data-type="attribution-link">Attribution Link: ' . esc_html( $al->label ) . '</a>' : '';
						}
					}

					$marketing_links = cfm_get_show_marketing_links( $show_id );

					if ( ! empty( $marketing_links ) && $marketing_links->affiliate ) {
						echo '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-show-affiliate-link" data-type="show">Captivate Affiliate Link</a>';
					}
					?>

					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-episode-author" data-type="episode">Episode Author</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-episode-explicit" data-type="episode">Episode Explicit</a>

					<?php if ( $is_edit ) : ?>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-episode-idea-notes" data-type="episode-idea">Episode Idea Host Notes</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-episode-idea-summary" data-type="episode-idea">Episode Idea Summary</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-episode-idea-title" data-type="episode-idea">Episode Idea Title</a>
					<?php endif; ?>

					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-episode-link" data-type="episode">Episode Link</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-episode-number" data-type="episode">Episode Number</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-episode-season" data-type="episode">Episode Season</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-episode-title" data-type="episode">Episode Title</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-episode-type" data-type="episode">Episode Type</a>

					<?php
					if ( $is_edit ) {
						if ( is_array( $bookings ) && ! empty( $bookings ) ) {
							foreach ( $bookings as $b ) {
								$g_name = $b->guest_first_name . ' ' . $b->guest_last_name;

								echo ( $b->guest_biography ) ? '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-guest-bio-' . esc_attr( $b->show_guest_id ) . '" data-type="guest">Guest bio: ' . esc_html( $g_name ) . '</a>' : '';
								echo ( $b->guest_fb_group_url ) ? '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-guest-fb-group-' . esc_attr( $b->show_guest_id ) . '" data-type="guest">Guest Facebook Group: ' . esc_html( $g_name ) . '</a>' : '';
								echo ( $b->guest_fb_page_url ) ? '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-guest-fb-page-' . esc_attr( $b->show_guest_id ) . '" data-type="guest">Guest Facebook Page: ' . esc_html( $g_name ) . '</a>' : '';
								echo ( $b->guest_insta_username ) ? '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-guest-instagram-' . esc_attr( $b->show_guest_id ) . '" data-type="guest">Guest Instagram: ' . esc_html( $g_name ) . '</a>' : '';
								echo '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-guest-name-' . esc_attr( $b->show_guest_id ) . '" data-type="guest">Guest name: ' . esc_html( $g_name ) . '</a>';
								echo ( $b->guest_twitter_username ) ? '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-guest-twitter-' . esc_attr( $b->show_guest_id ) . '" data-type="guest">Guest Twitter: ' . esc_html( $g_name ) . '</a>' : '';
								echo ( $b->guest_additional_url_1 ) ? '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-guest-url-' . esc_attr( $b->show_guest_id ) . '" data-type="guest">Guest URL: ' . esc_html( $g_name ) . '</a>' : '';
								echo ( $b->guest_youtube_url ) ? '<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-guest-youtube-' . esc_attr( $b->show_guest_id ) . '" data-type="guest">Guest YouTube: ' . esc_html( $g_name ) . '</a>' : '';
							}
						}
					}
					?>

					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-show-author" data-type="show">Podcast Author</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-show-copyright" data-type="show">Podcast Copyright</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-show-listen-link" data-type="show">Podcast Single Promo Link</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-show-site-link" data-type="show">Podcast Site Link</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-show-title" data-type="show">Podcast Title</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-research-links-list" data-type="research-link">Research Links</a>
					<a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="d-show-support-link" data-type="research-link">Tipping/Membership Link</a>

					<?php
					// Custom shortcodes.
					$shownotes_shortcodes = cfm_get_dynamic_text( $show_id, array( 'variable' ), array( 'all' ) );
					if ( is_array( $shownotes_shortcodes ) && ! empty( $shownotes_shortcodes ) ) {
						foreach ( $shownotes_shortcodes as $shortcode ) {
							echo '<a class="dropdown-item dt-custom-shortcode" data-bs-toggle="modal" data-bs-target="#cfm-insert-shortcode-modal" data-confirmation-reference="' . esc_html( $shortcode['name'] ) . '" data-type="custom">' . esc_html( $shortcode['name_human'] ) . '</a>';
						}
					}
					?>

					<?php
					// Conditional.
					$conditional_episodes = array(
						array('type' => 'type-full', 'text' => 'Episode is Full'),
						array('type' => 'type-bonus', 'text' => 'Episode is Bonus'),
						array('type' => 'type-trailer', 'text' => 'Episode is Trailer'),
						array('type' => 'has-guests', 'text' => 'Episode has Guests'),
					);
					if ( is_array( $conditional_episodes ) && ! empty( $conditional_episodes ) ) {
						foreach ( $conditional_episodes as $ce ) {
							echo '<a class="dropdown-item dt-conditional overflow-ellipsis d-flex" data-reference="d-condition-ep-' . esc_attr( $ce['type'] ) . '" data-type="conditional">
								<div class="d-flex align-items-center">
									<div class="conditional">IF</div>
									<div class="arrow-right"></div>
								</div>
								<div class="d-flex flex-wrap align-items-center">
									<div class="w-100">' . esc_attr( $ce['text'] ) . '</div>
									<div class="quarter-circle-top-right"></div>
									<div class="conditional conditional-then">THEN</div>
									<div class="arrow-right"></div>
									<div class="text-secondary">include your text...</div>
								</div>
							</a>';
						}
					}
					?>

				</div>
			</div>
		</div>
	</span>

</div>