Dropzone.autoDiscover = false;

jQuery( document ).ready(function($) {

	/**
	 * Current screens
	 */
	var publish_episode_screens = ['toplevel_page_cfm-hosting-publish-episode', 'admin_page_cfm-hosting-publish-episode', 'captivate-sync_page_cfm-hosting-publish-episode'],
		edit_episode_screens = ['toplevel_page_cfm-hosting-edit-episode', 'admin_page_cfm-hosting-edit-episode', 'captivate-sync_page_cfm-hosting-edit-episode'];

	/**
	 * Save form data locally - on keyup and every 6 hours
	 */
	if ( $.inArray(cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1 ) {
		$('#cfm-form-publish-episode').cfmLocalStorage({exclude_name: ['_sec','_wp_http_referer'], interval: 43200000});
	}

	/**
	 * Audio uploader
	 */
	var show_id    			= $( 'input[name=show_id]' ).val(),
		media_created_at    = $( 'input[name=media_created_at]' ),
		media_id       		= $( 'input[name=media_id]' ),
		media_bit_rate      = $( 'input[name=media_bit_rate]' ),
		media_bit_rate_str  = $( 'input[name=media_bit_rate_str]' ),
		media_duration      = $( 'input[name=media_duration]' ),
		media_duration_str  = $( 'input[name=media_duration_str]' ),
		media_id3_size      = $( 'input[name=media_id3_size]' ),
		media_name       	= $( 'input[name=media_name]' ),
		media_size     		= $( 'input[name=media_size]' ),
		media_type     		= $( 'input[name=media_type]' ),
		media_url      		= $( 'input[name=media_url]' ),
		media_shows_id 		= $( 'input[name=media_shows_id]' ),
		media_updated_at 	= $( 'input[name=media_updated_at]' ),
		media_users_id 		= $( 'input[name=media_users_id]' );

	$('#podcast-dropzone').dropzone({
		autoProcessQueue: true,
		uploadMultiple: false,
		parallelUploads: 1,
		maxFiles: 1,
		maxFilesize: 300,
		timeout: 500000,
		url: cfm_script.cfm_url + '/shows/' + show_id + '/media',
		acceptedFiles: '.mp3, .mp4, .m4a',
		addRemoveLinks: true,
		dictDefaultMessage: '<i class="far fa-waveform"></i><div class="dz-content">Drag and drop your audio file* <br> or <strong>browse files</strong><small>MP3, M4A, MP4 file types</small></div>',

		init: function() {
			var podcastDropzone = this;

			existingFile = media_url.val();

			if ( existingFile ) {

				var mockFile = {
					name: existingFile.replace(/^.*[\\\/] /, ''),
					size: 1,
					status: 'success',
					accepted: true,
					processing: true
				};

				podcastDropzone.files.push(mockFile);
			}

			podcastDropzone.on('addedfile', function(file) {
				var fileSize 	= file.size,
					filesCount  = podcastDropzone.files.length;

				if ( fileSize > 314572800 ) { // 300MB
					cfmsync_toaster('error', 'Audio file maximum allowed size exceeded (300MB).');
				}

				// remove other files.
				if ( filesCount > 1 ) {
					$.each(podcastDropzone.files, function(index, file) {
						if ( index < filesCount - 1 ) {
							podcastDropzone.removeFile(file);
						}
					});
				}
			});

			podcastDropzone.on('sending', function(file, xhr, formData) {
				let xfNr5Wsp = cfm_script.xfNr5Wsp;
				xfNr5Wsp = xfNr5Wsp.slice(29);
				xfNr5Wsp = xfNr5Wsp.slice(0, -29);
				xhr.setRequestHeader("Authorization", "Bearer " + xfNr5Wsp);
			});

			podcastDropzone.on('processing', function( file, response ) {
				// show preloader.
				$('#cfm-audio-uploader .dropzone-uploader').fadeOut(100, function () {
					$('#cfm-audio-uploader .dropzone-preloader').show();
					$('#cfm-audio-uploader .progress-info').html('Uploading <strong>' + file.upload.filename + '</strong>');
					$('#cfm-audio-uploader .dropzone-result').html('');

					$('#episode_draft, #episode_update').prop('disabled', true);
				});
			});

			podcastDropzone.on('uploadprogress', function(file, progress, bytesSent) {
				$('#cfm-audio-uploader .progress-bar').css('width', progress + '%');
			});

			podcastDropzone.on('success', function(file, response) {
				var media	= response['media'],
					bitrate_str 	= String(media['media_bit_rate']);

				media_created_at.val(media['created_at']);
				media_id.val(media['id']);
				media_bit_rate.val(media['media_bit_rate']);
				media_bit_rate_str.val(bitrate_str.substring(0,3) + 'kbps');
				media_duration.val(media['media_duration']);
				media_duration_str.val(cfm_milliseconds_to_str(media['media_duration']*1000));
				media_id3_size.val(media['media_id3_size']);
				media_name.val(media['media_name']);
				media_size.val(media['media_size']);
				media_type.val(media['media_type']);
				media_url.val(media['media_url']);
				media_shows_id.val(media['shows_id']);
				media_updated_at.val(media['updated_at']);
				media_users_id.val(media['users_id']);

				$('input[name=media_id]').trigger('change');

				$('#cfm-audio-uploader .dropzone-preloader').fadeOut(100, function () {
					$('#cfm-audio-uploader .dropzone-result').html('<audio controls="controls" preload="none"><source type="audio/mpeg" src="' + media['media_url'] + '"> Your browser does not support the audio element. </audio><div class="dropzone-result-info d-flex justify-content-between"><div class="result-info"><strong>' + media['media_name'] +'</strong> <br>' + bitrate_str.substring(0,3) + 'kbps | ' + cfm_milliseconds_to_str(media['media_duration']*1000) + '</div><div class="result-actions"><button class="replace-audio btn btn-outline-dark">Replace audio file</button></div></div>');
				});

				cfmsync_toaster('success', 'Audio file successfully uploaded to your episode.');

				$episode_publish_text = ( cfm_is_datetime_future($( "input[name=publish_date]" ).val() + ' ' + $( "input[name=publish_time]" ).val()) === true ) ? 'Schedule Episode' : 'Publish Episode';

				if ( ! $('#episode_update').length ) {
					$episode_publish_id = ( $.inArray(cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1 ) ? 'episode_publish' : 'episode_update';
					$('#cfm-episode-save').html('<button type="submit" id="episode_draft" name="episode_draft" class="btn btn-primary full-md-button me-3">Save As Draft</button><button type="submit" id="' + $episode_publish_id + '" name="' + $episode_publish_id + '" class="btn btn-primary full-md-button" >' + $episode_publish_text + '</button>');
				}

				$('#episode_draft, #episode_update').prop('disabled', false);

				// reset uploader.
				podcastDropzone.removeAllFiles( true );
			});

			podcastDropzone.on('error', function(file, response) {
				$('#cfm-audio-uploader .dropzone-preloader').fadeOut(100, function () {
					$('#cfm-audio-uploader .dropzone-result').html('<div class="cfm-alert cfm-alert-error"><span class="alert-icon"></span> <span class="alert-text">Media file upload error</span></div>');
					$('#cfm-audio-uploader .dropzone-uploader').show();
				});

				// reset uploader.
				podcastDropzone.removeAllFiles(true);
			});

		}
	});

	$(document).on('click', '#cfm-audio-uploader .cancel-upload', function(e) {
		e.preventDefault();

		// show dropzone.
		$( '#cfm-audio-uploader .dropzone-preloader' ).fadeOut(100, function () {
			$( '#cfm-audio-uploader .dropzone-uploader' ).show();
			$( '#cfm-audio-uploader .progress-info' ).html('');
			$( '#cfm-audio-uploader .progress-bar' ).css( 'width', '0' );
		});

		// reset uploader.
		Dropzone.forElement( "#podcast-dropzone" ).off('error');
		Dropzone.forElement( "#podcast-dropzone" ).removeAllFiles( true );
	});

	$(document).on('click', '#cfm-audio-uploader .replace-audio', function(e) {
		e.preventDefault();

		// show dropzone.
		$( '#cfm-audio-uploader .dropzone-result' ).html('');
		$( '#cfm-audio-uploader .dropzone-uploader' ).show();

		// reset uploader.
		Dropzone.forElement( "#podcast-dropzone" ).removeAllFiles( true );
	});

	/**
	 * Display a different episode title on Apple Podcasts?
	 */
	$('#post_title_check').change(function() {
		if ($('#post_title_check:checked').length == $('#post_title_check').length) {
			$('.cfm-field.cfm-itunes-title').fadeIn(200);
		}
		else {
			$('.cfm-field.cfm-itunes-title').fadeOut(200);
		}
	});

	/**
	 * Date and time picker
	 */
	function change_publish_button(datetime) {
		var d1 = new Date();
		var d2 = new Date( datetime );

		if (d1 > d2) {
			$( 'button[name=episode_publish]' ).html( "Publish Episode" );
			$( 'button[name=episode_update]' ).html( "Update Episode" );
		} else {
			$( 'button[name=episode_publish]' ).html( "Schedule Episode" );
			$( 'button[name=episode_update]' ).html( "Schedule Episode" );
		}
	}

	$("input[name=publish_date]").datepicker({
		changeMonth: true,
		changeYear: true,
		showOtherMonths: true,
		selectOtherMonths: true,
		defaultDate: new Date(),
		dateFormat: 'mm/dd/yy',
		dayNamesMin: [ "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa" ],
		onSelect: function(date) {

			change_publish_button( date + ' ' + $( 'input[name=publish_time]' ).val() );
			$('input[name=publish_date]').trigger('change');

		}
	});
	$(document).on('click', '.cfm-datepicker .input-group .btn', function(e) {
		e.preventDefault();
		$("input[name=publish_date]").focus();
	});

	$(document).on('click', '.cfm-timepicker .dropdown-menu a.dropdown-item', function(e) {
		var val = $( this ).text();
		change_publish_button( $( "input[name=publish_date]" ).val() + ' ' + val );

		$('input[name=publish_time]').val( val );
		$('input[name=publish_time]').trigger('change');
	});

	/**
	 * Artwork image uploader
	 */
	$(document).on('click', '#artwork-dropzone', function(e) {
		e.preventDefault();

		$this = $(this);

		var image_frame;
		if (image_frame) {
			image_frame.open();
		}

		// Define image_frame as wp.media object.
		image_frame = wp.media({
			title: 'Select Episode Cover Art',
			multiple : false,
			library : {
				type : 'image',
			}
		});

		image_frame.on('select', function() {
			var selection  = image_frame.state().get( 'selection' );
			var artwork_id = 0;

			if ( artwork_id == 0 ) {
				selection.each(function(attachment) {
					var mimeType = attachment.attributes.mime;
					var validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
					if (validImageTypes.includes(mimeType)) {
						artwork_id = attachment['id'];
					} else {
						alert('Please select a valid image (JPG, PNG, GIF, or WebP).');
					}
				});
			}

			if ( artwork_id != 0) {

				var media_attachment = image_frame.state().get('selection').first().toJSON();

				if ( media_attachment.url ) {

					$('#episode_artwork').val(media_attachment.url);
					$('#episode_artwork_id').val(artwork_id);
					$('#episode_artwork_width').val(media_attachment.width);
					$('#episode_artwork_height').val(media_attachment.height);
					$('#episode_artwork_type').val(media_attachment.mime);
					$('#episode_artwork_filesize').val(media_attachment.filesizeInBytes);
					$('#episode_artwork, #episode_artwork_id, #episode_artwork_width, #episode_artwork_height, #episode_artwork_type, #episode_artwork_filesize').trigger('change');

					$this.parent().hide();
					$('#cfm-artwork-uploader .fd-replace').fadeIn(200);
					$('#cfm-artwork-uploader .fd-result').html('<img src="' + media_attachment.url + '" width="200" height="200" class="img-fluid">').hide().fadeIn(650);
				}
			}

		});

		image_frame.on('open', function() {
			// On open, get the id from the hidden input.
			// and select the appropiate images in the media manager.
			var selection = image_frame.state().get( 'selection' );
			ids           = $( '#episode_artwork_id' ).val().split( ',' );
			ids.forEach(function(id) {
				attachment = wp.media.attachment( id );
				attachment.fetch();
				selection.add( attachment ? [ attachment ] : [] );
			});
		});

		image_frame.open();
	});

	$(document).on('click', '#cfm-artwork-uploader .remove-image', function(e) {
		e.preventDefault();
		$( '#cfm-artwork-uploader .fd-replace' ).fadeOut(100, function () {
			$('#cfm-artwork-uploader .fd-uploader' ).show();
			$('#cfm-artwork-uploader .fd-result').html('<i class="fal fa-image"></i>');

			$('#episode_artwork').val('');
			$('#episode_artwork_id').val('');
			$('#episode_artwork_width').val('');
			$('#episode_artwork_height').val('');
			$('#episode_artwork_type').val('');
			$('#episode_artwork_filesize').val('');

			$('#episode_artwork, #episode_artwork_id, #episode_artwork_width, #episode_artwork_height, #episode_artwork_type, #episode_artwork_filesize').trigger('change');
		});
	});

	/**
	 * Custom image uploader
	 */
	$( document ).on('click', '.fake-dropzone.cfm-image-uploader .dropzone', function(e) {
		e.preventDefault();

		var $this = $(this),
			$uploader_wrap = $this.closest('.fake-dropzone'),
			$fd_replace = $uploader_wrap.find('.fd-replace'),
			$fd_result = $uploader_wrap.find('.fd-result'),
			$image_id_input = $uploader_wrap.find('.fd-input-image-id'),
			$image_url_input = $uploader_wrap.find('.fd-input-image-url');

		var uploader_title = $uploader_wrap.attr('data-uploader-title');

		var image_frame;
		if ( image_frame ) {
			image_frame.open();
		}

		// Define image_frame as wp.media object.
		image_frame = wp.media({
			title: uploader_title,
			multiple : false,
			library : {
				type : 'image',
			}
		});

		image_frame.on('select', function() {
			var selection  = image_frame.state().get( 'selection' ),
				image_id = 0,
				image_url = "";

			selection.each(function(attachment) {
        		var mimeType = attachment.attributes.mime;
				var validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
				if (validImageTypes.includes(mimeType)) {
					image_id = attachment['id'];
					image_url = attachment.attributes.url;

				} else {
					alert('Please select a valid image (JPG, PNG, GIF, or WebP).');
				}
			});

			if ( image_id != 0 ) {
				$image_id_input.val(image_id);
				$image_id_input.trigger('change');
				$image_url_input.val(image_url);
				$image_url_input.trigger('change');

				$this.parent().hide();
				$fd_replace.fadeIn(200);
				$fd_result.html('<img src="' + image_url + '" width="200" height="200" class="img-fluid">').hide().fadeIn(650);
			}
		});

		image_frame.open();
	});

	$( document ).on('click', '.fake-dropzone.cfm-image-uploader .remove-image', function(e) {
		e.preventDefault();
		var $this = $(this),
			$uploader_wrap = $this.closest('.fake-dropzone'),
			$fd_uploader = $uploader_wrap.find('.fd-uploader'),
			$fd_replace = $uploader_wrap.find('.fd-replace'),
			$fd_result = $uploader_wrap.find('.fd-result'),
			$image_id_input = $uploader_wrap.find('.fd-input-image-id'),
			$image_url_input = $uploader_wrap.find('.fd-input-image-url');

		$fd_replace.fadeOut(100, function () {
			$fd_uploader.show();
			$fd_result.html('<i class="fal fa-image"></i>');

			$image_id_input.val(0);
			$image_id_input.trigger('change');
			$image_url_input.val('');
			$image_url_input.trigger('change');
		});
	});

	$(document).on('click', '#cfm-artwork-uploader .upload-new-image, .fake-dropzone.cfm-image-uploader .upload-new-image', function(e) {
		e.preventDefault();
		var $this = $(this),
			$uploader_wrap = $this.closest('.fake-dropzone'),
			$fd_uploader = $uploader_wrap.find('.fd-uploader'),
			$fd_replace = $uploader_wrap.find('.fd-replace');

		$fd_replace.fadeOut(100, function () {
			$fd_uploader.show();
		});
	});

	/**
	 * Change content editor
	 */
	$(document).on('click', '#enable_wordpress_editor', function(e) {
		if ( this.checked ) {
			$('.cfm-captivate-editor').addClass('hidden');
			$('.cfm-wordpress-editor').removeClass('hidden');
		}
		else {
			$('.cfm-captivate-editor').removeClass('hidden');
			$('.cfm-wordpress-editor').addClass('hidden');
		}
	});

	/**
	 * Submit validation
	 */
	$(window).keydown(function(e) {
		// prevent form submission on enter.
		if ( e.keyCode == 13 && e.target.tagName.toLowerCase() != 'textarea' ) {
			e.preventDefault();
			return false;
		}
	});

	var clicked_button = null;
	$(document).on('submit', '#cfm-form-publish-episode', function(e) {

		var $this = $('#' + clicked_button),
			$this_html = $this.html();

		$('button[type=submit]').prop('disabled', true);
		$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
		$('#episode-cancel').addClass('disabled');

		var post_title 	 = $('input[name=post_title]').val(),
		shownotes        = $('textarea[name=post_content]').val(),
		seo_description  = $('#seo_description').val(),
		media_id         = $('input[name=media_id]').val(),
		errors           = 0,
		error_feedback   = '';

		if ( media_id == '' && clicked_button != "episode_draft") {
			$('#cfm-audio-uploader').addClass('invalid-control');
			if ( ! $('#cfm-audio-uploader-error').length ) {
				$('<div id="cfm-audio-uploader-error" class="invalid-feedback">You must upload an audio for your episode.</div>').insertAfter('#cfm-audio-uploader');
			}
			errors += 1;
			error_feedback += '<br>You must upload an audio for your episode.<br>';
		}
		if ( post_title == '' ) {
			$('input[name=post_title]').addClass('is-invalid');
			if ( ! $( '#post_title-error' ).length ) {
				$( '<div id="post_title-error" class="invalid-feedback">You must enter a title for your episode.</div>' ).insertAfter('input[name=post_title]');
			}
			errors += 1;
			error_feedback += '<br>Episode Title: Check for any unusual or invalid characters, remove and resave.<br>';
		}
		if ( ( shownotes == '' || shownotes == '<p><br></p>' ) && $('.cfm-captivate-editor').is(":visible") && clicked_button != "episode_draft" ) {
			$( '#cfm-field-wpeditor' ).addClass( 'invalid-control is-invalid' );
			$( '.cfm-episode-shownotes .ql-toolbar.ql-snow' ).addClass('is-invalid');
			if ( ! $( '#captivate-shownotes-error' ).length ) {
				$( '<div id="captivate-shownotes-error" class="invalid-feedback">You must enter show notes for your episode.</div>' ).insertAfter( '#cfm-field-wpeditor' );
			}
			errors += 1;
			error_feedback += '<br>Episode Show NOTES: Check for any unusual or invalid characters, remove and resave.<br>';
		}

		if ( $('.cfm-wordpress-editor').is(":visible") && clicked_button != "episode_draft" ) {

			var wordpress_editor_shownotes = '';

			if ( $('#wp-post_content_wp-wrap').hasClass('html-active') ) {
				wordpress_editor_shownotes = $('#post_content_wp').val();
			}
			else {
				var activeEditor = tinymce.get('post_content_wp');
				wordpress_editor_shownotes = activeEditor.getContent();
			}

			if ( wordpress_editor_shownotes == '' ) {
				$( '#wp-post_content_wp-wrap' ).addClass( 'invalid-control' );
				if ( ! $( '#wp-shownotes-error' ).length ) {
					$( '<div id="wp-shownotes-error" class="invalid-feedback">You must enter show notes for your episode.</div>' ).insertAfter( '#wp-post_content_wp-wrap' );
				}
				errors += 1;
				error_feedback += '<br>Episode Show NOTES: Check for any unusual or invalid characters, remove and resave.<br>';
			}
		}

		if ( seo_description.length > 300 ) {
			$('#seo_description').addClass('is-invalid');
			errors += 1;
			error_feedback += '<br>SEO Description: length must be less than or equal to 300 characters long.<br>';
		}

		var artwork_id = $( 'input[name=episode_artwork_id]' ).val(),
			artwork_width = $( 'input[name=episode_artwork_width]' ).val(),
			artwork_height = $( 'input[name=episode_artwork_height]' ).val(),
			artwork_type = $( 'input[name=episode_artwork_type]' ).val(),
			artwork_filesize = $( 'input[name=episode_artwork_filesize]' ).val();
		if ( artwork_id != '' &&
			( artwork_width != 3000 ||
			artwork_height != 3000 ||
			artwork_filesize > 2097152 ||
			( artwork_type != "image/jpeg" && artwork_type != "image/jpg" && artwork_type != "image/png" ) ) ) {

			$('#cfm-artwork-uploader').addClass('invalid-control');

			if ( ! $( '#cfm-artwork-uploader-error' ).length ) {
				$('<div id="cfm-artwork-uploader-error" class="invalid-feedback mt-4">Your artwork must be exactly 3,000 x 3,000 pixels and less than 2MB (ideally below 512kb) in filesize. Only JPG or PNG images are allowed.</div>').insertAfter('#cfm-artwork-uploader');
			}

			errors += 1;
			error_feedback += '<br>Episode Artwork: Follow the artwork specifications, remove and reupload.<br>';
		}

		if ( ! $(this).validateACF() ) {
			$('#acf-fields').addClass('is-invalid');
			if ( ! $( '#acf-fields-error' ).length ) {
				$( '<div id="acf-fields-error" class="invalid-feedback">There is an issue with some of your ACF fields.</div>' ).insertAfter('#acf-fields');
			}
			errors += 1;
			error_feedback += '<br>ACF: There is an issue with some of your fields.<br>';
		}

		if ( errors > 0 ) {
			cfmsync_toaster('error', '<strong>Could not save - a setting is invalid</strong>' + error_feedback);
			$('button[type=submit]').prop('disabled', false);
			$('#episode-cancel').removeClass('disabled');
			$this.html($this_html);
			e.preventDefault();
			return false;
		}

	});
	$(document).on('click', 'button[name="episode_draft"]', function(e) {
		clicked_button = 'episode_draft';
		$('input[name="submit_action"]').val('draft');
	});
	$(document).on('click', 'button[name="episode_update"]', function(e) {
		clicked_button = 'episode_update';
		$('input[name="submit_action"]').val('update');
	});
	$(document).on('click', 'button[name="episode_publish"]', function(e) {
		clicked_button = 'episode_publish';
		$('input[name="submit_action"]').val('publish');
	});

	$(document).on('keyup', '#post_title', function(e) {
		if ( $(this).val() != '' ) {
			$(this).removeClass( 'is-invalid' );
			$( '#post_title-error' ).remove();
		}
	});

	if ( 'on' == $('#enable_wordpress_editor').val() ) {
		if ( $('#wp-post_content_wp-wrap').hasClass('html-active') ) {
			$(document).on('keyup', '#post_content_wp', function(e) {
				if ( $(this).val() != '' ) {
					$('#wp-post_content_wp-wrap').removeClass( 'invalid-control is-invalid' );
					$('#wp-shownotes-error').remove();
				}
			});
		}
		else {
			var activeEditor = tinymce.get('post_content_wp');
			if ( activeEditor!==null) {
				activeEditor.on('keyup',function(e){
					$('#wp-post_content_wp-wrap').removeClass( 'invalid-control is-invalid' );
					$('#wp-shownotes-error').remove();
				});
			}
		}
	}

	$ (document).on('keyup', '#seo_description', function(e) {
		var seo_description_width = $(this).val().length < 155 ? $(this).val().length / 155 * 100 : 100;
		var seo_description_color = "orange";
		if(seo_description_width >= 50 && seo_description_width <= 99) {
			seo_description_color = "#29ab57";
		} else if(seo_description_width >= 100) {
			seo_description_color = "#dc3545";
		}
		$('.cfm-seo-description-progress').css( "background-color", seo_description_color );
		$('.cfm-seo-description-progress').css( "width", seo_description_width + '%' );

	});

	/**
	 * Generate slug
	 */
	$(document).on('focus', '#post_title.post-title-empty', function(e) {
		$this = $(this);

		$this.blur(function() {
			if ( $this.hasClass('post-title-empty') ) {
				$('input[name=post_name]').val(cfm_convert_to_slug($this.val()));

				if ( $this.val() != '' ) {
					$this.removeClass('post-title-empty');
				}
			}
		});
	});

	/**
	 * Edit slug
	 */
	$(document).on('keyup', 'input[name=post_name]', function(e) {
		$(this).val(cfm_convert_to_slug($(this).val()));
	});

	/**
	 * Add category
	 */
	$(document).on('click', '#add-website-category', function(e) {
		e.preventDefault();

		var category_parent   = $('select[name=category_parent]').val(),
			category          = $('input[name=website_category]').val();

		if ( category != '' ) {
			$.ajax({
				url: cfmsync.ajaxurl,
				type: 'post',
				dataType: 'json',
				data: {
					action: 'add-webcategory',
					category_parent: category_parent,
					category: category,
					_nonce: cfmsync.ajaxnonce
				},
				success: function(response) {
					if ( 'error' == response ) {
						cfmsync_toaster('error', 'Something went wrong. Please refresh the page and try again.');
					}
					else {
						$('.cfm-website-categories-wrap > ul').prepend(response.cat_checklist);

						$('.cfm-category-parent').html(response.cat_parent);

						$('select[name=category_parent]').prop('selectedIndex', 0);
						$('input[name=website_category]').val('');

						cfmsync_toaster('success', 'Category has been successfully added and selected.');
					}
				},
				error: function(response) {
					cfmsync_toaster('error', 'Category already exists.');
				}
			});
		}
		else {
			$('input[name=website_category]').addClass('is-invalid is-sub-validation').focus();
		}

		e.preventDefault();
	});

	/**
	 * Add tags
	 */
	$(document).on('click', '#add-website-tags', function(e) {
		e.preventDefault();

		var tags = $('input[name=website_tags]').val(),
			tags_array = tags.split(','),
			tags_input = [],
			tags_input_lower = [],
			tags_existing = [];

		for ( i=0;i<tags_array.length;i++ ) {
			tags_input_lower.push($.trim(tags_array[i].toLowerCase()));
		}

		$('.cfm-website-tags-wrap ul li label').each(function() {
			var tags_check = $.trim($(this).text().toLowerCase());

			// check mark existing tags.
			if ( $.inArray(tags_check, tags_input_lower) !== -1 ) {
				$(this).find('input[type=checkbox]').prop('checked', true);
			}

			tags_existing.push(tags_check);
		});

		// get new tags.
		for (i=0;i<tags_array.length;i++) {
			var new_tags_lower = $.trim(tags_array[i].toLowerCase());

			if ( $.inArray(new_tags_lower, tags_existing) == -1 ) {
				tags_input.push($.trim(tags_array[i]));
			}
		}

		if ( tags_input.length !== 0 ) {
			$.ajax({
				url: cfmsync.ajaxurl,
				type: 'post',
				data: {
					action: 'add-webtags',
					tags: tags_input.toString(),
					_nonce: cfmsync.ajaxnonce
				},
				success: function(response) {
					if ( 'error' == response ) {
						cfmsync_toaster('error', 'Something went wrong. Please refresh the page and try again.');
					}
					else {
						$('.cfm-website-tags-wrap > ul').prepend(response);

						$('input[name=website_tags]').val('');
						cfmsync_toaster('success', 'Tag(s) has been successfully added and selected.');
					}
				}
			});
		}
		else {
			$( 'input[name=website_tags]').val('');
		}

		if (tags == '') {
			$('input[name=website_tags]').addClass('is-invalid is-sub-validation').focus();
		}

		e.preventDefault();
	});

	/**
	 * Transcript defaults
	 */
	var transcript_file = 'input[name=transcript_file]',
		transcript_text = 'textarea[name=transcript_text]',
		transcript_current = 'textarea[name=transcript_current]',
		transcript_type = 'input[name=transcript_type]',
		transcript_updated = 'input[name=transcript_updated]',
		transcript_add_default = '<a id="transcript-add" data-bs-toggle="modal" data-bs-target="#transcript-modal" href="#"><i class="fal fa-file-alt me-2"></i> Add a transcript to this episode </a>',
		transcript_upload_default = '<div class="transcript-text">Have a transcript file? Upload it directly... </div><a id="upload-transcript" href="javascript: void(0);"><i class="fal fa-cloud-upload" aria-hidden="true"></i> Upload File</a>';

	/**
	 * Transcript upload
	 */
	$(document).on('click', '#upload-transcript', function(e) {
		$(transcript_file).focus().trigger('click');
	});

	/**
	 * Transcript update
	 */
	$(document).on('click', '#update-transcript', function(e) {
		if ( $(transcript_file).get(0).files.length === 0 ) {
			if ('' != $(transcript_text).val()) {
				var transcript_text_new = '<strong>' + cfm_truncate($(transcript_text).val(), 20) + '</strong> <a id="cfm-transcript-edit" class="float-end" data-bs-toggle="modal" data-bs-target="#transcript-modal" href="#"><i class="fal fa-edit"></i> Edit</a><div class="mt-2"><a id="transcript-remove" class="transcript-remove text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a></div>';
			}
			else {
				var transcript_text_new = transcript_add_default;
			}

			$(transcript_current).val($(transcript_text).val());
			$(transcript_type).val('text');
		}
		else {
			var filename = $(transcript_file).val().replace(/C:\\fakepath\\/i, '');

			var transcript_text_new = '<strong>' + filename + '</strong> <a id="cfm-transcript-edit" class="float-end" data-bs-toggle="modal" data-bs-target="#transcript-modal" href="#"><i class="fal fa-undo fa-flip-horizontal"></i> Replace</a><div class="mt-2"><a id="transcript-remove" class="transcript-remove text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a></div>';

			$(transcript_current).val(filename);
			$(transcript_type).val('file');
		}

		$(transcript_updated).val('1');

		$('.cfm-episode-transcription .cmf-transcript-wrap').html(transcript_text_new);
		$("#transcript-modal").modal('hide');
	});

	/**
	 * Transcript cancel
	 */
	$(document).on('click', '#cancel-transcript, #close-transcript', function(e) {
		if ( 'file' == $(transcript_type).val() ) {
			$(transcript_text).val('');
			$('.transcript-upload-box').html('<div class="transcript-text">File uploaded: <strong>' + $(transcript_current).val() + '</strong></div><a id="remove-transcript-file" class="text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a>');
			$(transcript_text).prop('disabled', true);
			$('.transcript-upload-box').removeClass('disabled');
		}
		else {
			$(transcript_text).val($(transcript_current).val());
			$('.transcript-upload-box').html(transcript_upload_default);
			$('.transcript-upload-box').addClass('disabled');
			$(transcript_text).prop('disabled', false);
		}
	});

	/**
	 * Transcript remove
	 */
	$(document).on('click', '#transcript-remove', function(e) {
		$(transcript_text).val('');
		$(transcript_file).val('');
		$(transcript_current).val('');
		$(transcript_updated).val('1');
		$(transcript_text).prop('disabled', false);
		$('.transcript-upload-box').removeClass('disabled');

		$('.cfm-episode-transcription .cmf-transcript-wrap').html(transcript_add_default);
		$('.transcript-upload-box').html(transcript_upload_default);
	});

	/**
	 * Enable/disable upload/text
	 */
	$(document).on('change keyup', transcript_text, function(e) {
		if ( $(this).val() != '' ) {
			$('.transcript-upload-box').addClass('disabled');
			$('#update-transcript').prop('disabled', false);
		}
		else {
			$('.transcript-upload-box').removeClass('disabled');
			$('#update-transcript').prop('disabled', true);
		}
	});

	$( document ).on('change', transcript_file, function(e) {
		if ( $(this).get(0).files.length === 0 ) {
			$(transcript_text).prop('disabled', false);

			$('.transcript-upload-box').html(transcript_upload_default);
			$('#update-transcript').prop('disabled', true);
		}
		else {
			var filename = $(this).val().replace(/C:\\fakepath\\/i, '');

			$(transcript_text).prop('disabled', true);

			$('.transcript-upload-box').html('<div class="transcript-text">File uploaded: <strong>' + filename + '</strong></div><a id="remove-transcript-file" class="text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a>');
			$('#update-transcript').prop('disabled', false);
		}
	});

	/**
	 * Transcript file remove
	 */
	$(document).on('click', '#remove-transcript-file', function(e) {
		$(transcript_file).val('');
		$(transcript_file).trigger('change');
		$('#update-transcript').prop('disabled', false);
	});

	/**
	 * Transcript modal
	 */
	$('#transcript-modal').on('show.bs.modal', function (e) {
		$('#update-transcript').prop('disabled', true);
	});

	/**
	 * Change button text
	 */
	$(window).load(function() {
		if ( cfm_is_datetime_future($( "input[name=publish_date]" ).val() + ' ' + $( "input[name=publish_time]" ).val()) === true ) {
			$('button[name=episode_update] , button[name=episode_publish]').html('Schedule Episode');
		}
	});

	/**
	 * Field validation
	 */
	$(document).on('keyup', '.form-control.is-invalid', function(e) {
		if ( $(this).val() != '' ) {
			$(this).removeClass('is-invalid is-sub-validation');
		}
	});

	$(document).on('focus', '.form-control.is-sub-validation', function(e) {
		$(this).blur(function() {
			$(this).removeClass('is-invalid is-sub-validation');
		});
	});

	/**
	 * Duplicate episode
	 */
	 $(document).on('click', '#cfm-duplicate-episode', function(e) {
		e.preventDefault();

		var $this = $(this),
			$this_html = $this.html(),
			post_id = $this.attr('data-reference'),
			_nonce = $this.attr('data-nonce');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'duplicate-episode',
				_nonce: _nonce,
				post_id: post_id,
			},
			dataType: 'json',
			beforeSend: function() {
				$this.prop('disabled', true);
				$this.siblings('button').prop('disabled', true);
				$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Duplicating episode...');
			},
			success: function(response) {
				$this.prop('disabled', false);
				$this.siblings('button').prop('disabled', false);
				$this.html($this_html);
				$('#confirmation-modal').modal('hide');

				if ( 'success' == response.output ) {
					cfmsync_toaster('success', response.message);
					window.location.replace(response.redirect_url);
				}
				else {
					cfmsync_toaster('error', response.message);
				}
			}
		});

		e.preventDefault();
    });

	$('#acf-modal').on('hide.bs.modal', function (e) {
		if(!$(this).validateACF()) {
			e.preventDefault();
		}
		else {
			$('#acf-fields').removeClass('is-invalid');
			$('.cfm-website-acf').find('.acf-fields-error').remove();
		}
	});

	/**
	 * Validate ACF fields
	 */
	$.fn.validateACF = function() {
		var errors = 0;

		$('.modal-field-groups-wrap .acf-field').each(function() {
			var field_value = '';
			var is_required = $(this).hasClass('required');

			// For wysiwyg - switch to HTML for the textarea to update to get the latest value
			$(this).find('.switch-html').click();

			if ($(this).find('input[type="text"], input[type="number"], input[type="range"], input[type="email"], input[type="url"]').length) {
				field_value = $(this).find('input').val();
			} else if ($(this).find('textarea').length) {
				field_value = $(this).find('textarea').val();
			} else if ($(this).find('select').length) {
				field_value = $(this).find('select').val();
			} else if ($(this).find('input[type="radio"]:checked').length) {
				field_value = $(this).find('input[type="radio"]:checked').val();
			} else if ($(this).find('.acf-wysiwyg-container').length) {
				field_value = tinymce.get($(this).find('.wp-editor-area')).getContent();
			}

			// For wysiwyg - revert to TMCE
			$(this).find('.switch-tmce').click();

			// Check if the field is required and if the value is empty
			if (is_required && !field_value) {
				$(this).addClass('is-invalid');
				errors += 1;
				if ( ! $(this).find( '.acf-field-feedback' ).length ) {
					$(this).append('<div class="acf-field-feedback invalid-feedback">This field is required.</div>');
				}
			} else if ($(this).find('input, textarea').attr('maxlength')) {
				var maxlength = parseInt($(this).find('input, textarea').attr('maxlength'));
				if (field_value.length > maxlength) {
					$(this).addClass('is-invalid');
					errors += 1;
					if (!$(this).find('.acf-field-feedback').length) {
						$(this).append('<div class="acf-field-feedback invalid-feedback">This field cannot exceed ' + maxlength + ' characters.</div>');
					}
				} else {
					$(this).removeClass('is-invalid');
					$(this).find('.acf-field-feedback').remove();
				}
			} else {
				$(this).removeClass('is-invalid');
				$(this).find('.acf-field-feedback').remove();
			}

			// validate email.
			if ( $(this).hasClass('acf-field-type-email') && (is_required || field_value) ) {
				if ( !cfm_validate_email(field_value) ) {
					$(this).addClass('is-invalid');
					errors += 1;
					if ( ! $(this).find('.acf-field-feedback').length ) {
						$(this).append('<div class="acf-field-feedback invalid-feedback">Please enter a valid email address.</div>');
					}
					else {
						$(this).find('.acf-field-feedback').html('Please enter a valid email address.');
					}
				}
				else {
					$(this).removeClass('is-invalid');
					$(this).find('.acf-field-feedback').remove();
				}
			}

			// Validate number and range steps.
			if ( $(this).hasClass('acf-field-type-number') && (is_required || field_value) ) {

				var $input = $(this).find('input');
				var min = parseFloat($input.attr('min'));
				var max = parseFloat($input.attr('max'));
				var step = parseFloat($input.attr('step'));

				// Validate if the value is within the min and max range
				if (field_value < min) {
					errors += 1;
					$(this).addClass('is-invalid');
					if ( ! $(this).find('.acf-field-feedback').length ) {
						$(this).append('<div class="acf-field-feedback invalid-feedback">The value must be greater than or equal to ' + min + '.</div>');
					}
				} else if (field_value > max) {
					errors += 1;
					$(this).addClass('is-invalid');
					if ( ! $(this).find('.acf-field-feedback').length ) {
						$(this).append('<div class="acf-field-feedback invalid-feedback">The value must be less than or equal to ' + max + '.</div>');
					}
				}
				else if ( !isNaN(step) && step > 0 && field_value % step !== 0 ) {
					errors += 1;
					$(this).addClass('is-invalid');
					if (!$(this).find('.acf-field-feedback').length) {
						$(this).append('<div class="acf-field-feedback invalid-feedback">The value must be a multiple of ' + step + '.</div>');
					}
				} else {
					$(this).removeClass('is-invalid');
					$(this).find('.acf-field-feedback').remove();
				}
			}

			// validate URL and oEmbed.
			if ( ($(this).hasClass('acf-field-type-url') || $(this).hasClass('acf-field-type-oembed') ) && (is_required || field_value) ) {

				if ( !cfm_validate_url(field_value) ) {
					$(this).addClass('is-invalid');
					errors += 1;
					if ( ! $(this).find('.acf-field-feedback').length ) {
						$(this).append('<div class="acf-field-feedback invalid-feedback">Please enter a valid URL.</div>');
					}
					else {
						$(this).find('.acf-field-feedback').html('Please enter a valid URL.');
					}
				}
				else {
					$(this).removeClass('is-invalid');
					$(this).find('.acf-field-feedback').remove();
				}
			}

		});

		if (errors > 0) {
			$('.modal-body-acf').animate({
				scrollTop: $('.is-invalid').first().position().top + $('.modal-body-acf').scrollTop() - 100  // Adjust scroll position
			}, 500);
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * Variable confirmation modal
	 */
	 $('.cfm-insert-variable-modal').on('show.bs.modal', function (e) {
		var button = $(e.relatedTarget),
			reference_id = button.data('confirmation-reference'),
			data_type = button.data('type'),
			modal = $(this);
		modal.find('.modal-body input[name=dt_type]').val(['dynamic']);
		modal.find('.modal-footer .modal-confirm').attr('data-reference', reference_id);
		modal.find('.modal-footer .modal-confirm').attr('data-type', data_type);
	});
	$('.cfm-insert-variable-modal').on('hidden.bs.modal', function (e) {
		var modal = $(this);
		modal.find('.modal-footer .modal-confirm').removeAttr('data-reference');
		modal.find('.modal-footer .modal-confirm').removeAttr('data-type');
	});

	/**
	 * LOCALSTORAGE - save shownotes wordpress editor every 5 seconds.
	 */
	if ( $.inArray(cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1 ) {
		setInterval(function () {
			const enable_wordpress_editor_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'enable_wordpress_editor');
			if ( 'on' == enable_wordpress_editor_local ) {
				tinymce.triggerSave();
				var content_html =  '';

				if ( $('#wp-post_content_wp-wrap').hasClass('html-active') ) {
					content_html =  $("#post_content_wp").val();
				}
				else {
					var activeEditor = tinymce.get('post_content_wp');
					if ( activeEditor!==null) {
						content_html = activeEditor.getContent();
					}
				}

				localStorage.setItem(cfmsync.CFMH_SHOWID + '_post_content_wp_local', content_html);
			}
		}, 5*1000);
	}

	/**
	 * LOCALSTORAGE - populate fields.
	 */
	$(window).load(function() {

		$('.cfm-shownotes-editor').fadeIn();

		if ( $.inArray( cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1 ) {

			const enable_wordpress_editor_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'enable_wordpress_editor');
			const post_content_wp_local = localStorage.getItem(cfmsync.CFMH_SHOWID + '_post_content_wp_local');
			const media_url_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'media_url');
			const media_name_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'media_name');
			const media_bit_rate_str_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'media_bit_rate_str');
			const media_duration_str_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'media_duration_str');

			// populate post_content_wp.
			if ( 'on' == enable_wordpress_editor_local ) {
				$( '#enable_wordpress_editor' ).trigger('click');
			}

			if ( 'on' == enable_wordpress_editor_local && ( '' != post_content_wp_local && undefined !== post_content_wp_local && null !== post_content_wp_local ) ) {

				if ( $('#wp-post_content_wp-wrap').hasClass('html-active') ) {
					$('#post_content_wp').val(post_content_wp_local);
				}
				else {
					var activeEditor = tinymce.get('post_content_wp');
					if ( activeEditor!==null ) { // Make sure we're not calling setContent on null.
						activeEditor.setContent(post_content_wp_local);
					}
				}
			}

			// show audio.
			if ( '' != media_url_local && undefined !== media_url_local && null !== media_url_local ) {
				$( '#cfm-audio-uploader .dropzone-uploader' ).hide();
				$('#cfm-audio-uploader .dropzone-result').html( '<audio controls="controls" preload="none"><source type="audio/mpeg" src="' + media_url_local + '"> Your browser does not support the audio element. </audio><div class="dropzone-result-info d-flex justify-content-between"><div class="result-info"><strong>' + media_name_local +'</strong> <br>' + media_bit_rate_str_local + ' | ' + media_duration_str_local + '</div><div class="result-actions"><button class="replace-audio btn btn-outline-dark">Replace audio file</button></div></div>' );

				$('#episode_draft, #episode_update').prop('disabled', false);
			}

			// populate artwork.
			const artwork_url_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'episode_artwork');
			if ( '' != artwork_url_local && undefined !== artwork_url_local && null !== artwork_url_local ) {
				$('#cfm-artwork-uploader .fd-uploader').hide();
				$('#cfm-artwork-uploader .fd-replace').show();
				$('#cfm-artwork-uploader .fd-result').html('<img src="' + artwork_url_local + '" width="200" height="200" class="img-fluid">');
			}

			// populate image uploader.
			$('.fake-dropzone.cfm-image-uploader').each(function () {
				var $this = $(this),
					$fd_uploader = $this.find('.fd-uploader'),
					$fd_replace = $this.find('.fd-replace'),
					$fd_result = $this.find('.fd-result');
					$('input.fd-input-image-id').attr('name');

				var image_url_input = $this.find('.fd-input-image-url').attr('name');
				var image_url = $(document).cfmGetLocalStorage('cfm-form-publish-episode', image_url_input);

				if ( '' != image_url && undefined !== image_url && null !== image_url ) {
					$fd_uploader.hide();
					$fd_replace.show();
					$fd_result.html('<img src="' + image_url + '" width="200" height="200" class="img-fluid">');
				}
			});

			// show apple podcasts title if checked.
			const itunes_title_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'itunes_title');
			if ( '' != itunes_title_local && undefined !== itunes_title_local && null !== itunes_title_local ) {
				$('input[name=post_title_check]').prop('checked', true);
				$('.cfm-itunes-title').show();
			}
			else {
				$('input[name=post_title_check]').prop('checked', false);
			}

			// clear tags and categories input.
			$('select[name=category_parent]').val('-1');
			$('input[name=website_category]').val('');
			$('input[name=website_tags]').val('');
		}

		if ( $.inArray(cfmsync.CFMH_CURRENT_SCREEN, edit_episode_screens) !== -1 ) {
			var action_result = cfm_get_url_vars()["action"],
				eid = cfm_get_url_vars()["eid"];

			// LOCALSTORAGE - clear all.
			if ( 'published' == action_result || 'failed' == action_result ) {
				// local-storage.js
				var key = cfmsync.CFMH_SHOWID + '_cfm-form-publish-episode_save_storage';
				localStorage.removeItem(key);

				// custom.
				localStorage.removeItem(cfmsync.CFMH_SHOWID + '_post_content_wp_local');

				// quilljs.js.
				localStorage.removeItem(cfmsync.CFMH_SHOWID + '_shownotes_local');
				localStorage.removeItem(cfmsync.CFMH_SHOWID + '_shownotes_local_html');

				// Update URL to remove response and action params
				var new_url = cfmsync.CFMH_ADMINURL + 'admin.php?page=cfm-hosting-edit-episode&show_id=' + cfmsync.CFMH_SHOWID + '&eid=' + eid;
				setTimeout(function() {
					window.history.pushState(null, null, new_url);
				}, 2000);
			}
		}

	});

});