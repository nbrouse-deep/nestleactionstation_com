<?php if ( 'station' == get_post_type() ): ?>

	<?php

		$station_desc	=	get_field('hero_description');

	?>

	<article class="search-result list-block single-recipe">
		<div class="header">
			<h1>Station</h1>
		</div>
		<h3><a href="<?php the_permalink()?>"><?php the_title();?></a></h3>
		<?php echo $station_desc; ?>
		<div class="link">
			<a href="<?php the_permalink() ?>"><?php the_permalink() ?></a>
		</div>
	</article>

<?php endif; ?>

<?php if ( 'recipe' == get_post_type() ): ?>

	<?php

		$recipe_title				=	get_the_title();
		$recipe_slug 				=	$post->post_name;
		$recipe_desc				=	get_field('recipe_description');

		// Get the corresponding recipe theme for the searched recipe
		$searched_recipe_theme_obj 	=	wp_get_object_terms( array($post->ID), 'recipe_theme');
		$searched_recipe_theme		=	$searched_recipe_theme_obj[0]->slug;


		// Get stations that have recipe themes
		$args = array(
			'post_type' => 'station',
		    'meta_query' => array(
		        array(
		            'key' => 'bar_recipe_themes',
		            'value' => 0,
		            'type' => 'NUMERIC',
		            'compare' => '>'
		        )
		    )
		);

		$the_query = new WP_Query($args);

		if ( $the_query->have_posts() ) : while ($the_query->have_posts() ) : $the_query->the_post(); ?>

			<?php while( have_rows('bar_recipe_themes') ): the_row();


				// Check all recipe themes of the station
				$recipe_theme_obj	=	get_term(get_sub_field('theme'),'recipe_theme');
				$recipe_theme		=	$recipe_theme_obj->slug;

				// If the station contains the same recipe theme that belongs to the searched recipe,
				// display results and break. (only show one station that has the recipe)
				if( $searched_recipe_theme === $recipe_theme ):

					$station_with_searched_recipe	=	$post->post_name;

					$recipe_url = get_home_url() . '/station/' . $station_with_searched_recipe . '/recipes/#' . $recipe_slug;

					break;

				?>

				<?php endif; ?>

			<?php endwhile;

		endwhile; endif;

		wp_reset_query();

	?>

	<article class="search-result list-block single-recipe">
		<div class="header">
			<h1>Recipe</h1>
		</div>
		<h3><a href="<?php echo $recipe_url; ?>"><?php echo $recipe_title; ?></a></h3>
		<?php echo $recipe_desc; ?>
		<div class="link">
			<a href="<?php echo $recipe_url; ?>"><?php echo $recipe_url; ?></a>
		</div>
	</article>

<?php endif; ?>