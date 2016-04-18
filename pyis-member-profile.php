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
     * @since       1.0
     */
    class PyisMemberProfile {

        private static $instance = null;
        public static $plugin_id = 'pyis-member-profile';
        private $member_directory_regex = '/\/member-directory(|\/)(page\/\d)?/i';
        private $member_profile_regex = '/\/members\/.+(|\/)$/i';
        private $member_profile_json_regex = '/\/members\/.+(|\/)?json$/i';
        private $member_profile_public_regex = '/\/members\/.+(|\/)?public/i';

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
            
            add_filter( 'template_include', array( $this, 'template_redirect' ) );
            
            add_filter( 'the_title', array( $this, 'template_page_title' ), 10, 2 );
            add_filter( 'wp_title', array( $this, 'template_title_tag' ), 10, 3 );
            
            add_action( 'wp_head', array( $this, 'template_meta' ) );
            
            add_action( 'customize_register', array( $this, 'customize_register' ) );
            
            add_filter( 'user_contactmethods', array( $this, 'add_contact_methods' ) );
            
            add_action( 'show_user_profile', array( $this, 'add_skills_field' ) );
            add_action( 'edit_user_profile', array( $this, 'add_skills_field' ) );
            
            add_action( 'personal_options_update', array( $this, 'save_skills_field' ) );
            add_action( 'edit_user_profile_update', array( $this, 'save_skills_field' ) );
            add_action( 'profile_update', array( $this, 'save_skills_field' ) );

            add_filter( 'wp_default_editor', array( $this, 'visual_editor_forced_on_frontend' ) );
            
            add_filter( 'get_avatar', array( $this, 'get_avatar' ), 10, 6 );
            add_filter( 'user_profile_picture_description', array( $this, 'edit_user_gravatar_description' ) );
            add_filter( 'user_has_cap', array( $this, 'allow_subscribers_to_upload_avatars' ), 10, 3 );
            add_filter( 'upload_mimes', array( $this, 'subscribers_can_only_upload_images' ) );
            
            add_action( 'init', array( $this, 'register_styles_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
            
            add_action( 'wp_footer', array( $this, 'member_crop_modal' ) );
            
        }
        
        /**
         * On Plugin activation, create the Avatars directory if it doesn't exist.
         * We won't delete this on deactivation. This will be up to the User.
         * 
         * @access      public
         * @since       1.0
         */
        public static function create_avatars_directory() {
                
            $uploads = wp_upload_dir();
            
            $pyis_avatars = trailingslashit( $uploads['basedir'] ) . apply_filters( 'pyis_avatars_directory', 'pyis-avatars' );

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
         * @since       1.0
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
        
        /**
         * Takes a Data-URI and Streams it into a proper File
         * 
         * @access public
         * @since  1.0
         * @param  string $data_uri    Data-URI String
         * @param  string $output_file Path to the file output on the server
         * @return File  File in memory. Needs to be saved through another method.
         */
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
            
            if ( preg_match( $this->member_directory_regex, $_SERVER['REQUEST_URI'] ) ) {
                
                // make sure the server responds with 200 instead of error code 404
                header( 'HTTP/1.1 200 OK' );
                
                return $this->pyis_locate_template( 'member-directory.php' );
                
                // kill off the request so server doesn't render the 404 message 
                die();
            
            }
            else if ( preg_match( $this->member_profile_json_regex, $_SERVER['REQUEST_URI'] ) ) {
                
                header( 'HTTP/1.1 200 OK' );
                header( 'Content-Type: application/json' );
                
                return $this->pyis_locate_template( 'member-json.php' );
                
                die();
                
            }
            else if ( ( preg_match( $this->member_profile_public_regex, $_SERVER['REQUEST_URI'] ) )
                    || ( preg_match( $this->member_profile_regex, $_SERVER['REQUEST_URI'] ) ) ) {
                
                header( 'HTTP/1.1 200 OK' );
                
                return $this->pyis_locate_template( 'member-profile.php' );
                
                die();
                
            }
            
            return $template;
            
        }
        
        /**
         * Generate Page Title on the injected Page Template
         *
         * @access      public
         * @since       1.0
         * @return      string $title <title> Text
         */
        public function template_page_title( $title, $id ) {
            
            if ( $id == 0 ) {
            
                if ( preg_match( $this->member_directory_regex, $_SERVER['REQUEST_URI'] ) ) {

                    return __( 'Member Directory', $plugin->id );

                }
                else if ( preg_match( $this->member_profile_regex, $_SERVER['REQUEST_URI'] ) ) {

                    // Made available before wp_head() is called
                    global $pyis_user_data;

                    // If the user doesn't exist or isn't Subscriber-level or Admin-level
                    if ( ( ! $pyis_user_data ) 
                        || ( ( $pyis_user_data->roles[0] !== 'subscriber' ) && ( $pyis_user_data->roles[0] !== 'administrator' ) ) ) {
                        return __( "Member Not Found", $this->plugin_id );
                    }
                    else {
                        return sprintf( __( "%s's Profile", $this->plugin_id ), trim( $pyis_user_data->first_name ) . ' ' . trim( $pyis_user_data->last_name ) );
                    }

                }
                
            }
            
            return $title;
            
        }
        
        /**
         * Generate <title> tag based on the injected Page Template
         *
         * @access      public
         * @since       1.0
         * @return      string $title <title> Text
         */
        public function template_title_tag( $title, $sep, $seplocation ) {
            
            if ( preg_match( $this->member_directory_regex, $_SERVER['REQUEST_URI'] ) ) {
                
                return __( 'Member Directory', $plugin->id );
                
            }
            else if ( preg_match( $this->member_profile_regex, $_SERVER['REQUEST_URI'] ) ) {

                // Made available before wp_head() is called
                global $pyis_user_data;

                // If the user doesn't exist or isn't Subscriber-level or Admin-level
                if ( ( ! $pyis_user_data ) 
                    || ( ( $pyis_user_data->roles[0] !== 'subscriber' ) && ( $pyis_user_data->roles[0] !== 'administrator' ) ) ) {
                    return __( "Member Not Found", $this->plugin_id );
                }
                else {
                    return sprintf( __( "%s's Profile", $this->plugin_id ), trim( $pyis_user_data->first_name ) . ' ' . trim( $pyis_user_data->last_name ) );
                }

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
            
                if ( preg_match( $this->member_directory_regex, $_SERVER['REQUEST_URI'] ) ) { ?>
                    <meta property="og:title" content="<?php _e( 'Member Directory', $plugin->id ); ?>">
                    <meta property="twitter:title" content="<?php _e( 'Member Directory', $plugin->id ); ?>">
                <?php }
                else if ( preg_match( $this->member_profile_regex, $_SERVER['REQUEST_URI'] ) ) {

                    // Made available before wp_head() is called
                    global $pyis_user_data;
                    
                    // If the user doesn't exist or isn't Subscriber-level or Admin-level
                    if ( ( ! $pyis_user_data ) 
                        || ( ( $pyis_user_data->roles[0] !== 'subscriber' ) && ( $pyis_user_data->roles[0] !== 'administrator' ) ) ) : ?>

                        <meta property="og:title" content="<?php _e( "Member Not Found", $this->plugin_id ); ?>">
                        <meta property="twitter:title" content="<?php _e( "Member Not Found", $this->plugin_id ); ?>">
                        
                    <?php else : ?>

                        <meta property="og:title" content="<?php echo sprintf( __( "%s's Profile", $this->plugin_id ), trim( $pyis_user_data->first_name . ' ' . $pyis_user_data->last_name ) ); ?>">
                        <meta property="twitter:title" content="<?php echo sprintf( __( "%s's Profile", $this->plugin_id ), trim( $pyis_user_data->first_name . ' ' . $pyis_user_data->last_name ) ); ?>">

                    <?php endif; ?>

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
         * These have a nice Filter, so we can quickly add them this way. Skills needs to be added the hard way.
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
        
        /**
         * Add Skills Textarea to User Profile on the Backend. 
         * This gives us another method to view/edit aside from the Frontend.
         * 
         * @param WP_User $user WP User Object
         * @access public
         * @since 1.0
         */
        public function add_skills_field( $user ) { ?>
            
            <h3><?php _e( 'PyImageSearch Member Profile', $this->plugin_id ); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th>
                        <label for="pyis_skills"><?php _e( 'Skills', $this->plugin_id ); ?></label>
                    </th>
                    <td>
                        <textarea id="pyis_skills" name="pyis_skills" rows="5" cols="30"><?php echo get_user_meta( $user->data->ID, 'pyis_skills', true ); ?></textarea>
                    </td>
                </tr>
            </table>

        <?php    
        }
        
        /**
         * Save Skills Field from the Backend Form
         * 
         * @access public
         * @since 1.0
         * @param integer $user_id User ID
         * @return void
         */
        public function save_skills_field( $user_id ) {
            
            // WP temporarily grants any User "Edit Users" when they are viewing their own Profile
            if ( current_user_can( 'edit_users' ) ) {
                
                $skills = empty( $_POST['pyis_skills'] ) ? '' : $_POST['pyis_skills'];
                update_user_meta( $user_id, 'pyis_skills', $skills );
                
            }
            
        }
        
        /**
         * Force instances of wp_editor() the frontend to default to "Visual"
         * 
         * @access public
         * @since 1.0
         * @return string   Selected Tab
         */
        public function visual_editor_forced_on_frontend() {
            
            // Check if its our page
            if ( preg_match( $this->member_profile_regex, $_SERVER['REQUEST_URI'] ) ) {
            
                return 'tinymce';
                
            }
            
        }
        
        /**
         * Add to the Gravatar Description text a note about uploading images on the Frontend.
         * @param  string $description Original Description Text
         * @return string Modified Description Text
         */
        public function edit_user_gravatar_description( $description ) {
            
            return sprintf( __( "You can change your profile picture on <a href='%s'>Gravatar</a>, or via your profile page once you're logged in.", $this->plugin_id ), 'https://en.gravatar.com/' );
            
        }
        
        /**
         * If the User has a custom Avatar, show that instead of Gravatar
         * 
         * @access public
         * @since  1.0
         * @param  string   $avatar      HTML Output for the Avatar
         * @param  mixed    $id_or_email This can be either a User ID, Email Address, or Comment Object. Thanks, WP
         * @param  integer  $size        Height/Width of Avatar. Square Image.
         * @param  array    $default     Overrides for get_avatar()'s default values
         * @param  string   $alt         Alt Text for the Image
         * @param  array    $args        Additional Arguments for get_avatar(). Most notably, $args['extra_attr']
         * @return string   User Avatar HTML
         */
        public function get_avatar( $avatar, $id_or_email, $size, $default, $alt, $args ) {
            
            $user = false;

            if ( is_numeric( $id_or_email ) ) {

                $id = (int) $id_or_email;
                $user = get_user_by( 'id' , $id );

            }
            elseif ( is_object( $id_or_email ) ) {

            if ( ! empty( $id_or_email->user_id ) ) {
                $id = (int) $id_or_email->user_id;
                $user = get_user_by( 'id' , $id );
            }

            }
            else {
                $user = get_user_by( 'email', $id_or_email );	
            }

            if ( $user && is_object( $user ) ) {
                
                $user_login = $user->data->user_login;
                $uploads = wp_upload_dir();
                $pyis_avatars_directory = trailingslashit( $uploads['basedir'] ) . apply_filters( 'pyis_avatars_directory', 'pyis-avatars' );
                
                // Server Path
                $user_avatar = trailingslashit( $pyis_avatars_directory ) . $user_login . '.png';
                
                // Web-accessable path
                $user_avatar_url = trailingslashit( $uploads['baseurl'] ) . trailingslashit( apply_filters( 'pyis_avatars_directory', 'pyis-avatars' ) ) . $user_login . '.png';

                if ( file_exists( $user_avatar ) ) {
                    $avatar = "<img alt='{$alt}' src='{$user_avatar_url}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' {$args['extra_attr']} />";
                }

            }

            return $avatar;
            
        }
        
        /**
         * Give Subscribers the capabilty to upload files only on their profile page
         * 
         * @access public
         * @since 1.0
         * @param  array    $user_caps The User's Capabilities
         * @param  array    $req_cap   The required Capability
         * @param  array    $args      The Requested Capability, along with User and Object information
         * @return array    Temporarily modified Capabilities for the User
         */
        public function allow_subscribers_to_upload_avatars( $user_caps, $req_cap, $args ) {
            
            if ( preg_match( $this->member_profile_regex, $_SERVER['REQUEST_URI'] ) ) {
            
                global $current_user;
                global $pyis_user_data;

                if ( $current_user->data->ID == $pyis_user_data->data->ID ) {
                    
                    $user_caps['upload_files'] = true;
                    
                }
                
            }
            
            return $user_caps;
            
        }
        
        /**
         * Ensure that Subscribers can only upload Images
         * 
         * @access public
         * @since 1.0
         * @param  array    $mime_types Mime Types
         * @return array    Mime Types
         */
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
        
        /**
         * Register Styles/Scripts to be enqueued later
         * 
         * @access public
         * @since 1.0
         * @return void
         */
        public function register_styles_scripts() {
            
            wp_register_style( 
                $this->plugin_id . '-style',
                plugins_url( '/style.css', __FILE__ )
            );
            
            wp_register_script(
                $this->plugin_id . '-script',
                plugins_url( '/script.js', __FILE__ ),
                array( 'jquery' ),
                false,
                true
            );
            
        }
        
        /**
         * Enqueue Scripts/Styles for our Pages
         * 
         * @access public
         * @since 1.0
         @return void
         */
        public function wp_enqueue_scripts() {
            
            // Only load our styles on our own pages
            if ( ( preg_match( $this->member_directory_regex, $_SERVER['REQUEST_URI'] ) ) 
                || ( preg_match( $this->member_profile_regex, $_SERVER['REQUEST_URI'] ) ) ) {
 
                wp_enqueue_style( $this->plugin_id . '-style' );
                
            }
            
            // The scripts are only needed for Profile Editing
            if ( ( ! preg_match( $this->member_profile_public_regex, $_SERVER['REQUEST_URI'] ) ) && ( preg_match( $this->member_profile_regex, $_SERVER['REQUEST_URI'] ) ) ) {
                
                global $current_user;
                global $pyis_user_data;

                if ( $current_user->data->ID == $pyis_user_data->data->ID ) {
                    
                    wp_enqueue_media();
                    wp_enqueue_script( $this->plugin_id . '-script' );
                    
                }
                
            }
            
        }
        
        /**
         * Include the Modal template on our Edit Profile page within the Footer, to ensure no weird HTML quirks.
         * 
         * @access public
         * @since 1.0
         * @return void
         */
        public function member_crop_modal() {
            
            if ( preg_match( $this->member_profile_regex, $_SERVER['REQUEST_URI'] ) ) {
            
                global $current_user;
                global $pyis_user_data;

                if ( $current_user->data->ID == $pyis_user_data->data->ID ) {

                    include( $this->pyis_locate_template( 'member-modal.php' ) );

                }
                
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