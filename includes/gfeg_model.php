<?php

class GFEG_Model {

    /**
     * @return mixed
     */
    public static function get_group_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'gravityform_entry_group';
    }

    /**
     * @return mixed
     */
    public static function get_group_meta_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'gravityform_entry_group_meta';
    }

    /**
     * @param $group_id
     * @param $meta_key
     */
    public static function get_meta($group_id, $meta_key) {
        global $wpdb;
        $table_name = self::get_group_meta_table_name();
        $results = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$table_name} WHERE group_id=%d AND meta_key=%s", $group_id, $meta_key));
        // $results = 22;
        $value = isset($results[0]) ? $results[0]->meta_value : null;
        $meta_value = $value === null ? false : maybe_unserialize($value);
        return $meta_value;
    }

    /**
     * @param $group_id
     * @param $meta_key
     * @param $meta_value
     */
    public static function update_meta($group_id, $meta_key, $meta_value) {
        global $wpdb;
        if (!$meta_key || !is_numeric($group_id)) {
            return false;
        }

        $group_id = absint($group_id);
        if (!$group_id) {
            return false;
        }
        $table_name = self::get_group_meta_table_name();
        if (false === $meta_value) {
            $meta_value = '0';
        }
        $serialized_meta_value = maybe_serialize($meta_value);
        $meta_exists = self::get_meta($group_id, $meta_key) !== false;
        if ($meta_exists == true) {
            $result = $wpdb->update($table_name, array('meta_value' => $serialized_meta_value), array('group_id' => $group_id, 'meta_key' => $meta_key), array('%s'), array('%d', '%s'));
        } else {
            // $result = 112;
            $result = $wpdb->insert($table_name, array('group_id' => $group_id, 'meta_key' => $meta_key, 'meta_value' => $serialized_meta_value), array('%d', '%s', '%s'));
        }
        return $result;
    }

    /**
     * @param $group_id
     * @param $meta_key
     */
    public static function delete_meta($group_id, $meta_key) {
        global $wpdb;
        $table_name = self::get_group_meta_table_name();
        $meta_filter = empty($meta_key) ? '' : $wpdb->prepare('AND meta_key=%s', $meta_key);
        $result = $wpdb->query($wpdb->prepare("DELETE FROM {$table_name} WHERE group_id=%d {$meta_filter}", $group_id));
        return $result;
    }
    //make status form to trash
    public static function trash_form($form_id){
        global $wpdb;
        $table_name = self::get_group_table_name();
        $data = ['group_status' => 'trash'];
        $where = ['id'=>$form_id];
        // $query = "SELECT * FROM $group_table_name WHERE `group_status` = 'trash'";
        $result = $wpdb->update($table_name,$data,$where);
        // var_dump($result);die();
        return $result;
    }
    //delete form
    public static function delete_form($form_id){
        global $wpdb;
        $table_name = self::get_group_table_name();
        $where = ['id' => $form_id];
        $result = $wpdb->delete($table_name,$where);
        return $result;
    }
    //restore form from trash to publish
    public static function restore_form($form_id){
        global $wpdb;
        $table_name = self::get_group_table_name();
        $data = ['group_status' =>'publish'];
        $where = ['id' => $form_id];
        $result = $wpdb->update($table_name,$data,$where);
        return $result;
    }
    //trash muntilble form
    public static function trash_forms( $form_ids ) {
		foreach ( $form_ids as $form_id ) {
			self::trash_form( $form_id );
		}
	}
    //restore muntibel form
    public static function restore_forms( $form_ids ) {
		foreach ( $form_ids as $form_id ) {
			self::restore_form( $form_id );
		}
	}
    //update entry id to group_content
    public static function update_group_content_entry($id,$group_entry){
        global $wpdb;
        $table_name = self::get_group_table_name();
        $where = array('id' =>$id);

        $data = array('group_content' => $group_entry) ;
        // $result = self::update_meta(14, 'entry_group');
        $result = $wpdb->update($table_name,$data,$where);
        // $result = $wpdb->update('wp_gravityform_entry_group','123',array('id'=>$id));
        return $result;
        // echo $entry_id;  
        // UPDATE wp_gravityform_entry_group set group_content = "ok" WHERE id ='9';
        // $result = $wpdb->
    }
    //get all on_used entry of group
    public static function get_all_entry_group($group_id){
        global $wpdb;
        $table_name = self::get_group_meta_table_name();
        $where = ['id' => $group_id];
        // $result = $wpdb->get_results("SELECT group_content FROM $table_name $where");
        $result = $wpdb->get_col("SELECT meta_value FROM $table_name WHERE group_id = $group_id",);
        return $result;
    }
    //get title entry group
    public static function get_title($group_id){
        global $wpdb;
        $table_name = self::get_group_table_name();
        $where = ['id' => $group_id];
        $result = $wpdb->get_col ("SELECT group_title from $table_name WHERE id = $group_id");
        return $result;
    }


}