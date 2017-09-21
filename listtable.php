<?php
/*
Plugin Name: RD Dashboard pdf
Plugin URI:
Description: Display pdf on the dashboard. For example, user's manual etc.
Author: YAT
Version: 1.1.0
Text Domain: rd-dashboard-pdf
*/
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class RD_List_Table extends WP_List_Table {
	function __construct() {
		parent:: __construct( array (
			'singular' => 'rd_pdf',
			'plural'   => 'rd_pdfs',
			'ajax'     => false
		) );
	}
	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'id' :
				return $item[ $column_name ];
			case 'url':
				return $item[ $column_name ];
			case 'name':
				return $item[ $column_name ];
			case 'pdf-title':
				return $item[ $column_name ];
			default:
				return print_r( $item, true );
		}
	}

	function get_columns() {
		$columns = array(
			'id'           => __( 'ID', 'rd-dashboard-pdf' ),
			'url'          => __( 'Url', 'rd-dashboard-pdf' ),
			'name'         => __( 'Name', 'rd-dashboard-pdf' ),
			'pdf-title'    => __( 'title', 'rd-dashboard-pdf' )
		);
		return $columns;
	}

	function prepare_items() {
		global $wpdb;
		$per_page = 5;
		$columns = $this -> get_columns();
		$hidden = array();

		$this -> _column_headers = array( $columns, $hidden );

		$data = get_option( 'rd-dashboard-pdf' );

		//read database object
		global $wpdb;
		$results = $wpdb -> get_results( 'SELECT * FROM wp_options WHERE option_id = 1', OBJECT );
		$results = $GLOBALS[ 'wpdb' ] -> get_results( 'SELECT * FROM wp_option WHERE option_id =1', OBJECT );

		//pagenation
		$current_page = $this -> get_pagename();
		$total_items = count( $data );

		$data = array_slice( $result, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$data -> items = $data;

		$this -> set_pagenation_args( array (
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil( $total_items/$per_page )
		) );
	}

}

function rddp_render_list_page() {
	$rddpListTable = new RD_List_Table();
	$rddpListTable -> prepare_items();
?>
	<div class="wrap">
		<div id="icon-users" class="icon32"><br></div>
		<h2><?php esc_html_e( 'Upload pdf for display on dashboard.', 'rd-dashboard-pdf' ); ?></h2>

		<form id="rddp" method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST[ 'page' ] ?>">
			<?php $rddpListTable -> display(); ?>
		</form>

	</div>
	<?php
}
