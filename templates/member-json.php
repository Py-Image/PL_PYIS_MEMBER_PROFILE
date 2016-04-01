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

$full_name = explode( '/', $url );
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
$user_data = get_userdata( $user->data->ID );

$data = array(
    'last_name' => $user_data->last_name,
    'first_name' => $user_data->first_name,
    'url' => get_bloginfo( 'url' ) . $url,
);

die( json_encode( $data ) );