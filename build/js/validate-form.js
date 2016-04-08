jQuery( function( $ ) {
    
    $( document ).ready( function() {
    
        var $form = $( '#pyis-profile-edit-form' );
        $form[0].noValidate = true;

        if ( $form.length > 0 ) {

            $form.submit( function( event ) {
                
                // Assume form is invalid to start
                var valid = false;
                
                // Hide the Submission Error Message if shown
                $( '.pyis-error-message' ).hide();
                
                // Count the invalid fields
                var invalidFields = 0;
                
                $( '.pyis-validate-url' ).each( function( index, element ) {
                    
                    $( element ).removeClass( 'validation-error' );
                    $( element )[0].setCustomValidity( '' );
                    $( element ).next( '.pyis-error-hint' ).html( '' );
                    
                    if ( 
                        ( $( element ).val().length > 0 ) 
                        && ( $( element ).val().indexOf( $( element ).data( 'validate' ) ) == -1 )
                    ) {
                        
                        $( element ).addClass( 'validation-error' );
                        
                        var invalidText = 'Your entry must contain "' + $( element ).data( 'validate' ) + '"';
                        $( element )[0].setCustomValidity( invalidText );
                        $( element ).next( '.pyis-error-hint' ).html( invalidText );
                        
                        invalidFields++;
                        
                    }
                    
                } );
                
                $form[0].reportValidity();
                
                if ( invalidFields == 0 ) {
                    valid = true;
                }
                else {
                    
                    $( '.pyis-error-message' ).show();
                    
                }
                
                return valid;
                
            } );

        }
        
    } );
    
} );