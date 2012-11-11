<?php
/**
 * AppThemes CSV Importer
 *
 * @package Framework
 * @subpackage Importer
 */

require_once ABSPATH . 'wp-admin/includes/import.php';

class APP_Importer extends scbAdminPage {
	var $post_type;
	var $fields;
	var $custom_fields;
	var $taxonomies;
	var $tax_meta;
	var $geodata;

	/*
	 * Args can have 3 elements:
	 * 'taxonomies' => array( 'valid', 'taxonomies' ),
	 * 'custom_fields' => array(
	 * 		'csv_key' => 'internal_key',
	 *		'csv_key' => array(
	 *			'internal_key' => 'key',
	 *			'default' => 'value'
	 *		)
	 *	),
	 * 'tax_meta' => array( array( 'tax' => array( 'csv_key' => 'tax_key' ) )
	 */
	public function __construct( $post_type = 'post', $fields, $args = '' ) {
		$args = wp_parse_args( $args, array(
			'taxonomies' => array(),
			'custom_fields' => array(),
			'tax_meta' => array(),
			'geodata' => false
		) );

		$this->post_type = $post_type;
		$this->fields = $fields;
		$this->taxonomies = $args['taxonomies'];
		$this->tax_meta = $args['tax_meta'];
		$this->geodata = $args['geodata'];

		$this->custom_fields = array();
		foreach ( $args['custom_fields'] as $csv_key => $data ) {
			if ( !is_array( $data ) )
				$data = array( 'internal_key' => $data );

			$this->custom_fields[ $csv_key ] = wp_parse_args( $data, array(
				'internal_key' => $csv_key,
				'default' => ''
			) );
		}

		parent::__construct();
	}

	function setup() {
		$this->textdomain = APP_TD;

		$this->args = array(
			'page_title' => __( 'CSV Importer', APP_TD ),
			'menu_title' => __( 'Importer', APP_TD ),
			'page_slug' => 'app-importer',
			'parent' => 'app-dashboard',
			'screen_icon' => 'tools',
		);
	}

	function form_handler() {} // handled in page_content()

	function page_content() {
		if ( isset( $_GET['step'] ) && 1 == $_GET['step'] ) {
			$this->import();
		}

		if ( defined( 'WP_DEBUG' ) && isset( $_GET['step'] ) && 'export' == $_GET['step'] ) {
			$wud = wp_upload_dir();

			$name = '/export-' . substr( md5( rand() ), 0, 8 ) . '.csv';

			$this->export( $wud['basedir'] . $name );

			echo scb_admin_notice( 'CSV Generated: ' . html_link( $wud['baseurl'] . $name ) );
		}

		echo '<div class="narrow">';
		echo '<p>'. __( 'Below you will find a tool which allows you to import content from other systems via a CSV (comma-separated values) file, which can be edited using a program like Excel. Note that the file must be in the correct format for the import tool to work. You will find an example .csv file in the "examples" theme folder.', 'appthemes' ).'</p>';
		echo '<p>'.__( 'Choose a CSV file to upload, then click "Upload file and import".', 'appthemes' ).'</p>';
		wp_import_upload_form( 'admin.php?page=app-importer&amp;step=1' );
		echo '</div>';
	}

