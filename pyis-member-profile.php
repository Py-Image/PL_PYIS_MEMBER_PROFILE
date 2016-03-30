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