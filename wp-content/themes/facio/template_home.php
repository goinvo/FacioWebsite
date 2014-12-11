<?php
/**
 * Template Name: Home
 */

get_header(); ?>

		<div id="primary">
		
			<div class="container">
		
				<!-- <?php if(has_post_thumbnail()) {				
						the_post_thumbnail('full'); 
					  }
				?> -->
				
				<div id="content" role="main">
	
					<?php while ( have_posts() ) : the_post(); ?>
					
						<h1 class="masthead-h1"><?php echo get('header_title'); ?></h1>
						
						<h2 class="masthead-h2"><?php echo get('header_sub_title'); ?></h2>

					   <?php
						   $information = get_order_group('content_content');
						   foreach($information as $info){
					   ?>
					   				
					   				
					   			<?php 
					   				$layout = get('content_layout',$info); 
					   				$callout = get('content_callout_position',$info);
								    $withHr = get('content_hr', $info);
					   				
                                    $customClass = get('content_custom_class', $info)
					   							   			
					   			?>
					   			
					   			
				   				<div class="info <?php echo $layout; ?> <?php echo $callout; ?> <?php echo $customClass; ?> clearfix">
				   				
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
										
										<?php if(strlen(get('content_callout',$info)) > 0 || strlen(get('content_post_image_callout', $info)) > 0){ ?>
											<div class="callout">
												<?php 
													echo get('content_callout',$info); 
													$calloutImage = get('content_callout_image',$info);

													if( !empty($calloutImage) ){
												?>
														<img src="<?php echo $calloutImage; ?>" alt="callout" class="callout-thumb" />
												<?php
													}

													echo get('content_post_image_callout', $info)
												?>
											</div><!--//callout//-->
											
										<?php } ?>
										
									
									</div><!--//entry//-->
									
									<!-- Doesn't insert a hr for the last item -->									
									<?php if($withHr === "true") { ?>
										<hr />
									<?php } ?>
								</div><!--//info//-->

					   <?php } ?>
						
						
						
	
						<?php //get_template_part( 'content', 'page' ); ?>
	
					<?php endwhile; // end of the loop. ?>
	
				</div><!-- #content -->
			</div><!--//container//-->
		</div><!-- #primary -->

<?php get_footer(); ?>