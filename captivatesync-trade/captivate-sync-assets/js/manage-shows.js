var $ = jQuery.noConflict();

$(document).ready(function($) {

	'use strict';

	/**
	 * Manage shows
	 */
	$('#cfm-captivate-shows-modal').on('show.bs.modal', function (e) {
		$(document).loadCaptivateShows();
	});
	$('#cfm-captivate-shows-modal').on('hidden.bs.modal', function (e) {
		$('#cfm-captivate-shows').html('');
	});

	$(document).on('click', '#cfm-select-captivate-shows', function(e) {
		e.preventDefault();

		var $this = $(this);

		let selectedShows = [],
			showAuthors = {};

		$.each($("input[name='shows_to_sync']:checked"), function() {
			selectedShows.push($(this).val());
		});

		$.each($("#cfm-captivate-shows ul select"), function() {
			let selectName = $(this).attr("name"),
				selectedValue = $(this).val();
			showAuthors[selectName] = selectedValue;
		});

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'select-captivate-shows',
				shows: selectedShows,
				authors: showAuthors,
				_nonce: cfmsync.ajaxnonce
			},
			beforeSend: function() {

				$("#cfm-captivate-shows > li input").prop('disabled', true);

				$this.prop('disabled', true);
				$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Syncing podcasts and episodes...');

			},
			success: function(response) {

				var syncResponse = JSON.parse(response);

				if ( ! syncResponse.return ) {
					cfmsync_toaster('success', 'Podcasts already selected successfully.');
					setTimeout(function(){location.reload(true)}, 2000);
				}
				else {

					var totalSuccess = syncResponse.return.length;

	    			for (var i=0; i < syncResponse.return.length; ++i) {
	    				if (syncResponse.return[i].success == false) {
		    				$( 'li#show-' + syncResponse.return[i].id + ' label').append('<span class="text-danger">(' + syncResponse.return[i].error + ')</span>');
		    			} else {
		    				totalSuccess = totalSuccess - 1;
		    			}
	    			}

					if ( totalSuccess == 0 ) {
						cfmsync_toaster('success', 'Podcasts and episodes synced successfully.');
						setTimeout(function(){location.reload(true)}, 2000);
					} else {
						cfmsync_toaster('error', 'It looks like we\'ve ran into a few issues whilst selecting these podcasts to sync.');
						setTimeout(function(){location.reload(true)}, 5000);
					}

				}

				$this.html('Select &amp; Sync Podcasts <i class="fal fa-sync ms-2"></i>');

			}
		});

		e.preventDefault();
	});

	/**
	 * Sync all shows and episodes
	 */
	$(document).on('click', '#cfm-manual-sync-data', function(e) {
		e.preventDefault();

		var $this = $(this),
			$this_html = $this.html();

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'sync-shows',
				_nonce: cfmsync.ajaxnonce
			},
			beforeSend: function() {
				$this.prop('disabled', true);
				$this.siblings('button').prop('disabled', true);
				$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Syncing podcasts and episodes...');
			},
			success: function( response ) {
				if ( 'success' == response ) {
					cfmsync_toaster('success', 'Sync complete!');
				}
				else {
					cfmsync_toaster('error', response);
				}

				$this.prop('disabled', false);
				$this.siblings('button').prop('disabled', false);
				$this.html($this_html);

				$(document).loadWPShows($('input[name=data_content]').val(), '', $('#cfm-dropdown-sort-podcasts').data('sort'));
				$('#confirmation-modal').modal('hide');
			}
		});

		e.preventDefault();
    });

    /**
	 * Page mapping
	 */
	$(document).on('change', 'select[name=page_for_show]', function(e) {

		e.preventDefault();

		var $this = $(this),
			s_id = $this.closest( ".cfm-show-wrap" ).prop('id'),
			show_id = s_id.split('_')[1],
			page_id = $this.val();

		$(document).disableFields('input[name=display_episodes]');
		$(document).disableFields('select[name=display_episodes]');
		$(document).disableFields('select[name=page_for_show]');
		$(document).disableFields('select[name=author_for_show]');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'set-show-page',
				_nonce: cfmsync.ajaxnonce,
				show_id: show_id,
				page_id: page_id
			},
			success: function( response ) {

				if ( 'success' == response ) {
					cfmsync_toaster('success', 'Podcast episodes individual URL will now use the slug of the selected page.');
				}
				else if ( 'already_exists' == response ) {
					$this.val('0');
					cfmsync_toaster('error', 'The selected page is already mapped to one of your podcasts. Please select a different page.');
				}
				else {
					cfmsync_toaster('error', response);
				}

				setTimeout(function(){
					$(document).enableFields('input[name=display_episodes]');
					$(document).enableFields('select[name=display_episodes]');
					$(document).enableFields('select[name=page_for_show]');
					$(document).enableFields('select[name=author_for_show]');
				}, 5000);
			}
		} );

		e.preventDefault();

    });

	/**
	 * Set show default author
	 */
	$(document).on('change', 'select[name=author_for_show]', function(e) {

		e.preventDefault();

		var $this = $(this),
			s_id = $this.closest( ".cfm-show-wrap" ).prop('id'),
			show_id = s_id.split('_')[1],
			author_id = $(this).val();

		$(document).disableFields('input[name=display_episodes]');
		$(document).disableFields('select[name=display_episodes]');
		$(document).disableFields('select[name=page_for_show]');
		$(document).disableFields('select[name=author_for_show]');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'set-show-author',
				_nonce: cfmsync.ajaxnonce,
				show_id: show_id,
				author_id: author_id
			},
			success: function( response ) {

				if ( 'success' == response ) {
					cfmsync_toaster('success', 'Podcast author has been set successfully.');
				}
				else {
					cfmsync_toaster('error', response);
				}

				setTimeout(function(){
					$(document).enableFields('input[name=display_episodes]');
					$(document).enableFields('select[name=display_episodes]');
					$(document).enableFields('select[name=page_for_show]');
					$(document).enableFields('select[name=author_for_show]');
				}, 5000);
			}
		} );

		e.preventDefault();

    });

	/**
	 * Enable/disable episodes on page mapping
	 */
	$(document).on('change', 'input[name=display_episodes], select[name=display_episodes]', function(e) {

		e.preventDefault();

		var $this = $(this),
			s_id = $this.closest( ".cfm-show-wrap" ).prop('id'),
			show_id = s_id.split('_')[1],
			display_episodes = ( this.checked ) ? '1' :'0';

		if ( $this.is('select') ) {
			display_episodes = $this.val();
		}

		$(document).disableFields('input[name=display_episodes]');
		$(document).disableFields('select[name=display_episodes]');
		$(document).disableFields('select[name=page_for_show]');
		$(document).disableFields('select[name=author_for_show]');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'set-display-episodes',
				_nonce: cfmsync.ajaxnonce,
				show_id: show_id,
				display_episodes: display_episodes
			},
			success: function( response ) {

				if ( 'success' == response ) {
					if ( display_episodes == '0' ) {
						cfmsync_toaster('success', 'Podcast episodes will now appear on the selected page.');
					}
					else {
						cfmsync_toaster('success', 'Podcast episodes will not appear on the selected page.');
					}
				}
				else {
					cfmsync_toaster('error', response);
				}

				setTimeout(function(){
					$(document).enableFields('input[name=display_episodes]');
					$(document).enableFields('select[name=display_episodes]');
					$(document).enableFields('select[name=page_for_show]');
					$(document).enableFields('select[name=author_for_show]');
				}, 5000);
			}
		} );

		e.preventDefault();

    });

	/**
	 * Sync show and episodes
	 */
	 $(document).on('click', '#cfm-sync-show-and-episodes', function(e) {
		e.preventDefault();

		var $this = $(this),
			$this_html = $this.html(),
			show_id = $this.attr('data-reference');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'sync-show',
				show_id: show_id,
				_nonce: cfmsync.ajaxnonce
			},
			beforeSend: function() {
				$this.prop('disabled', true);
				$this.siblings('button').prop('disabled', true);
				$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Syncing podcast and episodes...');
			},
			success: function(response) {
				if ( 'success' == response ) {
					cfmsync_toaster('success', 'Sync complete!');
				}
				else {
					cfmsync_toaster('error', response);
				}

				$this.prop('disabled', false);
				$this.siblings('button').prop('disabled', false);
				$this.html($this_html);

				$(document).loadWPShows($('input[name=data_content]').val(), '', $('#cfm-dropdown-sort-podcasts').data('sort'));
				$('#confirmation-modal').modal('hide');
			}
		});

		e.preventDefault();
    });

	/**
	 * Clear publish saved data
	 */
	$(document).on('click', '#cfm-clear-publish-data', function(e) {
		e.preventDefault();

		var $this = $(this),
			show_id = $this.attr('data-reference');

		// LOCALSTORAGE local-storage.js - clear.
		var key = show_id + '_cfm-form-publish-episode_save_storage';
		localStorage.removeItem(key);

		// LOCALSTORAGE custom - clear.
		localStorage.removeItem(show_id + '_featured_image_url_local');
		localStorage.removeItem(show_id + '_post_content_wp_local');
		localStorage.removeItem(show_id + '_shownotes_local');
		localStorage.removeItem(show_id + '_shownotes_local_html');

		cfmsync_toaster('success', 'Publish episode auto-saved data cleared successfully.');

		$('#confirmation-modal').modal('hide');

		e.preventDefault();
    });

	/**
	 * Enable/disable fields
	 */
    $.fn.disableFields = function(field_attr) {

		if (field_attr != "") {
			$(field_attr).each(function() {
				$(this).prop('disabled', true);
			});
		}

	}

	$.fn.enableFields = function(field_attr) {

		if (field_attr != "") {
			var fields = $(field_attr);

			fields.each(function() {
				$(this).prop('disabled', false);
			});
		}

	}

	/**
	 * Load Captivate Shows
	 */
	$.fn.loadCaptivateShows = function() {
	    $.ajax(
			{
				url: cfmsync.ajaxurl,
				type: 'post',
				data: {
					action: 'manage-captivate-shows',
					_nonce: cfmsync.ajaxnonce,
				},
				beforeSend: function() {
					$('#cfm-captivate-shows').html(cfm_content_spinner);
				},
				success: function( response ) {
					$('#cfm-captivate-shows').html(response);
				}
			}
		);
   	};

	/**
	 * Shows grid/list/sort
	 */
	$('#cfm-grid-view').on('click', function (e) {
		$(this).addClass('disabled');
		$('input[name=data_content]').val('grid');
		$(document).loadWPShows('grid', $(this) ,'content_view');
	});

	$('#cfm-list-view').on('click', function (e) {
		$(this).addClass('disabled');
		$('input[name=data_content]').val('list');
		$(document).loadWPShows('list', $(this), 'content_view');
	});

	$('#cfm-dropdown-sort-podcasts .dropdown-item').on('click', function (e) {
		$('#cfm-dropdown-sort-podcasts').attr('data-sort', $(this).data('sort'));
		$(document).loadWPShows($('input[name=data_content]').val(), $(this), $(this).data('sort'));
	});

	/**
	 * Toggle row detail
	 */
	$(document).on('click', '.cfm-shows-list .toggle-row', function(e) {
        var show_id = $(this).data('show-id');
		var icon = $(this).find('i');

        $('#row_detail_' + show_id).toggle();

		$('.datatable-row-detail[data-show-id="' + show_id + '"]').toggle();

		if (icon.hasClass('fa-chevron-right')) {
			icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
		} else {
			icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
		}
    });

	/**
	 * Load WP Shows
	 */
   	$.fn.loadWPShows = function($layout, $this = '', $this_action) {

   		$layout = ($layout == 'list') ? 'list' : 'grid';

		if ( $layout == 'grid' ) {
			$('#cfm-grid-view').addClass('active');
			$('#cfm-list-view').removeClass('active');
		}

		if ( $layout == 'list' ) {
			$('#cfm-list-view').addClass('active');
			$('#cfm-grid-view').removeClass('active');
		}

		if ( $this != '' && $this_action != 'content_view' ) {
			$('#cfm-dropdown-sort-podcasts .dropdown-item').removeClass('active');
			$this.addClass('active');
		}

	    $.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'load-shows',
				_nonce: cfmsync.ajaxnonce,
				layout: $layout,
				this_action: $this_action,
			},
			beforeSend: function() {
				$('#cfm-shows').html(cfm_content_spinner);
			},
			success: function( response ) {
				$('#cfm-shows').addClass('cfm-shows-grid').removeClass('cfm-shows-list');
				$('#cfm-shows').html(response);
			},
			complete: function() {
				if ($this != '') { $this.removeClass('disabled'); }
			}
		});
   	};

   	$(document).loadWPShows($('input[name=data_content]').val(), '', $('#cfm-dropdown-sort-podcasts').data('sort'));

});


