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
    $pyis_avatars = $uploads['basedir'] . apply_filters( 'pyis_avatars_directory', '/pyis-avatars' );
    
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
        'user_url',
    );
    
    foreach ( $update_user_meta as $key ) {
        
        if ( ( isset( $_POST[ $key ] ) ) && ( $_POST[ $key ] !== '' ) ) {
            
            update_user_meta( $user_id, $key, $_POST[ $key ] );
            
        }
        
    }
    
    $insert_user_data = array(
        'ID' => $user_id,
    );
    foreach ( $update_user_data as $key ) {
        
        if ( ( isset( $_POST[ $key ] ) ) && ( $_POST[ $key ] !== '' ) ) {
            
            $insert_user_data[ $key ] = $_POST[ $key ];
            
        }
        
    }
    
    $update_user = wp_update_user( $insert_user_data );
    
    // Ensure Global User Data is up-to-date
    $pyis_user_data = get_userdata( $user_id );

}
else {
	// The security check failed, maybe show the user an error.
}

?>

<div class="pyis-member-edit-container x-container max width offset entry-wrap">
    <div class="full-width" role="main">
        
        <?php 
        if ( 
            isset( $_POST['pyis_profile_nonce'] ) 
            && wp_verify_nonce( $_POST['pyis_profile_nonce'], PyisMemberProfile::$plugin_id )
            && current_user_can( 'upload_files', 0 )
        ) : ?>
        
            <div class="pyis-success-message">
                <?php echo apply_filters( 'pyis_member_submission_success', __( 'Submission Successful', PyisMemberProfile::$plugin_id ) ); ?>
            </div>
        
        <?php endif; ?>
            
        <form id="pyis-profile-edit-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( PyisMemberProfile::$plugin_id, 'pyis_profile_nonce' ); ?>

            <div class="pyis-profile-top x-column x-sm x-1-1">

                <div class="pyis-avatar-container alignleft">

                    <?php echo get_avatar( $user_id, 150, false, false, array( 'extra_attr' => 'id="pyis-profile-image"' ) ); ?>
                    <input type="hidden" name="pyis_profile_image" id="pyis_profile_image_data" />

                    <p class="open-modal-container"><a id="open-modal-link" data-open="pyis-image-upload-modal">Upload a New Avatar</a></p>

                </div>

                <label>
                    <input type="text" name="first_name" value="<?php echo $pyis_user_data->first_name; ?>" placeholder="<?php _e( 'Enter Your First Name', PyisMemberProfile::$plugin_id ); ?>" /> <input type="text" name="last_name" value="<?php echo $pyis_user_data->last_name; ?>" placeholder="<?php _e( 'Enter Your Last Name', PyisMemberProfile::$plugin_id ); ?>" />

                <?php 

                $course_id = get_theme_mod( 'pyis_course', 0 );

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

            <div class="profile-bottom x-column x-sm x-1-1">

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

                <h6><?php _e( 'LinkedIn', PyisMemberProfile::$plugin_id ); ?></h6>
                <input type="text" name="linkedin" class="pyis-validate-url" data-validate="linkedin.com" value="<?php echo get_user_meta( $user_id, 'linkedin', true ); ?>" placeholder="<?php _e( 'Enter Your LinkedIn URL', PyisMemberProfile::$plugin_id ); ?>" /> <span class="pyis-error-hint"></span>

                <h6><?php _e( 'GitHub', PyisMemberProfile::$plugin_id ); ?></h6>
                <input type="text" name="github" class="pyis-validate-url" data-validate="github.com" value="<?php echo get_user_meta( $user_id, 'github', true ); ?>" placeholder="<?php _e( 'Enter Your GitHub URL', PyisMemberProfile::$plugin_id ); ?>" /> <span class="pyis-error-hint"></span>

                <h6><?php _e( 'Twitter', PyisMemberProfile::$plugin_id ); ?></h6>
                <input type="text" name="twitter" class="pyis-validate-url" data-validate="twitter.com" value="<?php echo get_user_meta( $user_id, 'twitter', true ); ?>" placeholder="<?php _e( 'Enter Your Twitter URL', PyisMemberProfile::$plugin_id ); ?>" /> <span class="pyis-error-hint"></span>

                <h6><?php _e( 'Website', PyisMemberProfile::$plugin_id ); ?></h6>
                <input type="text" name="user_url" value="<?php echo $pyis_user_data->user_url; ?>" placeholder="<?php _e( 'Enter Your Website', PyisMemberProfile::$plugin_id ); ?>" />

            </div>

            <input id="pyis_profile_submit" type="submit" value="<?php _e( 'Save Changes', PyisMemberProfile::$plugin_id ); ?>" />
                
            <div class="pyis-error-message" style="display: none;">
                <?php echo apply_filters( 'pyis_member_submission_error', __( 'Submission Error', PyisMemberProfile::$plugin_id ) ); ?>
            </div>

        </form>

    </div>
</div>