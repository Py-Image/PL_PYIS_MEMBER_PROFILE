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
$full_name = explode( '/', $_SERVER['REQUEST_URI'] );
$full_name = $full_name[ count( $full_name ) - 1 ];
$full_name = explode( '_', $full_name );

$first_name = urldecode( $full_name[0] );
$last_name = urldecode( $full_name[1] );

$user_query = new WP_User_Query(
    array(
        'role' => 'subscriber',
        'number' => 1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'     => 'first_name',
                'value'   => $first_name,
                'compare' => 'LIKE'
            ),
            array(
                'key'     => 'last_name',
                'value'   => $last_name,
                'compare' => 'LIKE'
            )
        )
    )
);

$user = $user_query->results[0];

// Now that we have queried the User from the URL, we can access a lot more data
global $user_data;
$user_data = get_userdata( $user->data->ID );

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
            $course_progress = get_user_meta( $user->data->ID, '_sfwd-course_progress', true );
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
                     ( learndash_course_completed( $user->data->ID, $course_id ) ? __( 'Yes', PyisMemberProfile::$plugin_id ) : __( 'No', PyisMemberProfile::$plugin_id ) ) 
                 ) 
            ); ?>

            <strong><?php _e( 'About Me:', PyisMemberProfile::$plugin_id ); ?></strong>
            <?php echo apply_filters( 'the_content', get_user_meta( $user->data->ID, 'mepr_user_message', true ) ); ?>

            <strong><?php _e( 'Skills:', PyisMemberProfile::$plugin_id ); ?></strong>
            <?php echo apply_filters( 'the_content', 'blahblabhablabhablha' ); ?>
            
            <?php echo get_user_meta( $user->data->ID, 'twitter', true ); ?>
            
        </div>

    </div>
</div>

<?php get_footer();