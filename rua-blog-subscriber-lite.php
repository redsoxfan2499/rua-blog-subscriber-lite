<?php
/*
Plugin Name: RUA Blog Subscriber Lite
Plugin URI: https://hyperdrivedesigns.com
Description: WordPress plugin that displays a subscribe to blog form using a shortcode. You can place the shortcode in a page or
  post. You can also place the form in a widget with the assitance of the Shortcode Widget plugin which is a free plugin avaiable from
  the WordPress Plugin Directory. This plugin also displays a list of email subscribers, their status, ability to filter by status along
  with the ability to delete a subscriber.
Author: Darren Ladner
Author URI: https://hyperdrivedesigns.com
Version: 1.5.4
Textdomain: rua-lite
Domain Path: /languages
*/

 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
|--------------------------------------------------------------------------
| CONSTANTS
|--------------------------------------------------------------------------
*/
// plugin folder url
if(!defined('RUA_PLUGIN_URL')) {
	define('RUA_PLUGIN_URL', plugin_dir_url( __FILE__ ));
}
// plugin folder path
if(!defined('RUA_PLUGIN_DIR')) {
	define('RUA_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
}
// plugin root file
if(!defined('RUA_PLUGIN_FILE')) {
	define('RUA_PLUGIN_FILE', __FILE__);
}
// plugin version
if(!defined('RUA_VERSION')) {
	define('RUA_VERSION', '1.5.4');
}

/*
|--------------------------------------------------------------------------
| GLOBALS
|--------------------------------------------------------------------------
*/
global $rua_blog_subscriber_db_version;
$rua_blog_subscriber_db_version = "1.0";

/*
|--------------------------------------------------------------------------
| INCLUDES
|--------------------------------------------------------------------------
*/
require_once( RUA_PLUGIN_DIR.'includes/recaptchalib.php' );
require_once( RUA_PLUGIN_DIR.'includes/new-page-templater.php' );
require_once( RUA_PLUGIN_DIR.'includes/install.php' );
require_once( RUA_PLUGIN_DIR.'includes/register-settings.php' );
require_once( RUA_PLUGIN_DIR.'uninstall.php' );

add_action( 'plugins_loaded', array( 'RUAPageTemplater', 'get_instance' ) );
/**
 * Attempt at Gutenberg Block.
 */
 function gutenberg_boilerplate_block() {
     wp_register_script(
         'gutenberg-boilerplate-es5-step01',
         plugins_url( 'js/block.js', __FILE__ ),
         array( 'wp-blocks', 'wp-element' )
     );
		 wp_register_style(
        'gutenberg-boilerplate-es5-step01-editor',
        plugins_url( 'css/editor.css', __FILE__ ),
        array( 'wp-edit-blocks' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'css/editor.css' )
    );
		wp_register_style(
        'gutenberg-boilerplate-es5-step02',
        plugins_url( 'css/style.css', __FILE__ ),
        array(),
        filemtime( plugin_dir_path( __FILE__ ) . 'css/style.css' )
    );

     register_block_type( 'gutenberg-boilerplate-es5/hello-world-step-01', array(
         'editor_script' => 'gutenberg-boilerplate-es5-step01',
				 'editor_style'  => 'gutenberg-boilerplate-es5-step01-editor',
				 'style' => 'gutenberg-boilerplate-es5-step02',
     ) );
 }
 add_action( 'init', 'gutenberg_boilerplate_block' );

 function rua_lite_gutenberg_block() {
    wp_register_script(
        'rua-lite-gutenberg-block-script',
        plugins_url( 'block/rua-lite-block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element' )
    );

    register_block_type( 'rua-lite-gutenblock/rua-lite-block', array(
        'editor_script' => 'rua-lite-gutenberg-block-script',
    ) );
}
add_action( 'init', 'rua_lite_gutenberg_block' );

/**
 * Enqueue scripts and styles.
 */
if ( !function_exists('rua_blog_public_subscriber_scripts' ) )
{
	function rua_blog_public_subscriber_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'rua-public-styles', untrailingslashit( RUA_PLUGIN_URL.'css/rua-blog-subscriber-public-styles.css' ) );
		wp_enqueue_script( 'rua', untrailingslashit( RUA_PLUGIN_URL.'js/custom.js' ) );
		wp_enqueue_style( 'rua-font-awesome-css', untrailingslashit( RUA_PLUGIN_URL.'css/font-awesome.min.css' ) );
		wp_localize_script( 'rua', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_script( 'rua-js-validation', untrailingslashit( RUA_PLUGIN_URL.'js/jquery.validate.min.js' ), '1.14.0' );

	}
}
add_action( 'wp_enqueue_scripts', 'rua_blog_public_subscriber_scripts' );

if ( !function_exists('rua_create_email_subscribers_menu' ) )
{
	function rua_create_email_subscribers_menu() {
		$menu = add_menu_page( 'Email Subscribers', 'Email Subscribers', 'manage_options', 'subscriber-admin.php', 'rua_create_subscriber_admin_page', 'dashicons-admin-users', 6 );
		$submenu = add_options_page( 'RUA Settings', 'Email Subscribers Settings', 'manage_options', 'rua-subscribers-settings', 'rua_create_email_subscribers_settings_page' );

		 add_action( 'admin_print_styles-' . $menu, 'rua_admin_custom_css' );
		 add_action( 'admin_print_scripts-' . $menu, 'rua_admin_custom_js' );
		 add_action( 'admin_print_styles-' . $submenu, 'rua_admin_custom_css' );
		 add_action( 'admin_print_scripts-' . $submenu, 'rua_admin_custom_js' );
	}
}
add_action( 'admin_menu', 'rua_create_email_subscribers_menu' );

