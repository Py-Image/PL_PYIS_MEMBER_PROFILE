<?php
/**
 * Public Member Page
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
            
            <div class="pyis-profile-top x-column x-sm x-1-1">
                
                <div class="pyis-avatar-container alignleft">

                    <?php echo get_avatar( $user_id, 150, array( 'extra_attr' => 'id="pyis-profile-image"' ) ); ?>
                    
                </div>

                <h3 style="margin-top: 0;"><?php echo $pyis_user_data->first_name; ?> <?php echo $pyis_user_data->last_name; ?></h3>

                <?php 

                $course_id = get_theme_mod( 'pyis_course', 0 );

                $register_date = new DateTime( $pyis_user_data->user_registered );
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
                
            </div>
            
            <div class="pyis-profile-bottom x-column x-sm x-1-1">

                <strong><?php _e( 'About Me:', PyisMemberProfile::$plugin_id ); ?></strong>
                <?php echo apply_filters( 'the_content', get_user_meta( $user_id, 'description', true ) ); ?>

                <strong><?php _e( 'Skills:', PyisMemberProfile::$plugin_id ); ?></strong>
                <?php echo apply_filters( 'the_content', 'blahblabhablabhablha' ); ?>

                <?php echo get_user_meta( $user_id, 'twitter', true ); ?>
                
            </div>
            
        </div>

    </div>
</div>