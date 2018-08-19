<?php 
/**
 * Install Function
 *
 * Runs on plugin install.
 *
*/

if ( !function_exists( 'rua_blog_subscriber_install' ) )
{
  function rua_blog_subscriber_install()
  {
    global $wpdb;
    global $rua_blog_subscriber_db_version;

    // $sql = "CREATE TABLE IF NOT EXISTS `wp_rua_blog_subscriber` (
    //   `id` int(11) NOT NULL AUTO_INCREMENT,
    //   `subscriber_name` varchar(255) NOT NULL,
    //   `subscriber_email` varchar(255) NOT NULL,
    //   `subscriber_status` varchar(100) NOT NULL,
    //   `site_id` varchar(100) NOT NULL,
    //   `subscribe_date` date NOT NULL,
    //   `unsubscribe_date` date NULL,
    //   `activation_key` varchar(100) NULL,
    //   PRIMARY KEY (`id`)
    //) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;";

    $rua_table_db = 'wp_rua_blog_subscriber';
    $sql = $wpdb->prepare("CREATE TABLE IF NOT EXISTS `$rua_table_db` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `subscriber_name` varchar(255) NOT NULL,
      `subscriber_email` varchar(255) NOT NULL,
      `subscriber_status` varchar(100) NOT NULL,
      `site_id` varchar(100) NOT NULL,
      `subscribe_date` date NOT NULL,
      `unsubscribe_date` date NULL,
      `activation_key` varchar(100) NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;");

    $wpdb->query($sql);

    add_option('rua_blog_subscriber_db_version', $rua_blog_subscriber_db_version);

    // clear permalinks
    flush_rewrite_rules();
  }
}

register_activation_hook( RUA_PLUGIN_FILE, 'rua_blog_subscriber_install' );