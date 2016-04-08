jQuery( document ).ready( function( $ ) {

    var $crop = $( '#image-cropper' );

    if ( $crop.length > 0 ) {

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

        $( '.cropit-save-image' ).click( function() {
            
            // Don't reduce quality
            var imageData = $( '#image-cropper' ).cropit( 'export', {
                type: 'image/jpeg',
                quality: 1,
                originalSize: true,
            } );

            // Cropit is great and all, but it doesn't resize the image nicely. Pica will get rid of these jaggities.
            
            var imgSrc = $crop.cropit( 'imageSrc' );
            var offset = $crop.cropit( 'offset' );
            var zoom = $crop.cropit( 'zoom' );
            var previewSize = $crop.cropit( 'previewSize' );
            var exportZoom = $crop.cropit( 'exportZoom' );

            var img = new Image();
            img.src = imgSrc;
            
            // Draw image in original size on a canvas
            var originalCanvas = document.createElement( 'canvas' );
            originalCanvas.width = previewSize.width / zoom;
            originalCanvas.height = previewSize.height / zoom;
            var ctx = originalCanvas.getContext( '2d' );
            ctx.drawImage(img, offset.x / zoom, offset.y / zoom);

            // We're saving images as 150x150
            var zoomedCanvas = document.createElement( 'canvas' );
            zoomedCanvas.width = 150;
            zoomedCanvas.height = 150;
            
            pica.resizeCanvas(originalCanvas, zoomedCanvas, {
                // Pica options, see https://github.com/nodeca/pica
            }, function( err ) {
                if ( err ) { return console.log(err); }
                // Resizing completed
                // Read resized image data
                var picaImageData = zoomedCanvas.toDataURL();
                
                $( '#pyis-profile-image' ).removeAttr( 'srcset' );
                $( '#pyis-profile-image' ).attr( 'src', picaImageData );
                $( '#pyis_profile_image_data' ).val( picaImageData );
                
            } );
            
        } );

    }

} );