	private function import() {
		check_admin_referer( 'import-upload' );

		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'appthemes' ) . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
		}

		$c = $this->process( $file['file'] );

		if ( false === $c ) {
			echo scb_admin_notice( __( 'The file could not be processed.', 'appthemes' ), 'error' );
		} else {
			echo scb_admin_notice( sprintf( __( 'Imported %s items.', 'appthemes' ), number_format_i18n( $c ) ) );
		}
	}

	private function process( $file ) {
		$handle = fopen( $file, 'r' );

		$headers = fgetcsv( $handle );

		if ( !$headers )
			return false;

		$count = 0;

		setlocale( LC_ALL, get_locale() . '.' . get_option( 'blog_charset' ) );

		while ( false !== $values = fgetcsv( $handle ) ) {
			// ignore blank lines
			if ( null === $values[0] )
				continue;

			$row = array_combine( $headers, $values );

			// ignore invalid lines
			if ( !$row )
				continue;

			if ( $this->import_row( $row ) )
				$count++;
		}

		fclose( $handle );

		return $count;
	}

	private function export( $file ) {
		$handle = fopen( $file, 'w+' );

		$posts = get_posts( array(
			'post_type' => $this->post_type,
			'nopaging' => true
		) );

		$post = array_shift( $posts );
		$row = $this->export_row( $post );

		fputcsv( $handle, array_keys( $row ) );
		fputcsv( $handle, $row );

		foreach ( $posts as $post )
			fputcsv( $handle, $this->export_row( $post ) );

		fclose( $handle );
	}

	private function export_row( $post ) {
		$user = get_user_by( 'id', $post->post_author );
		if ( $user )
			$post->post_author = $user->user_login;

		$row = array();

		foreach ( $this->fields as $col => $field ) {
			$row[ $col ] = $post->$field;
		}

		foreach ( $this->custom_fields as $col => $data ) {
			$row[ $col ] = get_post_meta( $post->ID, $data['internal_key'], true );
		}

		foreach ( $this->taxonomies as $col ) {
			$terms = get_the_terms( $post->ID, $col );
			if ( !$terms )
				$row[ $col ] = '';
			else
				$row[ $col ] = implode( ',', wp_list_pluck( $terms, 'name' ) );
		}

		// TODO: tax_meta

		if ( $this->geodata ) {
			$coord = appthemes_get_coordinates( $post->ID );
			$row['lat'] = $coord->lat;
			$row['lng'] = $coord->lng;
		}

		return $row;
	}

	private function import_row( $row ) {
		$post = array(
			'post_type' => $this->post_type,
			'post_status' => 'publish'
		);
		$post_meta = array();

		$tax_input = array();
		$tax_meta = array();

		foreach ( $this->fields as $col => $field ) {
			if ( isset( $row[ $col ] ) )
				$post[ $field ] = $row[ $col ];
		}

		foreach ( $this->custom_fields as $col => $data ) {
			if ( isset( $row[ $col ] ) )
				$val = $row[ $col ];
			elseif ( '' !== $data['default'] )
				$val = $data['default'];
			else
				continue;

			$post_meta[ $data['internal_key'] ] = $val;
		}

		foreach ( $this->taxonomies as $col ) {
			if ( isset( $row[ $col ] ) )
				$tax_input[ $col ] = array_filter( array_map( 'trim', explode( ',', $row[ $col ] ) ) );
		}

		foreach ( $this->tax_meta as $tax => $fields ) {
			foreach ( $fields as $col => $key ) {
				if ( isset( $row[ $col ] ) ) {
					$term = $tax_input[ $tax ][0];
					$tax_meta[ $tax ][ $term ][ $key ] = $row[ $col ];
				}
			}
		}

		foreach ( $tax_meta as $tax => $terms ) {
			foreach ( $terms as $term => $meta_data ) {
				if ( empty( $term ) )
					continue;

				$t = $this->maybe_create_term( $term, $tax );
				if ( is_wp_error( $t ) )
					continue;

				foreach ( $meta_data as $meta_key => $meta_value ) {
					if ( 'desc' == substr( $meta_key, -4 ) )
						wp_update_term( $t['term_id'], $tax, array( 'description' => sanitize_text_field( $meta_value ) ) );
					else if ( function_exists( 'update_metadata' ) )
						update_metadata( $tax, $t['term_id'], $meta_key, $meta_value );
				}
			}
		}

		foreach ( $tax_input as $tax => $terms ) {
			$_terms = array();
			foreach ( $terms as $term ) {
				if ( empty( $term ) )
					continue;

				$t = $this->maybe_create_term( $term, $tax );

				if ( !is_wp_error( $t ) )
					$_terms[] = (int) $t['term_id'];
			}
			$post['tax_input'][ $tax ] = $_terms;
		}

		if ( !empty( $post['post_author'] ) ) {
			$user = get_user_by( 'login', $post['post_author'] );
			if ( $user )
				$post['post_author'] = $user->ID;
		}

		if ( !empty( $post['post_date'] ) ) {
			$post['post_date'] = date( 'Y-m-d H:i:s', strtotime( $post['post_date'] ) );
		}

		$post_id = wp_insert_post( $post, true );
		if ( is_wp_error( $post_id ) )
			return false;

		foreach ( $post_meta as $meta_key => $meta_value )
			add_post_meta( $post_id, $meta_key, $meta_value, true );

		if ( $this->geodata ) {
			appthemes_set_coordinates( $post_id, $row['lat'], $row['lng'] );
		}

		return true;
	}

	private static function maybe_create_term( $term, $tax ) {
		if ( ! ( $t = term_exists( $term, $tax ) ) )
			$t = wp_insert_term( $term, $tax );

		return $t;
	}
}

