<?php

/**
 * Plugin Name: Mashshare Share Buttons
 * Plugin URI: https://www.mashshare.net
 * Description: Mashshare is a Share functionality inspired by the the great website Mashable for Facebook and Twitter. More networks available.
 * Author: René Hermenau
 * Author URI: https://www.mashshare.net
 * Version: 3.8.0
 * Text Domain: mashsb
 * Domain Path: /languages

 *
 * Mashshare Share Buttons is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Mashshare Share Buttons is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Mashshare Share Buttons. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package MASHSB
 * @category Core
 * @author René Hermenau
 * @version 2.3.6
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
    exit;

// Plugin version
if( !defined( 'MASHSB_VERSION' ) ) {
    define( 'MASHSB_VERSION', '3.8.0' );
}

// Debug mode
if( !defined( 'MASHSB_DEBUG' ) ) {
    define( 'MASHSB_DEBUG', false );
}


if( !class_exists( 'Mashshare' ) ) :

    /**
     * Main mashsb Class
     *
     * @since 1.0.0
     */
    final class Mashshare {
        /** Singleton ************************************************************ */

        /**
         * @var Mashshare The one and only Mashshare
         * @since 1.0
         */
        private static $instance;

        /**
         * MASHSB HTML Element Helper Object
         *
         * @var object
         * @since 2.0.0
         */
        public $html;

        /* MASHSB LOGGER Class
         * 
         */
        public $logger;
        
        /**
         * MASHSB TEMPLATE Object
         * @var object 
         */
        public $template;

        /**
         * Main Mashshare Instance
         *
         * Insures that only one instance of mashshare exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @since 1.0
         * @static
         * @staticvar array $instance
         * @uses mashshare::setup_constants() Setup the constants needed
         * @uses mashshare::includes() Include the required files
         * @uses mashshare::load_textdomain() load the language files
         * @see MASHSB()
         * @return The one true mashshare
         */
        public static function instance() {
            if( !isset( self::$instance ) && !( self::$instance instanceof Mashshare ) ) {
                self::$instance = new Mashshare;
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->load_hooks();
                self::$instance->html = new MASHSB_HTML_Elements();
                self::$instance->logger = new mashsbLogger( "mashlog_" . date( "Y-m-d" ) . ".log", mashsbLogger::INFO );
                self::$instance->template = new mashsbBuildTemplates();
            }
            return self::$instance;
        }

        /**
         * Throw error on object clone
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @since 1.0
         * @access protected
         * @return void
         */
        public function __clone() {
            // Cloning instances of the class is forbidden
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'MASHSB' ), '1.0' );
        }

        /**
         * Disable unserializing of the class
         *
         * @since 1.0
         * @access protected
         * @return void
         */
        public function __wakeup() {
            // Unserializing instances of the class is forbidden
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'MASHSB' ), '1.0' );
        }

        /**
         * Setup plugin constants
         *
         * @access private
         * @since 1.0
         * @return void
         */
        private function setup_constants() {
            global $wpdb;

            // Plugin Folder Path
            if( !defined( 'MASHSB_PLUGIN_DIR' ) ) {
                define( 'MASHSB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            // Plugin Folder URL
            if( !defined( 'MASHSB_PLUGIN_URL' ) ) {
                define( 'MASHSB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }

            // Plugin Root File
            if( !defined( 'MASHSB_PLUGIN_FILE' ) ) {
                define( 'MASHSB_PLUGIN_FILE', __FILE__ );
            }

            // Plugin database
            // Plugin Root File
            if( !defined( 'MASHSB_TABLE' ) ) {
                define( 'MASHSB_TABLE', $wpdb->prefix . 'mashsharer' );
            }
        }

        /**
         * Include required files
         *
         * @access private
         * @since 1.0
         * @return void
         */
        private function includes() {
            global $mashsb_options;

            require_once MASHSB_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
            $mashsb_options = mashsb_get_settings();
            require_once MASHSB_PLUGIN_DIR . 'includes/scripts.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/template-functions.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/class-mashsb-license-handler.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/class-mashsb-html-elements.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/debug/classes/MashDebug.interface.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/debug/classes/MashDebug.class.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/logger.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/actions.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/helper.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/class-mashsb-shared-posts-widget.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/admin/settings/metabox-settings.php'; /* move into is_admin */
            require_once MASHSB_PLUGIN_DIR . 'includes/admin/meta-box/meta-box.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/header-meta-tags.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/class-build-templates.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/sharecount-functions.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/shorturls.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/libraries/class-google-shorturl.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/libraries/class-bitly-shorturl.php';
            //require_once MASHSB_PLUGIN_DIR . 'includes/admin/tracking.php'; // Ensure cron is loading even on frontpage
            require_once MASHSB_PLUGIN_DIR . 'includes/debug/debug.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/amp.php';
            require_once MASHSB_PLUGIN_DIR . 'includes/cron.php';

            if( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
                require_once MASHSB_PLUGIN_DIR . 'includes/install.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/add-ons.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/admin-actions.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/admin-notices.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/admin-footer.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/admin-pages.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/plugins.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/welcome.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/settings/user-profiles.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/tools.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/dashboard.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/feedback.php';
                require_once MASHSB_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
            }
        }
        
        public static function load_hooks() {
            if( is_admin() && mashsb_is_plugins_page() ) {
                add_filter( 'admin_footer', 'mashsb_add_deactivation_feedback_modal' );
            }
        }

        /**
         * Loads the plugin language files
         *
         * @access public
         * @since 1.4
         * @return void
         */
        public function load_textdomain() {
            // Set filter for plugin's languages directory
            $mashsb_lang_dir = dirname( plugin_basename( MASHSB_PLUGIN_FILE ) ) . '/languages/';
            $mashsb_lang_dir = apply_filters( 'mashsb_languages_directory', $mashsb_lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'mashsb' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'mashsb', $locale );

            // Setup paths to current locale file
            $mofile_local = $mashsb_lang_dir . $mofile;
            $mofile_global = WP_LANG_DIR . '/mashsb/' . $mofile;
            //echo $mofile_local;
            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/MASHSB folder
                load_textdomain( 'mashsb', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/mashshare/languages/ folder
                load_textdomain( 'mashsb', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'mashsb', false, $mashsb_lang_dir );
            }
        }

    }

    endif; // End if class_exists check

/**
 * The main function responsible for returning the one true Mashshare
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: $MASHSB = MASHSB();
 *
 * @since 2.0.0
 * @return object The one true Mashshare Instance
 */
function MASHSB() {
    return Mashshare::instance();
}
// Get MASHSB Running
MASHSB();
