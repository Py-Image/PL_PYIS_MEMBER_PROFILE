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
            
            add_filter( 'template_include', array( $this, 'template_redirect' ) );
            add_filter( 'the_title', array( $this, 'template_title' ), 10, 2 );
            add_filter( 'wp_title', array( $this, 'template_title_tag' ), 10, 3 );
            
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
         * @access      private
         * @since       v1.0
         *
         * @param       string|array $template_names Template file(s) to search for, in order.
         * @param       bool $load If true the template file will be loaded if it is found.
         * @param       bool $require_once Whether to require_once or require. Default true. Has no effect if $load is false.
         * @return      string The template filename if one is located.
         */
        private function pyis_locate_template( $template_names, $load = false, $require_once = true ) {
            
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
            
            return $template;
            
        }
        
        public function template_title( $title, $id ) {
            
            if ( preg_match( '/\/member-directory(|\/)(page\/\d)?/i', $_SERVER['REQUEST_URI'] ) ) {
                return __( 'Member Directory', $plugin->id );
            }
            
            return $title;
            
        }
        
        public function template_title_tag( $title, $sep, $seplocation ) {
            
            if ( preg_match( '/\/member-directory(|\/)(page\/\d)?/i', $_SERVER['REQUEST_URI'] ) ) {
                return sprintf( __( 'Member Directory %s', $plugin->id ), $title );
            }
            
            return $title;
            
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
                <?php _e( '<p>The Plugin <strong>PyImageSearch Profile Plugin</strong> requires <strong><a href = "https://www.memberpress.com/" target="_blank">MemberPress</a></strong> to be Active!</p>', PyisMemberProfile::$plugin_id ); ?>
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
    
    if ( class_exists( 'MeprHooks' ) ) {
     
        return PyisMemberProfile::get_instance();
        
    }
    else {
        
        add_action( 'admin_notices', array( 'PyisMemberProfile', 'missing_memberpress_notice' ) );
        
    }
    
}