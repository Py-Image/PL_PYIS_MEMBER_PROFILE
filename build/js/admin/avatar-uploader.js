jQuery( document ).ready( function( $ ) {

    /* WP Media Uploader */
    var _pyis_media = true;
    var _orig_send_attachment = wp.media.editor.send.attachment;

    $( '#pyis-profile-image-upload' ).click( function() {

        var button = $( this ),
            textbox_id = $( this ).attr( 'data-id' ),
            image_id = $( this ).attr( 'data-src' ),
            _pyis_media = true;

        wp.media.editor.send.attachment = function( props, attachment ) {

            if ( _pyis_media && ( attachment.type === 'image' ) ) {
                if ( image_id.indexOf( "," ) !== -1 ) {
                    image_id = image_id.split( "," );
                    $image_ids = '';
                    $.each( image_id, function( key, value ) {
                        if ( $image_ids )
                            $image_ids = $image_ids + ',#' + value;
                        else
                            $image_ids = '#' + value;
                    } );

                    var current_element = $( $image_ids );
                } else {
                    var current_element = $( '#' + image_id );
                }

                $( '#' + textbox_id ).val( attachment.id );
                current_element.attr( 'src', attachment.url ).show();
            } else {
                alert( 'Please select a valid image file' );
                return false;
            }
        }

        wp.media.editor.open( button );
        return false;
        
    } );
    
    $( '#pyis-profile-image-default' ).click( function( event ) {

        var textbox_id = $( this ).attr( 'data-id' ),
            image_id = $( this ).attr( 'data-src' );
        
        $( '#' + textbox_id ).val( '' );
        $( '#' + image_id ).attr( 'src', $( '#' + image_id ).data( 'default' ) );
        
    } );

} );
