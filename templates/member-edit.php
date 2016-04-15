<?php
/**
 * Member Edit Template
 *
 * @since 1.0
 * @package pyis-member-profile
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

if ( 
	isset( $_POST['pyis_profile_nonce'] ) 
	&& wp_verify_nonce( $_POST['pyis_profile_nonce'], PyisMemberProfile::$plugin_id )
	&& current_user_can( 'upload_files', 0 )
) {
	// The nonce was valid and the user has the capabilities, it is safe to continue.

	// This file needs to be included when on the front end.
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
    
    $uploads = wp_upload_dir();
    $pyis_avatars = trailingslashit( $uploads['basedir'] ) . apply_filters( 'pyis_avatars_directory', 'pyis-avatars' );
    
    // Pica always converts the images to .PNGs.
    $image_path = PyisMemberProfile::pyis_data_uri_decode( $_POST['pyis_profile_image'], trailingslashit( $pyis_avatars ) . $pyis_user_data->user_login . '.png' );
    
    if ( $image_path !== false ) {
    
        $upload = wp_handle_upload( $image_path, array( 'test_form' => false ) );
        
    }
    
    // There's info from wp_usermeta and wp_users being shown here. We need to update it differently for each.
    $update_user_meta = array(
        'description',
        'linkedin',
        'github',
        'twitter',
    );
    
    $update_user_data = array(
        'first_name',
        'last_name',
    );
    
    foreach ( $update_user_meta as $key ) {
        
        if ( isset( $_POST[ $key ] ) ) {
            
            update_user_meta( $user_id, $key, $_POST[ $key ] );
            
        }
        
    }
    
    $insert_user_data = array(
        'ID' => $user_id,
    );
    
    // First and Last Name cannot be empty
    foreach ( $update_user_data as $key ) {
        
        if ( ( isset( $_POST[ $key ] ) ) && ( $_POST[ $key ] !== '' ) ) {
            
            $insert_user_data[ $key ] = $_POST[ $key ];
            
        }
        
    }
    
    // We allow blank values to be entered for user_url
    if ( isset( $_POST['user_url'] ) ) {
        $insert_user_data['user_url'] = $_POST['user_url'];
    }
    
    $update_user = wp_update_user( $insert_user_data );
    
    // Ensure Global User Data is up-to-date
    $pyis_user_data = get_userdata( $user_id );

}
else {
	// The security check failed, maybe show the user an error.
}

$course_id = get_theme_mod( 'pyis_course', 0 );

?>

<div class="pyis-member-edit-container x-container max width offset entry-wrap">
    <div class="full-width" role="main">
        
        <?php if ( $course_id == 0 ) : ?>
        
            <div class="pyis-error-message">
                <?php echo apply_filters( 'pyis_profile_course_not_set', sprintf( __( 'Course Not Set In <a href="%s">Customizer</a>', PyisMemberProfile::$plugin_id ), admin_url( 'customize.php?autofocus[control]=pyis_course' ) ) ); ?>
            </div>
        
        <?php endif; ?>
        
        <?php 
        if ( 
            isset( $_POST['pyis_profile_nonce'] ) 
            && wp_verify_nonce( $_POST['pyis_profile_nonce'], PyisMemberProfile::$plugin_id )
            && current_user_can( 'upload_files', 0 )
        ) : ?>
        
            <div class="pyis-success-message">
                <?php echo apply_filters( 'pyis_member_submission_success', __( 'Nice, your changes have been saved.', PyisMemberProfile::$plugin_id ) ); ?>
            </div>
        
        <?php endif; ?>
            
        <form id="pyis-profile-edit-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( PyisMemberProfile::$plugin_id, 'pyis_profile_nonce' ); ?>

            <div class="pyis-profile-top x-column x-sm x-1-1">

                <div class="pyis-avatar-container alignleft x-column x-sm x-1-5">

                    <?php echo get_avatar( $user_id, 150, false, false, array( 'extra_attr' => 'id="pyis-profile-image"' ) ); ?>
                    <input type="hidden" name="pyis_profile_image" id="pyis_profile_image_data" />

                    <p class="open-modal-container"><a id="open-modal-link" data-open="pyis-image-upload-modal">Upload a New Avatar</a></p>

                </div>
                
                <div class="pyis-user-course-meta alignleft x-column x-sm x-3-5">

                    <span class="pyis-name-error-hint" style="display: none;"></span>
                    <input required type="text" class="name-field" name="first_name" value="<?php echo $pyis_user_data->first_name; ?>" placeholder="<?php _e( 'Enter Your First Name', PyisMemberProfile::$plugin_id ); ?>" /> <input required type="text" class="name-field" name="last_name" value="<?php echo $pyis_user_data->last_name; ?>" placeholder="<?php _e( 'Enter Your Last Name', PyisMemberProfile::$plugin_id ); ?>" />

                    <?php 

                    $register_date = new DateTime( $pyis_user_data->user_registered );
                    $course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
                    $course_progress = ( $course_progress[ $course_id ]['completed'] / $course_progress[ $course_id ]['total'] ) * 100;

                    // If due to a LearnDash bug they have over 100% completion, reset to 100%
                    if ( $course_progress > 100 ) $course_progress = 100;

                    ?>

                    <?php echo apply_filters( 'the_content', 
                        sprintf( 
                            __( 'PyImageSearch Gurus Member Since %s', PyisMemberProfile::$plugin_id ), 
                            $register_date->format( 'F jS, Y' ) 
                        ) 
                    ); ?>

                    <?php if ( $course_progress == 100 ) {
                        echo apply_filters( 'the_content', 
                            sprintf( 
                                __( 'Course Progress: <strong>%g%%</strong>', PyisMemberProfile::$plugin_id ), 
                                number_format( $course_progress, 2, '.', ',' )
                            ) 
                        );
                    }
                    else {
                        echo apply_filters( 'the_content', 
                            sprintf( 
                                __( 'Course Progress: %g%%', PyisMemberProfile::$plugin_id ), 
                                number_format( $course_progress, 2, '.', ',' )
                            ) 
                        );
                    } ?>

                    <?php echo apply_filters( 'the_content', 
                         sprintf( 
                             __( 'Completed Course: %s', PyisMemberProfile::$plugin_id ),
                             ( learndash_course_completed( $user_id, $course_id ) ? '<strong>' . __( 'Yes', PyisMemberProfile::$plugin_id ) . '</strong>' : __( 'No', PyisMemberProfile::$plugin_id ) ) 
                         ) 
                    ); ?>
                    
                </div>
                
                <div class="pyis-user-public-view alignright x-column x-sm x-1-5">
                
                    <a href="?public" class="x-btn">View Public Profile</a>
                    
                </div>

            </div>

            <div class="profile-bottom x-column x-sm x-1-1">
                
                <div class="x-column x-sm x-3-4">

                    <h6><?php _e( 'About Me:', PyisMemberProfile::$plugin_id ); ?></h6>
                    <?php wp_editor( get_user_meta( $user_id, 'description', true ), 'description', array( 
                        'media_buttons' => false,
                        'textarea_rows' => 10,
                    ) ); ?>

                    <h6><?php _e( 'Skills:', PyisMemberProfile::$plugin_id ); ?></h6>
                    <?php wp_editor( get_user_meta( $user_id, 'pyis_skills', true ), 'pyis_skills', array( 
                        'media_buttons' => false,
                        'textarea_rows' => 10,
                    ) ); ?>
                    
                </div>
                
                <div class="x-column x-sm x-1-3">

                    <h6><?php _e( 'LinkedIn', PyisMemberProfile::$plugin_id ); ?></h6>
                    <input type="text" name="linkedin" class="pyis-validate-url" data-validate="linkedin.com" value="<?php echo get_user_meta( $user_id, 'linkedin', true ); ?>" placeholder="<?php _e( 'Enter Your LinkedIn URL', PyisMemberProfile::$plugin_id ); ?>" /> <span class="pyis-error-hint"></span>

                    <h6><?php _e( 'GitHub', PyisMemberProfile::$plugin_id ); ?></h6>
                    <input type="text" name="github" class="pyis-validate-url" data-validate="github.com" value="<?php echo get_user_meta( $user_id, 'github', true ); ?>" placeholder="<?php _e( 'Enter Your GitHub URL', PyisMemberProfile::$plugin_id ); ?>" /> <span class="pyis-error-hint"></span>

                    <h6><?php _e( 'Twitter', PyisMemberProfile::$plugin_id ); ?></h6>
                    <input type="text" name="twitter" class="pyis-validate-url" data-validate="twitter.com" value="<?php echo get_user_meta( $user_id, 'twitter', true ); ?>" placeholder="<?php _e( 'Enter Your Twitter URL', PyisMemberProfile::$plugin_id ); ?>" /> <span class="pyis-error-hint"></span>

                    <h6><?php _e( 'Website', PyisMemberProfile::$plugin_id ); ?></h6>
                    <input type="text" name="user_url" value="<?php echo $pyis_user_data->user_url; ?>" placeholder="<?php _e( 'Enter Your Website', PyisMemberProfile::$plugin_id ); ?>" />
                    
                </div>

            </div>

            <input id="pyis_profile_submit" class="x-btn x-btn-jumbo" type="submit" value="<?php _e( 'Save Changes', PyisMemberProfile::$plugin_id ); ?>" />
                
            <div class="pyis-error-message" style="display: none;">
                <?php echo apply_filters( 'pyis_member_submission_error', __( 'Hmm, it looks like there was a problem submitting your updates.', PyisMemberProfile::$plugin_id ) ); ?>
            </div>

        </form>

    </div>
</div>