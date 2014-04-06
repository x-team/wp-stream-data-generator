<?php

class WP_Stream_Data_Generator_Form {

	/**
	 * Public constructor
	 */
	public static function load() {

		// Enqueue our form scripts
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 11 );

	}

	/**
	 * Enqueue our scripts, in our own page only
	 *
	 * @action admin_enqueue_scripts
	 * @param  string $hook Current admin page slug
	 * @return void
	 */
	public static function enqueue_scripts( $hook ) {

		// If we are not on the right page we return early
		if ( $hook !== WP_Stream_Data_Generator::$screen_id ) {
			return;
		};

		// JavaScript register
		wp_register_script(
			'wp-stream-data-generator',
			WP_STREAM_DATA_GENERATOR_URL . 'ui/stream-data-generator.js',
			array(),
			WP_Stream_Data_Generator::VERSION,
			'screen'
		);

		// CSS register
		wp_register_style(
			'wp-stream-data-generator',
			WP_STREAM_DATA_GENERATOR_URL . 'ui/stream-data-generator.css',
			array(),
			WP_Stream_Data_Generator::VERSION,
			'screen'
		);

		// Localization
		wp_localize_script(
			'wp-stream-data-generator',
			'wp_stream_data_generator',
			array(
				'i18n' => array(
					'confirm_generate' => __( 'Are you sure you want to permanently create these dummy records? This action can not be undone.', 'stream-data-generator' ),
				),
				'gmt_offset' => get_option( 'gmt_offset' ),
			)
		);

		// JavaScript enqueue
		wp_enqueue_script(
			array(
				'jquery-ui-datepicker',
				'wp-stream-data-generator',
			)
		);

		// CSS enqueue
		wp_enqueue_style(
			array(
				'jquery-ui',
				'wp-stream-datepicker',
				'wp-stream-data-generator',
			)
		);

	}

	/**
	 * Display the export form
	 *
	 * @return null
	 */
	public static function display_form() {

		include( WP_STREAM_DATA_GENERATOR_VIEW_DIR . 'form.php' );

	}

}
