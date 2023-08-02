<?php

if (!class_exists('GFEntryGroup')) {
    exit;
}

class GFEntryGroupList {
    public static function group_list_page() {
        global $wpdb;
        $list_table = new GFEntryGroupList_Table();
        $list_table->process_action();
        $list_table->prepare_items();
        // var_dump($list_table->filter);
        // Set the screen option for items per page
        add_screen_option(
            'per_page',
            array(
                'label'   => __('Items per page', 'gfentrygroup'),
                'default' => 10,
                'option'  => 'gfentrygroup_items_per_page'
            )
        );

        ?>
            <div class="wrap">
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Entry Groups', 'gfentrygroup' ); ?></h1>
                <a href="?page=gf_add_group" class="page-title-action"><?php esc_html_e( 'Add New', 'gfentrygroup' ); ?></a>
                <hr class="wp-header-end">
                <h2 class="screen-reader-text">Filter posts list</h2>
                <form id="entry_group" method = "get" name="gfentrygroup"   >
                    
                <?php
                   
                    $list_table->views();
                    // $list_table->screen_options();
                    ?>
                    <form id="gf_edit_group" method="get">
                    <input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>"/>
                    <?php
                    // $list_table->search_box( __( 'Search' ), 'search-box-id');

                    $list_table->search_box('search', 'gf_edit_group' );
                    // $list_table->search_box( esc_html__( 'Search Forms', 'gravityforms' ), 'gfentrygroup' );
                    // var_dump($list_table);
                    ?>
                    
                    </form>
                    <?php
                    $list_table->display();
                   
                ?>
                
                </form>
               
            </div>
        <?php
    }
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class GFEntryGroupList_Table extends WP_List_Table{

    public $filter = '';

    public function __construct( $args = array() ){
        parent::__construct( $args );
        $this->filter = rgget( 'filter' );
        // $current_screen = get_current_screen();
        // // var_dump($current_screen);
        // $current_screen->add_option( $option, $args );
        // $screen= get_current_screen();
        // apply_filters( 'screen_options_show_screen', true,  $screen );
       
    }
  

    function get_columns() {
        $columns = array(
            'cb'         => '<input type="checkbox" />',
            'group_title' => esc_html__( 'Group Name', 'gfentrygroup' ),
            'form_id' => esc_html__( 'Form Name', 'gfentrygroup' ),
            'group_author' => esc_html__( 'Author', 'gfentrygroup' ),
            'group_date' => esc_html__( 'Date', 'gfentrygroup' ),
        );
        return $columns;
    }

    function get_sortable_columns() {
		return array(
			'group_title'   => array( 'group_title', false ),
			'group_author' => array( 'group_author', false ),
			'group_date' => array( 'group_date', false ),
		);
	}

    function get_views(){
        global $wpdb;
        $group_table_name = GFEG_Model::get_group_table_name();
        $all_count = $wpdb->get_var("SELECT COUNT(*) FROM $group_table_name WHERE group_status = 'publish'");
        $trash_count = $wpdb->get_var("SELECT COUNT(*) FROM $group_table_name WHERE group_status = 'trash'");

        $all_class = ( $this->filter == '' ) ? 'current' : '';
        $trash_class = ( $this->filter == 'trash' ) ? 'current' : '' ;
        $views = array(
			'all' => '<a class="' . $all_class . '" href="?page=gf_edit_group">' . esc_html( 'All' , 'gfentrygroup') . ' <span class="count">(<span id="all_count">'.$all_count.'</span>)</span></a>',
			'trash' => '<a class="' . $trash_class . '" href="?page=gf_edit_group&filter=trash">' . esc_html( 'Trash', 'gfentrygroup') . ' <span class="count">(<span id="trash_count">'.$trash_count.'</span>)</span></a>',
		);
        
		return $views;
    }

    function prepare_items() {
        global $wpdb;
        $search_query   = rgget( 's' );
        $filter = rgget('filter');
        var_dump($filter);
        $sort_column  = empty( $_GET['orderby'] ) ? 'group_title' : $_GET['orderby'];
		$sort_columns = array_keys( $this->get_sortable_columns() );

        if ( ! in_array( strtolower( $sort_column ), $sort_columns ) ) {
			$sort_column = 'group_title';
		}

        $sort_direction = empty( $_GET['order'] ) ? 'ASC' : strtoupper( $_GET['order'] );
		$sort_direction = $sort_direction == 'ASC' ? 'ASC' : 'DESC';

        $sort_column = sanitize_sql_orderby( $sort_column );
        $order_by    = ! empty( $sort_column ) ? "ORDER BY $sort_column $sort_direction" : '';

        $group_table_name = GFEG_Model::get_group_table_name();
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        // var_dump($group_table_name);
        if($search_query){ 
            $query = "SELECT * FROM $group_table_name WHERE `group_title` LIKE '%$search_query%'";
            $data = $wpdb->get_results( $query, ARRAY_A);
           
        }if($filter == 'trash'){
            $query = "SELECT * FROM $group_table_name WHERE `group_status` ='trash'";
            $data = $wpdb->get_results( $query, ARRAY_A);
        }
        else{
            $query = "SELECT * FROM $group_table_name WHERE `group_status` = 'publish'";
            
            $data = $wpdb->get_results($query, ARRAY_A);
        }
        
        /* pagination */
        // $option      = 'per_page';
        // $per_page = $this->get_items_per_page($option); // Default to 20 items per page;
        $current_page = $this->get_pagenum();
        // var_dump();string(21) "gfentrygroup_per_page"
        // get the current user ID
        $user = get_current_user_id();
        // get the current admin screen
        $screen = get_current_screen();
        // retrieve the "per_page" option
        $screen_option = $screen->get_option('per_page', 'option');
        // retrieve the value of the option stored for the current user
        $per_page = get_user_meta($user, $screen_option, true);
        // $per_page = $this->get_items_per_page('per_page'); // Default to 20 items per page;

        // var_dump($per_page);
        if ( empty ( $per_page) || $per_page < 1 ) {
            // get the default value if none is set
            $per_page = $screen->get_option( 'per_page', 'default' );
        }
        $total_items = count($data);

        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
                'total_items' => $total_items, // total number of items
                'per_page'    => $per_page, // items to show on a page
                'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up

        ));

