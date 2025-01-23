<?php
/**
 * Plugin Name: Navigation Posts
 * Plugin URI: https://github.com/beauweb/navigation-posts
 * Description: Provides navigation arrows for previous and next posts in the WordPress backend.
 * Version: 1.0.6
 * Requires at least: 4.7
 * Requires PHP: 7.0
 * Tested up to: 6.7.1
 * Stable tag: 1.0.6
 * Author: #beaubhavik
 * Author URI: https://spiderdunia.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: navigation-posts
 * Domain Path: /languages
 */

// Prevent direct file access
defined( 'ABSPATH' ) || exit;

// Ensure WordPress is loaded
if ( ! function_exists( 'add_action' ) ) {
    exit;
}

// Include WordPress Plugin API
if ( ! function_exists( 'is_admin' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// Define plugin constants
define( 'NAVIGATION_POSTS_VERSION', '1.0.6' );
define( 'NAVIGATION_POSTS_FILE', __FILE__ );
define( 'NAVIGATION_POSTS_PATH', plugin_dir_path( NAVIGATION_POSTS_FILE ) );
define( 'NAVIGATION_POSTS_URL', plugin_dir_url( NAVIGATION_POSTS_FILE ) );

// Add WordPress core functions check
function navigation_posts_check_wp_loaded() {
    if ( ! function_exists( 'add_action' ) ) {
        die( 'WordPress not loaded properly. Cannot run plugin.' );
    }
}
navigation_posts_check_wp_loaded();

// Compatibility check
function navigation_posts_check_compatibility() {
    global $wp_version;
    
    if ( version_compare( $wp_version, '4.7', '<' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( sprintf(
            /* translators: %s: WordPress version */
            esc_html__( 'Navigation Posts requires WordPress version %s or higher. Please upgrade WordPress first.', 'navigation-posts' ),
            '4.7'
        ) );
    }
}
register_activation_hook( __FILE__, 'navigation_posts_check_compatibility' );

// Version notice
function navigation_posts_version_notice() {
    global $wp_version;
    
    if ( version_compare( $wp_version, '6.7.1', '>' ) ) {
        $message = sprintf(
            /* translators: %s: WordPress version */
            esc_html__( 'Navigation Posts has not been tested with WordPress version %s. It may still work, but you should test it thoroughly before using it in production.', 'navigation-posts' ),
            esc_html( $wp_version )
        );
        printf(
            '<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
            wp_kses_post( $message )
        );
    }
}
add_action( 'admin_notices', 'navigation_posts_version_notice' );

// Enqueue assets
function navigation_posts_enqueue_assets( $hook ) {
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
        return;
    }

    wp_enqueue_style( 
        'navigation-posts-style', 
        plugins_url( 'css/navigation-posts.css', __FILE__ ), 
        array(), 
        NAVIGATION_POSTS_VERSION 
    );
    
    if ( is_rtl() ) {
        wp_enqueue_style(
            'navigation-posts-rtl', 
            plugins_url( 'css/rtl.css', __FILE__ ),
            array(),
            NAVIGATION_POSTS_VERSION
        );
    }
}
add_action( 'admin_enqueue_scripts', 'navigation_posts_enqueue_assets' );

// Add navigation arrows
function navigation_posts_add_arrows() {
    $post = get_post();
    
    if ( ! $post ) {
        return;
    }

    $post_type = get_post_type( $post );
    
    $all_posts = get_posts( array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'any',
        'fields'         => 'ids',
    ) );

    $current_index = array_search( $post->ID, $all_posts );
    $previous_post_id = ( $current_index < count( $all_posts ) - 1 ) ? $all_posts[ $current_index + 1 ] : null;
    $next_post_id = ( $current_index > 0 ) ? $all_posts[ $current_index - 1 ] : null;

    $prev_url = $previous_post_id ? get_edit_post_link( $previous_post_id ) : '';
    $next_url = $next_post_id ? get_edit_post_link( $next_post_id ) : '';

    $arrows_html = sprintf(
        '<div id="navigation-posts-arrows" class="navigation-posts-arrows">
            <a href="%s" %s title="%s" class="navigation-posts-arrow">&#9650;</a>
            <a href="%s" %s title="%s" class="navigation-posts-arrow">&#9660;</a>
        </div>',
        esc_url( $next_url ),
        $next_url ? '' : 'class="disabled navigation-posts-arrow"',
        esc_attr__( 'Next Post', 'navigation-posts' ),
        esc_url( $prev_url ),
        $prev_url ? '' : 'class="disabled navigation-posts-arrow"',
        esc_attr__( 'Previous Post', 'navigation-posts' )
    );

    echo wp_kses_post( $arrows_html );
}
add_action( 'admin_footer-post.php', 'navigation_posts_add_arrows' );
add_action( 'admin_footer-post-new.php', 'navigation_posts_add_arrows' );

// Load text domain
function navigation_posts_load_textdomain() {
    load_plugin_textdomain( 'navigation-posts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'navigation_posts_load_textdomain' );
