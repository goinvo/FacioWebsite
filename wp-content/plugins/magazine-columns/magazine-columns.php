<?php
/*
Plugin Name: Magazine Columns
Plugin URI: http://bavotasan.com/downloads/magazine-columns-wordpress-plugin/
Description: Divides your post or page content into two or more columns, like a magazine article.
Author: c.bavota
Version: 1.0.4
Author URI: http://www.bavotasan.com/
*/

// Replace the_content function if a <!--column--> tag is detected
function add_columns($content) { 
	if(stristr($content, '<!--column-->') && is_singular()) {
		if(stristr($content, '<!--startcolumns-->')) {
			$topcontent = explode('<!--startcolumns-->', $content);
			if(stristr($content, '<!--stopcolumns-->')) {
				$bottomcontent = explode('<!--stopcolumns-->', $topcontent[1]);
				$content = preg_replace('/width="([0-9]*)" height="([0-9]*)"/', '',$bottomcontent[0]);
			} else {
				$content = preg_replace('/width="([0-9]*)" height="([0-9]*)"/', '',$topcontent[1]);
			}
		} else {
			$content = preg_replace('/width="([0-9]*)" height="([0-9]*)"/', '',$content);		
		}
		$content = explode('<!--column-->', $content);
		$count = count($content);
		if($count == 2) {
			$colname = "col_two";
		} elseif($count == 3) {
			$colname = "col_three";
		} elseif($count == 4) {
			$colname = "col_four";
		} else {
			$colname = "col_five";
		}		
		$x = 1;
		if(!empty($topcontent[0])) {
			$top = explode('<br />',$topcontent[0]);
			$i = count($top);
			$top[$i-1] .= '</p>'."\n";
			$top = implode("", $top);
			echo $top;
		}		
		foreach($content as $column) {
			$output = '<div class="columns" id="'.$colname.$x.'">'.$column.'</div>';
			$output = str_replace('<div class="columns" id="'.$colname.$x.'"><br />','<div class="columns" id="'.$colname.$x.'"><p>', $output);
			if($x == 5) {
				unset($content[0]);
				unset($content[1]);
				unset($content[2]);
				unset($content[3]);
				$column = implode("", $content);
				echo $output;
				break;
			} else {
			echo $output;
			}
			$x++;
		}
		if(!empty($bottomcontent[1])) { 
			$bottom = explode('<br />',$bottomcontent[1]);
			$bottom[0] = '<p style="clear: both;">' . $bottom[0];
			$bottom = implode("", $bottom);
			echo $bottom;
		}
	} else {
		return $content;
	}
}

// Adds the add_columns() function to the_content()
add_filter('the_content', 'add_columns');

// Creates the CSS for columns
function add_columns_css() {
	global $post;
	$content = $post->post_content;
	if(stristr($content, '<!--column-->') && is_singular()) {
		$content = explode('<!--column-->', $content);
		$count = count($content);	
	
		$two = 47; // width of two columns
		$three = 29.5; // width of three columns
		$four = 21; // width of four columns
		$five = 15; // width of five columns 
		echo '<!-- Magazine Columns CSS -->'."\n";
		echo "<style type='text/css'>\n";
		if($count == 2) {
		echo " #col_two1 { float: left; width: ".$two."%; }\n";
		echo " #col_two2 { float: left; width: ".$two."%; margin: 0 0 0 5%; }\n";
		}
		if($count == 3) {	
		echo " #col_three1, #col_three3 { float: left; width: ".$three."%; }\n";
		echo " #col_three2 { float: left; width: ".$three."%; margin: 0 5%; }\n";
		}
		if($count == 4) {
		echo " #col_four1, #col_four4 { float: left; width: ".$four."%; }";
		echo " #col_four2 { float: left; width: ".$four."%; margin: 0 2.5% 0 5%; }\n";
		echo " #col_four3 { float: left; width: ".$four."%; margin: 0 5% 0 2.5%; }\n";
		}
		if($count == 5) {
		echo " #col_five1, #col_five5 { float: left; width: ".$five."%; }\n";
		echo " #col_five2 { float: left; width: ".$five."%; margin: 0 2.5% 0 5%; }\n";
		echo " #col_five3 { float: left; width: ".$five."%; margin: 0 2.5% 0 5%; }\n";
		echo " #col_five4 { float: left; width: ".$five."%; margin: 0 5% 0 2.5%; }\n";
		}
		echo " .columns img { width: 98% }\n";
		echo "</style>\n";
		echo '<!-- eof Magazine Columns CSS -->'."\n";
	}
}
add_action('wp_head', 'add_columns_css');

// Creates the columns quicktag button
function columns_quicktag_button() {
  if (strpos($_SERVER['REQUEST_URI'], 'post.php') ||
      strpos($_SERVER['REQUEST_URI'], 'post-new.php') ||
      strpos($_SERVER['REQUEST_URI'], 'page.php') ||
      strpos($_SERVER['REQUEST_URI'], 'page-new.php')) {
	echo '<script type="text/javascript" src="'  . get_option('siteurl') . '/wp-content/plugins/magazine-columns/js/mc.js"></script>';
    }
}

//Add the columns_quicktag_button() function to the proper admin pages
add_filter('admin_footer', 'columns_quicktag_button');
?>