<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

	</div><!-- #main -->

	<footer id="mainFooter" role="contentinfo">
			<div class="container">
				<?php
					/* A sidebar in the footer? Yep. You can can customize
					 * your footer with three columns of widgets.
					 */
					if ( ! is_404() )
						get_sidebar( 'footer' );
				?>

				<p>
                    <?php
                        echo sprintf("&copy; 2011-%d by Facio, Inc.", date("Y"))
                    ?>
                </p>
			</div><!--//container//-->			
	</footer><!-- #mainFooter -->
</div><!-- #page -->
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-26608463-1']);
  _gaq.push(['_setDomainName', 'facio.com']);
  _gaq.push(['_setAllowLinker', true]);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<?php wp_footer(); ?>

</body>
</html>