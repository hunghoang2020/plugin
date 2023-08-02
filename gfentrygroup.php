<?php
/*
Plugin Name: Gravity Forms Create Group Entry
Plugin URI:
Description: Create Group Entry.
Version: 1.0.0
Author: EFE Technology
Author URI:
Text Domain: gfentrygroup
 */

if (!defined('ABSPATH') || !class_exists('GFForms')) {
    exit;
}

define('GFEG_VERSION', '1.0.0');
define('GFEG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GFEG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GFEG_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once GFEG_PLUGIN_DIR . 'includes/gfeg_model.php';
require_once GFEG_PLUGIN_DIR . 'includes/gfeg_upgrade.php';
require_once GFEG_PLUGIN_DIR . 'includes/View/group_list.php';
require_once GFEG_PLUGIN_DIR . 'includes/View/group_new.php';
require_once GFEG_PLUGIN_DIR . 'includes/View/group_edit.php';

register_activation_hook(__FILE__, array('GFEG_Upgrade', 'activation'));

class GFEntryGroup {

    public function __construct() {
        add_action('admin_menu', array($this, 'create_menu'));
        self::register_scripts();
        // add_action('admin_menu',array($this, 'pippin_sample_settings_menu'));
       
        add_filter('set-screen-option',array($this,'pippin_set_screen_option') , 10, 3);
        add_action('wp_ajax_handle_update_form_group',array($this,'handle_update_form_group')  );
    }
  

    public  function create_menu() {
        $admin_icon = GFForms::get_admin_icon_b64(GFForms::is_gravity_page() ? '#fff' : '#a0a5aa');
        global $pippin_sample_page;
        $pippin_sample_page = add_menu_page(__('Entry Groups', 'gfentrygroup'), __('Entry Groups', 'gfentrygroup'), 'manage_options', 'gf_edit_group', array('GFEntryGroup', 'groups'), $admin_icon, '16.10');
        // add_submenu_page('gf_edit_group', __('Add New', 'gfentrygroup'), __('Add New', 'gfentrygroup'), 'manage_options', 'gf_add_group1', array('GFEntryGroup', 'new_group'));
      
        add_submenu_page('gf_edit_group', __('Add New', 'gfentrygroup'), __('Add New', 'gfentrygroup'), 'manage_options', 'gf_add_group', array('GFEntryGroup', 'new_group'));
        add_action("load-$pippin_sample_page",array ($this,'pippin_sample_screen_options'));
    
    }
    function pippin_set_screen_option($status, $option, $value) {

        if ( 'gfentrygroup_per_page' == $option ) return $value;
    }
   
    public function pippin_sample_screen_options() {
    
        global $pippin_sample_page;
        $screen = get_current_screen();
     
        // get out of here if we are not on our settings page
        if(!is_object($screen) || $screen->id != $pippin_sample_page)
            return;
     
        $args = array(
            'label' => __('Members per page', 'gfentrygroup'),
            'default' => 10,
            'option' => 'gfentrygroup_per_page'
        );
        add_screen_option( 'per_page', $args );
    }
   
    public static function groups() {
        // echo rgget('action');
        if(rgget('action') == 'edit'){
            GFEntryGroupList_Edit::group_list_edit_page();
        }else{
            GFEntryGroupList::group_list_page();
        }
        
    }

    public static function new_group() {
        GFEntryGroupNew::group_new_page();
        GFEntryGroupNew::add_new_page();
    }

    public static function register_scripts(){
        wp_register_style( 'gfeg_group_new_page', GFEG_PLUGIN_URL.'assets/admin/css/group_new.css', array(), GFEG_VERSION, 'all' );
        wp_register_style( 'gfeg_group_list_page', GFEG_PLUGIN_URL.'assets/admin/css/group_list.css', array(), GFEG_VERSION, 'all' );
        wp_register_style( 'gfeg_group_edit_page', GFEG_PLUGIN_URL.'assets/admin/css/group_edit.css', array(), GFEG_VERSION, 'all' );
        
    }
    //handle ajax update gruop content
    public function handle_update_form_group(){
        global $wpdb; // this is how you get access to the database
	    $id = $_POST['group_id'] ;
        $entry_id = $_POST['entry_id'];
        // $entry_id = json_encode( array('entry_group' =>[$_POST['entry_id']]));
        $current_meta_value = json_decode( GFEG_Model::get_meta($id,'group_entry'));
        
        if(in_array($entry_id,$current_meta_value)){
            if (($key = array_search($entry_id, $current_meta_value)) !== false) {
                array_splice($current_meta_value, $key,1); 
            }
            $result = GFEG_Model::update_meta($id,'group_entry',json_encode( $current_meta_value));
            wp_send_json_success( array('data' => $result ) , 200); 
        }else{
            array_push($current_meta_value, $entry_id);
            $result = GFEG_Model::update_meta($id,'group_entry',json_encode( $current_meta_value));
            wp_send_json_success( array('data' => $result ) , 200); 

        }
        wp_die();// this is required to terminate immediately and return a proper response
    }

}

new GFEntryGroup();