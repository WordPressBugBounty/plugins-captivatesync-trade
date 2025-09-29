jQuery( document ).ready(function($) {

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

});
