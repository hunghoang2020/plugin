<?php

if (!class_exists('GFEntryGroup')) {
    exit;
}

class GFEntryGroupNew{

    public static function group_new_page(){
        wp_print_styles( array( 'gfeg_group_new_page' ) );
        ?>
            <div class="wrap">
                <h1 class="wp-heading-inline"><?php esc_html_e( 'Add New Group', 'gfentrygroup' ); ?></h1>
                <hr class="wp-header-end">
                <br />
                <form method="post">
                    <div class="group_field">
                        <label for="gform-dropdown">Select Form</label>
                        <?php echo self::get_forms_dropdown(); ?>
                    </div>
                    <br />
                    <input type="submit" name="add_new_group" value="Add New" class="button" />
                </form>
            </div>
        <?php
    }

    public static function get_forms_dropdown($args = array()){
        $defaults = array(
            'selected'          => 0,
            'name'              => 'gform_dropdown',
            'id'                => 'gform-dropdown',
            'class'             => 'postform',
            'required'          => true,
        );
        $forms = array();
        $output = '';

        $parsed_args = wp_parse_args( $args, $defaults );

        $name     = esc_attr( $parsed_args['name'] );
        $class    = esc_attr( $parsed_args['class'] );
        $id       = $parsed_args['id'] ? esc_attr( $parsed_args['id'] ) : $name;
        $required = $parsed_args['required'] ? 'required' : '';

        if ( class_exists( 'GFAPI' ) ){
            $forms = GFAPI::get_forms();
        }
        $output .= "<select $required name='$name' id='$id' class='$class'>\n";
        $selected = ( '0' === (string) $parsed_args['selected'] ) ? " selected='selected'" : '';
        $output .= "<option value=''>Select Form</option>";
        if(!empty($forms)){
            foreach ( $forms as $form ){
                $id = $form['id'];
                $title = $form['title'];
                $selected = selected( $id, $parsed_args['selected'], false );
                $output .= "<option $selected value='$id'>$title</option>";
            }
        }
        $output .= "</select>";
        return $output;
    }

    public static function add_new_page(){
        if($_GET['page'] == 'gf_add_group' && isset( $_POST['add_new_group']) && !empty($_POST['gform_dropdown'])){
            global $wpdb;
            $form_id = intval( $_POST['gform_dropdown'] );
            $group_table_name = GFEG_Model::get_group_table_name();
            $current_user_id = get_current_user_id();
            $current_time = current_time( 'mysql' );
            $data_to_insert = array(
                'form_id' => $form_id,
                'group_author' => $current_user_id,
                'group_status' => 'publish',
                'group_date' => $current_time,
                'group_modified' => $current_time
            );
            $data_format_insert = array('%d', '%d', '%s', '%s', '%s');
            $result = $wpdb->insert( $group_table_name, $data_to_insert, $data_format_insert);
            if($result !== false){
                $post_id = $wpdb->insert_id;
                echo $post_id;
                // wp_redirect( $redirect_url );
            }
        }
    }
}