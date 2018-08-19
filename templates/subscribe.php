<?php
/*
Template Name: Subscribe Template
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

      $key = intval( $_REQUEST['key'] );
		if ( strlen( $key ) != 6)
		{
			echo 'Not a valid key';
		}
      $email = is_email( $_REQUEST['email'] );
		$email = sanitize_email( $email );
	
		global $wpdb;
		$wpdb->get_results( $wpdb->prepare( "SELECT * FROM wp_rua_blog_subscriber WHERE activation_key = %d AND subscriber_email = %s", $id, $email ) );
      //$wpdb->get_results( "SELECT * FROM wp_rua_blog_subscriber WHERE activation_key = '$key' AND subscriber_email = '$email'" );
      $is_in_database = $wpdb->num_rows;
			
      if ($is_in_database >= 1)
      {
        	global $wpdb;
					// Activate. Change status to subscribed
					$site_name = get_bloginfo('name');
				  	$rua_blog_url = get_option('rua_blog_url');
					?>
					<style>
					#subscribe-message {
						text-align: center;
						padding-top: 50px;
						padding-bottom: 50px;
					}
					</style>
					<div class="row">
						<div id="subscribe-message" class="col-1">
							<img id="success_icon" src="<?php echo plugin_dir_url(__FILE__) . 'images/success.png'; ?>" /><br>
         			<span class="text-green">Congratulations</span>
				 			<br/>
				 			<p style="font-weight: bold;padding-top: 35px;">Your subscription has been activated!</h5>
							<br/>
				 			<p>You are now subscribed to <?php echo esc_attr( $site_name ); ?> blog and will receive<br> an email notification
					 			when a new post is made.</p>
								<br>
					 		<p>
								<?php 
								if ($rua_blog_url != '')
								{
									?>
									<a class="btn btn-primary" href="<?php echo esc_url( $rua_blog_url ); ?>">VIEW POSTS</a>
									<?php
								}
								else
								{
									?>
								<a class="btn btn-primary" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">VIEW POSTS</a>
								<?php
								}
								?>
				 			</p>
					<?php
				 	// change subscriber status in DB from unverified to subscribed
					 //$wpdb->get_results( "UPDATE wp_rua_blog_subscriber SET subscriber_status = 'subscribed' WHERE subscriber_email = '$email'" );
					 $wpdb->get_results( $wpdb->prepare( "UPDATE wp_rua_blog_subscriber SET subscriber_status = 'subscribed' WHERE subscriber_email = %s", $email ) );
      }
      else
      {
				 // Verification Failed. Keep status to unverified
				 ?>
				 <style>
				 #veri-fail-message p {
					text-align: center;
					padding-top: 50px;
					padding-bottom: 50px;
				 }
				 .fa-frown-o {
				 	color: #999;
				 }
					 .frown-icon {
						 text-align: center;
					 }
				 </style>
				 <div class="row">
					 <div id="unsubscribe-message" class="col-1">
						 <img id="success_icon" src="<?php echo plugin_dir_url(__FILE__) . 'images/frown.png'; ?>" /><br>
						 </span>
							 <br/>
        				<p>Your verification has failed. Please use the contact form to contact the web administrator.
						<br>
						<?php 
 							$site_contact_form = get_option( 'rua_site_contact_form' );
						?>
						<p>
							<a class="btn btn-primary" href="<?php echo esc_url( $site_contact_form ); ?>">CONTACT FORM</a>
						</p>
				<?php
      }
			?>
				</div><!-- end col-1 -->
			</div><!-- end row -->
    </main><!-- .site-main -->
  </div><!-- .content-area -->

<?php
get_footer();