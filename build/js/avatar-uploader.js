jQuery( document ).ready( function( $ ) {
    
    var $crop = $( '#image-cropper' );

    $crop.cropit( {
        imageBackground: true,
    } );
    
    $( '.rotate-cw' ).click( function() {
        $( '#image-cropper' ).cropit( 'rotateCW' );
    } );
    
    $( '.rotate-ccw' ).click( function() {
        $( '#image-cropper' ).cropit( 'rotateCCW' );
    } );
    
    $( '.cropit-select-image' ).click( function() {
      $( '.cropit-image-input' ).click();
    } );
    
    $( '.cropit-save-image' ).click(function() {
      var imageData = $( '#image-cropper' ).cropit( 'export' );
      console.log( imageData );
    } );

} );
