<?php
/*
Template Name: Unsubscribe Template
*
*/
//* This file handles single entries, but only exists for the sake of child theme forward compatibility.
get_header();
?>
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
	
					<?php if ( have_posts() ) : ?>
        
        			<?php /* Start the Loop */ ?>
        			<?php while ( have_posts() ) : the_post(); ?>
        
        				<?php
        				/** get page content **/
        				the_content();
        				?>
        
        			<?php endwhile; ?>
        
        		<?php endif; ?>
      			
						<?php
						
						  $email = is_email( $_REQUEST['email'] );
							$email = sanitize_email ( $email );
							$unsubscribe_date = date('Y-m-d');

							global $wpdb;
							//$wpdb->get_results( "UPDATE wp_rua_blog_subscriber SET subscriber_status = 'unsubscribed', unsubscribe_date = '$unsubscribe_date' WHERE subscriber_email = '$email'" );
							$wpdb->get_results( $wpdb->prepare( "UPDATE wp_rua_blog_subscriber SET subscriber_status = 'unsubscribed', unsubscribe_date = %s WHERE subscriber_email = %s", $unsubscribe_date, $email) );
							$is_in_database = $wpdb->num_rows;

							if ($is_in_database == 0)
							{
								?>
								<style>
								#unsubscribe-message p {
									padding-top: 25px;
								}
								</style>
								<div class="row">
									<div id="message-container" class="col-1">
										<div id="unsubscribe-message">
											<img id="success_icon" src="<?php echo plugin_dir_url(__FILE__) . 'images/frown.png'; ?>" /><br>
											<p>You have been successfully removed from the subscribers list.<br/>
												You will no longer receive emails from us.<br/>
												Thank you for your support.
											</p>
											<br/>
											<p>
												If you unsubscribed by accident please contact us and we will change your subscription status.
											</p>
										</div>
								<?php
							}
							else
							{
							?>
							 <div class="row">
								<div id="message-container" class="col-1">
									<div id="unsubscribe-message">
										<p>There was an error in trying to unsubscribe you. Please contact the web administrator</p>
										<br/>
										<?php 
 											$site_contact_form = get_option( 'rua_site_contact_form' );
										?>
										<p>
											<a class="btn btn-primary" href="<?php echo esc_url( $site_contact_form ); ?>">CONTACT FORM</a>
										</p>
									</div>
							<?php
							}
							?>
				</div><!-- end col-1 -->
			</div><!-- end row -->
    </main><!-- .site-main -->
  </div><!-- .content-area -->
<?php
get_footer();