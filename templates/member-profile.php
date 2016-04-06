<?php
/**
 * Members Directory Template
 *
 * @since 1.0
 * @package pyis-member-profile
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

global $wp_query;

// Tricking WP Core functions into thinking we're a real Page.
$wp_query->is_404 = false;
$wp_query->is_page = true;

// Since we are not a real Page and have no context of where we came from, we need to grab the User from the URL
$user_name = explode( '/', $_SERVER['REQUEST_URI'] );
$user_name = $user_name[ count( $user_name ) - 1 ];

$user = get_user_by( 'login', $user_name );
$user_id = $user->data->ID;

// Now that we have queried the User from the URL, we can access a lot more data
global $user_data;
$user_data = get_userdata( $user_id );

get_header();
?>

<div class="x-container max width offset">
    <div class="full-width" role="main">
        
        <!-- Pushes content more toward center -->
        <div class="x-container max width offset">
        
            <?php echo get_avatar( $user_data->user_email, 150, false, '', array( 'class' => 'alignleft' ) ); ?>

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

<?php get_footer();