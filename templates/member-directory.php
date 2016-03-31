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

get_header();
?>

<?php 

$users = new WP_User_Query(
    array(
        'role' => 'subscriber',
        'number' => -1,
        'meta_key' => 'last_name',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    )
);

$users = $users->get_results();

foreach ( $users as $user ) {
    
    $user_data = get_userdata( $user->data->ID );
    
    echo "$user_data->first_name $user_data->last_name";
    echo '<br />';
    
}

?>

<?php
get_footer();