<?php

$home = is_page('home');
$description = get_field('hero_description');
$theme_url = get_bloginfo('template_directory');
$hero_aside = get_field('hero_aside_copy', 'options');
$hero_object = get_field('hero_photo');
if ( !isset($hero_object) || !isset($hero_object['sizes']['large'])) {
	$hero_url = $theme_url."/assets/images/hero.jpg";
} else $hero_url = $hero_object['sizes']['large'];

if ($home) { // check for new recipes
	$heading = get_field('hero_heading');
} else {
	$heading = get_the_title();
	$num_themes = 0;
	$bar_themes = array();

	$theme_field = get_field('bar_recipe_themes');
	if ( have_rows('bar_recipe_themes') ) {
		while ( have_rows('bar_recipe_themes') ) {
			the_row();
			$theme = get_sub_field('theme');
			array_push($bar_themes, $theme);
			$num_themes++;
		}
	}

	$args = array(
		'post_type' => 'recipe',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'tax_query' => array(
			array(
				'field' => 'id',
				'taxonomy' => 'recipe_theme',
				'terms' => $bar_themes,
			),
		),
		'caller_get_posts' => 1 );

	$recipes = new WP_Query($args); // get recipes from a bar's recipe themes

	$new_recipes = false;

	while ( $recipes->have_posts() ):
		$recipes->the_post();
		$recipe_id = get_the_id();
		if (get_field('recipe_new', $recipe_id) == 'yes') {
			$new_recipes = true; // check if any recipes are new
		}
	endwhile;

	wp_reset_postdata();
}

$test = 1; ?>

<div class="hero main-hero" style="background-image: url(<?php echo $hero_url; ?>)">
	<figure>
		<img src="<?php echo $hero_url; ?>" alt=""/>
	</figure>
    <div class="row columns">
    	<div data-sr class="info-container">
    		<div class="divider">
	    		<div class="cf">
				    <div class="info">

				        <h1><?php echo $heading; ?></h1>

				        <?php
				        echo $description;

				        if (!$home):
				        	$custom_merch_url = get_field('merchandise_fulfillment_url', 'options');

					        if ( $new_recipes == true ):
					        	$single_name = get_field('bar_single_name'); ?>
					        	<p class="new"><span>NEW</span><?php echo $single_name; ?> recipes available now.</p>
					        <?php endif; ?>

				        <?php endif; ?>

				    </div>

				    <?php if( have_rows('heading_items') ): ?>

						<ul class="aside">
							<?php if (!empty( $hero_aside) ) ?>
								<p class="aside-copy"><?php echo $hero_aside; ?></p>

							<?php while( have_rows('heading_items') ): the_row();

								$icon = get_sub_field('item_icon');
								$number = get_sub_field('item_number');
								$text = get_sub_field('item_text');
								$link = get_sub_field('item_url'); 
								?>

								<li>
									<?php if (!empty($link)) echo '<a href="'.$link.'">'; ?>
										<i class="<?php echo $icon; ?>"></i>
									<?php if (!empty($link)) echo '</a>'; ?>

									<?php echo (!$home) ? "<strong>".$number."</strong> - " : "" ?>
									<p><?php echo $text; ?></p>
								</li>

							<?php endwhile; ?>

						</ul>

					<?php endif; ?>

				</div>
			</div>
		</div>
		<a href="<?php echo ($home) ? "#view-all" : "#recipes"; ?>" class="arrow arrow-down"><i></i></a>
    </div>
</div>