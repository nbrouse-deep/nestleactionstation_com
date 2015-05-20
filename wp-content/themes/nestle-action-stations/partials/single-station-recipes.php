<?php

	$heading = get_the_title();
	$station_id = get_the_ID();
	$info_text = get_field('bar_info_text');

?>

<?php include 'hero-secondary-partial.php'; ?>

<div class="row columns">

	<?php include 'recipe-themes-partial.php'; ?>

	<?php if (!empty($info_text)): ?>

		<div class="alert pdf-alert"><p><?php echo $info_text; ?></p></div>

	<?php endif; ?>

	<div class="recipes-container">

		<?php

		for ($x=0; $x < count($bar_themes); ++$x) {

			$args = array(
				'post_type' => 'recipe',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'tax_query' => array(
					array(
						'field' => 'id',
						'taxonomy' => 'recipe_theme',
						'terms' => $bar_themes[$x],
					),
				),
				'caller_get_posts' => 1 );

				$recipes = new WP_Query($args); // get recipes from a bar's recipe themes

				$default_contains_url = get_field('recipe_contains_url', 'options');

				if ( $recipes->have_posts() ) : ?>

					<section class="list-block">

						<?php

							$term_obj 		= get_term($bar_themes[$x], 'recipe_theme');
							$term_name 		= $term_obj->name;
							$term_slug 		= $term_obj->slug;
							$kit_title 		= get_field( 'kit_title', 'recipe_theme_' . $bar_themes[$x] );
							if ( !isset($kit_title) )
								 $kit_title = "Prep &amp; Order Kit";
							$kit_file_obj 	= get_field( 'kit_file', 'recipe_theme_' . $bar_themes[$x] );
							if ( $kit_file_obj )
								$kit_file_url = $kit_file_obj['url'];

						?>

						<div data-sr class="header" id="<?php echo $term_slug; ?>">

							<h2><?php echo $term_name; ?></h2>

							<?php if( isset($kit_file_url) ): ?>
								<a href="<?php echo $kit_file_url; ?>" target="_blank">
									<i class="icon-document"></i>
									<h5><?php echo $kit_title; ?></h5>
									<p>Download Recipes, Prep &amp; Order Guides</p>
								</a>
							<?php endif; ?>

						</div>

						<?php while ($recipes->have_posts() ) : $recipes->the_post(); ?>

							<?php

								$post_slug			=	$post->post_name;

								$description 		=	get_field('recipe_description');
								$new				=	get_field('recipe_new');

								$pdf_obj 			= 	get_field('recipe_pdf');
								if($pdf_obj) {
									$pdf_url		=	$pdf_obj['url'];
								}

							?>

							<div data-sr class="single-recipe" id="<?php echo $post_slug; ?>">

					            <h3 class="<?php if($new === 'yes') echo 'new'; ?> contains-dl"><a href="<?php echo $pdf_url; ?>" target="_blank"><?php the_title(); ?></a></h3>

					            <?php echo $description; ?>

					            <?php if( have_rows('recipe_contains') ): ?>

									<ul class="recipe-contains">

										<li>Recipe contains:</li>

										<?php while( have_rows('recipe_contains') ): the_row();

											$label 	= get_sub_field('label');
											$link 	= get_sub_field('link');
											if ( !isset($link) ) $link = $default_contains_url;

										?>

											<li>

												<a href="<?php echo $link; ?>" target="_blank"><?php echo $label; ?></a>

											</li>

										<?php endwhile; ?>

									</ul>

								<?php endif; ?>

								<?php if( have_rows('recipe_downloads') ): ?>

									<ul class="recipe-downloads">

										<?php while( have_rows('recipe_downloads') ): the_row();

											$title 	= get_sub_field('download_title');
											$link 	= get_sub_field('download_file');

											// don't display if missing title or link
											if ( empty($title) || empty($link) ) break;

										?>

											<li>

												<a href="<?php echo $link; ?>"><?php echo $title; ?></a>

											</li>

										<?php endwhile; ?>

									</ul>

								<?php endif; ?>

							</div>

						<?php endwhile; ?>

					</section>

				<?php endif;

			}

		?>

	</div>

	<a href="#secondary" class="arrow arrow-up"><i></i></a>

</div>
