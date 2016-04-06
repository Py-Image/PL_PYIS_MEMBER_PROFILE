jQuery( document ).ready( function( $ ) {

    $( '#pyis-profile-image-upload' ).click( function() {

        var button = $( this ),
            textbox_id = $( this ).attr( 'data-id' ),
            image_id = $( this ).attr( 'data-src' );
        
        var frame;
        
        if ( frame ) {
            frame.open();
            return;
        }
        
        frame = wp.media( {
            'title': 'Upload a New Avatar',
            'button': {
                text: 'Select Avatar',
            },
            'library': {
                type: 'image',
            },
            'multiple': false,
        } );
        
        frame.on( 'select', function() {
            
            var attachment = frame.state().get( 'selection' ).first().toJSON();
            
            $( '#' + textbox_id ).val( attachment.id );
            $( '#' + image_id ).attr( 'src', attachment.url );
            
        } );
        
        frame.open();
        
        return false;
        
    } );
    
    $( '#pyis-profile-image-default' ).click( function( event ) {

        var textbox_id = $( this ).attr( 'data-id' ),
            image_id = $( this ).attr( 'data-src' );
        
        $( '#' + textbox_id ).val( '' );
        $( '#' + image_id ).attr( 'src', $( '#' + image_id ).data( 'default' ) );
        
    } );

} );
