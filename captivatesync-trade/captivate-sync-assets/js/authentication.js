jQuery( document ).ready( function( $ ) {

	'use strict';

	/**
	 * Create authentication
	 */
	$(document).on( 'click', '#create-authentication', function(e) {
		e.preventDefault();

		var $this = $(this),
			$this_html = $this.html(),
			auth_id = $( 'input[name=auth_id]' ).val(),
			auth_key = $( 'input[name=auth_key]' ).val();

		if ( '' == auth_id || '' == auth_key ) {
			cfmsync_toaster('error', 'Please fill in the required fields.');
		}
		else {
			$.ajax({
				url: cfmsync.ajaxurl,
				type: 'post',
				data: {
					action: 'create-authentication',
					auth_id: auth_id,
					auth_key: auth_key,
					_nonce: cfmsync.ajaxnonce
				},
				beforeSend: function() {
					$this.prop('disabled', true);
					$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Authenticating user...');
				},
				success: function(response) {
					if ( 'success' == response ) {
						cfmsync_toaster('success', 'User authenticated successfully.');
					}
					else {
						cfmsync_toaster('error', response);
					}

					$this.html($this_html);
					setTimeout(function(){location.reload(true)}, 5000);
				}
			} );
		}

		e.preventDefault();
    });

	/**
	 * Remove authentication
	 */
	$(document).on('click', '#remove-authentication', function(e) {
		e.preventDefault();

		var $this = $(this),
			$this_html = $this.html();

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'remove-authentication',
				_nonce: cfmsync.ajaxnonce
			},
			beforeSend: function() {
				$this.prop('disabled', true);
				$this.siblings('button').prop('disabled', true);
				$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
			},
			success: function(response) {
				if ( 'success' == response ) {
					cfmsync_toaster('success', 'User credentials credentials, shows, and episodes removed successfully.');
				}
				else {
					cfmsync_toaster('error', response);
				}

				$this.prop('disabled', false);
				$this.siblings('button').prop('disabled', false);
				$this.html($this_html);

				$('#confirmation-modal').modal('hide');
				setTimeout(function(){location.reload(true)}, 2000);
			}
		});

		e.preventDefault();
    });

});