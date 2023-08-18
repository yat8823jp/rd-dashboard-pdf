<?php
/**
 * Short description
 *
 * @package rd-dashboard-pdf
 * @version 3.0.0
 */

/*
Plugin Name: RD Dashboard pdf
Plugin URI: https://github.com/yat8823jp/rd-dashboard-pdf
Description: Display pdf on the dashboard. For example, user's manual etc.
Author: YAT
Version: 3.0.0
Text Domain: rd-dashboard-pdf
*/

/**
 * Init
 */

function rddp_init() {
	load_plugin_textdomain( 'rd-dashboard-pdf', false, false );
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
	wp_enqueue_style(  'rd-dashboard-pdf-css', plugin_dir_url( __FILE__ ) . 'assets/app.css' );
	wp_enqueue_script( 'rd-dashboard-pdf-js', plugin_dir_url( __FILE__ ) . 'assets/app.js', "", "", true );
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
			?>
		</form>
		<dialog class="delete-dialog">
			<p><?php _e( "Do you want to delete uploaded files?", 'rd-dashboard-pdf' ); ?></p>
			<div class="delete-dialog__buttons">
				<button name="delete" class="delete-button-close" autofocus=""><?php _e( "Delete", 'rd-dashboard-pdf' ); ?></button>
				<button class="delete-button-cancel"><?php _e( "Cancel", 'rd-dashboard-pdf' ); ?></button>
			</div>
		</dialog>
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

		$file = wp_unslash( $_FILES['rddp-file'] );
		$urls = wp_handle_upload( $file, $overrides, null );

		if ( isset( $urls['file'] ) ) {
			if ( 'application/pdf' === $urls['type'] ) {
				$wp_upload_dir = wp_upload_dir();
				$attachment = array(
					'guid'           => $wp_upload_dir["url"] . '/' . basename( $urls['url'] ),
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
			$data = get_option( 'rd-dashboard-pdf' );
			update_option( 'rd-dashboard-pdf', $data );
		}
		return false;
	}
}

function rddp_file_delete() {
	if ( isset( $_POST['delete'] ) ) {
		$data = get_option( 'rd-dashboard-pdf' );
		delete_option( 'rd-dashboard-pdf', $data );
		echo '<script>alert("' . $data . __( "Deleted", 'rd-dashboard-pdf' ) . '");</script>';
	} else {
	}
}
rddp_file_delete();

/**
 * File display
 */
function rddp_file_display() {
	$page = filter_input( INPUT_GET, 'page' );
	$file_name = "";
	?>
</td></tr>
	<form id="rddp" method="get">
		<div class="wrap">
			<div id="icon-users" class="icon32"><br></div>
			<input type="hidden" name="page" value="<?php echo esc_html( $page ); ?>">
		</div>
		<tr>
			<td>
				<?php
					$data = get_option( 'rd-dashboard-pdf' );
					if ( $data ) {
						$file_name = $data['name'];
						if ( $data['error'] ) {
							?>
								</td><td><?php echo esc_html( $data['error'] ); ?>
								<?php
						} else {
							?>
								<strong><?php _e( "Uploaded pdf file name", 'rd-dashboard-pdf' ); ?></strong>: <?php echo esc_html( $data['name'] ); ?>
							</td>
							<td>
								<button class="delete-pdf"><?php _e( "Delete", 'rd-dashboard-pdf' ); ?></button>
							<?php
						}
					} else {
						?>
							<input type="file" name="rddp-file" id="rddp-file">
							</td>
							<td>
								<?php submit_button( __( "Upload", 'rd-dashboard-pdf' ) ); ?>
							</td>
						<?php
					}
				?>
			</td>
		</tr>
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
	if ( $data ) {
		if( ! $data["error"] ) {
			if ( $data['name'] ) {
				$file_name = esc_html( $data['name'] );
			} else {
				$file_name = 'pdf';
			}
		} else {
			$file_name = 'No pdf file';
		}
		wp_add_dashboard_widget( 'my_theme_options_widget', $file_name, 'rddp_dashboard_widget_function' );
	}
}

/**
 * Widget
 */
function rddp_dashboard_widget_function() {
	$data = get_option( 'rd-dashboard-pdf' );
	if( ! $data["error"] ) {
		$url = preg_replace( '/^.*:/', "", $data['url'] );
	} else {
		__( "Pdf file does not exist. Please upload the pdf file from the settings", 'rd-dashboard-pdf' );
		return false;
	}
	if ( $data['error'] ) {
		echo esc_html( $data['error'] );
	} elseif ( isset( $data['url'] ) ) {
	?>
		<object data="<?php echo esc_url( $url ); ?>" type="application/pdf" style="width: 100%;"></object>
		<p><a href="<?php echo esc_url( $url ); ?>" target="_blank">open browser</a></p>
	<?php
	} else {
		__( "Pdf file does not exist. Please upload the pdf file from the settings", 'rd-dashboard-pdf' );
	}
}
