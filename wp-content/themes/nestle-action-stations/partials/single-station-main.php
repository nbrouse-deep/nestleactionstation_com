<?php

$theme_url = get_bloginfo('template_directory');

include 'hero-main.partial.php';

$download_kit_url = get_zip_download_link($post->post_name);

$show_recipes = get_field('bar_show_recipe');
if ($show_recipes == 'yes') {
	$recipe_bg_object = get_field('bar_recipe_heading_background');
	if ( empty($recipe_bg_object) ) $recipe_bg_url = $theme_url."/assets/images/spoons.jpg";
	else $recipe_bg_url = $recipe_bg_object['sizes']['large'];
	$recipe_heading = get_field('bar_heading_recipe');
	$recipe_description = get_field('bar_description_recipe');
	$guide_download = get_field('bar_guide_download');
	$guide_text = get_field('bar_guide_download_text');
	if ( empty($guide_text) ) $guide_text = "Download Station Set Up Guides";
	$order_prep_download = get_field('bar_order_prep_download');
	$order_prep_text = get_field('bar_order_prep_download_text');
	if ( empty($order_prep_text) ) $order_prep_text = "Download a Customizable Order and Prep Guide";
}

$show_calendar = get_field('bar_show_calendar');
if ($show_calendar == 'yes') {
	$calendar_bg_object = get_field('bar_calendar_heading_background');
	if ( empty($calendar_bg_object) ) $calendar_bg_url = $theme_url."/assets/images/spoons.jpg";
	else $calendar_bg_url = $calendar_bg_object['sizes']['large'];
	$calendar_heading = get_field('bar_heading_calendar');
	$calendar_description = get_field('bar_description_calendar');
	$calendar_download_url = get_field('bar_download_calendar');
}

$show_merchandising = get_field('bar_show_merchandising');
if ($show_merchandising == 'yes') {
	$merchandising_bg_object = get_field('bar_merchandising_heading_background');
	if ( empty($merchandising_bg_object) ) $merchandising_bg_url = $theme_url."/assets/images/spoons.jpg";
	else $merchandising_bg_url = $merchandising_bg_object['sizes']['large'];
	$merchandising_heading = get_field('bar_heading_merchandising');
	$merchandising_description = get_field('bar_description_merchandising');
	$merchandising_url = get_field('bar_see_all_merchandise');
	if ( empty($merchandising_url) ) $merchandising_url = get_field('merchandise_fulfillment_url', 'options');
	if ( empty($merchandising_url) ) $merchandising_url = "http://www.nestle.com/";
}

$show_sales = get_field('bar_show_sales');
if ($show_sales == 'yes') {
	$sales_bg_object = get_field('bar_sales_heading_background');
	if ( empty($sales_bg_object) ) $sales_bg_url = $theme_url."/assets/images/spoons.jpg";
	else $sales_bg_url = $sales_bg_object['sizes']['large'];
	$sales_heading = get_field('bar_heading_sales');
	$sales_description = get_field('bar_description_sales');
}

?>

<?php if( $show_recipes == 'yes' ): ?>
<div class="station-section-outer-wrap" id="recipes" style="background-image: url(<?php echo $recipe_bg_url; ?>)">
	<div class="station-section-inner-wrap">
		<div class="row station-section recipes">
			<div class="section-content">
				<i class="icon"></i>
				<h2><?php echo $recipe_heading; ?></h2>
				<?php echo $recipe_description; ?>
				<?php if ( !empty($guide_download) ): ?>
					<a href="<?php echo $guide_download; ?>"><?php echo $guide_text; ?></a>
				<?php endif; ?>
				<?php if ( !empty($order_prep_download) ): ?>
					<a href="<?php echo $order_prep_download; ?>"><?php echo $order_prep_text; ?></a>
				<?php endif; ?>
			</div>
			<div class="section-button">
				<a class="button fill" href="<?php echo get_permalink( $post->ID ); ?>recipes" alt="">See All Concepts</a>
			</div>
		</div>
	</div>
</div>

<div class="row columns station-themes">
	<?php include 'recipe-themes-partial.php'; ?>
</div>
<?php endif; ?>

