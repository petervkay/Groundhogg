<?php

namespace Groundhogg\Form_Blocks;

function register_form_category( $categories, $post ) {
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'groundhogg',
                'title' => __( 'Groundhogg Form Elements', 'groundhogg' ),
            ),
        )
    );
}

add_filter( 'block_categories',  __NAMESPACE__ . '\register_form_category', 10, 2 );

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
/**
 * Enqueue block editor only JavaScript and CSS.
 */
function enqueue_block_editor_assets() {
    // Make paths variables so we don't write em twice ;)
    $block_path = 'assets/js/editor.blocks.js';
    $style_path = 'assets/css/blocks.editor.css';

    // Enqueue the bundled block JS file
    wp_enqueue_script(
        'groundhogg-blocks',
        plugin_dir_url( WPGH_FILE ) . $block_path,
        [ 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components' ],
        filemtime( plugin_dir_path( WPGH_FILE ) . $block_path )
    );

    // Enqueue optional editor only styles
    wp_enqueue_style(
        'groundhogg-blocks-editor-css',
        plugin_dir_url( WPGH_FILE ) . $style_path,
        [ 'wp-blocks' ],
        filemtime( plugin_dir_path( WPGH_FILE ) . $style_path )
    );
}

add_action( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_assets' );
/**
 * Enqueue front end and editor JavaScript and CSS assets.
 */
function enqueue_assets() {
    $style_path = 'assets/css/blocks.style.css';
    wp_enqueue_style(
        'groundhogg-blocks',
        plugin_dir_url( WPGH_FILE ) . $style_path,
        [ 'wp-blocks' ],
        filemtime( plugin_dir_path( WPGH_FILE ) . $style_path )
    );
}

add_action( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_frontend_assets' );
/**
 * Enqueue frontend JavaScript and CSS assets.
 */
function enqueue_frontend_assets() {

    // If in the backend, bail out.
    if ( is_admin() ) {
        return;
    }

    $block_path = 'assets/js/frontend.blocks.js';
    wp_enqueue_script(
        'groundhogg-blocks-frontend',
        plugin_dir_url( WPGH_FILE ) . $block_path,
        [],
        filemtime( plugin_dir_path( WPGH_FILE ) . $block_path )
    );
}