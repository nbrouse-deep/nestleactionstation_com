<?php get_header() ?>

	<div class="row columns search-listing">

		<h2>Search Results for: <?php echo get_search_query(); ?></h2>

		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				$post_type = get_post_type($post);
				if ( $post_type == 'station' || $post_type == 'recipe' )
					include 'partials/search-results.php';
				else {
					include 'partials/search-none.php';
					// make sure not display page more than once if multiple invalid results
					break;
				}
			endwhile;
		else :
			include 'partials/search-none.php';
		endif;
		?>

	</div>

<?php get_footer() ?>