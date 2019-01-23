<?php
/**
 * Thinkery Importer
 *
 * @package Thinkery
 */

/**
 * This is the class for importing things from thinkery.me
 *
 * @since 0.6
 *
 * @package Thinkery
 * @author Alex Kirk
 */

class Thinkery_Importer {
	/**
	 * Contains a reference to the Thinkery class.
	 *
	 * @var Thinkery
	 */
	private $thinkery;

	/**
	 * Constructor
	 *
	 * @param Thinkery $thinkery A reference to the Thinkery object.
	 */
	public function __construct( Thinkery $thinkery ) {
		$this->thinkery = $thinkery;
		add_action( 'admin_init', array( $this, 'register_admin_hooks' ) );
	}

	/**
	 * Register the WordPress hooks
	 */
	public function register_admin_hooks() {
		register_importer( 'thinkery', 'Thinkery', __('Import things from thinkery.me', 'thinkery'), array( $this, 'dispatch' ) );
	}

	public function dispatch() {
		include __DIR__ . '/templates/importer/header.php';

		$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
		switch ( $step ) {
			case 0:
			include __DIR__ . '/templates/importer/step0.php';
				break;
			case 1:
				check_admin_referer( 'import-upload' );
				$this->handle_upload();
		}

		include __DIR__ . '/templates/importer/footer.php';
	}

	/**
	 * Handles the upload and parses the file
	 *
	 * @return bool False if error uploading or invalid file, true otherwise
	 */
	function handle_upload() {
		$file = get_attached_file( 3268 );
		// $file = wp_import_handle_upload(); $file = $file['file'];
		if ( isset( $file['error'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'thinkery' ) . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
		} else if ( ! file_exists( $file['file'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'thinkery' ) . '</strong><br />';
			printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'thinkery' ), esc_html( $file['file'] ) );
			echo '</p>';
			return false;
		}

		$parser = new Thinkery_XML_Parser();
		$import_data =  $parser->parse( $file );
		if ( is_wp_error( $import_data ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'thinkery' ) . '</strong><br />';
			echo esc_html( $import_data->get_error_message() ) . '</p>';
			return false;
		}

		foreach ( $import_data['posts'] as $post ) {
			$post = apply_filters( 'wp_import_post_data_raw', $post );

			$post_type_object = get_post_type_object( $post['post_type'] );
			$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );

			$post_id = wp_insert_post( $post, true );

		}
		return true;
	}

}
