<?php
/**
 * Template Name: Business
 */

get_header(); ?>

		<div id="primary">
		
				<div id="content" role="main">
	
					<?php while ( have_posts() ) : the_post(); ?>
						
						<div id="masthead">
							<div class="container">
					
								<h1><?php echo get('header_title'); ?></h1>
								
								<h2><?php echo get('header_sub_title'); ?></h2>
								
								<?php if(has_post_thumbnail()) {				
										the_post_thumbnail('full'); 
									  }
								?>
									<div class="callout">
										<?php the_content(); ?>
									</div><!--//callout//-->
								
								<span class="clear"></span>
							</div><!--//container//-->
						</div><!--//masthead//-->
						
						
						<div class="container">
												
					   <?php
						   $information = get_order_group('content_content');
						   foreach($information as $info){
					   ?>
					   				
					   				
					   			<?php 
					   				$layout = get('content_layout',$info); 
					   				$callout = get('content_callout_position',$info);					   			
					   			?>
					   			
					   			
					   			
				   				<div class="info <?php echo $layout; ?> <?php echo $callout; ?>">
				   				
				   					<?php
				   						$contentImage = get('content_image',$info);
				   						
				   						if( !empty($contentImage) ) {
				   					?>
					   					<img src="<?php echo get('content_image',$info); ?>" alt="screenshot" class="screen" />

					   				<?php
					   					}
					   				?>
									
									<div class="entry">
										<?php echo get('content_content',$info); ?>
										
										<div class="callout">
											<?php 
												echo get('content_callout',$info); 
												$calloutImage = get('content_callout_image',$info);
												
												if( !empty($calloutImage) ){
											?>
													<img src="<?php echo $calloutImage; ?>" alt="callout" class="callout-thumb" />
											<?php
												}
											?>
											<span class="clear"></span>
										</div><!--//callout//-->
									
										<span class="clear"></span>
									</div><!--//entry//-->
									<span class="clear"></span>									
								</div><!--//info//-->
					   <?php } ?>
						
						
						
						</div><!--//container//-->
						<?php //get_template_part( 'content', 'page' ); ?>
	
					<?php endwhile; // end of the loop. ?>
	
				</div><!-- #content -->
			
		</div><!-- #primary -->

<?php get_footer(); ?>