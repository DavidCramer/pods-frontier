<?php
// WP_List_Table is not loaded automatically so we need to load it in our application
if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table for listing Frontier Elements
 */
class Frontier_List_Table extends WP_List_Table {

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {

		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$data = $this->table_data();
		usort( $data, array( &$this, 'sort_data' ) );

		$perPage = 20;
		$currentPage = $this->get_pagenum();
		$totalItems = count( $data );

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page' => $perPage
		) );

		$data = array_slice( $data, ( ( $currentPage - 1 ) * $perPage ), $perPage );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $data;

	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {

		$columns = array(
			//'cb'			=> '<input type="checkbox" />',
			'id' => 'ID',
			'title' => __( 'Title', 'pods-frontier' ),
			'description' => __( 'Description', 'pods-frontier' ),
			'type' => __( 'Type', 'pods-frontier' ),
			'shortcode' => __( 'Shortcode', 'pods-frontier' ),
			'base_pod' => __( 'Base Pod', 'pods-frontier' )
		);

		return $columns;

	}

	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns() {

		return array( 'id' );

	}

	/**
	 * Define the sortable columns
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {

		return array( 'title' => array( 'title', false ), 'type' => array( 'type', false ) );

	}

	function column_cb( $item ) {

		return sprintf( '<input type="checkbox" name="book[]" value="%s" />', $item[ 'id' ] );

	}

	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function table_data() {

		$data = array();

		// get all elements
		$elements = (array) get_option( '_pods_frontier_elements', array() );

		//get element types
		$element_types = apply_filters( 'pods_frontier_get_element_types', array() );

		foreach ( $elements as $eid => $element ) {
			if ( !isset( $element_types[ $element[ 'type' ] ] ) ) {
				continue;
			}

			$data[] = array(
				'id' => $eid,
				'title' => $element[ 'name' ],
				'description' => $element[ 'description' ],
				'type' => $element_types[ $element[ 'type' ] ][ 'name' ],
				'shortcode' => '[frontier id="' . $eid . '"]',
				'base_pod' => ( isset( $element[ 'base_pod' ] ) ? $element[ 'base_pod' ] : __( 'Not Set', 'pods-frontier' ) )
			);
		}

		return $data;

	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  Array $item Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'id':
			case 'title':
				$out = $item[ $column_name ];
				$out .= '<div class="row-actions">';
				$out .= '<span class="edit"><a title=" ' . __( 'Edit this element', 'pods-frontier' ) . '" href="admin.php?page=pods-component-frontier&edit=' . $item[ 'id' ] . '">' . __( 'Edit', 'pods-frontier' ) . '</a> | </span>';
				$out .= '<span class="trash"><a href="admin.php?page=pods-component-frontier&delete=' . $item[ 'id' ] . '&_pfnonce=' . wp_create_nonce( 'delete_frontier_element' ) . '" title="' . __( 'Move this item to the Trash', 'pods-frontier' ) . '" class="submitdelete">' . __( 'Delete', 'pods-frontier' ) . '</a></span>';
				$out .= '</div>';

				return $out;
			case 'description':
			case 'type':
			case 'shortcode':
			case 'base_pod':
				return $item[ $column_name ];

			default:
				return print_r( $item, true );
		}

	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data( $a, $b ) {

		// Set defaults
		$orderby = 'title';
		$order = 'asc';

		// If orderby is set, use this as the sort column
		if ( !empty( $_GET[ 'orderby' ] ) ) {
			$orderby = $_GET[ 'orderby' ];
		}

		// If order is set use this as the order
		if ( !empty( $_GET[ 'order' ] ) ) {
			$order = $_GET[ 'order' ];
		}

		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

		if ( $order === 'asc' ) {
			return $result;
		}

		return -$result;

	}

}