<?php if( $show_calendar == 'yes' ): ?>
<div class="station-section-outer-wrap" id="calendar" style="background-image: url(<?php echo $calendar_bg_url; ?>)">
	<div class="station-section-inner-wrap">
		<div class="row station-section calendar">
			<div class="section-content">
				<i class="icon"></i>
				<h2><?php echo $calendar_heading; ?></h2>
				<?php echo $calendar_description; ?>
			</div>
			<div class="section-button">
				<a class="button fill" href="<?php echo $calendar_download_url; ?>" alt="">Download Calendar</a>
			</div>
		</div>
	</div>
</div>

<div data-sr class="row calendar-table">
	<?php include 'calendar-partial.php'; ?>
</div>
<?php endif; ?>

<?php if ( $show_merchandising == 'yes' && have_rows('bar_merchandise_assets') ): ?>
<div class="station-section-outer-wrap" id="merchandise" style="background-image: url(<?php echo $merchandising_bg_url; ?>)">
	<div class="station-section-inner-wrap">
		<div class="row station-section merchandising">
			<div class="section-content">
				<i class="icon"></i>
				<h2><?php echo $merchandising_heading; ?></h2>
				<?php echo $merchandising_description; ?>
			</div>
			<div class="section-button">
				<a class="button fill" href="<?php echo $merchandising_url; ?>" target="_blank" alt="see all merchandise">See All Merchandise</a>
			</div>
		</div>
	</div>
</div>

<div class="station-col-wrap">
	<?php while ( have_rows('bar_merchandise_assets') ):
		the_row();
		$merch_label = get_sub_field('label');
		$merch_description = get_sub_field('description');
		$merch_image_object = get_sub_field('media_preview');
		if (empty($merch_image_object)) break; // don't display merch if no image
		$merch_image_url = $merch_image_object['url'];
		$merch_customize_url = get_sub_field('customize'); ?>
		<div data-sr class="station-col">
			<img src="<?php echo $merch_image_url; ?>" />
			<h3><?php echo $merch_label; ?></h3>
			<?php echo $merch_description; ?>
			<a href="<?php echo $merch_customize_url; ?>" target="_blank" alt="customize">Customize</a>
		</div>
	<?php endwhile; ?>
</div>

<?php

$merch_phone = get_field('merchandise_fulfillment_phone', 'options');
$merch_email = get_field('merchandise_fulfillment_email', 'options');
if ( !isset($merch_phone) ) $merch_phone = "312.235.5725";
if ( !isset($merch_email) ) $merch_email = "nestle_help@brandmuscle.com";

?>

<div class="merch-info row">
	<h4>Merchandising is fulfilled by Brand Muscle. Need access?</h4>
	<p>Call <?php echo $merch_phone; ?> or email <a href="mailto:<?php echo $merch_email; ?>"><?php echo $merch_email; ?></a></p>
</div>

<?php endif; ?>

<?php if ( $show_merchandising == 'yes' && have_rows('bar_sales_assets') ): ?>
<div class="station-section-outer-wrap" id="support" style="background-image: url(<?php echo $sales_bg_url; ?>)">
	<div class="station-section-inner-wrap">
		<div class="row station-section sales-materials">
			<div class="section-content">
				<i class="icon"></i>
				<h2><?php echo $sales_heading; ?></h2>
				<?php echo $sales_description; ?>
			</div>
			<div class="section-button">
				<a class="button fill" href="<?php echo get_zip_download_link($post->post_name, true); ?>">Download Materials</a>
			</div>
		</div>
	</div>
</div>

<div class="station-col-wrap">
	<?php while ( have_rows('bar_sales_assets') ):
		the_row();
		$sales_title = get_sub_field('title');
		$sales_description = get_sub_field('description');
		$sales_image_object = get_sub_field('media_preview');
		$sales_image_url = $sales_image_object['url'];
		$sales_file_url = get_sub_field('file'); ?>
		<div data-sr class="station-col">
			<img src="<?php echo $sales_image_url; ?>" />
			<h3><?php echo $sales_title; ?></h3>
			<?php echo $sales_description; ?>
			<?php if ( !empty($sales_file_url) ): ?>
				<a href="<?php echo $sales_file_url; ?>" alt="customize" target="_blank">Download</a>
			<?php endif; ?>
		</div>
	<?php endwhile; ?>
</div>

<?php endif; ?>

<?php if ( !isset($single_name) ) $single_name = get_field('bar_single_name'); ?>

<div class="download-wrapper">
	<div class="row columns download">
		<p>Do you need everything? Download the <?php echo $single_name; ?> Action Station Kit. <a class="button fill" href="<?php echo $download_kit_url; ?>">Download Kit</a></p>
	</div>
</div>