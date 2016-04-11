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
} 

$course_id = get_theme_mod( 'pyis_course', 0 );

?>

<div class="pyis-member-profile-container x-container max width offset entry-wrap">
    <div class="full-width" role="main">
        
        <?php if ( $course_id == 0 ) : ?>
        
            <div class="pyis-error-message">
                <?php echo apply_filters( 'pyis_profile_course_not_set', sprintf( __( 'Course Not Set In <a href="%s">Customizer</a>', PyisMemberProfile::$plugin_id ), admin_url( 'customize.php?autofocus[control]=pyis_course' ) ) ); ?>
            </div>
        
        <?php endif; ?>
            
        <div class="pyis-profile-top x-column x-sm x-1-1">

            <div class="pyis-avatar-container alignleft">

                <?php echo get_avatar( $user_id, 150 ); ?>

            </div>

            <h3 style="margin-top: 0;"><?php echo $pyis_user_data->first_name; ?> <?php echo $pyis_user_data->last_name; ?></h3>

            <?php 

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

            <?php if ( get_user_meta( $user_id, 'description', true ) !== '' ) : ?>
                <h6><?php _e( 'About Me:', PyisMemberProfile::$plugin_id ); ?></h6>
                <?php echo apply_filters( 'the_content', get_user_meta( $user_id, 'description', true ) ); ?>
            <?php endif; ?>

            <?php if ( get_user_meta( $user_id, 'pyis_skills', true ) !== '' ) : ?>
                <h6><?php _e( 'Skills:', PyisMemberProfile::$plugin_id ); ?></h6>
                <?php echo apply_filters( 'the_content', get_user_meta( $user_id, 'pyis_skills', true ) ); ?>
            <?php endif; ?>
            
            <?php
            
            $user_meta_urls = array(
                'linkedin' => __( 'LinkedIn Profile', PyisMemberProfile::$plugin_id ),
                'github' => __( 'GitHub Profile', PyisMemberProfile::$plugin_id ),
                'twitter' => __( 'Twitter Profile', PyisMemberProfile::$plugin_id ),
            );

            foreach ( $user_meta_urls as $key => $label ) : 

                if ( get_user_meta( $user_id, $key, true ) !== '' ) : ?>

                    <h6><?php echo $label; ?></h6>
                    
                    <?php
            
                    // Ensure we have properly working links
                    $url = get_user_meta( $user_id, $key, true );
                    $has_http = preg_match_all( '/(http)?(s)?(:)?(\/\/)/i', $url, $matches );
                    
                    if ( $has_http == 0 ) {
                        $link = '//' . $url;
                    }
                    else {
                        $link = $url;
                    }
            
                    $link_title = sprintf( 
                        __( "%s's %s Profile", PyisMemberProfile::$plugin_id ), 
                        $pyis_user_data->first_name . ' ' . $pyis_user_data->last_name,
                        $label 
                    );
            
                    ?>
            
                    <a href="<?php echo $link; ?>" title="<?php echo $link_title; ?>"><?php echo $url; ?></a>

                <?php endif;

            endforeach;

            if ( $pyis_user_data->user_url !== '' ) : 
            
                // Ensure we have properly working links
                $url = $pyis_user_data->user_url;
                $has_http = preg_match_all( '/(http)?(s)?(:)?(\/\/)/i', $url, $matches );

                if ( $has_http == 0 ) {
                    $link = '//' . $url;
                }
                else {
                    $link = $url;
                }

                $link_title = sprintf( 
                    __( "%s's Website", PyisMemberProfile::$plugin_id ), 
                    $pyis_user_data->first_name . ' ' . $pyis_user_data->last_name
                );
            
                ?>

                <h6><?php _e( 'Website', PyisMemberProfile::$plugin_id ); ?></h6>
                <a href="<?php echo $link; ?>" title="<?php echo $link_title; ?>"><?php echo $url; ?></a>

            <?php endif; ?>

        </div>

    </div>
</div>