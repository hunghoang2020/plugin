<?php


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class GFEntryGroupList_Edit_Table extends WP_List_Table{

    public $filter = '';
    public $k=[];

    public function __construct( $args = array() ){
        parent::__construct( $args );
        $this->filter = rgget( 'filter' );
        $form_id = rgget('form_id');
        //  $search_arg = array(
        //     'status' => 'spam',
        //     'field_filters' => array(
        //         'mode' => 'all',
        //     )
        // );
        
        // $search_criteria = array();
        // $search_criteria['status'] = 'active';
        $paging = array( 'offset' => 0, 'page_size' => -1);
        $search_criteria['status'] = 'active';
        $result = GFAPI::count_entries(8,$search_criteria,$paging);
        //  var_dump($result);

        // $current_screen = get_current_screen();
        // // var_dump($current_screen);
        // $current_screen->add_option( $option, $args );
        // $screen= get_current_screen();
        // apply_filters( 'screen_options_show_screen', true,  $screen );
       
    }
  

    function get_columns() {
        $columns = array(
            // 'cb'         => '<input type="checkbox" />',
            'is_used' => esc_html('Status','gfentrygroup'),
            'entry_id' => esc_html__('Entry Id', 'gfentrygroup'),
            'entry_name' => esc_html__('Entry Name', 'gfentrygroup'),
            'entry_company' => esc_html__('Entry Company','gfentrygroup'),
            'entry_job' => esc_html__('Entry Job Role','gfentrygroup')
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
        // $trash_count = $wpdb->get_var("SELECT COUNT(*) FROM $group_table_name WHERE group_status = 'trash'");

        // $all_class = ( $this->filter == '' ) ? 'current' : '';
        // $trash_class = ( $this->filter == 'trash' ) ? 'current' : '' ;
        $paging = array( 'offset' => 0, 'page_size' => -1);
        // $search_criteria['status'] = 'active';
        $form_id = rgget('form_id');
        $all_count = count( GFAPI::get_entries($form_id,$paging));
        
        $views = array(
			'all' => '<a class="current" >' . esc_html( 'All' , 'gfentrygroup') . ' <span class="count">(<span id="all_count">'.$all_count.'</span>)</span></a>',
			// 'trash' => '<a class="' . $trash_class . '" href="?page=gf_edit_group&filter=trash">' . esc_html( 'Trash', 'gfentrygroup') . ' <span class="count">(<span id="trash_count">'.$trash_count.'</span>)</span></a>',
		);
        // $views = array();
        
		return $views;
    }

    function prepare_items() {
        global $wpdb;
        //get eidt form id
        $form_id = rgget('form_id');
        //get search query
        $search_query   = rgget( 's' );
        $filter = rgget('filter');

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
            $data = GFAPI::get_entries($form_id);
        }else if($filter == 'trash'){
            $query = "SELECT * FROM $group_table_name WHERE `group_status` = 'trash'";
            $data = GFAPI::get_entries($form_id);
        }
        else{
            $query = "SELECT * FROM $group_table_name WHERE `group_status` = 'publish'"; 
            $data = GFAPI::get_entries($form_id);
        };
        // var_dump($data);
        
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
        // var_dump($screen);

        // echo($per_page);
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
            '<input type="checkbox" name="trash[]" value="%s" />', $item['id']
        );
    }

    function column_default( $item, $column_name ) {
            // var_dump($item['id']);
        switch ($column_name) {
            case 'is_used' :
                // $text  = esc_html__( 'On Used', 'gravityforms' );
                // $class = 'gform-status--active';
                $data = GFEG_Model::get_all_entry_group(rgget('group_id'));
                $result = json_decode($data[0]);
                // var_dump($result );
                if(in_array($item['id'], $result)){
                    $text  = esc_html__( 'On Used', 'gravityforms' );
                    $class = 'gform-status--active';
                }else{
                    $text  = esc_html__( 'un Used', 'gravityforms' );
                    $class = 'gform-status--deactive';
                }

                ?>
                    <button type="button" class="gform-status-indicator <?php echo esc_attr( $class ); ?>" onclick="ToggleActive( this, <?php echo absint( $item['id'] ); ?> );" onkeypress="ToggleActive( this, <?php echo absint( $item['id'] ); ?> );">
                        <svg role="presentation" focusable="false" viewBox="0 0 6 6" xmlns="http://www.w3.org/2000/svg"><circle cx="3" cy="2" r="1" stroke-width="2"/></svg>
                        <span class="gform-status-indicator-status"><?php echo esc_html( $text ); ?></span>
			        </button>
                <?php
                
                break;
            case 'entry_id' :
                return  $item["id"] ;
                break;
            case 'entry_name' :
                return $item['1.3'] . ' '. $item['1.6'];
                break;
            case 'entry_company' :
                return $item[3];
                break;
            case 'entry_job' : 
                return $item[4];
                break;
            default:
                // $user = $this->get_user_by_id($item['group_author']);
                return 1;
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
        // $filter = $this->filter;
        // if($filter == 'trash'){
        //     $actions = array(
		// 		'restore' => esc_html__( 'Restore' ),
		// 		'delete' => esc_html__( 'Delete permanently' ),
		// 	);
        // }else{
        //     $actions = array(
        //         'trash' => 'trash',
        //     );

        // }
        $actions = array();
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
        // if($filter == 'trash'){
        //     $actions = array(
        //         'restore'   => sprintf('<a href="#" onClick=RestoreForm(%s) onkeypress=RestoreForm(%s) >Restore</a>',$item['id'], $item['id']),
        //         'delete' => sprintf('<a href="#" onClick=ConfirmDeleteForm(%s) onkeypress=ConfirmDeleteForm(%s) >Delete permanently</a>',  $item['id'],$item['id']),
        //     );
        // }else{
        //     $actions = array(
        //         // 'edit'   => sprintf('<a href="?page=%s&action=%s&group_id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),
        //         'edit'   => sprintf('<a href="?page=%s&action=%s&group_id=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['id']),

        //         'trash' => sprintf('<a href="#" onClick=TrashForm(%s) >Trash</a>',  $item['id']),

        //     );

        // }
        // var_dump($this->row_actions( $actions )); die();
        // var_dump($primary);
        return $this->row_actions( $actions );
    }
    public function process_action(){
        // $bulk_action = $this->current_action();
        // $group_id = rgget( 'group_id' );
        
        $group_list_single_action = rgpost( 'group_list_single_action' );
        $bulk_action = $this->current_action();
        // var_dump($bulk_action);

        if($group_list_single_action){
            check_admin_referer('group_list_update','group_list_update');
            $form_id = rgpost('group_list_single_action_argument');
            
            switch($group_list_single_action){
                case 'trash' : {
                    
                    GFEG_Model::trash_form($form_id);
                    break;
                }
                case 'delete' :{
                    GFEG_Model::delete_form($form_id);
                    break;
                }
                case 'restore' : {
                    GFEG_Model::restore_form($form_id);
                    break;
                }
            }
        }
        if($bulk_action){
            // echo'sss';
            $form_ids   = is_array( rgpost( 'trash' ) ) ? rgpost( 'trash' ) : array();
			// $form_count = count( $form_ids );
            // var_dump($form_ids);
            switch($bulk_action){
                case 'trash' :{
                check_admin_referer('group_list_update','group_list_update');

                    GFEG_Model::trash_forms($form_ids);
                    break;
                }
                case 'restore' :{
                check_admin_referer('group_list_update','group_list_update');

                    GFEG_Model::restore_forms($form_ids);
                    break;
                }
                // case 'edit' :{
                //     echo 'asas';
                //     break;
                // }
               
            }
        }
       
       
    }
    function extra_tablenav( $which ) { 
		if ( $which !== 'top' ) {
			return;
		}
		wp_nonce_field( 'group_list_update', 'group_list_update' );
		?>
		<input type="hidden" id="group_list_single_action" name="group_list_single_action" />
		<input type="hidden" id="group_list_single_action_argument" name="group_list_single_action_argument" />
		<?php
	}
    function edit_title(){
        ?>
        <div class="edit_title">
            <input type="text"></input>
            <input type="submit" name="submit" id="submit" class="button button-primary" value="apply">
        </div>
        <?php
    }

}