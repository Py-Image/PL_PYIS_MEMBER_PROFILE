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
} ?>

<div class="x-container max width offset">
    <div class="full-width" role="main">
        
        <!-- Pushes content more toward center -->
        <div class="x-container max width offset">
            <?php echo apply_filters( 'pyis_member_profile_nothing_found', __( 'No Member Found For Your Request', PyisMemberProfile::$plugin_id ) ); ?>
        </div>
        
    </div>
</div>