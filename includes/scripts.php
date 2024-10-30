<?php
/**
 * Scripts
 *
 * @package     LeaflyReviews\Scripts
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Load scripts and styles
 *
 * @since       1.0.0
 * @return      void
 */
function leaflyreviews_load_scripts() {
    //wp_enqueue_script( 'leaflyreviews', LEAFLYREVIEWS_URL . 'assets/js/scripts.js', array( 'jquery' ) );
    wp_enqueue_style( 'leaflyreviews', LEAFLYREVIEWS_URL . 'assets/css/style.css' );
}
add_action( 'wp_enqueue_scripts', 'leaflyreviews_load_scripts' );


/**
 * Load admin scripts and styles
 *
 * @since       1.1.0
 * @return      void
 */
function leaflyreviews_load_admin_scripts() {
    wp_enqueue_style( 'leaflyreviews', LEAFLYREVIEWS_URL . 'assets/css/admin.css' );
}
add_action( 'admin_enqueue_scripts', 'leaflyreviews_load_admin_scripts' );