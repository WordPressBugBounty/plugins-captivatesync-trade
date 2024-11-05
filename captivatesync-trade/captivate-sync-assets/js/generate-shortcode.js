jQuery( document ).ready(
	function($) {

		/**
		 * Enable color picker
		 */
		$( '.cfm-color-picker' ).wpColorPicker();

		/**
		 * Shows and episodes picker
		 */
		var selected_shows = [],
			selected_episodes = [];
		$(document).on('click', '.cfm-podcasts-picker .dropdown-menu a.dropdown-item', function(e) {
			e.preventDefault();

			var show_id = $(this).data('id'),
				show_title = $(this).text();

			if ( $.inArray(show_id, selected_shows ) === -1) {
				selected_shows.push(show_id);
				$('#cfm-podcasts-selected').append('<span data-id="'+ show_id + '" class="cfmsync-tooltip" aria-hidden="true" data-bs-placement="top" title="Click to remove">'+ show_title + '</span>');

				// empty episode selection.
				$('#cfm-episodes-selected').html('');
				selected_episodes = [];
			}
			else {
				cfmsync_toaster('error', 'The podcast you\'re trying to select is already added.');
			}

			// clear show selection field.
			$("input[name=select_shows]").val('');

			// reload episodes list.
			$(document).loadEpisodes(selected_shows);
		});

		$(document).on('click', '#cfm-podcasts-selected span', function(e) {
			var show_id = $(this).data('id');

			// remove clicked show.
			selected_shows = $.grep(selected_shows, function(value) {
				return value != show_id;
			});
			$(this).tooltip("hide").remove();

			// remove selected episodes based on removed shows.
			$('#cfm-episodes-selected span').each(function() {
				if ( $(this).data('show-id') === show_id ) {
					$(this).remove();

					var post_id = $(this).data('id');
					selected_episodes = $.grep(selected_episodes, function(value) {
						return value != post_id;
					});
				}
			});

			// reload episodes list.
			$(document).loadEpisodes(selected_shows);
		});

		// search for podcasts.
		$("input[name=select_shows]").on("keyup", function() {
			var value = $(this).val().toLowerCase();

			$(".cfm-podcasts-picker .dropdown-menu a.dropdown-item").show().filter(function() {
				return $(this).text().toLowerCase().indexOf(value) === -1;
			}).hide();
		});

		// select episodes.
		$(document).on('click', '.cfm-episodes-picker .dropdown-menu a.dropdown-item', function(e) {
			e.preventDefault();

			var post_id = $(this).data('id'),
				post_title = $(this).html(),
				show_id = $(this).data('show-id');

			if ( $.inArray(post_id, selected_episodes ) === -1) {
				selected_episodes.push(post_id);
				$('#cfm-episodes-selected').append('<span data-show-id="'+ show_id + '" data-id="'+ post_id + '" class="cfmsync-tooltip" aria-hidden="true" data-bs-placement="top" title="Click to remove">'+ post_title + '</span>');
			}
			else {
				cfmsync_toaster('error', 'The episode you\'re trying to select is already added.');
			}

			// clear episode selection field.
			$("input[name=select_episodes]").val('');
		});

		// remove clicked episode.
		$(document).on('click', '#cfm-episodes-selected span', function(e) {
			var post_id = $(this).data('id');
			selected_episodes = $.grep(selected_episodes, function(value) {
				return value != post_id;
			});
			$(this).tooltip("hide").remove();
		});

		// search for episodes.
		$("input[name=select_episodes]").on("keyup", function() {
			var value = $(this).val().toLowerCase();

			$(".cfm-episodes-picker .dropdown-menu a.dropdown-item").show().filter(function() {
				return $(this).text().toLowerCase().indexOf(value) === -1;
			}).hide();
		});

		/**
		 * Load episodes from selected shows
		 */
		 $.fn.loadEpisodes = function($show_ids) {
			$.ajax({
				url: cfmsync.ajaxurl,
				type: 'post',
				data: {
					action: 'shortcode-load-episodes',
					_nonce: cfmsync.ajaxnonce,
					show_ids: $show_ids,
				},
				beforeSend: function() {
					$('.cfm-episodes-picker .dropdown-menu').html('<span>Loading episodes...</span>');
				},
				success: function(response) {
					$('.cfm-episodes-picker .dropdown-menu').html(response);
				}
			});
		};


		/**
		 * Shortcode generator
		 */
		$(document).on('click', '#generate_shortcode', function(e) {

			if ( selected_shows.length === 0 && selected_episodes.length === 0 ) {
				cfmsync_toaster('error', 'Please select show(s) or episode(s).');
				e.preventDefault();
				return false;
			}
			else {

				var show_id    					= selected_shows,
					episode_id    				= selected_episodes,
					layout    					= $( 'input[name=shortcode_layout]:checked' ).val(),
					column    					= $( 'input[name=shortcode_column]:checked' ).val(),
					title    					= $( 'input[name=shortcode_title]:checked' ).val(),
					se_num    					= $( 'input[name=shortcode_se_num]:checked' ).val(),
					title_tag    				= $( 'input[name=shortcode_title_tag]' ).val(),
					title_color    				= $( 'input[name=shortcode_title_color]' ).val(),
					title_hover_color   		= $( 'input[name=shortcode_title_hover_color]' ).val(),
					image    					= $( 'input[name=shortcode_image]:checked' ).val(),
					image_size    				= $( 'input[name=shortcode_image_size]:checked' ).val(),
					content   					= $( 'input[name=shortcode_content]:checked' ).val(),
					player    					= $( 'input[name=shortcode_player]:checked' ).val(),
					content_length   			= $( 'input[name=shortcode_content_length]' ).val(),
					items    					= $( 'input[name=shortcode_items]' ).val(),
					link    					= $( 'input[name=shortcode_link]:checked' ).val(),
					link_text    				= $( 'input[name=shortcode_link_text]' ).val(),
					link_text_color    			= $( 'input[name=shortcode_link_text_color]' ).val(),
					link_text_hover_color    	= $( 'input[name=shortcode_link_text_hover_color]' ).val(),
					order    					= $( 'input[name=shortcode_order]:checked' ).val(),
					pagination    				= $( 'input[name=shortcode_pagination]:checked' ).val(),
					exclude    					= $( 'input[name=shortcode_exclude]:checked' ).val(),
					load_more_text    			= $( 'input[name=shortcode_pagination_load_more_text]' ).val(),
					load_more_class    			= $( 'input[name=shortcode_pagination_load_more_class]' ).val();

				var shortcode_column = '';
				if ( 'grid' == layout ) {
					shortcode_column = ' columns="' + column + '"';
				}

				var shortcode_title_color = '';
				if ( '' != title_color && 'hide' != title ) {
					shortcode_title_color = ' title_color="' + title_color + '"';
				}

				var shortcode_title_hover_color = '';
				if ( '' != title_hover_color && 'hide' != title ) {
					shortcode_title_hover_color = ' title_hover_color="' + title_hover_color + '"';
				}

				var shortcode_content_length = '';
				if ( 'excerpt' == content ) {
					shortcode_content_length = ' content_length="' + content_length + '"';
				}

				var shortcode_link_text = '';
				if ( 'show' == link ) {
					shortcode_link_text = ' link_text="' + link_text + '"';
				}

				var shortcode_link_text_color = '';
				if ( '' != link_text_color && 'hide' != link ) {
					shortcode_link_text_color = ' link_text_color="' + link_text_color + '"';
				}

				var shortcode_link_text_hover_color = '';
				if ( '' != link_text_hover_color && 'hide' != link ) {
					shortcode_link_text_hover_color = ' link_text_hover_color="' + link_text_hover_color + '"';
				}

				var shortcode_loadmore = '';
				if ( 'load_more' == pagination ) {
					load_more_text = ( '' != load_more_text ) ? load_more_text : 'Load More';
					load_more_class = ( '' != load_more_class ) ? load_more_class : '';
					shortcode_loadmore = ' load_more_text="' + load_more_text + '" load_more_class="' + load_more_class + '"';
				}

				var shortcode = '[cfm_captivate_episodes show_id="' + show_id + '" episode_id="' + episode_id + '" layout="' + layout + '" title="' + title + '" se_num="' + se_num + '" title_tag="' + title_tag + '"' + shortcode_column + shortcode_title_color + shortcode_title_hover_color + shortcode_link_text_color + shortcode_link_text_hover_color + ' image="' + image + '" image_size="' + image_size + '" content="' + content + '" ' + shortcode_content_length + ' player="' + player + '" link="' + link + '" ' + shortcode_link_text + ' order="' + order + '" exclude="' + exclude + '" items="' + items + '" pagination="' + pagination + '"' + shortcode_loadmore + ']';

				var shortcode_preview = '[cfm_captivate_episodes show_id="' + show_id + '" episode_id="' + episode_id + '" layout="' + layout + '" title="' + title + '" se_num="' + se_num + '" title_tag="' + title_tag + '"' + shortcode_column + shortcode_title_color + shortcode_title_hover_color + shortcode_link_text_color + shortcode_link_text_hover_color + ' image="' + image + '" image_size="' + image_size + '" content="' + content + '" ' + shortcode_content_length + ' player="' + player + '" link="' + link + '" ' + shortcode_link_text + ' order="' + order + '" exclude="' + exclude + '" items="6" pagination="hide" ' + shortcode_loadmore + ']';

				$('#clipboard-shortcode').text(shortcode);

				cfmsync_toaster('success', 'Shortcode updated');

				/* Save shortcode to wp_options */
				$.ajax({
					url: cfmsync.ajaxurl,
					type: 'post',
					data: {
						action: 'save-shortcode',
						_nonce: cfmsync.ajaxnonce,
						show_id: show_id,
						shortcode: shortcode,
						shortcode_preview: shortcode_preview
					},
					beforeSend: function() {
						// preview loader.
						$('#shortcode-preview').html(cfm_content_spinner);
					},
					success: function(response) {
						// show preview.
						$('#shortcode-preview').html(response);
					}
				});

			}

		});

	}
);
