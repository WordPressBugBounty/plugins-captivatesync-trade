jQuery( document ).ready(
	function( $ ) {

			/**
			 * Default
			 */
			$( '#cfm-datatable' ).DataTable(
				{
					searching: false,
					ordering:  true,
					bInfo:  false,
					bLengthChange: false,
					bFilter: true,
					bAutoWidth: false,
					pageLength: 20,
					fnDrawCallback: function() {
						 var paginateRow = $( 'div.dataTables_paginate' );
						 var pageCount   = Math.ceil( (this.fnSettings().fnRecordsDisplay()) / this.fnSettings()._iDisplayLength );

						if ( pageCount > 1 ) {
							paginateRow.css( "display", "block" );
						} else {
							paginateRow.css( "display", "none" );
						}
					}
				}
			);

			/**
			 * Podcast Episodes
			 */
			$( "#cfm-datatable-episodes" ).one(
				"preInit.dt",
				function () {
					$( "#cfm-datatable-episodes_filter input" ).addClass('form-control search');
					$( "#cfm-datatable-episodes_filter" ).append( '<div class="filter-actions"><a href="' + cfmsync.CFMH_ADMINURL + 'admin.php?page=cfm-hosting-publish-episode&show_id=' + cfmsync.CFMH_SHOWID + '" class="btn btn-primary">Publish New Episode <i class="fal fa-podcast ms-lg-2"></i></a></div>' );
				}
			);

			$( '#cfm-datatable-episodes' ).DataTable(
				{
					searching: true,
					ordering:  true,
					bInfo:  true,
					bLengthChange: false,
					bFilter: true,
					bAutoWidth: false,
					pageLength: 20,
					order: [[ 2, "desc" ]],
					columnDefs: [
						{bSortable: false, targets: [4]}
					],
      				responsive: true,
					pagingType: 'full_numbers',
					language: {
						paginate: {
							previous: '<i class="fal fa-chevron-left"></i>',
							next: '<i class="fal fa-chevron-right"></i>',
							first: '<i class="fal fa-chevron-double-left"></i>',
							last: '<i class="fal fa-chevron-double-right"></i>'
						},
						search: '<i class="fal fa-search"></i>',
						searchPlaceholder: 'Search your episodes...'
					},
					fnInfoCallback: function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
						return "Showing <strong>" + iStart + " to " + iEnd + "</strong> of " + iTotal;
					},
					fnDrawCallback: function() {
						var paginateRow = $( 'div.dataTables_paginate' );
						var pageCount   = Math.ceil( (this.fnSettings().fnRecordsDisplay()) / this.fnSettings()._iDisplayLength );

						if ( pageCount > 1 ) {
							paginateRow.css( "display", "block" );
						} else {
							paginateRow.css( "display", "none" );
						}
					}
				}
			);

	}
);
