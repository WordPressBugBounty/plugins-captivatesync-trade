var $ = jQuery.noConflict();

$(document).ready(function($) {

	'use strict';

	/**
	 * Share episode
	 */
	$('#cfm-episode-share-modal').on('show.bs.modal', function (e) {
		var invoker = $(e.relatedTarget).closest("tr").prop('id'),
			post_id = invoker.split('-')[1];
		$(document).loadEpisodeShareModal(post_id);
	});
	$('#cfm-episode-share-modal').on('hidden.bs.modal', function (e) {
		$('#cfm-episode-share').html('');
	});

	/**
	 * Load episode share
	 */
	 $.fn.loadEpisodeShareModal = function($invoker) {
	    $.ajax(
			{
				url: cfmsync.ajaxurl,
				type: 'post',
				data: {
					action: 'share-episode',
					_nonce: cfmsync.ajaxnonce,
					post_id: $invoker,
				},
				beforeSend: function() {
					$('#cfm-episode-share').html(cfm_content_spinner);
				},
				success: function( response ) {
					$('#cfm-episode-share').html(response);
				}
			}
		);
   	};

	/**
	 * Disable/enable episode
	 */
	$(document).on('click', '#cfm-toggle-episode', function(e) {
		e.preventDefault();

		var $this = $(this),
			$this_html = $this.html(),
			post_id = $this.attr('data-reference'),
			_nonce = $this.attr('data-nonce');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'toggle-episode',
				_nonce: _nonce,
				post_id: post_id,
			},
			beforeSend: function() {
				$this.prop('disabled', true);
				$this.siblings('button').prop('disabled', true);
				$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
			},
			success: function(response) {
				$this.prop('disabled', false);
				$this.siblings('button').prop('disabled', false);
				$this.html($this_html);
				$('#confirmation-modal').modal('hide');

				if ( 'episode_deactivated' == response ) {
					console.log('deactivated');
					$('tr#post-'+post_id+' .btn-toggle').replaceWith('<a aria-label="Toggle" class="btn btn-toggle" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Activate Episode" data-confirmation-content="Are you sure you want to activate this episode? This episode will be activated and will be available publicly on this website." data-confirmation-button="cfm-toggle-episode" data-confirmation-button-text="Activate Episode" data-confirmation-reference="'+post_id+'" data-confirmation-nonce="'+_nonce+'"><i class="fal fa-play"></i></a>');

					cfmsync_toaster('success', 'Episode deactivated on this website.');
				}
				else if ( 'episode_activated' == response ) {
					console.log('activated');
					$('tr#post-'+post_id+' .btn-toggle').replaceWith('<a aria-label="Toggle" class="btn btn-toggle" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-confirmation-title="Deactivate Episode" data-confirmation-content="Are you sure you want to deactivate this episode? This episode will be deactivated and will not be available publicly on this website. This action will not change the episode status and will not affect the episode in Captivate." data-confirmation-button="cfm-toggle-episode" data-confirmation-button-text="Deactivate Episode" data-confirmation-reference="'+post_id+'" data-confirmation-nonce="'+_nonce+'"><i class="fal fa-pause"></i></a>');

					cfmsync_toaster('success', 'Episode activated on this website.');
				}
				else {
					cfmsync_toaster('error', 'Something went wrong. Please refresh the page and try again.');
				}
			}
		});

		e.preventDefault();
    });

	/**
	 * Trash episode
	 */
	$(document).on('click', '#cfm-trash-episode', function(e) {
		e.preventDefault();

		var $this = $(this),
			$this_html = $this.html(),
			post_id = $this.attr('data-reference'),
			_nonce = $this.attr('data-nonce');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'trash-episode',
				_nonce: _nonce,
				post_id: post_id,
			},
			beforeSend: function() {
				$this.prop('disabled', true);
				$this.siblings('button').prop('disabled', true);
				$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Deleting episode...');

				$('tr#post-'+post_id).css({
					"background-color": "#ff3333"
				}, 500);
			},
			success: function(response) {
				$this.prop('disabled', false);
				$this.siblings('button').prop('disabled', false);
				$this.html($this_html);
				$('#confirmation-modal').modal('hide');

				if ( 'success' == response ) {
					$('tr#post-'+post_id).fadeOut(500, function() {
						$('tr#post-'+post_id).remove();
					});
					cfmsync_toaster('success', 'Episode deleted on this website and on Captivate.');
				}
				else if ( 'duplicate_episode' == response ) {
					$('tr#post-'+post_id).css({
						"background-color": "#ffffff"
					}, 500);
					cfmsync_toaster('error', 'Cannot delete episode. Possible duplicate found. Please contact support.');
				}
				else if ( 'success_wp' == response ) {
					$('tr#post-'+post_id).fadeOut(500, function() {
						$('tr#post-'+post_id).remove();
					});
					cfmsync_toaster('success', 'Episode deleted on this website (episode not on Captivate).');
				}
				else {
					$('tr#post-'+post_id).css({
						"background-color": "#ffffff"
					});
					cfmsync_toaster('error', 'Something went wrong. Please refresh the page and try again.');
				}
			}
		});

		e.preventDefault();
    });

});
