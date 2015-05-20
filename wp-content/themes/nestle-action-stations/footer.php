	<footer>
		<?php get_search_form(); ?>

		<?php

		$contact_phone = get_field('footer_phone_number', 'options');
		$contact_email = get_field('footer_email', 'options');
		$logos_object = get_field('footer_logos', 'options');
		$logos_url = $logos_object['url'];

		?>

		<div class="contact-container full">
			<a href="<?php echo home_url(); ?>" class="main-logo hide-text">Nestle Action Stations</a>
			<p class="contact">Call <?php echo $contact_phone; ?> or email <a href="mailto:<?php echo $contact_email; ?>"><?php echo $contact_email; ?></a></p>
		</div>

		<div class="logo-container full">
			<a href="https://www.nestleprofessional.com"><img src="<?php echo $logos_url; ?>" /></a>
			<p>All trademarks owned by Société des Produits Nestlé S.A., Vevey, Switzerland.</p>
		</div>
	</footer>

	<!--<?php include 'partials/auth-modal.php'; ?>-->

	<?php wp_footer(); ?>
	<script src="<?php bloginfo('template_directory') ?>/assets/js/main.js"></script>
	<?php if(ENVIRONMENT == 'staging' || ENVIRONMENT == 'testing'){ ?>
		<script type='text/javascript'>
			(function (d, t) {
				var bh = d.createElement(t), s = d.getElementsByTagName(t)[0],
					apiKey = 'fay9fhhw8zalgwcjzptgfg';
				bh.type = 'text/javascript';
				bh.src = '//www.bugherd.com/sidebarv2.js?apikey=' + apiKey;
				s.parentNode.insertBefore(bh, s);
			})(document, 'script');
		</script>
	<?php } ?>
	<!-- Analytics -->
	<script>
 (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
 (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
 m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
 })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

 ga('create', 'UA-47243567-1', 'auto');
 ga('send', 'pageview');

</script>
</body>
</html>