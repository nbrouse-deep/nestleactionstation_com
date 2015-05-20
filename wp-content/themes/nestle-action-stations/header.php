<!DOCTYPE html>
<!--[if lt IE 9]>         	<html class="no-js lt-ie9 lt-ie10"> <![endif]-->
<!--[if IE 9]>         		<html class="no-js lt-ie10"> <![endif]-->
<!--[if gt IE 9]>			<!--> <html class="no-js reveal"> <!--<![endif]-->
<head profile="http://gmpg.org/xfn/11">
	<style id="antiClickjack">body{display:none !important;}</style>
<script type="text/javascript">
   if (self === top) {
       var antiClickjack = document.getElementById("antiClickjack");
       antiClickjack.parentNode.removeChild(antiClickjack);
   } else {
       top.location = self.location;
   }
</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<!--<meta http-equiv="X-UA-Compatible" content="IE=edge"> -->
	<meta http-equiv="X-Frame-Options" content="Deny" />
	<meta http-equiv="X-FRAME-OPTIONS" content="SAMEORIGIN">
	<link rel="shortcut icon" type="image/ico" href="<?php bloginfo('template_directory') ?>/favicon.ico" />
	<link rel="apple-touch-icon" href="<?php bloginfo('template_directory') ?>/assets/images/favicon/touch-60.png">
	<link rel="apple-touch-icon" sizes="76x76" href="<?php bloginfo('template_directory') ?>/assets/images/favicon/touch-76.png">
	<link rel="apple-touch-icon" sizes="120x120" href="<?php bloginfo('template_directory') ?>/assets/images/favicon/touch-120.png">
	<link rel="apple-touch-icon" sizes="152x152" href="<?php bloginfo('template_directory') ?>/assets/images/favicon/touch-152.png">
	<?php
	if (is_user_logged_in() || is_front_page() ){
}
else {
    header( 'Location: http://stag1.nestleactionstations.com/password.php' ) ;
};
?>
<!--<?php

If ($authenication_successful) {
        $_session["authenticated"] = true;
        Session_regenerate_id();
    }

    ?> -->
    <?php
	header( 'X-Frame-Options: DENY' );
	header( 'X-Frame-Options: SAMEORIGIN' );
?>

	<title><?php wp_title('&raquo;','true','right'); ?></title>
	<script src="//use.typekit.net/bns5aup.js"></script>
<script>try{Typekit.load();}catch(e){}</script>
<script src="//use.typekit.net/neo0xlo.js"></script>
<script>try{Typekit.load();}catch(e){}</script>
	<script src="//use.typekit.net/yxz3qnx.js"></script>
	<script>try{Typekit.load();}catch(e){}</script>
	<link rel="stylesheet" href="<?php bloginfo('template_directory') ?>/style.css" />

	<script>
		
		// Place Google Analytics code here

		// Set up site configuration
		window.config = window.config || {};

		// The base URL for the WordPress theme
		window.config.baseUrl = "<?php bloginfo('url')?>";

		// Empty default Gravity Forms spinner functions
		var gformInitSpinner = function() {};
	</script>
	<script src="<?php bloginfo('template_directory') ?>/assets/jsvendor/modernizr.min.js"></script>
	<script src="<?php bloginfo('template_directory') ?>/assets/jsvendor/ie8.js"></script>
	<?php wp_head();?>

</head>
<body <?php body_class(); ?>  id="<?php echo get_template_name(); ?>">

	<?php
		$args = array(
		'post_type' => 'station',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
		'caller_get_posts' => 1 );

		$stations_query = new WP_Query($args);

		switch ($wp_query->query_vars['custom_post_type_sub_page']) {
			case 'recipes':
				$page = "recipes";
			  	break;
			default:
				$page = "station";
			  	break;
		}

		if ( $post->ID == 107 || is_front_page() ) $page = "home";
	?>

	<header>
		<?php if ( $page == "home" && !user_has_access() ): ?>
			<div class="info-banner">
				<p>Action Stations is a member's only source for insight-driven recipes and ideas. <a href="#" class="get-started-btn">Get Started</a></p>
			</div>
		<?php endif; ?>
		<div class="row">
			<a href="<?php echo home_url(); ?>" class="main-logo hide-text">Nestle Action Stations</a>
			<?php if ( $page == "station" && !is_search() ): ?>
			<nav>
				<ul>
					<li><a href="#recipes" title="Recipes">Recipes</a></li>
					<li><a href="#merchandise" title="Merchandise">Merchandise</a></li>
					<li><a href="#support" title="Support Materials">Support Materials</a></li>
				</ul>
			</nav>
			<?php endif; ?>
			<a class="contact-us" href="mailto:customerservice@us.nestleprofessional.com" title="Contact Us">Contact Us</a>
			<select class="station-select">
				<?php if ($page == "home"): ?>
					<option value="">Select a Station</option>
				<?php endif;

				$all_stations = array();
				$page_title = get_the_title();

				while ( $stations_query->have_posts() ):
					$stations_query->the_post();

					// if not on home, display current page at top of list
					if ( get_the_title() == $page_title ): ?>
						<option value="<?php the_permalink(); ?>"><?php the_title(); ?></option>
					<?php

					// store other station values in array for iteration
					else: array_push($all_stations, array(
						"permalink" => get_permalink(),
						"title" 	=> get_the_title(),
					));
					endif;

				endwhile; wp_reset_postdata();

				// iterate over all remaining stations and display
				foreach ($all_stations as $station): ?>
					<option value="<?php echo $station['permalink']; ?>"><?php echo $station['title']; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</header>