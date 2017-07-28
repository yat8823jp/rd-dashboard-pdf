<?php
/*
Plugin Name: RD Dashboard pdf
Plugin URI:
Description: Display pdf on the dashboard. For example, user's manual etc.
Author: YAT
Version: 0.0.1
Text Domain: rd-dashboard-pdf
*/

if ( ! function_exists( 'wp_handle_upload' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

//----------------------------------------------------------------
// init
//----------------------------------------------------------------
function rddp_init() {
	add_settings_section( "rddp-section", "File upload", null, "rddp" );
	add_settings_field( "rddp-file", "pdf file to display on the dashboard", "rddp_file_display", "rddp", "rddp-section", array( 'label_for' => 'rddp-file' ) );
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
		<h1><?php echo __( 'Upload pdf for display on dashboard.', 'rd-dashboard-pdf' ); ?></h1>
		<form action="options.php" id="rddp-form" method="post">
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
	// if( !empty( $_FILES["rddp-file"]["tmp_name"] ) ) {
		$urls = wp_handle_upload( $_FILES["rddp-file"], array( 'test_form' => FALSE ), NULL );

		if( isset( $urls["file"] ) ) {
			$attachment = array(
				'post_mime_type' => $urls["type"],
				'post_title' => 'Uploaded image ' . $urls["file"],
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment( $attachment, $urls["file"] );

      media_handle_upload( $attach_id, 0, $attachment, array( 'test_form' => FALSE ) );
      require_once( ABSPATH . "wp-admin" . '/includes/image.php' );

		} else {
			$upload_feedback = 'There was a problem with your upload.';
		}

		if ( $urls && ! isset( $urls['error'] ) ) {
			echo __( "File is valid, and was successfully uploaded.\n", 'rd-dashboard-pdf' );
		} else {
			echo $urls['error'];
		}
		$temp = $urls["url"];
		echo "saaaa";
	if( $temp ) {
		return $temp;
	} else {
	// }
		return $option;
	}
}

/*
	file display
*/
function rddp_file_display() {
	?>
		<input type="file" name="rddp-file" id="rddp-file">
		<?php echo get_option( 'rddp-file' ); ?>
	<?php
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
	// echo '<object data="' .  . ' type="application/pdf" style="width: 100%; height:440px;"></object>';
	// echo '<p><a href="' . get_template_directory_uri() . '/pdf/manual.pdf"' . ' target="_blank">Open pdf in browser.</a></p>';
	?>
		<p>テスト</p>
	<?php
		echo get_option( 'rddp-file' );
}