// load bootstrap css for email subscriber admin page only
if ( !function_exists('rua_admin_custom_css' ) )
{
	function rua_admin_custom_css()
	{
		wp_enqueue_style( 'rua-admin-font-awesome-css', untrailingslashit( RUA_PLUGIN_URL.'css/font-awesome.min.css', '4.4.0' ) );
		wp_enqueue_style( 'rua-admin-bootstrap-css', untrailingslashit( RUA_PLUGIN_URL.'css/bootstrap.min.css', '3.3.5' ) );
		wp_enqueue_style( 'rua-admin-dataTables-bootstrap-css', untrailingslashit( RUA_PLUGIN_URL.'css/dataTables.bootstrap.css' ) );
		wp_enqueue_style( 'rua-admin-styles', untrailingslashit( RUA_PLUGIN_URL.'css/rua-blog-subscriber-admin-styles.css' ) );
	}
}

// load bootstrap ans custom js file for subscriber admin page only
if ( !function_exists('rua_admin_custom_js' ) )
{
	function rua_admin_custom_js()
	{
		wp_enqueue_script( 'rua-admin-bootstrap-js', untrailingslashit( RUA_PLUGIN_URL.'js/bootstrap.min.js', array( 'jquery' ) ) );
		wp_enqueue_script( 'rua-blog-subscriber-custom-js', untrailingslashit( RUA_PLUGIN_URL.'/js/custom.js' ) );
		wp_enqueue_script( 'rua-blog-subscriber-custom-datatables-js', untrailingslashit( RUA_PLUGIN_URL.'/js/custom-datatables.js' ) );
		wp_enqueue_script( 'rua-admin-datatables-js', untrailingslashit( RUA_PLUGIN_URL.'js/jquery.dataTables.min.js', '1.10.11' ) );
 		wp_enqueue_script( 'rua-admin-datatables-bootstrap-js', untrailingslashit( RUA_PLUGIN_URL.'js/dataTables.bootstrap.js' ) );
	};
}

