<?php if (!user_has_access() && !is_404()) { ?>

<?php $contact_phone = get_field('footer_phone_number', 'options'); ?>

<div class="auth-modal mfp-hide">
	<div class="auth-input-container">
		<h4>Nestlé Action Stations Log In</h4>
		<p class="error" style="display:none;">Your password is invalid. Please try again.</p>
		<p>Password</p>
		<div class="auth-form-wrap">
			<form action="<?php echo admin_url('admin-ajax.php'); ?>">
			<div class="auth-form-outer" >
				<div class="auth-form-inner">
					<input class="password-field" type="password" value="" name="password" />
					<input class="password-submit" type="submit" value="GO" />
				</div>
			</div>
			<input class="checkbox" type="checkbox" id="remember" value="1" checked />
			<label for="remember">Keep me logged in</label>
			</form>
		</div>
	</div>
	<div class="auth-info-container">
		<p>This site is password protected to ensure our valued customers have access to unique recipes, tools and ideas that will assist them in setting up an action station.</p>
		<p>To request a password, contact your Nestlé Professional sales representative or call <strong><?php echo (isset($contact_phone)) ? $contact_phone : "1 (800) 243-8822" ?></strong></p>
	</div>
</div>
<?php } ?>