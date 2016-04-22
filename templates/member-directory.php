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
$number_per_page = 15;
$course_id = get_theme_mod( 'pyis_course', 0 );

get_header();
?>

<div class="pyis-member-directory-container x-container max width offset entry-wrap">
    <div class="full-width" role="main">
        
        <?php if ( $course_id == 0 ) : ?>
        
            <div class="pyis-error-message">
                <?php echo apply_filters( 'pyis_profile_course_not_set', sprintf( __( 'Course Not Set In <a href="%s">Customizer</a>', PyisMemberProfile::$plugin_id ), admin_url( 'customize.php?autofocus[control]=pyis_course' ) ) ); ?>
            </div>
        
        <?php endif; ?>
        
        <form method="GET" action="/member-directory/" class="pyis-directory-form-search x-container max width offset">
            <div class="x-container max width offset">
                <div class="x-column x-sm x-1-6 pyis-directory-form-label">
                    <label><?php echo apply_filters( 'pyis_member_directory_form_label', __( 'Filter Members:', PyisMemberProfile::$plugin_id ) ); ?></label>
                </div>
                <div class="x-column x-sm x-1-3 pyis-directory-form-search-field">
                    <label for="s" class="visually-hidden"><?php _e( 'Search by First or Last Name', PyisMemberProfile::$plugin_id ); ?></label>
                    <input type="text" class="search-query" name="s" value="<?php echo $_GET['s']; ?>" placeholder="<?php _e( 'Search by First or Last Name', PyisMemberProfile::$plugin_id ); ?>" />
                </div>
                <div class="x-column x-sm x-1-6 pyis-directory-form-order">
                    <label for="order"><?php _e( 'Filter By:', PyisMemberProfile::$plugin_id ); ?>
                        <select name="order">
                            <?php
                                $options = array(
                                    'asc' => __( 'Last Name A-Z', PyisMemberProfile::$plugin_id ),
                                    'desc' => __( 'Last Name Z-A', PyisMemberProfile::$plugin_id ),
                                );
                            
                                if ( isset( $_GET['order'] ) ) :
                                    $set_value = trim( strtolower( $_GET['order'] ) );
                                else :
                                    $set_value = 'asc';
                                endif;
                            
                                foreach( $options as $key => $value ) : ?>
                                    
                                    <option value="<?php echo $key; ?>"<?php echo ( $set_value == $key ? ' selected' : '' ); ?>><?php echo $value; ?></option>
                                    
                                <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="x-column x-sm x-1-6 pyis-directory-form-graduated">
                    <label for="graduated">
                        <input type="checkbox" name="graduated" value="yes" <?php echo ( trim( strtolower( $_GET['graduated'] ) ) == 'yes' ? 'checked="checked"' : '' ); ?> /> <?php _e( 'Graduated', PyisMemberProfile::$plugin_id ); ?>
                    </label>
                </div>
                <div class="x-column x-sm x-1-6 pyis-directory-form-submit">
                    <a href="/member-directory/" class="pyis-directory-reset-filters x-btn x-btn-regular" title="<?php _e( 'Reset Filters', PyisMemberProfile::$plugin_id ); ?>"><?php _e( 'Reset Filters', PyisMemberProfile::$plugin_id ); ?></a>
                    <input type="submit" value="<?php _e( 'Apply Filters', PyisMemberProfile::$plugin_id ); ?>"/>
                </div>
            </div>
            <div class="x-container max width offset">
                
            </div>
        </form>
        
        <?php 
        
        global $wpdb;
        
        $user_args = array(
            'number' => $number_per_page,
            'meta_key' => 'last_name',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'paged' => $paged,
            'meta_query'     => array(
                'relation' => 'AND', // Based on $_GET, we tack onto this with successive rules that must all be TRUE
                array(
                    'relation' => 'OR', // In order to query two Roles with wp_user_query() you need to use a Meta Query. Not very intuitive.
                    array( 
                        'key' => $wpdb->prefix . 'capabilities',
                        'value' => 'subscriber',
                        'compare' => 'LIKE',
                    ),
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => $wpdb->prefix . 'capabilities',
                            'value' => 'administrator',
                            'compare' => 'LIKE',
                        ),
                        array(
                            'key' => 'first_name',
                            'value' => 'Adrian',
                            'compare' => 'LIKE',
                        ),
                        array(
                            'key' => 'last_name',
                            'value' => 'Rosebrock',
                            'compare' => 'LIKE',
                        )
                    ),
                ),
            ),
        );
        
        if ( ( isset( $_GET['s'] ) ) && ( $_GET['s'] !== '' ) ) {
            
            $user_args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key' => 'first_name',
                    'value' => esc_attr( trim( urldecode( $_GET['s'] ) ) ),
                    'compare' => 'LIKE',
                ),
                array(
                    'key' => 'last_name',
                    'value' => esc_attr( trim( urldecode( $_GET['s'] ) ) ),
                    'compare' => 'LIKE',
                ),
            );
            
        }
        
        if ( isset( $_GET['order'] ) && ( trim( strtolower( $_GET['order'] ) ) == 'asc' ) ) {
            $user_args['order'] = 'ASC';
        }
        else if ( isset( $_GET['order'] ) ) {
            $user_args['order'] = 'DESC';
        }
        
        if ( isset( $_GET['graduated'] ) && ( trim( strtolower( $_GET['graduated'] ) ) == 'yes' ) ) {
            $user_args['meta_query'][] = array(
                'key' => 'course_completed_' . $course_id,
                'compare' => 'EXISTS',
            );
        }
        else if ( isset( $_GET['graduated'] ) ) {
            $user_args['meta_query'][] = array(
                'key' => 'course_completed_' . $course_id,
                'compare' => 'NOT EXISTS',
            );
        }

        $user_query = new WP_User_Query( $user_args );

        if ( ! empty( $user_query->results ) ) : ?>

            <table>
                
                <thead>
                    <th></th>
                    <th><?php _e( 'Name', PyisMemberProfile::$plugin_id ); ?></th>
                    <th><?php _e( 'Course Progress', PyisMemberProfile::$plugin_id ); ?></th>
                    <th><?php _e( 'Graduated', PyisMemberProfile::$plugin_id ); ?></th>
                </thead>

            <?php foreach ( $user_query->results as $user ) :
                
                $user_id = $user->data->ID;
                $pyis_user_data = get_userdata( $user_id );
                
                $course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
                
                $course_progress = ( $course_progress[ $course_id ]['completed'] / $course_progress[ $course_id ]['total'] ) * 100;
                
                // If due to a LearnDash bug they have over 100% completion, reset to 100%
                if ( $course_progress > 100 ) $course_progress = 100;
                
                ?>

                <tr>
                    
                    <td>
                        <a href="/members/<?php echo trailingslashit( strtolower( rawurlencode( $pyis_user_data->user_login ) ) ); ?>" title="<?php echo sprintf( __( "%s's Profile", PyisMemberProfile::$plugin_id ), trim( $pyis_user_data->first_name ) . ' ' . trim( $pyis_user_data->last_name ) ); ?>">
                            <?php echo get_avatar( $user_id, 48 ); ?>
                        </a>
                    </td>
                    <td>
                        <a href="/members/<?php echo trailingslashit( strtolower( rawurlencode( $pyis_user_data->user_login ) ) ); ?>" title="<?php echo sprintf( __( "%s's Profile", PyisMemberProfile::$plugin_id ), trim( $pyis_user_data->first_name ) . ' ' . trim( $pyis_user_data->last_name ) ); ?>">
                            <?php echo trim( $pyis_user_data->last_name ) . ', ' . trim( $pyis_user_data->first_name ); ?>
                        </a>
                    </td>
                    
                    <?php if ( $course_progress == 100 ) : ?>
                        <td><strong><?php echo sprintf( '%g%%', number_format( $course_progress, 2, '.', ',' ) ); ?></strong></td>
                    <?php else : ?>
                        <td><?php echo sprintf( '%g%%', number_format( $course_progress, 2, '.', ',' ) ); ?></td>
                    <?php endif; ?>
                    
                    <td><?php echo ( learndash_course_completed( $user_id, $course_id ) ? '<strong>' . __( 'Yes', PyisMemberProfile::$plugin_id ) . '</strong>' : __( 'No', PyisMemberProfile::$plugin_id ) ) ; ?></td>

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
        
        echo apply_filters( 'pyis_member_directory_nothing_found', sprintf( __( 'Sorry, no members were found using your supplied filters. Looking for someone in particular? <a href="%s">Send me a message</a>', PyisMemberProfile::$plugin_id ), '/contact' ) );

        endif; ?>
        
    </div>
    
</div>

<?php get_footer();