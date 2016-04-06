<?php
/**
 * Member Edit Template
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
            
            <?php var_dump( current_user_can( 'edit_post', 19625 ) ); global $current_user; var_dump( $current_user ); ?>

            <?php
                $profile_pic = ( $user->data !== 'add-new-user' ) ? get_user_meta( $user_id, 'pyis_profile_image', true ) : false;

                if ( ! empty( $profile_pic ) ) {
                    $image = wp_get_attachment_image_src( $profile_pic, 'thumbnail' );
                }
            ?>

            <div class="pyis-image-wrapper alignleft text-center">
                <img id="pyis-profile-image" src="<?php echo ! empty( $profile_pic ) ? $image[0] : get_avatar_url( $user->ID, array( 'size' => 150 ) ); ?>" style="max-width: 150px; max-height: 150px;" />
                <br />
                <input type="button" data-id="pyis-profile-image-id" data-src="pyis-profile-image" class="button" id="pyis-profile-image-upload" value="<?php _e( 'Upload', PyisMemberProfile::$plugin_id ); ?>" />
                <input type="button" data-id="pyis-profile-image-id" data-src="pyis-profile-image" class="button" id="pyis-profile-image-default" value="<?php _e( 'Reset to Default', PyisMemberProfile::$plugin_id ); ?>" />
            </div>

            <h3 style="margin-top: 0;"><?php echo $user_data->first_name; ?> <?php echo $user_data->last_name; ?></h3>

            <?php 

            $course_id = get_theme_mod( 'pyis_course', 0 );

            $register_date = new DateTime( $user_data->user_registered );
            $course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
            $course_progress = ( $course_progress[ $course_id ]['completed'] / $course_progress[ $course_id ]['total'] ) * 100;

            ?>

            <?php echo apply_filters( 'the_content', 
                sprintf( 
                    __( 'PyImageSearch Gurus Member Since %s', PyisMemberProfile::$plugin_id ), 
                    $register_date->format( 'F jS, Y' ) 
                ) 
            ); ?>

            <?php echo apply_filters( 'the_content', 
                sprintf( 
                    __( 'Course Progress: %g%%', PyisMemberProfile::$plugin_id ), 
                    number_format( $course_progress, 2, '.', ',' )
                ) 
            ); ?>

            <?php echo apply_filters( 'the_content', 
                 sprintf( 
                     __( 'Completed Course: %s', PyisMemberProfile::$plugin_id ),
                     ( learndash_course_completed( $user_id, $course_id ) ? __( 'Yes', PyisMemberProfile::$plugin_id ) : __( 'No', PyisMemberProfile::$plugin_id ) ) 
                 ) 
            ); ?>

            <strong><?php _e( 'About Me:', PyisMemberProfile::$plugin_id ); ?></strong>
            <?php echo apply_filters( 'the_content', get_user_meta( $user_id, 'mepr_user_message', true ) ); ?>

            <strong><?php _e( 'Skills:', PyisMemberProfile::$plugin_id ); ?></strong>
            <?php echo apply_filters( 'the_content', 'blahblabhablabhablha' ); ?>

            <?php echo get_user_meta( $user_id, 'twitter', true ); ?>
            
        </div>

    </div>
</div>