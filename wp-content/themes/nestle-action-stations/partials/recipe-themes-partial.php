<div class="recipes-top">

	<p>
		<?php if($recipes_page): ?>
			Select any theme to view concepts.
		<?php else: ?>
			Select a recipe theme to see <?php echo strtolower($single_name); ?> concepts.
		<?php endif; ?>
	</p>

	<div class="recipe-categories">

		<?php

		for ($x=0; $x< count($bar_themes); ++$x):

			$term_obj 	= 	get_term($bar_themes[$x], 'recipe_theme');
			$term_slug 	=	$term_obj->slug;
			$term_name	= 	$term_obj->name; ?>

			<?php if (isset($term_name) && isset($term_slug)): ?>

				<a href="<?php if(!$recipes_page) echo get_permalink( $post->ID ) . 'recipes/';  ?>#<?php echo $term_slug; ?>" class="basic-btn gray"><?php echo $term_name; ?></a>

			<?php endif; ?>

		<?php endfor; ?>

	</div>

</div>
