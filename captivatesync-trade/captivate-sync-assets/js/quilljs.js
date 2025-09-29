/**
 * QuilJs
 * Used to generate our QuillJs powered shownotes section.
 */

 jQuery(document).ready(function($){

	/**
	 * Episode show notes quill
	 */
	var publish_episode_screens = ['toplevel_page_cfm-hosting-publish-episode', 'admin_page_cfm-hosting-publish-episode', 'captivate-sync_page_cfm-hosting-publish-episode'];

	const DynamicText = Quill.import('blots/embed');
	class VariableEmbed extends DynamicText {
		static create(data) {
			const node = super.create(data);
			node.setAttribute('data-dt-name', data);
			node.innerHTML = '{{'+data+'}}';
			return node;
		}
		static value(node) {
			return node.getAttribute('data-dt-name');
		}
	}
	VariableEmbed.blotName = 'variable';
	VariableEmbed.tagName = 'dt-variable';
	Quill.register('formats/variable', VariableEmbed);

	const StaticText = Quill.import('blots/block/embed');
	class StaticEmbed extends StaticText {
		static create(data) {
			const node = super.create(data);
			node.setAttribute('contenteditable', false);
			node.setAttribute('data-dt-name', 'cfm-static-variable-e7ef859fa5c6');
			node.innerHTML = data;
			return node;
		}
		static value(node) {
			return node.innerHTML;
		}
	}
	StaticEmbed.blotName = 'static';
	StaticEmbed.tagName = 'dt-static';
	Quill.register('formats/static', StaticEmbed);

	var quill = '',
		quill_container = '#cfm-field-wpeditor',
		quill_editor = $(quill_container),
		quill_post_content = document.querySelector('textarea[name=post_content]'),
		is_text_changed = false,
		render_count = 0;

	if ( $( quill_container ).length ) {

		quill = new Quill( quill_container, {
			modules: {
				toolbar: '#quilljs-toolbar'
			},
			placeholder: 'Insert text here ...',
			theme: 'snow'
		});
		quill.root.setAttribute('spellcheck', false);

		var form = document.querySelector('#cfm-form-publish-episode');

		form.onsubmit = function() {
			var ql_html = quill_editor.find('.ql-editor').html();

			// Populate hidden field on submit.
			quill_post_content.value = revertDynamicText(ql_html);
		};

		quill.on('text-change', function() {
			var	ql_html = quill_editor.find('.ql-editor').html();

			if ( ql_html != '' && ql_html != '<p><br></p>' ) {
				$('#cfm-field-wpeditor' ).removeClass('invalid-control is-invalid');
				$('.cfm-episode-shownotes .ql-toolbar.ql-snow').removeClass('is-invalid');
				$('#shownotes-error').remove();
			}

			// LOCALSTORAGE - save custom localstorage.
			if ( $.inArray(cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1 ) {
				localStorage.setItem(cfmsync.CFMH_SHOWID + '_shownotes_local', JSON.stringify(quill.getContents()));
				localStorage.setItem(cfmsync.CFMH_SHOWID + '_shownotes_local_html', revertDynamicText(ql_html));
			}

			quill_post_content.value = revertDynamicText(ql_html);
			render_count++;
			if ( render_count > 1 ) {
				is_text_changed = true;
			}
			//quill.history.ignoreChange = false;
		});

		// Trigger blur-like behavior when the click is outside the editor.
		$(document).on('mousedown touchstart', function(event) {
			if ( !$(event.target).closest('.cfm-captivate-editor').length ) {
				if ( is_text_changed ) {
					$(document).renderVariables();
				}
			}
		});

		// Expand editor.
		$('.cfm-captivate-editor .expand').click(function() {
			if ($(quill_container).height() === 640) {
				$(quill_container).height(340);
				$(this).html('Expand Writing Area <i class="fa-regular ms-1 fa-expand"></i>');
			} else {
				$(quill_container).height(640);
				$(this).html('Reduce Writing Area <i class="fa-regular ms-1 fa-arrows-minimize"></i>');
			}
		});

		// LOCALSTORAGE - populate custom localstorage.
		if ( $.inArray( cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1 ) {
			quill.setContents(JSON.parse(localStorage.getItem(cfmsync.CFMH_SHOWID + '_shownotes_local')));
		}
	}

	// copy data-dt-name value only instead of the full custom blot.
	quill.clipboard.addMatcher('dt-variable', (node, delta) => {
		const Delta = Quill.import('delta');
		let ops = delta.ops;
		return new Delta().insert('{{'+ops[0].insert['variable']+'}}');
	});

	/**
	 * Show/hide default shortcodes
	 */
	$('.dt-show-custom-shortcodes input[type=checkbox]').on('change', function (e) {
		if ( this.checked ) {
			$('.dropdown-contents .dropdown-item:not(.dt-custom-shortcode)').hide();
		}
		else {
			$('.dropdown-contents .dropdown-item').show();
		}
	});

	/**
	 * Change shownotes template
	 */
	$(document).on('click', '#cfm-change-shownotes-template', function(e) {
		e.preventDefault();

		var $this = $(this),
			$this_html = $this.html(),
			data_reference = $this.attr('data-reference');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'change-shownotes-template',
				show_id: cfmsync.CFMH_SHOWID,
				template_name: data_reference,
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

				if ( 'error' == response ) {
					cfmsync_toaster('error', 'Something went wrong! Please contact the support team.');
				}
				else {
					cfmsync_toaster('success', 'Show Notes Template applied.');

					if ( $('#enable_wordpress_editor:checked').length == $('#enable_wordpress_editor').length ) {
						if ( $('#wp-post_content_wp-wrap').hasClass('html-active') ) {
							$('#post_content_wp').val(response);
						}
						else {
							var activeEditor = tinymce.get('post_content_wp');
							if ( activeEditor!==null ) {
								activeEditor.setContent(response);
							}
						}
					}
					else {
						quill.root.innerHTML = response;
						document.querySelector('textarea[name=post_content]').value = response;
					}
				}

				$(document).renderVariables();
			}
		});

		e.preventDefault();
    });

	/**
	 * Insert block
	 */
	 $(document).on('click', '#cfm-insert-dt-block', function(e) {
		e.preventDefault();

		var $this = $(this),
			$this_html = $this.html(),
			data_reference = $this.attr('data-reference'),
			post_id = $('input[name=post_id]').val(),
			dt_type = $('input[name=dt_type]:checked').val(),
			quill_selection = quill.selection.savedRange.index;

		if ( 'dynamic' == dt_type ) {
			if ( '' != data_reference || 'undefined' != data_reference ) {
				quill.insertEmbed(quill_selection, 'variable', data_reference);
				$('dt-variable').contents().unwrap();
				setTimeout(() => quill.setSelection(quill_selection + 1, 0), 0);
				setTimeout(() => quill.insertText(quill_selection + 1, ' '), 1);
			}
			$('#cfm-insert-block-modal').modal('hide');
		}
		else {
			$.ajax({
				url: cfmsync.ajaxurl,
				type: 'post',
				data: {
					action: 'insert-static-block',
					show_id: cfmsync.CFMH_SHOWID,
					post_id: post_id,
					data_reference: data_reference,
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
					$('#cfm-insert-block-modal').modal('hide');

					if ( 'error' == response ) {
						cfmsync_toaster('error', 'Something went wrong! Please contact the support team.');
					}
					else {
						if ( response ) {
							$e_title = $('input[name=post_title]').val();
							$e_number = $('input[name=episode_number]').val();
							$e_season = $('input[name=season_number]').val();
							$e_type = $('input[name=episode_type]:checked').val();
							$e_explicit = $('input[name=episode_explicit]:checked').val();

							$episode_title = ( '' != $e_title ) ? $e_title : 'Untitled Episode';
							response = response.replaceAll('{{d-episode-title}}', $episode_title);

							$episode_number = ( '' != $e_number ) ? $e_number : '(No episode number)';
							response = response.replaceAll('{{d-episode-number}}', $episode_number);

							$episode_season = ( '' != $e_season ) ? $e_season : '(Not in a season)';
							response = response.replaceAll('{{d-episode-season}}', $episode_season);

							response = response.replaceAll('{{d-episode-type}}', cfm_ucwords($e_type));

							$episode_explicit = ( '0' != $e_explicit ) ? $e_explicit : $('input[name=episode_explicit]:checked').attr('data-explicit-default');
							response = response.replaceAll('{{d-episode-explicit}}', cfm_ucwords($episode_explicit));

							const range = quill.getSelection();
							quill.insertEmbed(range.index, 'static', response);
							$('dt-static').contents().unwrap();
							quill.setSelection(range.index + 1);
						}
					}
				}
			});
		}

		$(document).renderVariables();

		e.preventDefault();
    });

	/**
	 * Insert shortcode
	 */
	 $(document).on('click', '#cfm-insert-dt-shortcode', function(e) {
		e.preventDefault();

		var $this = $(this),
			$this_html = $this.html(),
			data_reference = $this.attr('data-reference'),
			data_type = $this.attr('data-type'),
			post_id = $('input[name=post_id]').val(),
			dt_type = $('input[name=dt_type]:checked').val(),
			quill_selection = quill.selection.savedRange.index;

		if ( 'dynamic' == dt_type ) {
			if ( '' != data_reference || 'undefined' != data_reference ) {
				quill.insertEmbed(quill_selection, 'variable', data_reference);
				$('dt-variable').contents().unwrap();
				setTimeout(() => quill.setSelection(quill_selection + 1, 0), 0);
				setTimeout(() => quill.insertText(quill_selection + 1, ' '), 1);
			}
			$('#cfm-insert-shortcode-modal').modal('hide');
		}
		else {
			$.ajax({
				url: cfmsync.ajaxurl,
				type: 'post',
				data: {
					action: 'insert-static-shortcode',
					show_id: cfmsync.CFMH_SHOWID,
					post_id: post_id,
					data_reference: data_reference,
					data_type: data_type,
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
					$('#cfm-insert-shortcode-modal').modal('hide');

					if ( 'error' == response ) {
						cfmsync_toaster('error', 'Something went wrong! Please contact the support team.');
					}
					else {
						if ( response ) {
							$e_title = $('input[name=post_title]').val();
							$e_number = $('input[name=episode_number]').val();
							$e_season = $('input[name=season_number]').val();
							$e_type = $('input[name=episode_type]:checked').val();
							$e_explicit = $('input[name=episode_explicit]:checked').val();

							$episode_title = ( '' != $e_title ) ? $e_title : 'Untitled Episode';
							response = response.replaceAll('{{d-episode-title}}', $episode_title);

							$episode_number = ( '' != $e_number ) ? $e_number : '(No episode number)';
							response = response.replaceAll('{{d-episode-number}}', $episode_number);

							$episode_season = ( '' != $e_season ) ? $e_season : '(Not in a season)';
							response = response.replaceAll('{{d-episode-season}}', $episode_season);

							response = response.replaceAll('{{d-episode-type}}', cfm_ucwords($e_type));

							$episode_explicit = ( '0' != $e_explicit ) ? $e_explicit : $('input[name=episode_explicit]:checked').attr('data-explicit-default');
							response = response.replaceAll('{{d-episode-explicit}}', cfm_ucwords($episode_explicit));

							const range = quill.getSelection();
							quill.insertEmbed(range.index, 'static', response);
							$('dt-static').contents().unwrap();
							quill.setSelection(range.index + 1);
						}
					}
				}
			});
		}

		$(document).renderVariables();


		e.preventDefault();
    });

	/**
	 * Insert conditional shortcode
	 */
	 $(document).on('click', '#cfm-dropdown-dt-shortcodes .dt-conditional', function(e) {
		e.preventDefault();

		var $this = $(this),
			data_reference = $this.attr('data-reference'),
			quill_selection = quill.selection.savedRange.index;

			quill.insertEmbed(quill_selection, 'variable', data_reference);
			quill.insertEmbed(quill_selection+1, 'variable', 'd-condition-end');
			$('dt-variable').contents().unwrap();
			setTimeout(() => quill.insertText(quill_selection + 1, '  '), 0);
			setTimeout(() => quill.setSelection(quill_selection + 2, 0), 1);

		$(document).renderVariables();

		e.preventDefault();
    });

	function parseAttributes(string) {

		var pattern = /<dt-variable\s*([^>]*)\s*\/?>/g;
		var result;
		var count = 0;
		var output = new Array();
		while((result = pattern.exec(string)) !== null) {
			output[count] = result[1];
			count++;
		}
		return output;
	}

	function revertDynamicText(content) {
		output = content;
		get_all_data_dt_name_value = parseAttributes( content );
		get_all_data_dt_name_value.forEach(val => {
			// get value from data-dt-name="value".
			const dt_val = val.match(/"(.*?)"/);

			search = new RegExp("<dt-variable " + val + ">(.*?)<\/dt-variable>", "g");
			replace = '{{'+dt_val[1]+'}}';
			output = output.replace(search, replace);
		});
		return output;
	}

	$.fn.renderVariables = function() {

		const shownotes_textarea = document.querySelector('textarea[name=post_content]');
		shownotes = shownotes_textarea.value;

		const reg = /{{([^{}]*)}}/g;
		var result;

		while ((result = reg.exec(shownotes)) !== null) {
			// translate only valid slug.
			const pattern = /^[A-Za-z0-9]+(?:[_-][A-Za-z0-9]+)*$/g;

			if ( pattern.test(result[1]) ) {
				quill.history.ignoreChange = true;
				dt = '<dt-variable data-dt-name="'+cfm_convert_to_slug(result[1])+'">Loading...</dt-variable>';

				dt_result = result[1].split('-');
				dt_result = dt_result[0]+'-'+dt_result[1];
				if ( dt_result == 'd-condition' ) {
					dt = '<dt-variable data-dt-name="'+cfm_convert_to_slug(result[1])+'" data-conditional-depth="1">Loading...</dt-variable>';
				}

				shownotes = shownotes.replace(result[0], dt);
			}
		}

		/* increment depth to the d-condition variables */
		// Parse the input string as HTML.
		const parser = new DOMParser();
		const htmlDoc = parser.parseFromString(shownotes, 'text/html');

		// Get all the <dt-variable> elements.
		const dt_variable_elements = htmlDoc.querySelectorAll('dt-variable');

		// Initialize the depth counter.
		let depth = 1;

		// Loop through all the <dt-variable> elements.
		for (let i = 0; i < dt_variable_elements.length; i++) {

			const dt_variable_element = dt_variable_elements[i];

			var attribute_value = dt_variable_element.getAttribute('data-dt-name');

			// make sure only d-condition variables will have a depth attribute.
			if (attribute_value.indexOf("d-condition") !== -1) {

				// If the element has a data-dt-name attribute of "d-condition-end", decrease the depth counter.
				if (dt_variable_element.getAttribute('data-dt-name') === 'd-condition-end') {
					depth--;
				}

				// Add the current depth counter to the data-depth attribute of the element
				dt_variable_element.setAttribute('data-conditional-depth', depth);

				// If the element has a data-dt-name attribute that is not "d-condition-end", increase the depth counter.
				if (dt_variable_element.getAttribute('data-dt-name') !== 'd-condition-end') {
					depth++;
				}

				quill.history.ignoreChange = false;
			}
		}

		// Get the updated HTML markup.
		const output_string = htmlDoc.body.innerHTML;

		quill.root.innerHTML = output_string;
		setTimeout(() => quill.setSelection(quill.selection.savedRange.index, 0), 0);

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'render-dt-variables',
				show_id: cfmsync.CFMH_SHOWID,
				post_id: $('input[name=post_id]').val(),
				content: shownotes_textarea.value,
			},
			success: function(response) {
				if ( 'error' == response ) {
					cfmsync_toaster('error', 'Error loading the editor, please refresh the page.');
				}
				else {
					var dt_vars = JSON.parse(response);

					$.each(dt_vars, function (i, val) {
						// quill.history.ignoreChange = true;
						quill.history.ignoreChange = false;
						var name_human = ( null != val && '' != val ) ? val : 'Unrecognized Variable';
						$('[data-dt-name='+ cfm_convert_to_slug(i) + '] span').text(name_human);
					});

				}
			}
		});

		is_text_changed = false;
	};

	$(document).renderVariables();

});