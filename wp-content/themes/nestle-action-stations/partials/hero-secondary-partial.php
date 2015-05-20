<?php

$theme_url = get_bloginfo('template_directory');
$hero_object = get_field('recipe_subpage_heading_background');
if ( !isset($hero_object) || !isset($hero_object['sizes']['large'])) {
	$hero_url = $theme_url."/assets/images/hero.jpg";
}
else {
	$hero_url = $hero_object['sizes']['large'];
}

$bar_themes = [];

if ( have_rows('bar_recipe_themes') ) {
	while ( have_rows('bar_recipe_themes') ) {
		the_row();
		$theme = get_sub_field('theme');
		array_push($bar_themes, $theme);
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

$num_recipes = get_field('bar_recipe_count', $station_id);
if (empty($num_recipes)) $num_recipes = $recipes->found_posts;

$new_recipes = false;
while ( $recipes->have_posts() ):
	$recipes->the_post();
	$recipe_id = get_the_id();
	if (get_field('recipe_new', $recipe_id) == 'yes') {
		$new_recipes = true; // check if any recipes are new
	}
endwhile;

wp_reset_postdata();

?>

<div class="hero secondary-hero" id="secondary" style="background-image: url(<?php echo $hero_url; ?>)">
	<figure>
		<img src="<?php echo $hero_url; ?>" alt=""/>
	</figure>
    <div class="row columns">
    	<div data-sr class="info-container">
    		<a href="../" class="back-btn"><i class="icon-arrow-left"></i></a>
    		<div class="divider">
	    		<div class="cf">
				    <div class="info">
				        <h1><?php echo $heading; ?></h1>
				        <p>Explore the bounds of <?php echo strtolower($single_name); ?> with <?php echo $num_recipes; ?> on-trend <?php echo strtolower($single_name); ?> themed recipes.</p>
				        <?php if ( $new_recipes == true ):
							$single_name = get_field('bar_single_name'); ?>
							<p class="new"><span>NEW</span><?php echo $single_name; ?> recipes available now.</p>
				        <?php endif; ?>
				    </div>
				    <div class="aside">
				    	<i class="icon-pdf"></i>
				    	<p>Need all the recipes?</p>
				    	<a href="<?php echo get_zip_download_link($post->post_name); ?>">Download all <?php echo $heading; ?> recipes</a>
				    </div>
				</div>
			</div>
		</div>
    </div>
</div>