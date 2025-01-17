<?php


//namespace Groundhogg\Blocks\Gutenberg_New;

use function Groundhogg\get_db;


/**
 * Get form HTML to display in a Groundhogg Gutenberg block.
 *
 * @param array $attr Attributes passed by WPForms Gutenberg block.
 *
 * @return string
 * @since 1.4.8
 *
 */
function get_gutenberg_form_html( $attr ) {

	$id = ! empty( $attr['formId'] ) ? absint( $attr['formId'] ) : 0;

	if ( empty( $id ) ) {
		return '';
	}

	$title = ! empty( $attr['displayTitle'] ) ? true : false;
	if ( empty( $id ) ) {
		return '';
	}

	ob_start();

	echo "<div class='" . $attr['className'] . "'>";

	echo do_shortcode( ' [gh_form id="' . $id . '" title="' . $title . '"] ' );

	echo "</div>";

	return ob_get_clean();
}


add_action( 'init', 'groundhogg_gutenberg_form_selector_init' );
function groundhogg_gutenberg_form_selector_init() { // phpcs:ignore


	$i18n = array(
		'title'            => esc_html__( 'Groundhogg', 'groundhogg' ),
		'description'      => esc_html__( 'Select and display one of your forms.', 'groundhogg' ),
		'form_select'      => esc_html__( 'Select a Form', 'groundhogg' ),
		'form_settings'    => esc_html__( 'Form Settings', 'groundhogg' ),
		'form_selected'    => esc_html__( 'Form', 'groundhogg' ),
		'show_title'       => esc_html__( 'Show Title', 'groundhogg' ),
		'show_description' => esc_html__( 'Show Description', 'groundhogg' ),
	);

	// Register block styles for both frontend + backend.
	wp_register_style(
		'groundhogg-form-style-css', // Handle.
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		is_admin() ? array( 'wp-editor' ) : null, // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	);

	// Register block editor script for backend.
	wp_register_script(
		'groundhogg-form-block-js', // Handle.
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
		null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		true // Enqueue the script in the footer.
	);

	// Register block editor styles for backend.
	wp_register_style(
		'groundhogg-form-block-editor-css', // Handle.
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
	);

	$forms = get_db( 'steps' )->query( [ 'step_type' => 'form_fill' ] );

	wp_localize_script(
		'groundhogg-form-block-js',
		'groundhogg_gutenberg_form_selector',
		array(
			'logo_url' => GROUNDHOGG_ASSETS_URL . 'images/phil-340x340.png',
			'forms'    => ! empty( $forms ) ? $forms : array(),
			'i18n'     => $i18n,
		)
	);


	// Enqueue the Groundhogg form style.
	register_block_type( 'groundhogg/forms', array(
		'attributes' => array(
			'formId'       => array(
				'type' => 'string',
			),
			'displayTitle' => array(
				'type' => 'boolean',
			),
			'displayDesc'  => array(
				'type' => 'boolean',
			),
		),

		'render_callback' => 'get_gutenberg_form_html',
		'style'           => 'groundhogg-form-style-css',
		// Enqueue blocks.build.js in the editor only.
		'editor_script'   => 'groundhogg-form-block-js',
		// Enqueue blocks.editor.build.css in the editor only.
		'editor_style'    => 'groundhogg-form-block-editor-css',

	) );


}

