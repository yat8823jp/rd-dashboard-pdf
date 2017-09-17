<?php
/*
Plugin Name: RD Dashboard pdf
Plugin URI:
Description: Display pdf on the dashboard. For example, user's manual etc.
Author: YAT
Version: 1.0.3
Text Domain: rd-dashboard-pdf
*/

//----------------------------------------------------------------
// init
//----------------------------------------------------------------
function rddp_init() {
	add_settings_section( "rddp-section", "File upload", null, "rddp" );
	add_settings_field( "rddp-file", __( "pdf file to display on the dashboard", 'rd-dashboard-pdf' ), "rddp_file_display", "rddp", "rddp-section", array( 'label_for' => 'rddp-file' ) );
	register_setting( "rddp-section", "rddp-file", "rddp_file_upload" );

	add_action( 'wp_dashboard_setup', 'rddp_dashboard_widgets' );
}

add_action( "admin_init", "rddp_init" );


//----------------------------------------------------------------
// Setting page
//----------------------------------------------------------------
function rddp_setting_page() {
?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Upload pdf for display on dashboard.', 'rd-dashboard-pdf' ); ?></h1>
		<form action="options.php" id="rddp-form" method="post" enctype="multipart/form-data">
			<?php
				settings_fields( "rddp-section" );
				do_settings_sections( "rddp" );
				submit_button();
			?>
		</form>
	</div>
<?php
}

/*
	file upload
*/
function rddp_file_upload( $option ) {

	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	if ( ! empty( $_FILES["rddp-file"]["tmp_name"] ) ) {

		//old media file delete.
		if( get_option( 'rd-dashboard-pdf' ) ) {
			$old_data = get_option( 'rd-dashboard-pdf' );
			if( isset( $old_data['id'] ) ) {
				wp_delete_attachment( $old_data['id'] );
			}
		}

		$overrides = array( 'test_form' => false );
		$urls = wp_handle_upload( $_FILES["rddp-file"], $overrides, NULL );

		if ( isset( $urls["file"] ) ) {
			if( $urls["type"] != "application/pdf" ) {
				$data = array(
					'error' => __( "Upload file is not pdf.\n", 'rd-dashboard-pdf' )
				);
			} else {
				$wp_upload_dir = wp_upload_dir();
				$attachment = array(
					'guid'           => $wp_upload_dir . "/" . basename( $urls["url"] ),
					'post_mime_type' => $urls["type"],
					'post_title'     => 'Uploaded image ' . $urls["file"],
					'post_content'   => '',
					'post_status'    => 'inherit'
				);
				$attach_id = wp_insert_attachment( $attachment, $urls["url"] );
				media_handle_upload( $attach_id, 0, $attachment, array( 'test_form' => FALSE ) );
				require_once( ABSPATH . "wp-admin" . '/includes/image.php' );

				$filename = rddp_getfilename( $urls['url'] );

				$data = array(
					'id'   => $attach_id,
					'url'  => $urls['url'],
					'type' => $urls['type'],
					'name' => $filename,
					'error' => ''
				);
				$temp = $data['url'];
			}
		} else {
			$data = array(
				'error' => __( "There was a problem with your upload.\n", 'rd-dashboard-pdf' )
			);
			$temp = $data['error'];
		}
		update_option( 'rd-dashboard-pdf', $data );
		return $temp;
	} else {
		return $option;
	}
}

/*
	file display
*/
function rddp_file_display() {
	?>test</td>
	<td><input type="file" name="rddp-file" id="rddp-file">
		<?php
			$data = get_option( 'rd-dashboard-pdf' );
			if( $data['error'] ) {
				?></td>
				<td>
					<?php echo esc_html( $data['error'] ); ?>
				</td><?php
			} else {
				?></td>
				<td><?php echo esc_html( $data['name'] ); ?>
				<?php
			}
		?>
	<?php
}

/*
	file check
*/
function rddp_getfilename( $dataurl ) {
	$filename = strrchr( $dataurl, "/"  );
	$filename = substr( $filename, 1 );
	$output = $filename;
	return $output;
}

//----------------------------------------------------------------
// add menu
//----------------------------------------------------------------
function rddp_add_menu() {
	add_options_page(
		'rd-dashboard-pdf',
		'rd-dashboard-pdf',
		'activate_plugins',
		'rddp',
		'rddp_setting_page'
	);
}//rpdp_add_menu
add_action( 'admin_menu', 'rddp_add_menu' );

//----------------------------------------------------------------
// Display Dashboard
//----------------------------------------------------------------
function rddp_dashboard_widgets() {
	wp_add_dashboard_widget( 'my_theme_options_widget', 'pdf', 'rddp_dashboard_widget_function' );
}
function rddp_dashboard_widget_function() {
	?>
	<?php
	$data = get_option( 'rd-dashboard-pdf' );
	if( $data['error'] ) {
		?><?php echo esc_html( $data['error'] ); ?><?php
	} elseif( isset( $data['url'] ) ) {
		?>
			<object data="<?php echo esc_url( $data['url'] ); ?>" type="application/pdf" style="width: 100%;"></object>
			<p><a href="<?php echo esc_url( $data['url'] ); ?>" target="_blank">open browser</a></p>
		<?php
	} else {
		 esc_html_e( "Pdf file does not exist", "rd-dashboard-pdf" );
	}
}