// create admin dashboard page
if ( !function_exists('rua_create_subscriber_admin_page' ) )
{
	function rua_create_subscriber_admin_page() {
		$site_id = get_current_blog_id();
		global $wpdb;
		//$wpdb->get_results( "SELECT * FROM wp_rua_blog_subscriber WHERE subscriber_status = 'subscribed' AND site_id = '$site_id'" );
		$wpdb->get_results( $wpdb->prepare( "SELECT * FROM wp_rua_blog_subscriber WHERE subscriber_status = 'subscribed' AND site_id = %d", $site_id ) );
		$subscriber_count = $wpdb->num_rows;

		//$all_subscribers = $wpdb->get_results( "SELECT * FROM wp_rua_blog_subscriber WHERE site_id = '$site_id'" );
		$all_subscribers = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wp_rua_blog_subscriber WHERE site_id = %d", $site_id ) );
		?>
		<div class="wrap">
			<div class="container">
				<h3 class="text-center"><?php _e( 'Email Subscribers', 'rua-lite' ); ?></h3>
					<div class="row">
						<div class="col-md-12">
							<div>
								<p class="text-center">
								<?php	_e( 'NEED MORE FEATURES/OPTIONS&nbsp;&nbsp;', 'rua-lite' ); ?>
									<strong><?php _e( 'Upgrade to RUA BLOG SUBSCRIBER PRO', 'rua-lite' ); ?></strong>&nbsp;&nbsp;
									<a class="btn btn-upgrade-sm" href="https://hyperdrivedesigns.com/shop/" target="_blank" role="button"><?php _e( 'UPGRADE NOW', 'rua-lite' ); ?></a>
								</p>
							</div>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-3">
							<?php _e( 'Total Members:', 'rua-lite' ); ?> <?php echo esc_attr( $subscriber_count ); ?>
						</div>
						<div class="col-md-5">
							<div class="btn-group">
								<a href="#" id="all" class="btn btn-default btn-sm">
									<span class="glyphicon glyphicon-th-list"></span>
										 <?php _e( 'All', 'rua-lite' ); ?>
								</a>
								<a href="#" id="subscribed" class="btn btn-default btn-sm">
									<span class="glyphicon glyphicon-th"></span>
										 <?php _e( 'Subscribed', 'rua-lite' ); ?>
								</a>
								<a href="#" id="unsubscribed" class="btn btn-default btn-sm">
									<span class="glyphicon glyphicon-th"></span>
										 <?php _e( 'Unsubscribed', 'rua-lite' ); ?>
								</a>
								<a href="#" id="unverified" class="btn btn-default btn-sm">
									<span class="glyphicon glyphicon-th"></span>
										 <?php _e( 'Unverified', 'rua-lite' ); ?>
								</a>
							</div>
						</div>
						<div class="col-md-4">
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<hr />
							<table id="datatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
								<thead>
									<th><?php _e( 'ID', 'rua-lite' ); ?></th>
									<th><?php _e( 'Name', 'rua-lite' ); ?></th>
									<th><?php _e( 'Email', 'rua-lite' ); ?></th>
									<th><?php _e( 'Subscribe Date', 'rua-lite' ); ?></th>
									<th><?php _e( 'Status', 'rua-lite' ); ?></th>
									<th><?php _e( 'Unsubscribe Date', 'rua-lite' ); ?></th>
									<th><?php _e( 'Delete', 'rua-lite' ); ?></th>
								</thead>
								<tfoot>
									<tr>
										<th><?php _e( 'ID', 'rua-lite' ); ?></th>
										<th><?php _e( 'Name', 'rua-lite' ); ?></th>
										<th><?php _e( 'Email', 'rua-lite' ); ?></th>
										<th><?php _e( 'Subscribe Date', 'rua-lite' ); ?></th>
										<th><?php _e( 'Status', 'rua-lite' ); ?></th>
										<th><?php _e( 'Unsubscribe Date', 'rua-lite' ); ?></th>
										<th><?php _e( 'Delete', 'rua-lite' ); ?></th>
									</tr>
								</tfoot>
								<tbody>
									<?php
										foreach( $all_subscribers as $subscriber ) {
											if( $subscriber->subscriber_status == 'subscribed' )
											{
													$data = 'subscribed';
											}
											elseif( $subscriber->subscriber_status == 'unsubscribed' )
											{
												$data = 'unsubscribed';
											}
											elseif( $subscriber->subscriber_status == 'unverified' )
											{
												$data = 'unverified';
											}
									?>
										<tr class="<?php echo esc_attr( $data ); ?>">
											<td><?php echo esc_attr( $subscriber->id ); ?></td>
											<td><?php echo esc_attr( $subscriber->subscriber_name ); ?></td>
											<td><?php echo esc_attr( $subscriber->subscriber_email ); ?></td>
											<td><?php echo esc_attr( $subscriber->subscribe_date ); ?></td>
											<td>
											<?php
												if ( $subscriber->subscriber_status == 'subscribed' )
												{
												?>
												<button type="button" class="btn btn-success btn-xs">
													<?php echo esc_attr( $subscriber->subscriber_status ); ?>
												</button>
												<?php
												}
												elseif ( $subscriber->subscriber_status == 'unsubscribed' )
												{
												?>
												<button type="button" class="btn btn-danger btn-xs">
													<?php _e( 'Unsubscribed', 'rua-lite' ); ?>
												</button>
												<?php
												}
												elseif ( $subscriber->subscriber_status == 'unverified' )
												{
												?>
												<button type="button" class="btn btn-warning btn-xs">
													<?php _e( 'Unverified' ,'rua-lite' ); ?>
												</button>
												<?php
												}
												?>
											</td>
											<td>
												<?php
													if ( $subscriber->unsubscribe_date == '' )
													{
														echo _e( '----', 'rua-lite' );
													}
													else
													{
														echo esc_attr( $subscriber->unsubscribe_date );
													}
												?>
											</td>
											<td>
												<a href="#delete" id="trash" data-id="<?php echo esc_attr( $subscriber->id ); ?>" class="trash btn btn-danger btn-xs" role="button" data-toggle="modal" data-title="Delete" data-target="#delete">
													<span class="glyphicon glyphicon-trash"></span></i>
												</a>
											</td>
										</tr>
										<?php
										}
										?>
								</tbody>
							</table>
						</div><!-- end col-md-12 -->
					</div><!-- end row -->

			<div class="row">
				<!-- modal for deleting subscribers -->
				<div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="edit" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
									<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
								</button>
								<h4 class="modal-title custom_align" id="Heading"><?php _e( 'Delete this entry', 'rua-lite' ); ?></h4>
							</div>
							<div class="modal-body">
								<?php
									if( !empty( $_POST['subscriber_id'] ) )
									{
										$id = intval( $_POST["subscriber_id"] );
										$retrieved_nonce 	= $_POST['_wpnonce'];
										if ( wp_verify_nonce( $retrieved_nonce, 'rua_delete_subscriber_nonce' ) )
										{
											global $wpdb;
											//$wpdb->get_results( "DELETE FROM wp_rua_blog_subscriber WHERE id = '$id'" );
											$wpdb->get_results( $wpdb->prepare( "DELETE FROM wp_rua_blog_subscriber WHERE id = %d", $id ) );
 											echo '<script>location.reload();</script>';
										}
									}
									 ?>
									 <form id="delete-form" class="form-horizontal" method="post" action="">
										 <?php wp_nonce_field( 'rua_delete_subscriber_nonce' ); ?>
										 <input type="hidden" value="subscriber_id" id="subscriber_id" name="subscriber_id" />
											<div class="alert alert-danger">
												<span class="glyphicon glyphicon-warning-sign"></span>
												<?php _e( 'Are you sure you want to delete this Record with ID of <span id="sub_id_holder"></span>', 'rua-lite' ); ?>
											</div>
							</div>
							<div class="modal-footer">
										<input id="modalDelete" type="submit" class="btn btn-success" value="<?php _e( 'Yes', 'rua-lite' ); ?>">
										<span class="glyphicon glyphicon-ok-sign"></span> 
										<input type="reset" class="btn btn-default" data-dismiss="modal" value="<?php _e( 'No', 'rua-lite' ); ?>">
										<span class="glyphicon glyphicon-remove"></span> 
									</form>
							</div><!-- end modal footer -->
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- end modal fade -->
			</div><!-- end row -->
			</div><!-- end container -->
		</div><!-- end wrap -->
		<?php
	}
}

