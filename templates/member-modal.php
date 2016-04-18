<?php
/**
 * Member Avatar Upload Modal
 *
 * @since 1.0
 * @package pyis-member-profile
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die;
} ?>

<div class="foundation reveal" id="pyis-image-upload-modal" data-reveal data-v-offset="0">
    <h3><?php echo apply_filters( 'pyis_member_avatar_title', __( 'Upload a New Profile Picture', PyisMemberProfile::$plugin_id ) ); ?></h3>

    <div id="image-cropper" class="x-container">
        
        <div class="x-column x-sm x-1-2">
            <div class="cropit-preview"></div>

            <div class="controls-wrapper x-container">
                <div class="rotate-controls x-column x-sm x-1-3">
                    <span class="rotate-ccw x-icon-rotate-left" data-x-icon="&#xf0e2"></span>
                    <span class="rotate-cw x-icon-rotate-right" data-x-icon="&#xf01e"></span>
                </div>
                <div class="zoom-control x-column x-sm x-2-3">
                    <span class="x-icon-picture-o small-icon" data-x-icon="&#xf03e"></span>
                    <input type="range" class="cropit-image-zoom-input" min="0" max="1" step="0.01">
                    <span class="x-icon-picture-o large-icon" data-x-icon="&#xf03e"></span>
                </div>
            </div>
        </div>
        
        <div class="x-column x-sm x-1-2">
            <?php 
            $cropping_instructions = apply_filters( 'pyis_member_avatar_cropping', __( '<ol><li>Click the "Upload" button to select a new profile picture.</li><li>Crop, zoom, and position your profile picture.</li><li>Click "Done" to set your new profile picture.</li></ol>', PyisMemberProfile::$plugin_id ) );
            echo apply_filters( 'the_content', $cropping_instructions ); ?>
            <input type="file" class="cropit-image-input" multiple="false" />
            <button class="cropit-select-image"><?php _e( 'Upload', PyisMemberProfile::$plugin_id ); ?></button>
            <button class="cropit-save-image" data-close><?php _e( 'Done', PyisMemberProfile::$plugin_id ); ?></button>
        </div>

    </div>

    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>