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

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$number_per_page = 4;

get_header();
?>

<div class="x-container max width offset">
    <div class="full-width" role="main">

        <?php 

        $user_query = new WP_User_Query(
            array(
                'role' => 'subscriber',
                'number' => $number_per_page,
                'meta_key' => 'last_name',
                'orderby' => 'meta_value',
                'order' => 'ASC',
                'paged' => $paged,
            )
        );

        if ( ! empty( $user_query->results ) ) : ?>

            <table>
                
                <thead>
                    <th></th>
                    <th><?php _e( 'Name', PyisMemberProfile::$plugin_id ); ?></th>
                    <th><?php _e( 'Course Progress', PyisMemberProfile::$plugin_id ); ?></th>
                    <th><?php _e( 'Graduated', PyisMemberProfile::$plugin_id ); ?></th>
                </thead>

            <?php foreach ( $user_query->results as $user ) :

                $user_data = get_userdata( $user->data->ID ); 
                
                $course_progress = get_user_meta( $user->data->ID, '_sfwd-course_progress', true );
                $course_progress = ( $course_progress[386]['completed'] / $course_progress[386]['total'] ) * 100;
                
                ?>

                <tr>
                    
                    <td>
                        <a href="/members/<?php echo strtolower( rawurlencode( $user_data->first_name ) ); ?>_<?php echo strtolower( rawurlencode( $user_data->last_name ) ); ?>" title="<?php echo $user_data->first_name; ?> <?php echo $user_data->last_name; _e( "'s Profile", PyisMemberProfile::$plugin_id ); ?>">
                            <?php echo get_avatar( $user_data->user_email ); ?>
                        </a>
                    </td>
                    <td>
                        <a href="/members/<?php echo strtolower( rawurlencode( $user_data->first_name ) ); ?>_<?php echo strtolower( rawurlencode( $user_data->last_name ) ); ?>" title="<?php echo $user_data->first_name; ?> <?php echo $user_data->last_name; _e( "'s Profile", PyisMemberProfile::$plugin_id ); ?>">
                            <?php echo "$user_data->last_name, $user_data->first_name"; ?>
                        </a>
                    </td>
                    <td><?php echo sprintf( '%g%%', number_format( $course_progress, 2, '.', ',' ) ); ?></td>
                    <td><?php echo ( learndash_course_completed( $user->data->ID, 386 ) ? __( 'Yes', PyisMemberProfile::$plugin_id ) : __( 'No', PyisMemberProfile::$plugin_id ) ) ; ?></td>

                </tr>

            <?php endforeach; ?>

            </table>
        
            <?php $total_pages = ceil( $user_query->total_users / $number_per_page ); ?>

            <div class="x-pagination">
                <?php echo paginate_links( array(
                    'current' => $paged,  
                    'total' => $total_pages,  
                    'prev_text' => __( '&laquo;', PyisMemberProfile::$plugin_id ),
                    'next_text' => __( '&raquo;', PyisMemberProfile::$plugin_id ),
                    'type' => 'list',
                ) ); ?>
            </div>
        
        <?php else :
        
        _e( 'No Members Found Matching Your Request', PyisMemberProfile::$plugin_id );

        endif; ?>
        
    </div>
    
</div>

<?php get_footer();