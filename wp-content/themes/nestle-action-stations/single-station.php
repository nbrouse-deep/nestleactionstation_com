<?php get_header();

$single_name 	= 	get_field('bar_single_name');
$recipes_page 	=	false;

switch ($wp_query->query_vars['custom_post_type_sub_page']) {
case 'recipes':
	$recipes_page = true;
  	include 'partials/single-station-recipes.php';
  	break;
default:
  	include 'partials/single-station-main.php';
  	break;
}

get_footer() ?>