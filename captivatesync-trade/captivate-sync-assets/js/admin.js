var $ = jQuery.noConflict();

$(document).ready(function($) {

	/**
	 * Tooltip
	 */
	$( 'body' ).tooltip({
		selector: '.cfmsync-tooltip'
	});

	/**
	 * Clipboard
	 */
	var clipboard = new ClipboardJS('.clipboard');

	clipboard.on('success', function(e) {
		var data_response = $(e.trigger).data('clipboard-response');
		cfmsync_toaster('success', data_response);
		e.clearSelection();
	});

	clipboard.on('error', function(e) {
		cfmsync_toaster('error', 'Clipboard error.');
		e.clearSelection();
	});

	/**
	 * Bootstrap dropdown set selected
	 */
	$('.dropdown-menu').on('click', '.dropdown-item', function() {
		$(this).closest('.dropdown-sort').find('.dropdown-toggle').text($(this).text() + ' ');

		$(this).closest('.dropdown-menu').prev().dropdown('toggle');
	});

	/**
	 * Bootstrap dropdown search
	 */
	$('.dropdown-search input[type=search]').on('input', function() {
		var value = $(this).val().toLowerCase();
		$(this).closest('.dropdown-menu').find('.dropdown-item').filter(function() {
			$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
		});
	});

	/**
	 * Bootstrap dropdown prevent close on click
	 */
	$('.dropdown-checkbox, .dropdown-search').on('click', function (e) {
		e.stopPropagation();
	});

	/**
	 * Settings
	 */
	 $(document).on('click', '#cfm-save-settings', function(e) {
		e.preventDefault();

		var $this = $(this);

		var formData = $this.closest('form').serializeArray();
  		//formData.push({ name: this.name, value: this.value });

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'save-settings',
				form_data: formData,
				_nonce: cfmsync.ajaxnonce
			},
			beforeSend: function( response ) {
				$this.prop('disabled', true);
				$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Saving settings...');
			},
			success: function( response ) {
				if ( 'success' == response ) {
					cfmsync_toaster('success', 'Settings saved successfully.');
				}
				else {
					cfmsync_toaster('error', response);
				}

				$this.prop('disabled', false);
				$this.html('Save Settings <i class="fal fa-cog ms-2"></i>');
			}
		} );

		e.preventDefault();
    });
	$(document).on('keyup', 'input[name=archive_slug], input[name=single_slug], input[name=category_archive_slug], input[name=tag_archive_slug]', function(e) {
		$(this).val(cfm_convert_to_slug($(this).val()));
	});

	/**
	 * Confirmation modal
	 */
	$('#confirmation-modal').on('show.bs.modal', function (e) {
		var button = $(e.relatedTarget),
			title = button.data('confirmation-title'),
			content = button.data('confirmation-content'),
			confirm_button = button.data('confirmation-button'),
			confirm_button_text = button.data('confirmation-button-text'),
			reference_id = button.data('confirmation-reference'),
			nonce = button.data('confirmation-nonce'),
			modal = $(this);

		modal.find('.modal-title').text(title);
		modal.find('.modal-body p').text(content);
		modal.find('.modal-footer .modal-confirm').prop('id', confirm_button);
		modal.find('.modal-footer .modal-confirm').text(confirm_button_text);
		modal.find('.modal-footer .modal-confirm').attr('data-reference', reference_id);
		modal.find('.modal-footer .modal-confirm').attr('data-nonce', nonce);
	});

	$('#confirmation-modal').on('hidden.bs.modal', function (e) {
		var modal = $(this);

		modal.find('.modal-title').text('');
		modal.find('.modal-body p').text('');
		modal.find('.modal-footer .modal-confirm').removeAttr('id');
		modal.find('.modal-footer .modal-confirm').text('Confirm');
		modal.find('.modal-footer .modal-confirm').removeAttr('data-reference');
		modal.find('.modal-footer .modal-confirm').removeAttr('data-nonce');
	});

	/**
	 * Load show settings
	 */
	$(document).on('click', '.cfm-display-show-settings', function(e) {
		e.preventDefault();

		var $this = $(this),
			show_id = $this.attr('data-reference');

			console.log('aaa');

		$('#cfm-save-show-settings').attr('data-reference', show_id);

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'load-show-settings',
				show_id: show_id,
				_nonce: cfmsync.ajaxnonce
			},
			beforeSend: function() {
				$('#cfm-show-settings').html(cfm_content_spinner);
			},
			success: function(response) {
				$('#cfm-show-settings').html(response);
			}
		});

		e.preventDefault();
    });

	/**
	 * Save show settings
	 */
	 $(document).on('click', '#cfm-save-show-settings', function(e) {
		e.preventDefault();

		var $this = $(this),
			show_id = $this.attr('data-reference'),
			use_artwork  = $( 'input[name=use_artwork]:checked' ).val(),
			se_num  = $( 'input[name=se_num]:checked' ).val(),
			se_num_text  = $( 'input[name=se_num_text]' ).val(),
			bonus_trailer_text  = $( 'input[name=bonus_trailer_text]' ).val();

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'save-show-settings',
				show_id: show_id,
				use_artwork: use_artwork,
				se_num: se_num,
				se_num_text: se_num_text,
				bonus_trailer_text: bonus_trailer_text,
				_nonce: cfmsync.ajaxnonce
			},
			beforeSend: function() {
				$this.prop('disabled', true);
				$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Saving podcast settings...');
			},
			success: function(response) {

				if ( 'success' == response ) {
					cfmsync_toaster('success', 'Podcasts settings saved successfully.');
				}
				else {
					cfmsync_toaster('error', response);
				}

				$this.html('Save Podcast Settings');
				$this.prop('disabled', false);
				$('#cfm-show-settings-modal').modal('hide');
			}
		});

		e.preventDefault();
    });

});