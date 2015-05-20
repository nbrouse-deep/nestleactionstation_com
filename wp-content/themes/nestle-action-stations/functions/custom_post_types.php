<?php
// http://codex.wordpress.org/Function_Reference/register_post_type

function register_CPTs()
{

	// Stations
	$labels = array(
		'name' => _x('Stations', 'post type general name'),
		'singular_name' => _x('Stations', 'post type singular name'),
		'add_new' => _x('Add New', 'Station'),
		'add_new_item' => __('Add New Station'),
		'edit_item' => __('Edit Station'),
		'new_item' => __('New Station'),
		'view_item' => __('View Station'),
		'search_items' => __('Search Stations'),
		'not_found' =>  __('No Stations found'),
		'not_found_in_trash' => __('No Stations found in Trash'),
		'parent_item_colon' => '',
		'menu_name' => 'Stations'
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_nav_menus' => true,
		'query_var' => true,
		'rewrite' => Array('slug'=>'station'),
		'capability_type' => 'post',
		'has_archive' => true,
		'hierarchical' => false,
		'menu_position' => 30,
		'supports' => array('title')
	);
	register_post_type('station',$args);


	// Recipes
	$labels = array(
		'name' => _x('Recipes', 'post type general name'),
		'singular_name' => _x('Recipes', 'post type singular name'),
		'add_new' => _x('Add New', 'Recipe'),
		'add_new_item' => __('Add New Recipe'),
		'edit_item' => __('Edit Recipe'),
		'new_item' => __('New Recipe'),
		'view_item' => __('View Recipe'),
		'search_items' => __('Search Recipes'),
		'not_found' =>  __('No Recipes found'),
		'not_found_in_trash' => __('No Recipes found in Trash'),
		'parent_item_colon' => '',
		'menu_name' => 'Recipes'
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_nav_menus' => true,
		'query_var' => true,
		'rewrite' => Array('slug'=>'recipe'),
		'capability_type' => 'post',
		'has_archive' => true,
		'hierarchical' => false,
		'menu_position' => 30,
		'supports' => array('title')
	);
	register_post_type('recipe',$args);

}

add_action('init', 'register_CPTs');