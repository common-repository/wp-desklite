( function ( $ ) {
	"use strict";

	/** Submit a reply or note **/
	$( document.body ).on( 'click', '.wpdl-ajax-link', function( event ) {
		event.preventDefault();
		
		var el 			= $( this ),
			id 			= el.attr( 'data-id' ),
			action 		= el.attr( 'data-action' ),
			security	= wpdl_frontend.ajax_nonce,
			div			= el.parents( '.wpdl-reply' );

		div.block( { message: null, overlayCSS: { 'background-color' : 'rgba(255,255,255,0.8)' } } );

		$.ajax( { type: 'post', url: wpdl_frontend.ajax_url, data: { action: action, security: security, id: id },
			success: function ( response ) {
				div.unblock();
				div.hide();
			}
		} );

		return false;
	} );

	/** Submit a reply or note **/
	$( document.body ).on( 'click', '.wpdl-ajax', function( event ) {
		event.preventDefault();
		var ticket 		= $( this ).attr( 'data-ticket' ),
			type 		= $( this ).hasClass( 'wpdl-add-note' ) ? 'ticket_note' : 'ticket_reply',
			content 	= tinymce.get( '_wpdl_reply' ).getContent(),
			div     	= $( this ).parents( '.wpdl-element' ),
			priority 	= div.find( '#_wpdl_set_priority' ).val(),
			resolved 	= div.find( '#_wpdl_set_resolved' ).is( ':checked' ) ? 'yes' : 'no',
			wrap		= $( this ).parents( '.wpdl-area' ).find( '.wpdl-thread' );

		if ( ! content || ! ticket ) {
			return false;
		}

		// Get data.
		var data = new FormData();
		data.append( 'action', 'wpdl_add_ticket_reply' );
		data.append( 'security', wpdl_frontend.ajax_nonce );
		data.append( 'ticket', ticket );
		data.append( 'priority', priority );
		data.append( 'resolved', resolved );
		data.append( 'content', content );
		data.append( 'type', type );

		if ( div.find( '#_wpdl_files' ).length ) {
			$.each( div.find( '#_wpdl_files' )[0].files, function( i, file ) {
				data.append( 'attachment_' + i, file );
			} );
		}

		div.block( { message: null, overlayCSS: { 'background-color' : 'rgba(255,255,255,0.8)' } } );
		$.ajax( {
			type: 			'post',
			url: 			wpdl_frontend.ajax_url,
			contentType:	false,
			processData: 	false,
			data: 			data,
			success: function ( response ) {
				div.unblock();
				div.find( 'input:file' ).val( '' );
				tinymce.get( '_wpdl_reply' ).setContent( '' );
				if ( response ) {
					wrap.append( response );
				}
			}
		} );

		return false;
	} );

} )(jQuery);