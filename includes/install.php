<?php

function contact__create_table() {

    global $wpdb;

    // Check for capability
    if ( !current_user_can('activate_plugins') )
        return;

    $table_name = $wpdb->prefix . 'contact';
    if( $wpdb->get_var( "show tables like '{$table_name}'" ) != $table_name ) {
        $sql = "CREATE TABLE " . $table_name . " (
            id BIGINT(20) NOT NULL AUTO_INCREMENT ,
            type VARCHAR(255) DEFAULT '' NOT NULL ,
            name VARCHAR(255) DEFAULT '' NOT NULL ,
            body text NULL ,
            enterdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY id (id)
        );";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

// Creating tables for all blogs in a WordPress Multisite installation
function contact__activate( ) {

    global $wpdb;
    if ( is_multisite() ) {
        // Get all blogs in the network and activate plugin on each one
        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        foreach ( $blog_ids as $blog_id ) {
            switch_to_blog( $blog_id );
            contact__create_table();
            restore_current_blog();
        }
    } else {
        contact__create_table();
    }
}

// Creating table whenever a new blog is created
function on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    if ( is_plugin_active_for_network( 'plugin-name/plugin-name.php' ) ) {
        switch_to_blog( $blog_id );
        contact__create_table();
        restore_current_blog();
    }
}

register_activation_hook( __FILE__, 'contact__activate' );
add_action( 'wpmu_new_blog', 'on_create_blog', 10, 6 );

// Deleting the table whenever a blog is deleted
function contact__delete_blog( $tables ) {
    global $wpdb;
    $tables[] = $wpdb->prefix . 'contact';
    return $tables;
}
add_filter( 'wpmu_drop_tables', 'contact__delete_blog' );
