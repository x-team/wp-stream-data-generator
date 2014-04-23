<?php
/**
 * Plugin Name: Stream Data Generator
 * Depends: Stream
 * Plugin URI: http://x-team.com
 * Description: TBD
 * Version: 0.1.0
 * Author: X-Team
 * Author URI: http://x-team.com/wordpress/
 * License: GPLv2+
 * Text Domain: stream-data-generator
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2014 X-Team (http://x-team.com/)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

class WP_Stream_Data_Generator {

	/**
	 * Holds plugin minimum version
	 *
	 * @const string
	 */
	const STREAM_MIN_VERSION = '1.4.0';

	/**
	 * Holds this plugin version
	 * Used in assets cache
	 *
	 * @const string
	 */
	const VERSION = '0.1.0';

	/*
	 * @const string
	 */
	const GENERATOR_PAGE_SLUG = 'wp_stream_data_generator';

	/**
	 * Capability for the generator screen to be viewed
	 *
	 * @const string
	 */
	const VIEW_CAP = 'manage_options'; // was: view_stream_generator

	/**
	 * Hold Stream Data Generator instance
	 *
	 * @var string
	 */
	public static $instance;

	/**
	 * Screen ID for admin data generator page
	 * @var string
	 */
	public static $screen_id;

	/**
	 * Holds admin notices messages
	 *
	 * @var array
	 */
	public static $messages = array();

	/**
	 * Hold the random blog list list
	 */
	public $blogs = array();

	/**
	 * Hold the random authors list
	 */
	public $authors = array();

	/**
	 * Hold the random summary list
	 */
	public $summaries = array(
		'Lorem ipsum dolor sit amet',
		'Has homero erroribus definitionem ut',
		'Ei mei quas etiam dicant homero',
		'Assum conceptam vim ei per recteque',
		'Novum constituto id sit no vivendum',
		'Graeco recteque salutatus eum at',
		'Utinam fabellas verterem ad mei',
		'Id quis sale natum mei expetenda',
		'Ea qui doctus vidisse denique recteque',
		'Odio expetenda in his semper constituto',
		'Id est iudico possit ipsum doctus',
		'Nisl democritum tae ex tota periculis',
		'Voluptua scaevola ut nec nam ullum',
		'No nam vivendum urbanitas id vim',
		'In utamur minimum vix dicat lobortis',
		'Ad quis aliquid democritum quo et',
	);

	/**
	 * Hold the random connector list
	 */
	public $connectors = array(
		'WP_Stream_Connector_Comments',
		'WP_Stream_Connector_Editor',
		'WP_Stream_Connector_Installer',
		'WP_Stream_Connector_Media',
		'WP_Stream_Connector_Posts',
		'WP_Stream_Connector_Settings',
		'WP_Stream_Connector_Taxonomies',
		'WP_Stream_Connector_Users',
		'WP_Stream_Connector_Widgets',
	);

	/**
	 * Hold the random IP list
	 */
	public $ips = array(
		'20.234.34.176',
		'229.96.46.213',
		'243.2.209.232',
		'54.181.100.17',
		'113.122.220.87',
		'248.11.219.94',
		'147.99.134.174',
		'19.198.32.69',
		'119.142.227.29',
		'57.111.227.148',
		'165.36.249.185',
		'143.144.15.113',
		'53.76.251.191',
		'59.136.23.181',
		'205.90.239.27',
		'88.173.38.89',
	);

	/**
	 * Class constructor
	 */
	private function __construct() {
		define( 'WP_STREAM_DATA_GENERATOR_DIR', plugin_dir_path( __FILE__ ) );
		define( 'WP_STREAM_DATA_GENERATOR_URL', plugin_dir_url( __FILE__ ) );
		define( 'WP_STREAM_DATA_GENERATOR_INC_DIR', WP_STREAM_DATA_GENERATOR_DIR . 'includes/' );
		define( 'WP_STREAM_DATA_GENERATOR_VIEW_DIR', WP_STREAM_DATA_GENERATOR_DIR . 'views/' );

		add_action( 'plugins_loaded', array( $this, 'load' ) );
	}

	/**
	 * Load our classes, actions/filters, only if Stream is activated.
	 *
	 * @return void
	 */
	public function load() {
		add_action( 'all_admin_notices', array( __CLASS__, 'admin_notices' ) );

		if ( ! $this->is_dependency_satisfied() ) {
			return;
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

		if ( is_network_admin() ) {
			$this->blogs = array_merge(
				array(
					array( 'blog_id' => 0 ),
				),
				wp_get_sites()
			);
		}

		$this->authors = get_users();

		if ( is_network_admin() ) {
			// Network admin menu
			add_action( 'network_admin_menu', array( $this, 'register_menu' ), 15 );
		} else {
			// Admin menu
			add_action( 'admin_menu', array( $this, 'register_menu' ), 15 );
		}
	}

	public function register_menu() {
		self::$screen_id = add_submenu_page(
			WP_Stream_Admin::RECORDS_PAGE_SLUG,
			__( 'Data Generator', 'stream' ),
			__( 'Data Generator', 'stream' ),
			self::VIEW_CAP,
			self::GENERATOR_PAGE_SLUG,
			array( __CLASS__, 'page' )
		);

		// Load form class
		require_once( WP_STREAM_DATA_GENERATOR_INC_DIR . 'form.php' );
		add_action( 'load-' . self::$screen_id, array( 'WP_Stream_Data_Generator_Form', 'load' ) );

		add_action( 'load-' . self::$screen_id, array( $this, 'prepare_records' ) );
	}

	public static function page() {
		WP_Stream_Data_Generator_Form::display_form();
	}

	public function prepare_records() {
		$page  = wp_stream_filter_input( INPUT_POST, 'page' );
		$form  = wp_stream_filter_input( INPUT_POST, 'wp_stream_data_generator' );
		$nonce = wp_stream_filter_input( INPUT_POST, 'stream_data_generator_nonce' );

		if ( WP_Stream_Data_Generator::GENERATOR_PAGE_SLUG != $page ) {
			return false;
		}

		if ( ! $form ) {
			return false;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return false;
		}

		if ( ! current_user_can( self::VIEW_CAP ) ) {
			return false;
		}

		if ( ! wp_verify_nonce( $nonce, 'stream-data-generator-page' ) ) {
			_e( 'Cheating huh?', 'stream-data-generator' );
			die();
		}

		$errors = false;

		if ( empty ( $form['date_from'] ) || empty ( $form['date_to'] ) ) {
			self::$messages['wp_stream_data_generator_date_from'] = sprintf(
				'<div class="error"><p>%s</p></div>',
				esc_html__( 'The Start Date or End Date was not defined. Please try again.', 'stream-data-generator' )
			); // xss okay
			return false;
		}

		$args = array(
			'date_from'            => $form['date_from'],
			'date_to'              => $form['date_to'],
			'entries_per_day_from' => intval( $form['entries_per_day_from'] ),
			'entries_per_day_to'   => intval( $form['entries_per_day_to'] ),
		);
		$this->generate( $args );
	}

	public function generate( $args ) {
		wp_parse_args(
			$args,
			array(
				'date_from'            => '-1 year',
				'date_to'              => 'today',
				'entries_per_day_from' => 2,
				'entries_per_day_to'   => 40,
			)
		);

		$i = strtotime( $args['date_from'] );

		while ( $i < strtotime( $args['date_to'] ) ) {

			$rand_author = array_rand( $this->authors, 1 );
			$rand_author = $this->authors[ $rand_author ]->ID;

			$author      = get_user_by( 'id', $rand_author );
			$author_role = isset( $author->roles[0] ) ? $author->roles[0] : null;

			if ( is_multisite() ) {
				if ( is_network_admin() ) {
					$rand_blog = array_rand( $this->blogs, 1 );
					$blog_id   = $this->blogs[ $rand_blog ]['blog_id'];
				} else {
					$blog_id = get_current_blog_id();
				}
			} else {
				$blog_id = 1;
			}

			$rand_date = date( 'Y-m-d H:i:s', $i );

			$rand_summary = array_rand( $this->summaries, 1 );
			$rand_summary = $this->summaries[ $rand_summary ];

			$rand_connector = array_rand( $this->connectors, 1 );
			$rand_connector = $this->connectors[ $rand_connector ];

			$contexts     = $rand_connector::get_context_labels();
			$rand_context = array_rand( $contexts, 1 );

			$actions     = $rand_connector::get_action_labels();
			$rand_action = array_rand( $actions, 1 );

			$rand_ip = array_rand( $this->ips, 1 );
			$rand_ip = $this->ips[ $rand_ip ];

			$recordarr = array(
				'blog_id'     => $blog_id,
				'object_id'   => null,
				'author'      => $rand_author,
				'author_role' => $author_role,
				'created'     => $rand_date,
				'summary'     => $rand_summary,
				'parent'      => 0,
				'connector'   => $rand_connector::$name,
				'contexts'    => array( $rand_context => $rand_action ),
				'meta'        => array(),
				'ip'          => $rand_ip,
			);

			WP_Stream_DB::get_instance()->insert( $recordarr );

			$i = $i + ( 86400 / mt_rand( $args['entries_per_day_from'], $args['entries_per_day_to'] ) );
		}
		self::$messages['wp_stream_data_generator_date_from'] = sprintf(
			'<div class="updated"><p>%s</p></div>',
			esc_html__( 'The records have been successfully generated.', 'stream-data-generator' )
		); // xss okay
	}

	/**
	 * Check if plugin dependencies are satisfied and add an admin notice if not
	 *
	 * @return bool
	 */
	public function is_dependency_satisfied() {
		$message = '';

		if ( ! class_exists( 'WP_Stream' ) ) {
			$message .= sprintf( '<p>%s</p>', __( 'Stream Data Generator requires the Stream plugin to be present and activated.', 'stream-data-generator' ) );
		} else if ( version_compare( WP_Stream::VERSION, self::STREAM_MIN_VERSION, '<' ) ) {
			$message .= sprintf( '<p>%s</p>', sprintf( __( 'Stream Data Generator requires Stream version %s or higher', 'stream-data-generator' ), self::STREAM_MIN_VERSION ) );
		}

		if ( ! empty( $message ) ) {
			self::$messages['wp_stream_db_error'] = sprintf(
				'<div class="error">%s<p>%s</p></div>',
				$message,
				sprintf(
					__( 'Please <a href="%s" target="_blank">install</a> Stream plugin version %s or higher for Stream Data Generator to work properly.', 'stream-data-generator' ),
					esc_url( 'http://wordpress.org/plugins/stream/' ),
					self::STREAM_MIN_VERSION
				)
			); // xss okay

			return false;
		}

		return true;
	}

	/**
	 * Display all messages on admin board
	 *
	 * @return void
	 */
	public static function admin_notices() {
		foreach ( self::$messages as $message ) {
			echo wp_kses_post( $message );
		}
	}

	/**
	 * Return active instance of WP_STREAM_DATA_GENERATOR, create one if it doesn't exist
	 *
	 * @return WP_STREAM_DATA_GENERATOR
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			$class = __CLASS__;
			self::$instance = new $class;
		}

		return self::$instance;
	}

}

$GLOBALS['WP_STREAM_DATA_GENERATOR'] = WP_Stream_Data_Generator::get_instance();
