<?php

// HIDE ADMIN BAR ALWAYS
add_filter( 'show_admin_bar', '__return_false' );

// REMOVE DEFAULT JQUERY & LOAD GOOGLE IN FOOTER
add_action( 'wp_enqueue_scripts', 'no_wp_jquery' );
function no_wp_jquery(){
	wp_deregister_script('jquery');
	wp_register_script('jquery', ('//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'), false, '1.11.0', true);
	wp_enqueue_script('jquery');
}


// CLEAN UP WP HEAD
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'start_post_rel_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');


// ADD WORDPRESS FEATURE SUPPORT
add_theme_support( 'post-thumbnails' );
add_theme_support( 'menus' );


// ADD CUSTOM PHOTO CROPS
if ( function_exists( 'add_image_size' ) ) {
	add_image_size('small_circle', 90, 90, true);
}


// CUSTOM FILES
include_once('functions/custom_post_types.php');
include_once('functions/custom_taxonomies.php');
include_once('functions/custom_sidebars.php');

// CUSTOM FUNCTIONS
include_once('functions/wordpress/utility.php');

// Add CPT icons as well as seperators to the admin menu
include_once('functions/wordpress/admin-menu.php');

// ENVIRONMENT STUFF
include_once('functions/environment.php');

// Zip generation
include_once('functions/site/zip-generator.php');

// Custom pass code authentication
include_once('functions/site/authentication.php');

// SECURITY STUFF
define('DISALLOW_FILE_EDIT', true);

// Update WordPress upload limits - gotta love EOD
function update_wp_upload_limit( $rules ) {
$wp_upload_mod = <<<EOD
\n # BEGIN WordPress Upload
php_value memory_limit 34M
php_value post_max_size 33M
php_value upload_max_filesize 32M
php_value max_execution_time 600
# END WordPress Upload \n
EOD;
return $wp_upload_mod . $rules;
}
add_filter('mod_rewrite_rules', 'update_wp_upload_limit');

// MAKE USERS LOGIN TO SEE SITE


$loginpage = "/password.php";
        $currentpage = $_SERVER['REQUEST_URI'];
        if($loginpage==$currentpage) {
	        


}