<?php
/**
 * Plugin Name: Navigation Posts
 * Plugin URI: https://github.com/beaushowcase/navigation-posts
 * Description: Provides navigation arrows for previous and next posts in the WordPress backend.
 * Version: 1.0.2
 * Author: #beaubhavik
 * Author URI: https://spiderdunia.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: navigation-posts
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Enqueue styles and scripts
add_action( 'admin_enqueue_scripts', 'navigation_posts_enqueue_assets' );

/**
 * Enqueue plugin assets
 */
function navigation_posts_enqueue_assets( $hook ) {
    // Only enqueue on post.php and post-new.php pages
    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
        return;
    }

    wp_enqueue_style( 'navigation-posts-style', plugins_url( 'css/navigation-posts.css', __FILE__ ), array(), '1.0.2' );
}

// Add action to include our navigation in the admin area
add_action( 'admin_footer-post.php', 'navigation_posts_add_arrows' );
add_action( 'admin_footer-post-new.php', 'navigation_posts_add_arrows' );

/**
 * Function to add navigation arrows
 */
function navigation_posts_add_arrows() {
    $post = get_post();
    
    if ( ! $post ) {
        return;
    }

    $post_type = get_post_type( $post );
    
    // Get all posts of the current post type
    $all_posts = get_posts( array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => 'any',
        'fields'         => 'ids',
    ) );

    // Find the current post index
    $current_index = array_search( $post->ID, $all_posts );

    // Get previous and next post IDs
    $previous_post_id = ( $current_index < count( $all_posts ) - 1 ) ? $all_posts[ $current_index + 1 ] : null;
    $next_post_id = ( $current_index > 0 ) ? $all_posts[ $current_index - 1 ] : null;

    // Prepare URLs for previous and next posts
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

/**
 * Load plugin textdomain
 */
function navigation_posts_load_textdomain() {
    load_plugin_textdomain( 'navigation-posts', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'navigation_posts_load_textdomain' );
