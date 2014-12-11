<?php
/**
 * Template Name: Contact
 */

get_header(); ?>

		<div id="primary">
		
			<div class="container">
		
				<?php if(has_post_thumbnail()) {				
						the_post_thumbnail('full'); 
					  }
				?>
			</div><!--//container//-->	
				<div id="content" role="main">
	
					<?php while ( have_posts() ) : the_post(); ?>
						
						<div id="masthead">
							<div class="container">
					
								<h1 class="masthead-h1"><?php echo get('header_title'); ?></h1>
								
								<h2 class="masthead-h2"><?php echo get('header_sub_title'); ?></h2>


								<div class="clearfix">
								<?php the_content(); //contact form ?>
                                </div>

                                <p class="addy">Facio, Inc.<br/>
                                1020 Dennison Ave., Suite 302<br/>
                                Columbus, OH 43201
                                </p>

							</div><!--//container//-->
						</div><!--//masthead//-->
						
						
						<?php //get_template_part( 'content', 'page' ); ?>
	
					<?php endwhile; // end of the loop. ?>
	
				</div><!-- #content -->
			
		</div><!-- #primary -->

<script type="text/javascript">
// HTML5 placeholder plugin version 1.01
// Copyright (c) 2010-The End of Time, Mike Taylor, http://miketaylr.com
// MIT Licensed: http://www.opensource.org/licenses/mit-license.php
//
// Enables cross-browser HTML5 placeholder for inputs, by first testing
// for a native implementation before building one.
//
//
// USAGE:
//$('input[placeholder]').placeholder();

// <input type="text" placeholder="username">
(function($){
    //feature detection
    var hasPlaceholder = 'placeholder' in document.createElement('input');

    //sniffy sniff sniff -- just to give extra left padding for the older
    //graphics for type=email and type=url
    var isOldOpera = $.browser.opera && $.browser.version < 10.5;

    $.fn.placeholder = function(options) {
        //merge in passed in options, if any
        var options = $.extend({}, $.fn.placeholder.defaults, options),
        //cache the original 'left' value, for use by Opera later
            o_left = options.placeholderCSS.left;

        //first test for native placeholder support before continuing
        //feature detection inspired by ye olde jquery 1.4 hawtness, with paul irish
        return (hasPlaceholder) ? this : this.each(function() {
            //TODO: if this element already has a placeholder, exit

            //local vars
            var $this = $(this),
                inputVal = $.trim($this.val()),
                inputWidth = $this.width(),
                inputHeight = $this.height(),

            //grab the inputs id for the <label @for>, or make a new one from the Date
                inputId = (this.id) ? this.id : 'placeholder' + (+new Date()),
                placeholderText = $this.attr('placeholder'),
                placeholder = $('<label class="html5placeholder-polyfill" for='+ inputId +'>'+ placeholderText + '</label>');

            //stuff in some calculated values into the placeholderCSS object
            options.placeholderCSS['width'] = inputWidth;
            options.placeholderCSS['height'] = inputHeight;
            options.placeholderCSS['color'] = options.color;

            // adjust position of placeholder
            options.placeholderCSS.left = (isOldOpera && (this.type == 'email' || this.type == 'url')) ?
                '11%' : o_left;
            placeholder.css(options.placeholderCSS);

            //place the placeholder
            $this.wrap(options.inputWrapper);
            $this.attr('id', inputId).after(placeholder);

            //if the input isn't empty
            if (inputVal){
                placeholder.hide();
            };

            //hide placeholder on focus
            $this.focus(function(){
                if (!$.trim($this.val())){
                    placeholder.hide();
                };
            });

            //show placeholder if the input is empty
            $this.blur(function(){
                if (!$.trim($this.val())){
                    placeholder.show();
                };
            });
        });
    };

    //expose defaults
    $.fn.placeholder.defaults = {
        //you can pass in a custom wrapper
        inputWrapper: '<span class="html5placeholder-polyfill-container"></span>',

        //more or less just emulating what webkit does here
        //tweak to your hearts content
        placeholderCSS: {
            'position': 'absolute',
            'overflow-x': 'hidden',
            'display': 'block'
        }
    };
})(jQuery);


jQuery(function() {
    jQuery("input[type='text'], input[type='email'], input[type='password'], textarea").placeholder();
});
</script>

<?php get_footer(); ?>