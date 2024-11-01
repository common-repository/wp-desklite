( function ( $ ) {
	"use strict";

	// Trigger update button.
	$( document.body ).on( 'click', '#wpdl-update', function( event ) {
		event.preventDefault();
		$( '#publish' ).trigger( 'click' );
	} );

	// Tooltips
	$( document.body ).on( 'wpdl-init-tooltips', function() {
		$( '.wpdl-tip' ).tipTip( {
			'attribute': 	'title',
			'fadeIn': 		50,
			'fadeOut': 		50,
			'delay': 		200
		} );
		$( '.wpdl-help-tip' ).tipTip( {
			'attribute': 	'data-tip',
			'fadeIn': 		50,
			'fadeOut': 		50,
			'delay': 		200
		} );
	} ).trigger( 'wpdl-init-tooltips' );

	// Selectize
	$( document.body ).on( 'wpdl-init-selects', function() {
		$( '.wpdl-select' ).selectize( {
			dropdownParent: 'body',
			allowClear: true,
		} );

		$( '.wpdl-select-multi' ).selectize( {
			dropdownParent: 'body',
			plugins: [ 'remove_button', 'drag_drop' ],
			delimiter: ',',
			persist: false,
			create: function( input ) {
				return {
					value: input,
					text: input
				}
			}
		} );

	} ).trigger( 'wpdl-init-selects' );

} )(jQuery);