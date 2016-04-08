jQuery( function( $ ) {
    
    $( document ).ready( function() {
    
        var $form = $( '#pyis_profile_edit' );
        $form[0].noValidate = true;

        if ( $form.length > 0 ) {

            $form.submit( function( event ) {
                
                event.preventDefault();
                
                // Assume form is invalid to start
                var valid = false;
                
                // Count the invalid fields
                var invalidFields = 0;
                
                $( '.pyis-validate-url' ).each( function( index, element ) {
                    
                    $( element ).removeClass( 'validation-error' );
                    $( element ).next( '.pyis-error-message' ).html( '' );
                    
                    if ( $( element ).val().indexOf( $( element ).data( 'validate' ) ) == -1 ) {
                        
                        $( element ).addClass( 'validation-error' );
                        
                        var invalidText = 'Your entry must contain "' + $( element ).data( 'validate' ) + '"';
                        $( element )[0].setCustomValidity( invalidText );
                        $( element ).next( '.pyis-error-message' ).html( invalidText );
                        
                        invalidFields++;
                        
                    }
                    
                } );
                
                console.log( invalidFields );
                
                $form[0].reportValidity();
                
                if ( invalidFields == 0 ) {
                    //valid = true;
                }
                
                return valid;
                
            } );

        }
        
    } );
    
} );