<?php get_header() ?>

<?php if ( is_404() ): ?>
	<div class="four-oh-four">
		<h1>Whoops, this page does not exist.</h1>
		<p><a href="<?php echo site_url(); ?>">Please return to the homepage.</a></p>
	</div>
<?php endif; ?>

<?php get_footer() ?>