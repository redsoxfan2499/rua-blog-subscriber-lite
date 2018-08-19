<?php 
/**
 * Uninstall Function
 *
 * Runs on plugin uninstall.
 *
*/

if ( !function_exists( 'rua_blog_subscriber_uninstall' ) )
{
  function rua_blog_subscriber_uninstall()
  {
    global $wpdb;
    //$sql = "DROP TABLE `wp_rua_blog_subscriber`";
    $rua_table_db = 'wp_rua_blog_subscriber';
    $sql = $wpdb->prepare("DROP TABLE `$rua_table_db`");
    $wpdb->query($sql);

    delete_option('rua_blog_subscriber_db_version');
    
    delete_option('rua_site_name');
    delete_option('rua_site_url');
    delete_option('rua_site_contact_form');
    delete_option('rua_company_address');
    delete_option('rua_company_city');
    delete_option('rua_company_state');
    delete_option('rua_company_zip');
    delete_option('rua_company_phone_number');
    delete_option('rua_from_email_address');
    delete_option('rua_email_subject');
    delete_option('rua_email_logo');
    delete_option('rua_form_header');
    delete_option('rua_custom_message');
    delete_option('rua_button_text');
    
    flush_rewrite_rules();
  }
}
register_uninstall_hook( RUA_PLUGIN_FILE, 'rua_blog_subscriber_uninstall');
