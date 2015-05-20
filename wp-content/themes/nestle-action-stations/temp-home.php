<?php
/**
*
*
*
	Template Name: Home
*
*
*
*
*
*/

get_header();

include 'partials/hero-main.partial.php';

$args = array(
	'post_type' => 'station',
	'post_status' => 'publish',
	'posts_per_page' => -1,
	'orderby' => 'title',
	'order' => 'ASC',
	'caller_get_posts' => 1 );

$args_two = array(
	'post_type' => 'recipe',
	'post_status' => 'publish',
	'posts_per_page' => -1,
	'tax_query' => array(
		array(
			'field' => 'id',
			'taxonomy' => 'recipe_theme',
			'terms' => '',
		),
	),
	'caller_get_posts' => 1 );

$query = new WP_Query($args); // get all stations

$theme_url = get_bloginfo('template_directory');

if ( $query->have_posts() ): ?>
	<div class="all-stations" id="view-all">
		<?php
		$all_themes = array();
		while ( $query->have_posts() ):
			$query->the_post();
			$post_id = get_the_ID();
			$title = get_the_title();
			array_push($all_themes, $title);
			$link = get_permalink();
			$image_object = get_field('hero_photo', $post_id);
			if ( !isset($image_object) || !isset($image_object['sizes']['small_circle']))
				$image_url = $theme_url."/assets/images/hero.jpg";
			else $image_url = $image_object['sizes']['small_circle'];
			// $description = get_field('bar_description_recipe', $post_id);
			$description = get_field('hero_description', $post_id);
			$recipe_count = get_field('bar_recipe_count', $post_id);
			$guess_recipe_count = 0;
			$bar_themes = array(); // get all themes for query -> determine if new recipes

			if ( have_rows('bar_recipe_themes', $post_id) ) {
				while ( have_rows('bar_recipe_themes', $post_id) ) {
					the_row();
					$theme = get_sub_field('theme');
					array_push($bar_themes, $theme);
					$term = get_term($theme, 'recipe_theme');
					$guess_recipe_count += $term->count;
				}
			}

			$args_two['tax_query'][0]['terms'] = $bar_themes;

			$bar_recipes = new WP_Query($args_two); // get all recipes of bar

			$new_recipes = false;

			while ( $bar_recipes->have_posts() ) { // check if any are new
				$bar_recipes->the_post();
				$recipe_id = get_the_id();
				if (get_field('recipe_new', $recipe_id) == 'yes') {
					$new_recipes = true;
				}
			}

			wp_reset_postdata();

			?>
			<section data-sr class="station-wrap">
				<article class="row station" style="background-image: url(<?php echo $image_url; ?>)">

					<div class="home-station-col image">
						<figure>
							<img src="<?php echo $image_url; ?>" alt="" />
							<?php if ($new_recipes): ?>
								<figcaption>NEW</figcaption>
							<?php endif; ?>
						</figure>
					</div>

					<div class="info-wrap">
						<div class="home-station-col station-info">
							<h3><a href="<?php echo $link; ?>"><?php echo $title; ?></a></h3>
							<p><?php echo $description; ?></p>
						</div>

						<div class="home-station-col recipe-info">
							<p class="recipes">
								<?php if (empty($recipe_count)) $recipe_count = $guess_recipe_count; ?>
								<strong><?php echo $recipe_count; ?></strong>
								<span><?php echo ($recipe_count == 1) ? "recipe" : "recipes"; ?></span>
							</p>
						</div>

						<div class="home-station-col recipe-button">
							<a class="button fill" href="<?php echo $link; ?>" alt="">Get Started</a>
						</div>
					</div>
				</article>
			</section>

		<?php endwhile; wp_reset_postdata(); ?>

	</div>
<?php
endif; ?>

<?php get_footer() ?>