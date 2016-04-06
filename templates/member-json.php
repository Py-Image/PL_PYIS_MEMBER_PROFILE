<?php
/**
 * Members JSON Output
 *
 * @since 1.0
 * @package pyis-member-profile
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

// Since we are not a real Page and have no context of where we came from, we need to grab the User from the URL

$url = str_replace( '?json', '', $_SERVER['REQUEST_URI'] );

$user_name = explode( '/', $url );
$user_name = $user_name[ count( $user_name ) - 1 ];

$user = get_user_by( 'login', $user_name );
$user_id = $user->data->ID;

// Now that we have queried the User from the URL, we can access a lot more data
$user_data = get_userdata( $user_id );
$course_id = get_theme_mod( 'pyis_course', 0 );

$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
$course_progress = ( $course_progress[ $course_id ]['completed'] / $course_progress[ $course_id ]['total'] ) * 100;

$data = array(
    'last_name' => $user_data->last_name,
    'first_name' => $user_data->first_name,
    'course_progress' => $course_progress,
    'course_completed' => learndash_course_completed( $user_id, $course_id ),
    'url' => get_bloginfo( 'url' ) . $url,
);

die( json_encode( $data ) );