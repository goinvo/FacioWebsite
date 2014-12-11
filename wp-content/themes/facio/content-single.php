<?php
/**
 * The template for displaying content in the single.php template
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h1 class="entry-title"><?php the_title(); ?></h1>

		<?php if ( 'post' == get_post_type() ) : ?>
		<div class="entry-meta">
			<?php twentyeleven_posted_on(); ?>
		</div><!-- .entry-meta -->
		<?php endif; ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php the_content(); ?>
		<?php wp_link_pages( array( 'before' => '<div class="page-link"><span>' . __( 'Pages:', 'twentyeleven' ) . '</span>', 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->

    <div class="entry-footer">
        <div class="post-share clearfix">
            <div class="post-left">
                <p>Share: <br>

                    <a href="http://www.twitter.com/home?status=<?php the_permalink(); ?>" target="_blank"><img src="/wp-content/uploads/2012/07/twitter_32.png" alt="Post to Twitter"></a>
                    <a href="http://www.facebook.com/sharer.php?u=<?php the_permalink(); ?>" target="_blank"><img src="/wp-content/uploads/2012/07/facebook_32.png" alt="Post to Facebook"></a>
                    <a href="http://www.digg.com/submit?phase=2&amp;url=<?php the_permalink(); ?>" target="_blank"><img src="/wp-content/uploads/2012/07/digg_32.png" alt="Post to Digg"></a>
                    <a href="http://www.delicious.com/post?url=<?php the_permalink(); ?>" target="_blank"><img src="/wp-content/uploads/2012/07/delicious_32.png" alt="Post to Delicious"></a>
                    <a href="http://www.stumbleupon.com/submit?url=<?php the_permalink(); ?>" target="_blank"><img src="/wp-content/uploads/2012/07/stumbleupon_32.png" alt="Post to Stumble"></a>
                    <a href="http://www.reddit.com/submit?url=<?php the_permalink(); ?>" target="_blank"><img src="/wp-content/uploads/2012/07/reddit_32.png" alt="Post to Reddit"></a>


                </p>
            </div><!-- //post-left -->
            <div class="post-right">
                <p>Subscribe:<br>
                    <a href="http://feedburner.google.com/fb/a/mailverify?uri=Facio&amp;loc=en_US" target="_blank"><img src="/wp-content/uploads/2012/07/email_32.png" alt="Email Subscription"></a>
                    <a href="<?php bloginfo("rss2_url") ?>" target="_blank"><img src="/wp-content/uploads/2012/07/rss_32.png" alt="RSS Subscription"></a>

                </p>
            </div><!-- //post-right -->
        </div><!-- //post-share -->

        <div class="tags-list">
            <?php
            $tags_list = get_the_tag_list('', __(', ', 'twentyeleven' ) );
            if ('' != $tags_list) {
                printf("Tags: %s", $tags_list);

            }
            ?>
        </div><!-- //tags-list -->
    </div>

</article><!-- #post-<?php the_ID(); ?> -->
