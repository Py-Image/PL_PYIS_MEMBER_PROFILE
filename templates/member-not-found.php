<?php
/**
 * Member Not Found Template
 *
 * @since 1.0
 * @package pyis-member-profile
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die;
} 

get_header();

?>

<div class="pyis-member-profile-nothing-found-container x-container max width offset">
    <div class="full-width" role="main">
        
        <div class="pyis-profile-top x-column x-sm x-1-1">
            
            <div class="pyis-member-profile-nothing-found alignleft x-column x-sm x-4-5">
            
                <?php echo apply_filters( 'pyis_member_profile_nothing_found', __( 'No Member Found For Your Request', PyisMemberProfile::$plugin_id ) ); ?>
                
            </div>
            
            <div class="pyis-member-nothing-found-links alignright x-column x-sm x-1-5">
                
                <a href="/member-directory" class="x-btn x-btn-regular"><?php _e( 'Back to Member Directory', PyisMemberProfile::$plugin_id ); ?></a>
                
            </div>
            
        </div>
        
    </div>
</div>