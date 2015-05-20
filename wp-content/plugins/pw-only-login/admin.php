<?php

	add_action('admin_init', 'pol_options_init'); 

	add_action('admin_menu', 'pol_add_page'); 

	add_action('admin_init', 'pol_restrict_dashboard'); 
	

	function pol_add_page() 
	{
		add_options_page('Password only login', 'Password only login', 'administrator',  __FILE__, 'pol_options_page');
	}
	
	function pol_options_page() 
	{ 
	?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>
			<h2>Password only login</h2>
			<form action="options.php" method="POST">
		
			<?php settings_fields('pol_options'); ?>
			<?php do_settings_sections(__FILE__); ?>
			
			<p class="submit">
				<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
			</p>
			
			</form>
		</div>
	
	<?php
	}


	function pol_options_init()
	{
		register_setting('pol_options', 'pol_options', 'pol_options_validate' );

		add_settings_section('main_section', '', 'pol_main_section_callback', __FILE__);	
		add_settings_field('pol_user_select', '', 'pol_user_callback', __FILE__, 'main_section');
	}

	function pol_options_validate($input) {
		return $input; 
	}


	function pol_main_section_callback() 
	{
	}
	
	
	function pol_user_callback() 
	{
		$pol_options = get_option('pol_options');

		$users = $pol_options['users'];

	    $subscribers = get_users('orderby=nicename&role=subscriber');

		if($subscribers): ?>
			<p>Select one or more user accounts used to determine the passwords allowed in the login form.</p>
			
			<select name="pol_options[users][]" style="height:200px; width:200px;" multiple="multiple">
		
			<?php foreach($subscribers as $sub): ?>
				<?php $selected = !empty($users) && in_array($sub->ID, $users) ? 'selected="selected"' : ''; ?>
				<option value="<?= $sub->ID ?>" <?= $selected ?>><?= $sub->display_name ?></option>
			<?php endforeach; ?>
		
			</select>
		
		<?php else: ?>
			<p>No user accounts found. Only accounts assigned with the subscriber role are listed.</p>
		
		<?php endif; 
	}


	function pol_restrict_dashboard() 
	{
		global $current_user;
      	
		$pol_options = get_option('pol_options');

		$users = $pol_options['users'];

		if(is_array($users) && in_array($current_user->ID, $users)):
			wp_die('Sorry, this area is restricted and you do not have access - <a href="' . site_url() .'">Return to the homepage</a>', 
					'Access is restricted', 
					array('back_link'=>true));	
			
		endif;	
	}

?>