<?php
/**
 * Short description
 *
 * @package rd-dashboard-pdf
 * @version 1.1.6
 */

/*
Plugin Name: RD Dashboard pdf
Plugin URI: https://github.com/yat8823jp/rd-dashboard-pdf
Description: Display pdf on the dashboard. For example, user's manual etc.
Author: YAT
Version: 1.1.6
Text Domain: rd-dashboard-pdf
*/

/**
 * Init
 */
function rddp_init() {
	add_settings_section(
		'rddp-section',
		'File upload',
		null,
		'rddp'
	);
	add_settings_field(
		'rddp-file',
		__( 'pdf file to display on the dashboard', 'rd-dashboard-pdf' ),
		'rddp_file_display',
		'rddp',
		'rddp-section',
		array(
			'label_for' => 'rddp-file',
		)
	);
	register_setting(
		'rddp-section',
		'rddp-file',
		'rddp_file_upload'
	);

	add_action( 'wp_dashboard_setup', 'rddp_dashboard_widgets' );
}

add_action( 'admin_init', 'rddp_init' );


/**
 * Setting page
 */
function rddp_setting_page() {
?>
	<div class="wrap">
		<form action="options.php" id="rddp-form" method="post" enctype="multipart/form-data">
			<?php
				settings_fields( 'rddp-section' );
				do_settings_sections( 'rddp' );
				submit_button();
			?>
		</form>
	</div>
<?php
}

/**
 * File upload
 */
function rddp_file_upload() {

	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	if ( ! empty( $_FILES['rddp-file']['tmp_name'] ) ) {

		if ( get_option( 'rd-dashboard-pdf' ) ) {
			$old_data = get_option( 'rd-dashboard-pdf' );
			if ( isset( $old_data['id'] ) ) {
				wp_delete_attachment( $old_data['id'] );
			}
		}

		$overrides = array(
			'test_form' => false,
		);

		$urls = wp_handle_upload( wp_unslash( $_FILES['rddp-file'] ), $overrides, null );

		$pdftitle = filter_input( INPUT_POST, 'rddp-title' );

		if ( isset( $urls['file'] ) ) {
			if ( 'application/pdf' === $urls['type'] ) {
				$wp_upload_dir = wp_upload_dir();
				$attachment = array(
					'guid'           => $wp_upload_dir . '/' . basename( $urls['url'] ),
					'post_mime_type' => $urls['type'],
					'post_title'     => 'Uploaded image ' . $urls['file'],
					'post_content'   => '',
					'post_status'    => 'inherit',
				);
				$attach_id = wp_insert_attachment( $attachment, $urls['url'] );
				media_handle_upload(
					$attach_id,
					0,
					$attachment,
					array(
						'test_form' => false,
					)
				);
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				$filename = rddp_getfilename( $urls['url'] );

				$data = array(
					'id'   => $attach_id,
					'url'  => $urls['url'],
					'type' => $urls['type'],
					'pdf-title' => $pdftitle,
					'name' => $filename,
					'error' => '',
				);
				$temp = $data['url'];
			} else {
				$data = array(
					'error' => __( "Upload file is not pdf.\n", 'rd-dashboard-pdf' ),
				);
			}
		} else {
			$data = array(
				'error' => __( "There was a problem with your upload.\n", 'rd-dashboard-pdf' ),
			);
			$temp = $data['error'];
		}
		update_option( 'rd-dashboard-pdf', $data );
		return $temp;
	} else {
		if ( get_option( 'rd-dashboard-pdf' ) ) {
			$prrdtitle = filter_input( INPUT_POST, 'rddp-title' );
			$data = get_option( 'rd-dashboard-pdf' );
			$data['pdf-title'] = esc_html( $prrdtitle );
			update_option( 'rd-dashboard-pdf', $data );
		}
		return false;
	}
}

/**
 * File display
 */
function rddp_file_display() {
	$page = filter_input( INPUT_GET, 'page' );
	?>
</td></tr>
	<form id="rddp" method="get">
		<div class="wrap">
			<div id="icon-users" class="icon32"><br></div>
			<input type="hidden" name="page" value="<?php echo esc_html( $page ); ?>">
		</div>
	<tr><td>
		<input type="file" name="rddp-file" id="rddp-file">
	<?php
	$data = get_option( 'rd-dashboard-pdf' );
	if ( $data['error'] ) {
		?>
	</td><td><?php echo esc_html( $data['error'] ); ?>
		<?php
	} else {
		?>
			</td><td><strong>pdf title</strong>: <input type="rddp-title" name="rddp-title" id="rddp-title" value="<?php echo esc_html( $data['pdf-title'] ); ?>">
			</td><td><strong>pdf file name</strong>: <?php echo esc_html( $data['name'] ); ?>
		<?php
	}
		?>
		</form>
<?php
}//end rddp_file_display()

/**
 * File check
 *
 * @param int $dataurl file url.
 * @return string|void
 */
function rddp_getfilename( $dataurl ) {
	if ( isset( $dataurl ) ) {
		$filename = strrchr( $dataurl, '/' );
		$filename = substr( $filename, 1 );
		$output = $filename;
		return $output;
	} else {
		return $dataurl;
	}
}

/**
 * Add menu
 */
function rddp_add_menu() {
	add_options_page(
		'rd-dashboard-pdf',
		'rd-dashboard-pdf',
		'activate_plugins',
		'rddp',
		'rddp_setting_page'
	);
}//end rddp_add_menu()
add_action( 'admin_menu', 'rddp_add_menu' );

/**
 * Display Dashboard
 */
function rddp_dashboard_widgets() {
	$data = get_option( 'rd-dashboard-pdf' );
	if ( $data['pdf-title'] ) {
		$title = esc_html( $data['pdf-title'] );
	} else {
		$title = 'pdf';
	}
	wp_add_dashboard_widget( 'my_theme_options_widget', $title, 'rddp_dashboard_widget_function' );
}

/**
 * Widget
 */
function rddp_dashboard_widget_function() {
	$data = get_option( 'rd-dashboard-pdf' );
	$url = preg_replace( '/^.*:/', "", $data['url'] );
	if ( $data['error'] ) {
		echo esc_html( $data['error'] );
	} elseif ( isset( $data['url'] ) ) {
	?>
		<object data="<?php echo esc_url( $url ); ?>" type="application/pdf" style="width: 100%;"></object>
		<p><a href="<?php echo esc_url( $url ); ?>" target="_blank">open browser</a></p>
	<?php
	} else {
		 esc_html_e( 'Pdf file does not exist', 'rd-dashboard-pdf' );
	}
}
