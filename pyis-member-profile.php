<?php
/*
Plugin Name: PyImageSearch Profile Plugin
Description: Dynamically constructs a “member profile” page for each member of the PyImageSearch Gurus course.
Author: Eric Defore
Version: 1.0
Author URI: http://realbigmarketing.com
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PyisMemberProfile' ) ) {

    /**
     * Main PyisMemberProfile class
     *
     * @since       1.0.0
     */
    class PyisMemberProfile {

        private static $instance = null;
        public static $plugin_id = 'pyis-member-profile';

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0
         * @return      object self::$instance The one true PyisMemberProfile
         */
        public static function get_instance() {

            if ( self::$instance == null ) {
                self::$instance = new PyisMemberProfile();
                self::$instance->setup_constants();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;

        }

        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'PYIS_VER', '1.0' );

            // Plugin path
            define( 'PYIS_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'PYIS_URL', plugin_dir_url( __FILE__ ) );
        }

        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0
         * @return      void
         */
        private function hooks() {
            
            //register_activation_hook( __FILE__, array( $this, 'create_avatars_directory' ) );
            //add_action( 'admin_init', array( $this, 'create_avatars_directory' ) );
            
            add_filter( 'template_include', array( $this, 'template_redirect' ) );
            add_filter( 'the_title', array( $this, 'template_title' ), 10, 3 );
            add_filter( 'wp_title', array( $this, 'template_title' ), 10, 3 );
            
            add_action( 'wp_head', array( $this, 'template_meta' ) );
            
            add_action( 'customize_register', array( $this, 'customize_register' ) );
            
            add_filter( 'user_contactmethods', array( $this, 'add_contact_methods' ) );
            
            add_action( 'show_user_profile', array( $this, 'add_profile_fields' ) );
            add_action( 'edit_user_profile', array( $this, 'add_profile_fields' ) );
            add_action( 'user_new_form', array( $this, 'add_profile_fields' ) );
            
            add_action( 'profile_update', array( $this, 'profile_update' ) );
            add_action( 'user_register', array( $this, 'profile_update' ) );
            
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            
            add_action( 'wp_footer', array( $this, 'member_crop_modal' ) );
            
            add_filter( 'posts_where', array( $this, 'subscribers_can_only_see_own_files' ) );
            
            add_filter( 'upload_mimes', array( $this, 'subscribers_can_only_upload_images' ) );
            
        }
        
        public static function create_avatars_directory() {
                
            $uploads = wp_upload_dir();
            
            $pyis_avatars = apply_filters( 'pyis_avatars_directory', $uploads['basedir'] . '/pyis-avatars' );

            if ( ! is_dir( $pyis_avatars ) ) :
                wp_mkdir_p( $pyis_avatars );
            endif;
            
        }
        
        /**
         * Retrieve the name of the highest priority template file that exists.
         *
         * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
         * inherit from a parent theme can just overload one file. If the template is
         * not found in either of those, it looks in the theme-compat folder last.
         *
         * Taken from bbPress
         *
         * @access      public
         * @since       v1.0
         *
         * @param       string|array $template_names Template file(s) to search for, in order.
         * @param       bool $load If true the template file will be loaded if it is found.
         * @param       bool $require_once Whether to require_once or require. Default true. Has no effect if $load is false.
         * @return      string The template filename if one is located.
         */
        public static function pyis_locate_template( $template_names, $load = false, $require_once = true ) {
            
            // No file found yet
            $located = false;

            // Try to find a template file
            foreach ( ( array )$template_names as $template_name ) {

                // Continue if template is empty
                if ( empty( $template_name ) )
                    continue;

                // Trim off any slashes from the template name
                $template_name = ltrim( $template_name, '/' );

                if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'pyis/' . $template_name ) ) {
                    // Check child theme first
                    $located = trailingslashit( get_stylesheet_directory() ) . 'pyis/' . $template_name;
                    break;
                }
                elseif ( file_exists( trailingslashit( get_template_directory() ) . 'pyis/' . $template_name ) ) {
                    // Check parent theme next
                    $located = trailingslashit( get_template_directory() ) . 'pyis/' . $template_name;
                    break;
                }
                elseif ( file_exists( trailingslashit( PYIS_DIR ) . 'templates/' . $template_name ) ) {
                    // Check plugin directory last
                    $located = trailingslashit( PYIS_DIR ) . 'templates/' . $template_name;
                    break;
                }
                
            }

            if ( ( true == $load ) && ! empty( $located ) )
                load_template( $located, $require_once );

            return $located;
            
        }
        
        public static function pyis_data_uri_decode( $data_uri, $output_file ) {
            
            if ( $data_uri == '' ) {
                return false;
            }
            
            $file_stream = fopen( $output_file, "wb" );
            
            $data = explode( ',', $data_uri );
            
            fwrite( $file_stream, base64_decode( $data[1] ) );
            fclose( $file_stream );
            
            return $output_file;
            
        }
        
        /**
         * Load different template based on Page Slug without making a Page within WordPress
         *
         * @access      public
         * @since       1.0
         * @return      string $template Template file to be loaded
         */
        public function template_redirect( $template ) {
            
            if ( preg_match( '/\/member-directory(|\/)(page\/\d)?/i', $_SERVER['REQUEST_URI'] ) ) {
                
                // make sure the server responds with 200 instead of error code 404
                header( 'HTTP/1.1 200 OK' );
                
                return $this->pyis_locate_template( 'member-directory.php' );
                
                // kill off the request so server doesn't render the 404 message 
                die();
            
            }
            else if ( preg_match( '/\/members\/.+(|\/)?json$/i', $_SERVER['REQUEST_URI'] ) ) {
                
                header( 'HTTP/1.1 200 OK' );
                header( 'Content-Type: application/json' );
                
                return $this->pyis_locate_template( 'member-json.php' );
                
                die();
                
            }
            else if ( preg_match( '/\/members\/.+(|\/)$/i', $_SERVER['REQUEST_URI'] ) ) {
                
                header( 'HTTP/1.1 200 OK' );
                
                return $this->pyis_locate_template( 'member-profile.php' );
                
                die();
                
            }
            
            return $template;
            
        }
        
        /**
         * Generate <title> tag based on the injected Page Template
         *
         * @access      public
         * @since       1.0
         * @return      string $title <title> Text
         */
        public function template_title( $title, $sep, $seplocation ) {
            
            if ( preg_match( '/\/member-directory(|\/)(page\/\d)?/i', $_SERVER['REQUEST_URI'] ) ) {
                return __( 'Member Directory', $plugin->id );
            }
            else if ( preg_match( '/\/members\/.+(|\/)$/i', $_SERVER['REQUEST_URI'] ) ) {
    
                // Made available before wp_head() is called
                global $user_data;

                return sprintf( __( "%s's Profile", $this->plugin_id ), $user_data->first_name . ' ' . $user_data->last_name );
                
            }
            
            return $title;
            
        }
        
        /**
         * Generate <meta> tag based on the injected Page Template
         *
         * @access      public
         * @since       1.0
         * @return      void
         */
        public function template_meta() {
            
            // Yoast SEO handles this if installed
            if ( ! class_exists( 'WPSEO_Frontend' ) ) {
            
                if ( preg_match( '/\/member-directory(|\/)(page\/\d)?/i', $_SERVER['REQUEST_URI'] ) ) { ?>
                    <meta property="og:title" content="<?php _e( 'Member Directory', $plugin->id ); ?>">
                    <meta property="twitter:title" content="<?php _e( 'Member Directory', $plugin->id ); ?>">
                <?php }
                else if ( preg_match( '/\/members\/.+(|\/)$/i', $_SERVER['REQUEST_URI'] ) ) {

                    // Made available before wp_head() is called
                    global $user_data;
                    ?>

                    <meta property="og:title" content="<?php echo sprintf( __( "%s's Profile", $this->plugin_id ), $user_data->first_name . ' ' . $user_data->last_name ); ?>">
                    <meta property="twitter:title" content="<?php echo sprintf( __( "%s's Profile", $this->plugin_id ), $user_data->first_name . ' ' . $user_data->last_name ); ?>">

                <?php }
                
            }
            
        }
        
        /**
         * Add Customizer Control to choose the Course for Member Listing Metadata.
         *
         * @access      public
         * @since       1.0
         * @return      void
         */
        public function customize_register( $wp_customize ) {

            // General Theme Options
            $wp_customize->add_section( 'pyis_customizer_section' , array(
                    'title'      => __( 'PyImageSearch Profile Settings', $this->plugin_id ),
                    'priority'   => 30,
                ) 
            );
            
            $args = array(
                'posts_per_page' => '-1',
                'post_type' => 'sfwd-courses',
                'post_status' => 'publish',
                'orderby' => 'title',
                'order' => 'ASC',
            );
            
            $course_query = get_posts( $args );
            $courses = array( __( 'Select a Course', $this->plugin_id ) ); // No Posts are assigned post_id of 0 anyway
            
            foreach ( $course_query as $course ) {
                
                $courses[ $course->ID ] = $course->post_title; 
                
            }

            $wp_customize->add_setting( 'pyis_course', array(
                    'default' => 0,
                    'transport' => 'refresh',
                ) 
            );
            $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'pyis_course', array(
                'label' => __( 'Course', $this->plugin_id ),
                'section' => 'pyis_customizer_section',
                'settings' => 'pyis_course',
                'type' => 'select',
                'choices' => $courses,
            ) ) );
            
        }

        /**
         * Add Contact methods to the User Profile screen
         *
         * @access      public
         * @since       1.0
         * @return      Array $profile_fields All Contact Method Fields
         */
        public function add_contact_methods( $profile_fields ) {
            
            $profile_fields['linkedin'] = 'LinkedIn';
            $profile_fields['github'] = 'GitHub';
            
            return $profile_fields;
            
        }
        
        public function add_profile_fields( $user ) {
 
            $profile_pic = ( $user !== 'add-new-user' ) ? get_user_meta( $user->ID, 'pyis_profile_image', true ) : false;

            if ( ! empty( $profile_pic ) ) {
                $image = wp_get_attachment_image_src( $profile_pic, 'thumbnail' );
            } 
            ?>

            <table class="form-table pyis-profile-upload-options">
                <tr>
                    <th>
                        <label for="image"><?php _e( 'PyImageSearch Profile Avatar', $this->plugin_id ) ?></label>
                    </th>

                    <td>
                        <img id="pyis-profile-image" src="<?php echo ! empty( $profile_pic ) ? $image[0] : get_avatar_url( $user->ID, array( 'size' => 100 ) ); ?>" style="max-width: 100px; max-height: 100px;" data-default="<?php echo get_avatar_url( $user->ID, array( 'size' => 100 ) ); ?>" />
                        <input type="button" data-id="pyis-profile-image-id" data-src="pyis-profile-image" class="button" id="pyis-profile-image-upload" value="<?php _e( 'Upload', $this->plugin_id ); ?>" />
                        <input type="button" data-id="pyis-profile-image-id" data-src="pyis-profile-image" class="button" id="pyis-profile-image-default" value="<?php _e( 'Reset to Default', $this->plugin_id ); ?>" />
                        <input type="hidden" class="button" name="pyis_profile_image_id" id="pyis-profile-image-id" value="<?php echo ! empty( $profile_pic ) ? $profile_pic : ''; ?>" />
                        <p class="description"><?php
                            if ( IS_PROFILE_PAGE ) {
                                /* translators: %s: Gravatar URL */
                                $description = sprintf( __( 'You can set your Avatar here, or via <a href="%s">Gravatar</a>.' ),
                                    __( '//en.gravatar.com/' )
                                );
                            } else {
                                $description = '';
                            }

                            /**
                             * Filter the user profile picture description displayed under the Gravatar.
                             *
                             * @since 1.0
                             *
                             * @param string $description The description that will be printed.
                             */
                            echo apply_filters( 'pyis_profile_picture_description', $description );
                        ?></p>
                    </td>
                </tr>
            </table>

        <?php                
        }
        
        function profile_update( $user_id ) {
 
            if ( current_user_can( 'edit_users' ) ) {
                $profile_pic = empty( $_POST['pyis_profile_image_id'] ) ? '' : $_POST['pyis_profile_image_id'];
                update_user_meta( $user_id, 'pyis_profile_image', $profile_pic );
            }

        }
        
        public function subscribers_can_only_see_own_files( $where ){
            
            global $current_user;

            if ( is_user_logged_in() && $current_user->roles[0] == 'subscriber' ){
                // we spreken over een ingelogde user
                if ( isset( $_POST['action'] ) ){
                    // library query
                    if( $_POST['action'] == 'query-attachments' ){
                        $where .= ' AND post_author=' . $current_user->data->ID;
                    }
                }
            }

            return $where;
            
        }
        
        public function subscribers_can_only_upload_images( $mime_types ) {
            
            global $current_user;
            
            if ( $current_user->roles[0] == 'subscriber' ) :
            
                $mime_types = array(
                    'jpg|jpeg|jpe' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'png' => 'image/png',
                );
            
            endif;
            
            return $mime_types;
            
        }
        
        public function admin_enqueue_scripts() {
 
            wp_enqueue_media();
            wp_enqueue_script( 'pyis-admin-uploader', plugins_url( '/script.js', __FILE__ ), array( 'jquery' ), false, true );
            wp_enqueue_style( 'pyis-style', plugins_url( '/style.css', __FILE__ ) );
            
        }
        
        public function member_crop_modal() {
            
            global $current_user;
            global $user_data;
            
            if ( $current_user->data->ID == $user_data->data->ID ) {
                
                include( $this->pyis_locate_template( 'member-modal.php' ) );
                
            }
            
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0
         * @return      void
         */
        public function load_textdomain() {

            // Set filter for language directory
            $lang_dir = PYIS_DIR . '/languages/';
            $lang_dir = apply_filters( 'pyis_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), $this->plugin_id );
            $mofile = sprintf( '%1$s-%2$s.mo', $this->plugin_id, $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/' . $this->plugin_id . '/' . $mofile;

            if ( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/pyis-member-profile/ folder
                // This way translations can be overridden via the global /wp-content/languages directory
                load_textdomain( $this->plugin_id, $mofile_global );
            }
            else if( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/pyis-member-profile/languages/ folder
                load_textdomain( $this->plugin_id, $mofile_local );
            }
            else {
                // Load the default language files
                load_plugin_textdomain( $this->plugin_id, false, $lang_dir );
            }

        }

        /**
         * Send an error message if MemberPress is not installed
         *
         * @access      public
         * @since       1.0
         * @return      void
         */
        public static function missing_memberpress_notice() {
            ?>

            <div id="message" class="error notice is-dismissible">
                <?php echo sprintf( __( '<p>The Plugin <strong>PyImageSearch Profile Plugin</strong> requires <strong><a href = "%s" target="_blank">MemberPress</a></strong> to be Active!</p>', PyisMemberProfile::$plugin_id ), '//www.memberpress.com/' ); ?>
            </div>

            <?php
        }
        
        /**
         * Send an error message if LearnDash is not installed
         *
         * @access      public
         * @since       1.0
         * @return      void
         */
        public static function missing_learndash_notice() {
            ?>

            <div id="message" class="error notice is-dismissible">
                <?php echo sprintf( __( '<p>The Plugin <strong>PyImageSearch Profile Plugin</strong> requires <strong><a href = "%s" target="_blank">LearnDash</a></strong> to be Active!</p>', PyisMemberProfile::$plugin_id ), '//www.learndash.com/' ); ?>
            </div>

            <?php
        }

    }
    
} // End Class Exists Check

/**
 * The main function responsible for returning the one true PyisMemberProfile
 * instance to functions everywhere
 *
 * @since       1.0
 * @return      \PyisMemberProfile The one true PyisMemberProfile
 */
add_action( 'plugins_loaded', 'PyisMemberProfile_load', 999 );
function PyisMemberProfile_load() {
    
    if ( class_exists( 'MeprHooks' ) && class_exists( 'SFWD_LMS' ) ) {
     
        return PyisMemberProfile::get_instance();
        
    }
    
    if ( ! class_exists( 'MeprHooks' ) ) {
        
        add_action( 'admin_notices', array( 'PyisMemberProfile', 'missing_memberpress_notice' ) );
        
    }
    
    if ( ! class_exists( 'SFWD_LMS' ) ) {
        
        add_action( 'admin_notices', array( 'PyisMemberProfile', 'missing_learndash_notice' ) );
        
    }
    
}

register_activation_hook( __FILE__, array( 'PyisMemberProfile', 'create_avatars_directory' ) );