jQuery( document ).ready( function( $ ) {

	$(document).on('click', '.cfm-episodes-loadmore button', function(e) {
		e.preventDefault();

		$button = $(this);
		$button_html = $button.html();

		$shortcode_container = $button.parent().prop('id');
		$shortcode_id = $(this).attr('data-shortcode-id');
		$shortcode_atts = $(this).attr('data-shortcode-atts');
		$max_page = $(this).attr('data-max-page');
		$current_page = $(this).attr('data-current-page');
		$current_page++;

		$button.prop('disabled', true).html('Loading...');

		$.ajax({
			url: cfmsync_front.ajaxurl,
			type: 'post',
			data: {
				action: 'shortcode-loadmore',
				_nonce: cfmsync_front.ajaxnonce,
				shortcode_id: $shortcode_id,
				shortcode_atts: JSON.parse($shortcode_atts),
				max_page: $max_page,
				current_page: $current_page
			},
			success: function(response) {

				if ( 'no_more' == response ) {
					$button.remove();
				}
				else if ( 'nothing_found' == response ) {
					//console.log('nothing_found');
				}
				else {
					$button.prop('disabled', false).html($button_html);
					$('#cfm-episodes-' + $shortcode_id).append(response);

					$button.attr('data-current-page', $current_page);

					if ( $current_page == $max_page )
						$button.remove();
				}

			}
		} );


	});

});