// create email subscribers settings options page
if ( !function_exists('rua_create_email_subscribers_settings_page' ) )
{
	function rua_create_email_subscribers_settings_page() {
		if('POST' == $_SERVER['REQUEST_METHOD'])
		{
			$rua_site_name = sanitize_text_field( $_POST['rua_site_name'] );
			$rua_site_url = sanitize_text_field( $_POST['rua_site_url'] );
			$rua_site_contact_form = sanitize_text_field($_POST['rua_site_contact_form']);
			$rua_company_address = sanitize_text_field( $_POST['rua_company_address'] );
			$rua_company_city = sanitize_text_field( $_POST['rua_company_city'] );
			$rua_company_state = sanitize_text_field( $_POST['rua_company_state'] );
			$rua_company_zip = sanitize_text_field( $_POST['rua_company_zip'] );
			$rua_company_phone_number = sanitize_text_field( $_POST['rua_company_phone_number'] );
			$rua_from_email_address = sanitize_email( $_POST['rua_from_email_address'] );
			$rua_email_subject = sanitize_text_field( $_POST['rua_email_subject'] );
			$rua_email_logo = sanitize_text_field( $_POST['rua_email_logo'] );
			$rua_form_header = sanitize_text_field( $_POST['rua_form_header'] );
			$rua_custom_message = sanitize_text_field( $_POST['rua_custom_message'] );
			$rua_button_text = sanitize_text_field( $_POST['rua_button_text'] );
			$rua_blog_url = sanitize_text_field( $_POST['rua_blog_url'] );
			$rua_recaptcha_site_key = sanitize_text_field( $_POST['rua_recaptcha_site_key'] );
			$rua_recaptcha_secret_key = sanitize_text_field( $_POST['rua_recaptcha_secret_key'] );

			update_option( 'rua_site_name', $rua_site_name );
			update_option( 'rua_site_url', $rua_site_url );
			update_option( 'rua_site_contact_form', $rua_site_contact_form );
			update_option( 'rua_company_address', $rua_company_address );
			update_option( 'rua_company_city', $rua_company_city );
			update_option( 'rua_company_state', $rua_company_state );
			update_option( 'rua_company_zip', $rua_company_zip );
			update_option( 'rua_company_phone_number', $rua_company_phone_number );
			update_option( 'rua_from_email_address', $rua_from_email_address );
			update_option( 'rua_email_subject', $rua_email_subject );
			update_option( 'rua_email_logo', $rua_email_logo );
			update_option( 'rua_form_header', $rua_form_header );
			update_option( 'rua_custom_message', $rua_custom_message );
			update_option( 'rua_button_text', $rua_button_text );
			update_option( 'rua_blog_url', $rua_blog_url );
			update_option( 'rua_recaptcha_site_key', $rua_blog_url );
			update_option( 'rua_recaptcha_secret_key', $rua_blog_url );
		}
		?>
		<div class="wrap">
			<div class="container">
				<form id="es_settings" name="es_settings" class="form-horizontal" action="" method="post">
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<h2 class="text-center"><?php _e( 'Email Subscribers Settings', 'rua-lite' ); ?></h2>
							<p class="text-center"><?php _e( 'Use these options to set values for the emails that will be sent to subscribers.', 'rua-lite' ); ?>
							</p>
						</div>
					</div>
				</div>
				<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_site_name"><?php _e( 'Name:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_site_name" name="rua_site_name" value="<?php echo esc_attr( get_option( 'rua_site_name' ) ); ?>" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_site_url"><?php _e( 'Site Url:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_site_url" name="rua_site_url" value="<?php echo esc_attr( get_option( 'rua_site_url' ) ); ?>" />
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_company_address"><?php _e( 'Address:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_company_address" name="rua_company_address" value="<?php echo esc_attr( get_option( 'rua_company_address' ) ); ?>" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_company_city"><?php _e( 'City:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_company_city" name="rua_company_city" value="<?php echo esc_attr( get_option( 'rua_company_city' ) ); ?>" />
								</div>
							</div>
						</div>
				</div>
				<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_company_state"><?php _e( 'State:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_company_state" name="rua_company_state" value="<?php echo esc_attr( get_option( 'rua_company_state' ) ); ?>" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_company_zip"><?php _e( 'Zip:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_company_zip" name="rua_company_zip" value="<?php echo esc_attr( get_option( 'rua_company_zip' ) ); ?>" />
								</div>
						 </div>
						</div>
				</div>

				<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_company_phone_number"><?php _e( 'Phone Number:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_company_phone_number" name="rua_company_phone_number" value="<?php echo esc_attr( get_option( 'rua_company_phone_number' ) ); ?>" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_email_subject"><?php _e( 'From Email Subject:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_email_subject" name="rua_email_subject" value="<?php echo esc_attr( get_option( 'rua_email_subject' ) ); ?>" />
								</div>
							</div>
						</div>
				</div>
				<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_from_email_address"><?php _e( 'From Email Address:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_from_email_address" name="rua_from_email_address" value="<?php echo esc_attr( get_option( 'rua_from_email_address' ) ); ?>" />
								</div>
							</div>
						</div>
					  <div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_form_header"><?php _e( 'Form Header:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_form_header" name="rua_form_header" value="<?php echo esc_attr( get_option( 'rua_form_header' ) ); ?>" />
								</div>
							</div>
						</div>
				</div>
				<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<div class="col-md-4">
									<label for="rua_button_text"><?php _e( 'Form Button Text:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_button_text" name="rua_button_text" value="<?php echo esc_attr( get_option( 'rua_button_text' ) ); ?>" />
								</div>
						 	</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
							<div class="col-md-4">
								<label for="rua_email_logo"><?php _e( 'Email Logo URL:', 'rua-lite' ); ?></label>
							</div>
							<div class="col-md-8">
								<input type="text" id="rua_email_logo" name="rua_email_logo" value="<?php echo esc_attr( get_option( 'rua_email_logo' ) ); ?>" />
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
								<div class="col-md-4">
									<label for="rua_site_contact_form"><?php _e( 'Site Contact Form URL:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_site_contact_form" name="rua_site_contact_form" value="<?php echo esc_attr( get_option( 'rua_site_contact_form' ) ); ?>" />
								</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							  <div class="col-md-4">
									<label for="rua_blog_url"><?php _e( 'Your Blog Page URL:', 'rua-lite' ); ?></label>
								</div>
							  <div class="col-md-8">
									<input type="text" id="rua_blog_url" name="rua_blog_url" value="<?php echo esc_attr( get_option( 'rua_blog_url' ) ); ?>" />
								</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
								<div class="col-md-4">
									<label for="rua_recaptcha_enable"><?php _e( 'Recaptcha Enable:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<select id="rua_recaptcha_enable" name="rua_recaptcha_enable">
										<option value="<?php echo esc_attr( get_option( 'rua_recaptcha_enable' ) ); ?>"><?php echo esc_attr( get_option( 'rua_recaptcha_enable' ) ); ?></option>
										<option value="off">Off</option>
										<option value="on">On</option>
									</select>
								</div>
						</div>
					</div>
					<div class="col-md-6">
						<p>To understand and get your Google Recapthca Keys go here <a href="https://developers.google.com/recaptcha/docs/versions">Google Recaptcha</a>. RUA Lite is using reCAPTCHA V2.</p> 
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
								<div class="col-md-4">
									<label for="rua_recaptcha_site_key"><?php _e( 'Recaptcha Site Key:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<input type="text" id="rua_recaptcha_site_key" name="rua_recaptcha_site_key" value="<?php echo esc_attr( get_option( 'rua_recaptcha_site_key' ) ); ?>" />
								</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							  <div class="col-md-4">
									<label for="rua_recaptcha_secret_key"><?php _e( 'Recaptcha Secret Key:', 'rua-lite' ); ?></label>
								</div>
							  <div class="col-md-8">
									<input type="text" id="rua_recaptcha_secret_key" name="rua_recaptcha_secret_key" value="<?php echo esc_attr( get_option( 'rua_recaptcha_secret_key' ) ); ?>" />
								</div>
						</div>
					</div>
				</div>

				<br>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
								<div class="col-md-4">
									<label for="rua_custom_message"><?php _e( 'Custom Success Message:', 'rua-lite' ); ?></label>
								</div>
								<div class="col-md-8">
									<textarea class="form-control" id="rua_custom_message" name="rua_custom_message" rows="3">
										<?php echo esc_textarea( get_option( 'rua_custom_message' ) ); ?>
									</textarea>
								</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">

							<div class="col-md-4">
								<label for="rua_custom_message"><?php _e( 'Your Email Logo:', 'rua-lite' ); ?></label>
							</div>
							<div class="col-md-8">
								<img src="<?php echo esc_url( get_option( 'rua_email_logo' ) ); ?>" />
							</div>

						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6 col-md-offset-3">
						<div class="form-group">
							  <div class="col-md-12 text-center">
										<input name="plugin_rua_options[submit]" id="submit_options_form" type="submit" class="btn btn-upgrade" value="<?php esc_attr_e( 'Save Settings', 'rua' ); ?>" />
									</div>
						</div>
					</div>
				</div>
				</form>
				<hr>
				<div class="row">
					<div class="col-md-6">
						<div class="upgrade">
							<p class="text-center upgrade-header">
								<span class="dashicons dashicons-tickets"></span><br>
								<strong><?php _e( 'Upgrade to RUA BLOG SUBSCRIBER PRO', 'rua-lite' ); ?></strong>
							</p>
							<hr>
							<p>
								<ul class="upgrade-list">
									<li><?php _e( 'Upgrade to RUA BLOG SUBSCRIBER PRO and get a lot of extra features:', 'rua-lite' ); ?></li>
									<li><?php _e( 'Subscriber Form Customization', 'rua-lite' ); ?></li>
									<li><?php _e( 'Ability to create Custom Emails', 'rua-lite' ); ?></li>
									<li><?php _e( 'Custom Hooks', 'rua-lite' ); ?></li>
									<li><?php _e( 'Custom Filters', 'rua-lite' ); ?></li>
									<li><?php _e( 'Ability for Extensions to Third Party Email List Services', 'rua-lite' ); ?></li>
									<li><?php _e( 'Excellent Customer Support', 'rua-lite' ); ?></li>
									<li><?php _e( 'Compatibility with WordPress Multisites', 'rua-lite' ); ?></li>
							</ul>
							</p>
							<br>
							<p class="text-center">
								<a class="btn btn-upgrade btn-lg" href="https://hyperdrivedesigns.com/shop/" target="_blank" role="button">
									<?php _e( 'UPGRADE NOW', 'rua-lite' ); ?></a>
							</p>
						</div>
					</div>
					<div class="col-md-6">
						<div class="reviews">
							<p class="text-center upgrade-header">
								<i class="fa fa-heartbeat fa-4x"></i><br>
								<strong class="reviews-header">
									<?php _e( 'Are you enjoying RUA Blog Subscriber?', 'rua-lite' ); ?></strong>
							</p>
							<hr>
							<p>
								<?php _e( 'If you have any suggestions for RUA Blog Subscriber Lite, please send them to us at
								<a href="http://ruablogsubscriber.com/contact/" target="_blank">RUA Blog Subscriber</a>.
								If you need a little help, you can get in touch with us two ways. You can leave a Support ticket at Hyperdrive Designs located
								<a href="http://hyperdrivedesigns.com/custom-wordpress-plugins/js-support-ticket-controlpanel/" target="_blank">HERE</a> or
								if you are in need of DIRECT support, you can leave a RUA Blog Subscriber
								Support Ticket directly on our website at <a href="http://ruablogsubscriber.com/js-support-ticket-controlpanel/" target="_blank">RUA Blog Subscriber</a>.',
								'rua-lite' ); ?>
							</p>
							<p>
								<ul class="review">
									<li><i class="fa fa-exclamation-circle fa-lg"></i>
										<a href="https://hyperdrivedesigns.com/shop/" target="_blank"><?php _e( 'Upgrade to the PRO version!', 'rua-lite' ); ?></a>
									</li>
									<li><i class="fa fa-exclamation-circle fa-lg"></i>
										<a href="https://hyperdrivedesigns.com/shop/" target="_blank"><?php _e( 'More Plugins?', 'rua-lite' ); ?></a>
									</li>
									<li><i class="fa fa-exclamation-circle fa-lg"></i>
										<a href="https://hyperdrivedesigns.com/shop/" target="_blank"><?php _e( 'How about a Theme?', 'rua-lite' ); ?></a>
									</li>
									<li><i class="fa fa-exclamation-circle fa-lg"></i>
										<a href="https://twitter.com/HyperdriveD" target="_blank"><?php _e( 'Follow us on Twitter.', 'rua-lite' ); ?></a>
									</li>
								</ul>
							</p>
						</div>
					</div>
					</div>

			</div><!-- end container -->
		</div><!-- end wrap -->
		<?php
	}
}

if ( !function_exists( 'rua_email_validation' ) )
{
	function rua_email_validation() {
		$email = is_email( $_POST['ruaemail'] );
		$email = sanitize_email( $email );
		$site_id = get_current_blog_id();
		global $wpdb;

		//$wpdb->get_results( "SELECT subscriber_email FROM wp_rua_blog_subscriber WHERE subscriber_email = '$email' AND site_id = '$site_id'" );
		$wpdb->get_results( $wpdb->prepare( "SELECT subscriber_email FROM wp_rua_blog_subscriber WHERE subscriber_email = %s AND site_id = %d", $email, $site_id ) );
		$is_in_database = $wpdb->num_rows;
		if ($is_in_database >= 1)
		{
			 echo '1'; // Record match in DB. Email already being used
		}
		else
		{
				 echo '0'; // Email is available
		}
		die();
	}
}
add_action( 'wp_ajax_rua_email_validation', 'rua_email_validation' );
add_action( 'wp_ajax_nopriv_rua_email_validation', 'rua_email_validation' );

/* fix deprecated functon in PHP 7.2 */
$versionphp = phpversion();
if ( $versionphp < 7.2 )
{
	add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
} 
else {
	add_filter( 'wp_mail_content_type', function() {
		return 'text/html';
	});
}
if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

if ( !function_exists( 'rua_save_subscriber' ) )
{
	function rua_save_subscriber(){
		 // Verify nonce
		if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( $_POST['nonce'], 'rua_blog_subscriber' ) )
		{
   		print 'Sorry, your nonce did not verify. Please try again.';
   		exit;
		}
		else
		{
			$response = $_POST['recaptcha'];

			if (empty($_POST['recaptcha'])) {
				write_log('Please set recaptcha variable');
			}
			// validate recaptcha
			$post = http_build_query(
				array (
					'response' => $response,
					'secret' => '6Lf-o2oUAAAAAAZ3Q1r3E4m9mqERZSTIxP2rFnHq',
					'remoteip' => $_SERVER['REMOTE_ADDR']
				)
			);
			$opts = array('http' => 
				array (
					'method' => 'POST',
					'header' => 'application/x-www-form-urlencoded',
					'content' => $post
				)
			);
			$context = stream_context_create($opts);
			$serverResponse = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
			if (!$serverResponse) {
				write_log('Failed to validate Recaptcha');
			}
			$result = json_decode($serverResponse);
			if (!$result->success) {
				write_log('Invalid Recaptcha');
			} 
			if($result->success)
			{
				$name     = sanitize_text_field( $_POST['ruaname'] );
				$email    = sanitize_email( $_POST['ruaemail'] );
				$status   = sanitize_text_field( $_POST['ruasubstatus'] );
				$siteid   = sanitize_text_field( $_POST['ruasiteid'] );
				$subdate  = sanitize_text_field( $_POST['ruasubdate'] );
				$key      = rand ( 100000, 999999 );
			
				global $wpdb;

				$wpdb->insert(
					'wp_rua_blog_subscriber',
					array(
						'subscriber_name'     => $name,
						'subscriber_email'    => $email,
						'subscriber_status'   => $status,
						'site_id'             => $siteid,
						'subscribe_date'      => $subdate,
						'activation_key'      => $key,
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			 	// send emails
				$site_name = get_option( 'rua_site_name' );
				$site_url = get_option( 'rua_site_url' );
				$site_email_logo = get_option( 'rua_email_logo' );
				$site_address = get_option( 'rua_company_address' ) .' '. get_option( 'rua_company_city' ) .' '.get_option( 'rua_company_zip' ) .' '. get_option( 'rua_company_state' ) .' '. get_option( 'rua_company_phone_number' );
				$from_email_address = get_option( 'rua_from_email_address' );
				$email_subject = get_option( 'rua_email_subject' );
				$name    = $name;
				$email   = $email;
				$to      = $email;
				$subject = 'Confirm your subscription for '.$site_name.'';

				$message = '
									<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
									<html xmlns="http://www.w3.org/1999/xhtml">
									<head>
										<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
										<meta name="viewport" content="width=device-width"/>
									</head>
									<body style="width: 100% !important;  min-width: 100%;  -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; margin: 0;  padding: 0; color: #222222;
			font-family: \'Helvetica\', \'Arial\', sans-serif;  font-weight: normal;  text-align: left; line-height: 1.3;">
										<table style="border-spacing: 0; margin:0px auto; border-collapse: collapse; padding: 0;  vertical-align: top;  text-align: left;">
											<tr>
												<td style="word-break: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto;  border-collapse: collapse !important; padding: 0; vertical-align: top;  text-align: left;" align="center" valign="top">
													<center style="width: 100%;">
														<table style="width: 100%;  margin: 0 auto; text-align: inherit; font-weight:500; max-width: 580px !important;">
															<tr>
																<td style="word-break: break-word;  -webkit-hyphens: auto;  -moz-hyphens: auto; hyphens: auto;  border-collapse: collapse !important;   vertical-align: top;  border-bottom: 1px solid #e5e5e5;   padding: 10px;  text-align: center;" >
																	<img src="'.$site_email_logo.'" style="outline: none; text-decoration: none;  -ms-interpolation-mode: bicubic;  width: auto !important;
																								 height: auto !important; max-width: 100%;  clear: both;" />
																</td>
															</tr>
															<tr>
																<td style="word-break: break-word;  -webkit-hyphens: auto;  -moz-hyphens: auto; hyphens: auto;  border-collapse: collapse !important; padding: 0; vertical-align: top; text-align: left; border-bottom: 1px solid #e5e5e5;  padding: 10px;  text-align: left; padding: 45px; color: #676767; font-size: 14px; line-height: 20px;">
																	<p  style="color: #676767;  font-size: 16px;  line-height: 25px; padding-bottom: 15px; margin:0px;">Hi '.$name.',
																	</p>
																	<p  style="color: #676767;  font-size: 16px;  line-height: 25px;  padding-bottom: 30px; margin:0px;">
																		You recently followed <a href="'.$site_url.'">'. $site_name .'</a> blog posts. This means you will receive each new post by email.
																		</p>
																	<p style="color: #676767;    font-size: 16px; margin:0px;    line-height: 25px;">
																		To activate your subscription, click confirm below. If you believe you received this email in error, ignore this message.
																	</p>
																	<p style="padding: 15px 0px; margin:0px;">
																	<a href="'.$site_url.'/subscribe/?key='.$key.'&email='.$email.'" style="background: #0061aa;  padding: 10px 0px;  text-transform: uppercase;  display: inline-block;  color: #fff;  font-size: 16px;  text-decoration: none;  width: 200px; text-align: center;">
																	confirm follow</a>
																	</p>
																</td>
															</tr>
															<tr>
																<td  style="word-break: break-word; -webkit-hyphens: auto;  -moz-hyphens: auto; hyphens: auto;text-align:center; padding:10px 0px;  border-collapse: collapse !important; padding: 0; vertical-align: top;  text-align: left;">
																	<p style="color: #676767; margin:0px; font-size: 12px;  line-height: 25px; text-align:center; ">
																		&#169; '. $site_name .'
																	</p>
																	<p style="color: #676767; margin:0px; font-size: 12px;  line-height: 25px; text-align:center; ">
																		'.$site_address.'
																	</p>
																	<p style="color: #676767; margin:0px; font-size: 12px;  line-height: 25px; text-align:center; ">
																		<a href="'.$site_url.'">'.$site_name.'</a>
																	</p>
																</td>
															</tr>
														</table>
													</center>
												</td>
											</tr>
										</table>
									</body>
								</html>
							 '
			 ;

			 $headers = 'From: '.$site_name.' <'.$from_email_address.'>';

			 wp_mail( $to, $subject, $message, $headers );

			 die();
			}
		}
	}
}

add_action( 'wp_ajax_rua_save_subscriber', 'rua_save_subscriber' );
add_action( 'wp_ajax_nopriv_rua_save_subscriber', 'rua_save_subscriber' );

if ( !function_exists( 'rua_show_subscriber_form' ) )
{
	function rua_show_subscriber_form( $atts, $content = null ) {
		$site_id = get_current_blog_id();
		$subscribe_date = date('Y-m-d');
		$rua_form_header = get_option('rua_form_header');
		$rua_custom_message = get_option('rua_custom_message');
		$rua_button_text = get_option('rua_button_text');

		$a = shortcode_atts( array(
				"site_id" 				=> $site_id,
				"subscribe_date" 		=> $subscribe_date,
				"rua_form_header" 		=> $rua_form_header,
				"rua_custom_message" 	=> $rua_custom_message,
				"rua_button_text" 		=> $rua_button_text,
			), $atts );
			$publickey = "6Lf-o2oUAAAAAN5Xfy5xmJ-WheJGR-HhlkxEIfK0";
		$content .= '<script src="https://www.google.com/recaptcha/api.js"></script><div id="formwrapper">';

		$content  .= '<h3 class="subscribe_label">'. esc_attr( $a['rua_form_header'] ) .'</h3>';

		$content .= '<div id="subscribe_form">
					<p>Enter your email address to subscribe to this blog and receive notifications of new posts by email.</p>
					<div class="iconspinner"><i class="fa fa-spinner fa-3x fa-spin-custom"></i></div>
					<form id="subscribeform" novalidate="novalidate">
						<div>
							<label for="ruaname">NAME <span>*</span></label>
								<div>
									<input type="text" id="ruaname" name="ruaname" />
								</div>
						</div>
						<div>
							<label for="ruaemail">EMAIL <span>*</span></label>
								<div>
									<input type="email" id="ruaemail" name="ruaemail" />
								</div>
						</div>
						<div>
							<div class="g-recaptcha" data-sitekey="6Lf-o2oUAAAAAN5Xfy5xmJ-WheJGR-HhlkxEIfK0"></div>
						</div>
						<div>
						   '. wp_nonce_field( 'rua_blog_subscriber', 'rua_blog_subscriber_nonce' ) .'
							<input type="hidden" id="ruasubstatus" name="ruasubstatus" value="unverified" />			
							<input type="hidden" id="ruasiteid" name="ruasiteid" value="' . esc_attr( $a['site_id'] ) . '" />
							<input type="hidden" id="ruasubdate" name="ruasubdate" value="' . esc_attr( $a['subscribe_date'] ) . '" />
							<input type="submit" class="subscribe_btn btn" id="ruaSubmit" name="ruaSubmit" value="'. esc_attr( $a['rua_button_text'] ) .'" /><br>
							<span id="ruaValidationEmailMesage"></span>
						</div>
					</form>
				</div>';
				?>
				<style>
				#successMessage {
					background: #FBFBFB url("<?php echo RUA_PLUGIN_URL."/images/email.png"; ?>") no-repeat 50% bottom;
				}
				</style>
				<?php
				$content .=  '<div id="successMessage" name="successMessage">
						<p class="success_description">'. esc_attr( $a['rua_custom_message'] ) .'
						</p>
						<i class="fa fa-check-circle-o fa-3x"></i>
				<p class="subscribe-text-green">Success!</p>
						</div>';

				$content .= '</div>';

		return $content;
	}
}
add_shortcode( 'ruasubscriber', 'rua_show_subscriber_form' );

if ( !function_exists( 'rua_email_members' ) )
{
	function rua_email_members( $post_id )  {
			$site_id = get_current_blog_id();
			$site_name = get_option( 'rua_site_name' );
			$site_url = get_option( 'rua_site_url' );
			$site_email_logo = get_option( 'rua_email_logo' );
			$site_address = get_option( 'rua_company_address' ) .' '. get_option( 'rua_company_city' ) .' '. get_option( 'rua_company_zip' ) .' '. get_option( 'rua_company_state' ) .' '. get_option( 'rua_company_phone_number' );
			$from_email_address = get_option( 'rua_from_email_address' );
			$email_subject = get_option( 'rua_email_subject' );
			$post_url = get_post_permalink( $post_id );
			global $wpdb;

			//$subscribers_name_array = $wpdb->get_results( "SELECT subscriber_name, subscriber_email FROM wp_rua_blog_subscriber WHERE subscriber_status = 'subscribed' AND site_id = '$site_id'", ARRAY_A );
			$subscribers_name_array = $wpdb->get_results( $wpdb->prepare( "SELECT subscriber_name, subscriber_email FROM wp_rua_blog_subscriber WHERE subscriber_status = 'subscribed' AND site_id = %d", $site_id, ARRAY_A ) );
			$subject = 'New Blog Post on '.$site_name.'';

			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' );

			foreach( $subscribers_name_array as $subscriber )
			{

				$message = '
								<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
								<html xmlns="http://www.w3.org/1999/xhtml">
								<head>
										<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
										<meta name="viewport" content="width=device-width"/>
								</head>
								<body style="width: 100% !important;  min-width: 100%;  -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; margin: 0;  padding: 0; color: #222222;
		 font-family: \'Helvetica\', \'Arial\', sans-serif; font-weight: normal;  text-align: left; line-height: 1.3;">
									<table style="border-spacing: 0; margin:0px auto; border-collapse: collapse; padding: 0;  vertical-align: top;  text-align: left;">
										<tr>
											<td style="word-break: break-word;  -webkit-hyphens: auto;  -moz-hyphens: auto; hyphens: auto;  border-collapse: collapse !important; padding: 0; vertical-align: top;  text-align: left;" align="center" valign="top">
												<center style="width: 100%;">
														<table cellpadding="0" cellspacing="0" style="width: 100%;  margin: 0 auto; text-align: inherit; font-weight:500; max-width: 580px !important;">
															<tr>
																	<td style="word-break: break-word;  -webkit-hyphens: auto;  -moz-hyphens: auto; hyphens: auto;  border-collapse: collapse !important;   vertical-align: top;  border-bottom: 1px solid #e5e5e5;   padding: 10px;  text-align: center;" >
																			<img src="'.$site_email_logo.'" style="outline: none; text-decoration: none;  -ms-interpolation-mode: bicubic;  width: auto !important;height: auto !important; max-width: 100%;  clear: both;" />
																	</td>
															</tr>
															<tr>
																<td style="word-break: break-word;  -webkit-hyphens: auto;  -moz-hyphens: auto; hyphens: auto;  border-collapse: collapse !important; padding: 0; vertical-align: top; text-align: left; border-bottom: 1px solid #e5e5e5;  padding: 10px;  text-align: center; padding: 45px; color: #676767; font-size: 14px; line-height: 20px;">
																	<p  style="color: #676767;  font-size: 16px;  line-height: 25px; padding-bottom: 15px; margin:0px; font-weight:bold;">
																		'.$subscriber['subscriber_name'].', there is a new blog post on
																		 <a href="'.$site_url.'" style="color: #0d4fa0;text-decoration: none;">
																		 '.$site_name.'
																		 </a>
																		 </p>
																 <p style="margin:0px;">
																		<img src="' . $image[0] . '" style="outline: none;  text-decoration: none;  -ms-interpolation-mode: bicubic;  width: auto !important; height: auto !important; max-width: 100%; clear: both;" />
																 </p>
																 <p style="color: #676767;font-size: 16px; margin:0px;line-height: 25px; font-weight:bold; padding:10px 0px; color:#181818;">
																		Welcome to the '.$site_name.' Blog!
																 </p>
																 <br>
																 <p style="padding: 15px 0px; margin:0px; text-align:center;">
																		<a href="'.$post_url.'" class="confirm_follow" style="background: #0061aa;padding: 10px 0px;display: inline-block;color: #fff;font-size: 16px;text-decoration: none;width: 120px;  text-align: center;">
																			VIEW POST
																		</a>
																 </p>
																</td>
															</tr>
															<tr>
																	<td  style="word-break: break-word; -webkit-hyphens: auto;  -moz-hyphens: auto; hyphens: auto;text-align:center; padding:10px 0px;  border-collapse: collapse !important; padding: 0; vertical-align: top;  text-align: left;">
																		<p style="color: #676767; margin:0px; font-size: 12px;line-height: 25px; text-align:center; ">
																			&#169; '. $site_name .'
																		</p>
																		<p style="color: #676767; margin:0px; font-size: 12px;line-height: 25px; text-align:center; ">
																			'.$site_address.'
																		</p>
																		<p style="color: #676767; margin:0px; font-size: 12px;line-height: 25px; text-align:center; ">
																			<a href="'.$site_url.'">'.$site_name.'</a>
																		</p>
																		<p style="color: #676767; margin:0px; font-size: 12px;line-height: 25px; text-align:center; ">
																			You are receiving this email because you opted in on our website. If you no longer want to receive these updates, you may
																			<a href="'.$site_url.'/unsubscribe/?email='.$subscriber['subscriber_email'].'">unsubscribe</a>.
																		</p>
																	</td>
															</tr>
														</table>
												</center>
											</td>
										</tr>
									</table>
								</body>
							</html>
						';
						$headers = 'From: '.$site_name.' <'.$from_email_address.'>';
						wp_mail( $subscriber['subscriber_email'], $subject, $message, $headers );
			}
	}
}
add_action( 'publish_post', 'rua_email_members' );

function rua_blog_subscriber_deactivation()
{
  flush_rewrite_rules();
}

register_deactivation_hook( RUA_PLUGIN_FILE, 'rua_blog_subscriber_deactivation' );