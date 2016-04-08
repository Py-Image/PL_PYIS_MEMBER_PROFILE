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

// Sometimes extra parameters that aren't our ?json one may get added. In this case, let's ignore them.
$user_name = explode( '?', $user_name );
$user_name = $user_name[0];

$user = get_user_by( 'login', $user_name );

$user_id = $user->data->ID;

// Now that we have queried the User from the URL, we can access a lot more data
// We're declaring it as Global so we can easily use this information for <title> and <meta> tags
global $pyis_user_data;
$pyis_user_data = get_userdata( $user_id );

get_header();

if ( $user->roles[0] == 'subscriber' ) :

    if ( get_current_user_id() == $user_id ) {
        include( PyisMemberProfile::pyis_locate_template( 'member-edit.php' ) );
    }
    else {
        include( PyisMemberProfile::pyis_locate_template( 'member-public.php' ) );
    }
            
else :
    include( PyisMemberProfile::pyis_locate_template( 'member-not-found.php' ) );
endif;

get_footer();