<?php
// http://codex.wordpress.org/Function_Reference/register_taxonomy

function register_taxonomies(){

	// Recipe Theme
	register_taxonomy(
		'recipe_theme', // taxononmy ID. Make this unique from CPTs and Pages to avoid URL rewrite headaches.
		array(
			'recipe'
		),
		array(
			'hierarchical' => true,
			'show_ui' => true,
			'public' => true,
			'label' => __('Recipe Themes'),
			'show_in_nav_menus' => true,
			'labels' => array(
				'add_new_item' => 'Add New Theme'
			),
			'query_var' => true,
		)
	);


}

add_action('init', 'register_taxonomies');