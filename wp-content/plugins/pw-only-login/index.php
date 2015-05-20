<?php
/*
Plugin Name: Password only login
Description: Select one or more user accounts that can login to the site using just their password via a custom login form supplied with the plugin. For security purposes, only user accounts with subscriber role can be selected and any accounts selected will be prevented from accessing the wordpress backend. The custom login/logout form is added to a template file using a PHP function 'pol_showform()'. Like on the standard login form, if you pass redirect_to on the querystring, it will use this as the url to redirect to after the login/logout.
Author: Calva 
Author URI: http://calvaweb.com
Tags: password, login, form  
License: GPL v2
Version: 0.2
*/


require_once dirname( __FILE__ ) . '/admin.php';

add_action('init', 'pol_request'); 


/* Handle the Login/logout requests
*/	
function pol_request()
{
	$pol_action = trim($_POST['a']);

	if("login" == $pol_action) 
		pol_login();
		
	elseif("logout" == $pol_action)
		pol_logout();
	
} // pol_request
	
	
	
/* Login 
*/
function pol_login()
{
	global $pol_login_failed;

	$passwd = $_POST['pwd'];

	//no password 
	if(false == $passwd):
		$pol_login_failed = true;
		return;
	endif;
		
	$username = '';
	$pol_options = get_option('pol_options');
	$users = $pol_options['users'];

	if(!empty($users)):
	  foreach ($users as $user_id):
		$user_info = get_userdata($user_id);

		if(true == wp_check_password($passwd, $user_info->user_pass)): 
			$username = $user_info->user_login;
		endif;	
	  endforeach;
	endif;
    
	//no matching user  
	if(false == $username):
		$pol_login_failed = true;
		return;
	endif;
	
	
	$creds = array('user_login'    => $username, 
				   'user_password' => $passwd, 
				   'remember' 	   => false);

	$user = wp_signon( $creds, false );
	
	// this error should never be called since we have taken the password from the db
	if ( is_wp_error($user) ):
		$pol_login_failed = true;
		return;
	endif;

	wp_redirect('https://nestleactionstations.com');
	exit();
	
} // pol_login



/* Logout
*/
function pol_logout()
{
	wp_clearcookie();
	do_action('wp_logout');
	nocache_headers();
	wp_redirect(pol_redirect_uri());
	exit();
	
} // pol_logout



/* Show form
*/
//add_shortcode('password_only_login', 'pol_showform'); 

function pol_showform()
{
	global $pol_login_failed;
	
	if (false == is_user_logged_in()): ?>
	
		<form name="form" method="POST" action="<?= pol_current_uri() ?>">
		<div class="pol_form">
			<div class="auth-modal mfp-hide">
	<div class="auth-input-container">
		<h4>Nestl&egrave; Action Stations Log In</h4>
		<div class="auth-form-outer" >
				<div class="auth-form-inner">
			<input class="password-field" type="password" value="" name="pwd" />
					<input class="password-submit" type="submit" value="GO" />
			<input type="hidden" name="redirect_to" value="<?= pol_redirect_uri() ?>" />
			<input type="hidden" name="a" value="login" />
				</div>
		</div>
			<?php if (true == $pol_login_failed): ?>
			<span class="pol_error">Invalid password. Please try again.</span>
			<?php endif; ?>
		</div>
	</div>
	<div class="auth-info-container">
		<p>This site is password protected to ensure our valued customers have access to unique recipes, tools and ideas that will assist them in setting up an action station.</p>
		<p>To request a password, contact your Nestl&egrave; Professional sales representative or call <strong><?php echo (isset($contact_phone)) ? $contact_phone : "1 (800) 243-8822" ?></strong></p>
	</div>
			</div>
			
		</form>
	

	<?php else: ?>
	
		<form name="form" method="POST" action="<?= pol_current_uri() ?>">
		<div class="pol_form">
			<span class="pol_message">You are logged in.</span>
			<input class="pol_submit" type="submit" value="Log Out" />
			<input type="hidden" name="redirect_to" value="<?= pol_redirect_uri() ?>" />
			<input type="hidden" name="a" value="logout" />
		</div>
		</form>
	  	
	<?php
	endif;

} //pol_showform



/* Get redirect uri
*/
function pol_redirect_uri()
{
	return isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 
		(isset($_GET['redirect_to']) ? $_GET['redirect_to'] : pol_current_uri());
		
} //pol_redirect_uri



/* Get current uri
*/
function pol_current_uri()
{
	return (is_ssl() ? 'https' : 'http'). '://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
} //pol_current_uri


?>
