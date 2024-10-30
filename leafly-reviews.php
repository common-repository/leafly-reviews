<?php
/**
 * Plugin Name:     Leafly Reviews
 * Plugin URI:      https://www.wpdispensary.com
 * Description:     Easily display your leafly dispensary reviews on your own website
 * Version:         1.0.1
 * Author:          WP Dispensary
 * Author URI:      https://www.wpdispensary.com
 * Text Domain:     leafly-reviews
 *
 * @package         LeaflyReviews
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! class_exists( 'LeaflyReviews' ) ) {


    /**
     * Main LeaflyReviews class
     *
     * @since       1.0.0
     */
    class LeaflyReviews {


        /**
         * @access      private
         * @since       1.0.0
         * @var         LeaflyReviews $instance The one true LeaflyReviews
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true LeaflyReviews
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new LeaflyReviews();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin path
            define( 'LEAFLYREVIEWS_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'LEAFLYREVIEWS_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include required files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            require_once LEAFLYREVIEWS_DIR . 'includes/functions.php';
            require_once LEAFLYREVIEWS_DIR . 'includes/shortcodes.php';
            require_once LEAFLYREVIEWS_DIR . 'includes/scripts.php';
            require_once LEAFLYREVIEWS_DIR . 'includes/widget.php';
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'lealyreviews_language_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), '' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'leafly-reviews', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/leafly-reviews/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/leafly-reviews/ folder
                load_textdomain( 'leafly-reviews', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/leafly-reviews/languages/ folder
                load_textdomain( 'leafly-reviews', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'leafly-reviews', false, $lang_dir );
            }
        }
    }
}


/**
 * The main function responsible for returning the one true LeaflyReviews
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      LeaflyReviews The one true LeaflyReviews
 */
function lealyreviews_load() {
    return LeaflyReviews::instance();
}
add_action( 'plugins_loaded', 'lealyreviews_load' );


/**
 * Create the settings page for the LeaflyReviews plugin
 *
 * @since       1.0.0
 */

// create custom plugin settings menu
add_action('admin_menu', 'lrwp_create_menu');

function lrwp_create_menu() {

	//create new top-level menu
	add_submenu_page( 'options-general.php', 'Leafly Reviews', 'Leafly Reviews', 'manage_options', 'leafly_reviews', 'lrwp_settings_page' );

	//call register settings function
	add_action( 'admin_init', 'register_mysettings' );
}


function register_mysettings() {
	//register our settings
	register_setting( 'lrwp-settings-group', 'app_id' );
	register_setting( 'lrwp-settings-group', 'app_key' );
}

function lrwp_settings_page() {
?>
<div class="wrap">
<h2>Leafly Reviews</h2>
<p>In order to utilize this plugin, you need to create your API ID and KEY. Learn more about this <a href="http://developer.leafly.com/" target="_blank">here</a></p>

<form method="post" action="options.php">
    <?php settings_fields( 'lrwp-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">APP ID:</th>
        <td><input type="text" name="app_id" value="<?php echo get_option('app_id'); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">APP KEY</th>
        <td><input type="text" name="app_key" value="<?php echo get_option('app_key'); ?>" /></td>
        </tr>

    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php } ?>