        $this->items = $data;
    }

    function column_cb( $item ) {
      
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }

    function column_default( $item, $column_name ) {
        switch ($column_name) {
            case 'group_title':
                return !empty($item[ $column_name ]) ? $item[ $column_name ] : ('No Title');
                break;
            case 'form_id':
                
                $form_name = $this->get_form_name($item[ $column_name ]);
                return $form_name;
                break;
            case 'group_date':
                $item['group_status'];
                echo $item['group_status'].'</br>' . date("d/m/Y \a\\t H:i a", strtotime($item[$column_name]));
                
                break;
            default:
                $user = $this->get_user_by_id($item['group_author']);
                return $user;
                break;
        }
    }

    function get_form_name($item){
        
        if ( class_exists( 'GFAPI' ) ){
            $result = GFAPI::get_form($item);
        }
        // $result = GFAPI::get_form( $form_id );
        // $result['title'];
        return $result['title'];
    }
    function get_user_by_id($item){
        if(class_exists('WP_User')){
            $result = WP_User::get_data_by( 'id', $item)->display_name;
        }
        // var_dump($result->display_name);
        return $result;
    }

    function display_rows() {
        foreach ( $this->items as $item ) {
            echo '<tr>';
            $this->single_row( $item );
            echo '</tr>';
        }
    }

    function get_bulk_actions() {
        
        $actions = array(
            'bulk-delete' => 'Delete',
        );
        return $actions;
    }

    function process_bulk_action() {
        global $wpdb;
    }

    function handle_row_actions( $item, $column_name, $primary ) {
        if ( $primary !== $column_name ) {
            return '';
        }
        $filter = rgget('filter');

        $actions = array();
        if($filter == 'trash'){
            $actions = array(
                'restore'   => sprintf('<a href="?page=%s&action=%s&group_id=%s">Restore</a>', $_REQUEST['page'], 'restore', $item['id']),
                'delete_permanently' => sprintf('<a href="?page=%s&action=%s&group_id=%s">Delete permanently</a>', $_REQUEST['page'], 'delete_permanently', $item['id']),
            );
        }else{
            $actions = array(
                'edit'   => sprintf('<a href="?page=%s&action=%s&group_id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
                'trash' => sprintf('<a href="?page=%s&action=%s&group_id=%s">Trash</a>', $_REQUEST['page'], 'trash', $item['id']),
            );

        }
        // var_dump($this->row_actions( $actions )); die();
        // var_dump($primary);
        return $this->row_actions( $actions );
    }
    public function process_action(){
        $group_id = rgget( 'group_id' );
        $bulk_action = $this->current_action();
        echo $group_id;
    }
    function extra_tablenav( $which ) { 
		if ( $which !== 'top' ) {
			return;
		}
		wp_nonce_field( 'gforms_update_forms', 'gforms_update_forms' );
		?>
		<input type="hidden" id="group_list_single_action" name="group_list_single_action" />
		<input type="hidden" id="group_list_single_action_argument" name="group_list_single_action_argument" />
		<?php
	}
   

    
    

  

}