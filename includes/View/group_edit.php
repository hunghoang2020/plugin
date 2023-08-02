<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class GFEntryGroupList_Edit {
    public static function group_list_edit_page() {
        wp_print_styles( array( 'gfeg_group_edit_page' ) );
        global $wpdb;
        // $action = rgget('action');
        // if($action == 'edit'){
            // require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
            // $dir = plugin_dir_path( __DIR__ ). "View/group_edit.php";
            // echo $dir;
            // require_once($dir);
            // $list_table = new GFEntryGroupList_Edit_Table();

        // }else{

            $list_table = new GFEntryGroupList_Edit_Table();
        // }
        $list_table->process_action();
        // $list_table->prepare_items();
        // var_dump($list_table->filter);
        // Set the screen option for items per page
        add_screen_option(
            'per_page',
            array(
                'label'   => __('Items per page', 'gfentrygroup'),
                'default' => 10,
                'option'  => 'gfentrygroup_per_page'
            )
        );

        ?>
        <script text="text/javascript">
			// function TrashForm(form_id) {
			// 	jQuery("#group_list_single_action_argument").val(form_id);
			// 	jQuery("#group_list_single_action").val("trash");
			// 	jQuery("#entry_group")[0].submit();
			// }
            // function DeleteForm(form_id) {
			// 	jQuery("#group_list_single_action_argument").val(form_id);
			// 	jQuery("#group_list_single_action").val("delete");
			// 	jQuery("#entry_group")[0].submit();
			// }
            // function ConfirmDeleteForm(form_id){
			// 	if( confirm(<?php //echo json_encode( __( 'WARNING: You are about to delete this form and ALL entries associated with it. ', 'gravityforms' ) . esc_html__( 'Cancel to stop, OK to delete.', 'gravityforms' ) ); ?>) ){
			// 		DeleteForm(form_id);
			// 	}
			// }
            // function RestoreForm(form_id) {
			// 	jQuery("#group_list_single_action_argument").val(form_id);
			// 	jQuery("#group_list_single_action").val("restore");
			// 	jQuery("#entry_group")[0].submit();
			// }
            
            function ToggleActive( btn, entry_id ) {

                var is_active = jQuery( btn ).hasClass( 'gform-status--active' );

              
				jQuery.ajax(
					{
						url:      '<?php echo admin_url( 'admin-ajax.php' ); ?>',
						method:   'POST',
						dataType: 'json',
						data: {
							action: 'handle_update_form_group',
							
							group_id: "<?= rgget('group_id')?>",
                            entry_id: entry_id,
							// is_active: is_active ? 0 : 1,
						},
						success:  function(response) {
							// UpdateCount( 'active_count', is_active ? -1 : 1 );
							// UpdateCount( 'inactive_count', is_active ? 1 : -1 );

							if ( is_active ) {
								setToggleInactive();
							} else {
								setToggleActive();
							}
                            // console.log(response.data);
						},
						error:    function(response) {
                            console.log('error');
                            console.log(response);
							// if ( ! is_active ) {
							// 	setToggleInactive();
							// } else {
							// 	setToggleActive();
							// }

							// alert( ' echo esc_js( __( 'Ajax error while updating form', 'gravityforms' ) ); ?>' );
						}
					}
				)

                function setToggleInactive() {
                    jQuery( btn ).removeClass( 'gform-status--active' ).addClass( 'gform-status--inactive' ).find( '.gform-status-indicator-status' ).html( <?php echo wp_json_encode( esc_attr__( 'Un Used', 'gravityforms' ) ); ?> );
                }

                function setToggleActive() {
                    jQuery( btn ).removeClass( 'gform-status--inactive' ).addClass( 'gform-status--active' ).find( '.gform-status-indicator-status' ).html( <?php echo wp_json_encode( esc_attr__( 'On Used', 'gravityforms' ) ); ?> );
                }

            }
            
        </script>
        
            <div class="wrap">
                <div class="list_left">
                    <div class="group_entry">
                        <h1 class="wp-heading-inline"><?php esc_html_e( 'Entry Groups', 'gfentrygroup' ); ?></h1>
                        <a href="?page=gf_add_group" class="page-title-action "><?php esc_html_e( 'Add New', 'gfentrygroup' ); ?></a>
                        <?php $list_table->edit_title(); ?>
                    </div>
                    <hr class="wp-header-end">
                    <h2 class="screen-reader-text">Filter posts list</h2>
                    <div class="group_entry_nav">
                        <?php
                        $list_table->views();
                        $list_table->prepare_items();
                        ?>
                        <form id="gf_edit_group" method="get">
                            <input type="hidden" name="page" value="<?= esc_attr($_REQUEST['page']) ?>"/>
                            <?php $list_table->search_box('search', 'gf_edit_group' );?>
                        </form>
                    </div>
                    <form id="entry_group" method = "post" name="gfentrygroup"   >
                       <?php $list_table->display();?>
                    </form>
                </div>
                <div class="side_right">
                    <?php $list_table->side()?>
                </div>

                
            </div>
        <?php
    }
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
        $trash_count = $wpdb->get_var("SELECT COUNT(*) FROM $group_table_name WHERE group_status = 'trash'");

        $all_class = ( $this->filter == '' ) ? 'current' : '';
        $current = ( $this->filter == 'on_used' ) ? 'current' : '' ;
        $paging = array( 'offset' => 0, 'page_size' => -1);
        // $search_criteria['status'] = 'active';
        $form_id = rgget('form_id');
        $all_count = count( GFAPI::get_entries($form_id,$paging));
        // var_dump($this->filter);
        $views = array(
			'all' => '<a class="'.$all_class.'" href="?page=gf_edit_group&action=edit&group_id=14&form_id=12">' . esc_html( 'All' , 'gfentrygroup') . ' <span class="count">(<span id="all_count">'.$all_count.'</span>)</span></a>',
			'on_used' => '<a class="'.$current.'" href="?page=gf_edit_group&action=edit&filter=on_used&group_id=14&form_id=12">' . esc_html( 'On Used', 'gfentrygroup') . ' <span class="count">(<span id="trash_count">'.$trash_count.'</span>)</span></a>',
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
            // $query = "SELECT * FROM $group_table_name WHERE `group_title` LIKE '%$search_query%'";
            var_dump($_REQUEST);
            $data = GFAPI::get_entries($form_id);
        }else if($filter == 'on_used'){
            // fixx hear
            $data = array();
            $group_id = rgget("group_id");
            $query = "SELECT meta_value FROM wp_gravityform_entry_group_meta WHERE `group_id` = $group_id ";
            $result = $wpdb->get_results( $query, ARRAY_A );
            $res = json_decode( $result[0]['meta_value'] );
            foreach ($res as $key => $value) {
                array_push($data , GFAPI::get_entry( $value ));            
            }
           
        }
        else{
            // $query = "SELECT * FROM $group_table_name WHERE `group_status` = 'publish'"; 
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
                // var_dump($item );die();
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
                // var_dump($item);
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
        $actions = array(
            'on_used' => 'On Used',
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
        $title = GFEG_Model::get_title(rgget('group_id'));
        ?>
        <div class="edit_title">
            <input type="text" placeholder="Enter Group Name" value="<?=$title[0]?>"></input>
            <!-- <input type="submit" name="submit" id="submit" class="button button-primary" value="apply"> -->
        </div>
        <?php
    }
    function side(){
        ?>
            <div id="submitdiv" class="postbox ">
                <div class="postbox-header"><h2 class="hndle ui-sortable-handle">Publish</h2>
                <div class="handle-actions hide-if-no-js"><button type="button" class="handle-order-higher" aria-disabled="true" aria-describedby="submitdiv-handle-order-higher-description"><span class="screen-reader-text">Move up</span><span class="order-higher-indicator" aria-hidden="true"></span></button><span class="hidden" id="submitdiv-handle-order-higher-description">Move Publish box up</span><button type="button" class="handle-order-lower" aria-disabled="false" aria-describedby="submitdiv-handle-order-lower-description"><span class="screen-reader-text">Move down</span><span class="order-lower-indicator" aria-hidden="true"></span></button><span class="hidden" id="submitdiv-handle-order-lower-description">Move Publish box down</span><button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Publish</span><span class="toggle-indicator" aria-hidden="true"></span></button></div></div><div class="inside">
                <div class="submitbox" id="submitpost">

                <div id="minor-publishing">

                        <div style="display:none;">
                        <p class="submit"><input type="submit" name="save" id="save" class="button" value="Save"></p>	</div>

                    <div id="minor-publishing-actions">
                        <div id="save-action">
                                            <input type="submit" name="save" id="save-post" value="Save Draft" class="button">
                                <span class="spinner"></span>
                                    </div>

                                    <div id="preview-action">
                                                <a class="preview button" href="http://localhost/wordpress/?p=46&amp;preview=true" target="wp-preview-46" id="post-preview">Preview<span class="screen-reader-text"> (opens in a new tab)</span></a>
                                <input type="hidden" name="wp-preview" id="wp-preview" value="">
                            </div>
                                    <div class="clear"></div>
                    </div>

                    <div id="misc-publishing-actions">
                        <div class="misc-pub-section misc-pub-post-status">
                            Status:			<span id="post-status-display">
                                Draft			</span>

                                            <a href="#post_status" class="edit-post-status hide-if-no-js" role="button"><span aria-hidden="true">Edit</span> <span class="screen-reader-text">
                                    Edit status				</span></a>

                                <div id="post-status-select" class="hide-if-js">
                                    <input type="hidden" name="hidden_post_status" id="hidden_post_status" value="draft">
                                    <label for="post_status" class="screen-reader-text">
                                        Set status					</label>
                                    <select name="post_status" id="post_status">
                                                                    <option value="pending">Pending Review</option>
                                                                    <option selected="selected" value="draft">Draft</option>
                                                            </select>
                                    <a href="#post_status" class="save-post-status hide-if-no-js button">OK</a>
                                    <a href="#post_status" class="cancel-post-status hide-if-no-js button-cancel">Cancel</a>
                                </div>
                                        </div>

                        <div class="misc-pub-section misc-pub-visibility" id="visibility">
                            Visibility:			<span id="post-visibility-display">
                                Public			</span>

                                            <a href="#visibility" class="edit-visibility hide-if-no-js" role="button"><span aria-hidden="true">Edit</span> <span class="screen-reader-text">
                                    Edit visibility				</span></a>

                                <div id="post-visibility-select" class="hide-if-js">
                                    <input type="hidden" name="hidden_post_password" id="hidden-post-password" value="">
                                                            <input type="checkbox" style="display:none" name="hidden_post_sticky" id="hidden-post-sticky" value="sticky">
                                    
                                    <input type="hidden" name="hidden_post_visibility" id="hidden-post-visibility" value="public">
                                    <input type="radio" name="visibility" id="visibility-radio-public" value="public" checked="checked"> <label for="visibility-radio-public" class="selectit">Public</label><br>

                                                            <span id="sticky-span"><input id="sticky" name="sticky" type="checkbox" value="sticky"> <label for="sticky" class="selectit">Stick this post to the front page</label><br></span>
                                    
                                    <input type="radio" name="visibility" id="visibility-radio-password" value="password"> <label for="visibility-radio-password" class="selectit">Password protected</label><br>
                                    <span id="password-span"><label for="post_password">Password:</label> <input type="text" name="post_password" id="post_password" value="" maxlength="255"><br></span>

                                    <input type="radio" name="visibility" id="visibility-radio-private" value="private"> <label for="visibility-radio-private" class="selectit">Private</label><br>

                                    <p>
                                        <a href="#visibility" class="save-post-visibility hide-if-no-js button">OK</a>
                                        <a href="#visibility" class="cancel-post-visibility hide-if-no-js button-cancel">Cancel</a>
                                    </p>
                                </div>
                                    </div>

                                    <div class="misc-pub-section curtime misc-pub-curtime">
                                <span id="timestamp">
                                    Publish <b>immediately</b>				</span>
                                <a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" role="button">
                                    <span aria-hidden="true">Edit</span>
                                    <span class="screen-reader-text">
                                        Edit date and time					</span>
                                </a>
                                <fieldset id="timestampdiv" class="hide-if-js">
                                    <legend class="screen-reader-text">
                                        Date and time					</legend>
                                    <div class="timestamp-wrap"><label><span class="screen-reader-text">Month</span><select class="form-required" id="mm" name="mm">
                            <option value="01" data-text="Jan">01-Jan</option>
                            <option value="02" data-text="Feb">02-Feb</option>
                            <option value="03" data-text="Mar">03-Mar</option>
                            <option value="04" data-text="Apr">04-Apr</option>
                            <option value="05" data-text="May">05-May</option>
                            <option value="06" data-text="Jun">06-Jun</option>
                            <option value="07" data-text="Jul">07-Jul</option>
                            <option value="08" data-text="Aug" selected="selected">08-Aug</option>
                            <option value="09" data-text="Sep">09-Sep</option>
                            <option value="10" data-text="Oct">10-Oct</option>
                            <option value="11" data-text="Nov">11-Nov</option>
                            <option value="12" data-text="Dec">12-Dec</option>
                </select></label> <label><span class="screen-reader-text">Day</span><input type="text" id="jj" name="jj" value="01" size="2" maxlength="2" autocomplete="off" class="form-required"></label>, <label><span class="screen-reader-text">Year</span><input type="text" id="aa" name="aa" value="2023" size="4" maxlength="4" autocomplete="off" class="form-required"></label> at <label><span class="screen-reader-text">Hour</span><input type="text" id="hh" name="hh" value="08" size="2" maxlength="2" autocomplete="off" class="form-required"></label>:<label><span class="screen-reader-text">Minute</span><input type="text" id="mn" name="mn" value="35" size="2" maxlength="2" autocomplete="off" class="form-required"></label></div><input type="hidden" id="ss" name="ss" value="19">

                <input type="hidden" id="hidden_mm" name="hidden_mm" value="08">
                <input type="hidden" id="cur_mm" name="cur_mm" value="08">
                <input type="hidden" id="hidden_jj" name="hidden_jj" value="01">
                <input type="hidden" id="cur_jj" name="cur_jj" value="01">
                <input type="hidden" id="hidden_aa" name="hidden_aa" value="2023">
                <input type="hidden" id="cur_aa" name="cur_aa" value="2023">
                <input type="hidden" id="hidden_hh" name="hidden_hh" value="08">
                <input type="hidden" id="cur_hh" name="cur_hh" value="08">
                <input type="hidden" id="hidden_mn" name="hidden_mn" value="35">
                <input type="hidden" id="cur_mn" name="cur_mn" value="35">

                <p>
                <a href="#edit_timestamp" class="save-timestamp hide-if-no-js button">OK</a>
                <a href="#edit_timestamp" class="cancel-timestamp hide-if-no-js button-cancel">Cancel</a>
                </p>
                                    </fieldset>
                            </div>
                                </div>
                    <div class="clear"></div>
                </div>

                <div id="major-publishing-actions">
                        <div id="delete-action">
                                    <a class="submitdelete deletion" href="http://localhost/wordpress/wp-admin/post.php?post=46&amp;action=trash&amp;_wpnonce=f36f130924">Move to Trash</a>
                                </div>

                    <div id="publishing-action">
                        <span class="spinner"></span>
                                            <input name="original_publish" type="hidden" id="original_publish" value="Publish">
                                    <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Publish">						</div>
                    <div class="clear"></div>
                </div>

                </div>
                    </div>
        </div>
        <?php
    }

}