<?php

class GFEG_Upgrade {

    /**
     * @return mixed
     */
    public static function list_db_schema() {
        global $wpdb;
        $tables = array();
        $charset_collate = $wpdb->get_charset_collate();
        $group_table_name = GFEG_Model::get_group_table_name();
        $tables[$group_table_name] = "CREATE TABLE IF NOT EXISTS $group_table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            form_id mediumint UNSIGNED NOT NULL DEFAULT 0,
            group_author BIGINT(20) NOT NULL,
            group_title VARCHAR(255) NULL,
            group_content LONGTEXT NULL,
            group_status VARCHAR(20) NOT NULL,
            group_date DATETIME DEFAULT '0000-00-00 00:00:00',
            group_modified DATETIME DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id)
        ) $charset_collate;";

        $group_meta_table_name = GFEG_Model::get_group_meta_table_name();
        $tables[$group_meta_table_name] = "CREATE TABLE IF NOT EXISTS $group_meta_table_name (
            meta_id BIGINT(20) NOT NULL AUTO_INCREMENT,
            group_id BIGINT(20) UNSIGNED NOT NULL,
            form_id mediumint UNSIGNED NOT NULL DEFAULT 0,
            meta_key VARCHAR(255) NULL,
            meta_value VARCHAR(255) NULL,
            FOREIGN KEY (group_id) REFERENCES $group_table_name(id) ON DELETE CASCADE,
            PRIMARY KEY (meta_id),
            UNIQUE (group_id)
        ) $charset_collate;";

        return $tables;
    }

    public static function activation() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $schema = self::list_db_schema();
        foreach ($schema as $table_name => $sql) {
            dbDelta($sql);
        }
    }

    public static function deactivation() {}
}
