<?php
/*
Plugin Name: Better wpautop 
Plugin URI: http://www.simonbattersby.com/blog/plugin-to-stop-wordpress-adding-br-tags/
Description: Amend the wpautop filter to stop wordpress doing its own thing
Version: 1.0
Author: Simon Battersby
Author URI: http://www.simonbattersby.com
*/

function better_wpautop($pee){
return wpautop($pee,false);
}

remove_filter( 'the_content', 'wpautop' );
add_filter( 'the_content', 'better_wpautop' , 99);
add_filter( 'the_content', 'shortcode_unautop',100 );
?>