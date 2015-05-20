<?php
class PluginOrganizer {
	var $pluginPageActions = "1";
	var $regex;
	var $absPath;
	var $urlPath;
	var $nonce;
	function __construct($absPath, $urlPath) {
		$this->absPath = $absPath;
		$this->urlPath = $urlPath;
		$this->regex = array(
			"permalink" => "/^((https?):((\/\/)|(\\\\))+[\w\d:#@%\/;$()~_?\+-=\\\.&]*)$/",
			"group_name" => "/^[A-Za-z0-9_\-]+$/",
			"new_group_name" => "/^[A-Za-z0-9_\-]+$/",
			"default" => "/^(.|\\n)*$/"
		);
		if (get_option("PO_version_num") != "5.7.6" && !in_array($pagenow, array("plugins.php", "update-core.php", "update.php"))) {
			$this->activate();
		}
	}

	function move_old_posts($oldPosts) {
		global $wpdb;
		foreach($oldPosts as $post) {
			$enabledMobilePlugins = get_post_meta($post->ID, '_PO_enabled_mobile_plugins', $single=true);
			$disabledMobilePlugins = get_post_meta($post->ID, '_PO_disabled_mobile_plugins', $single=true);
			$enabledPlugins = get_post_meta($post->ID, '_PO_enabled_plugins', $single=true);
			$disabledPlugins = get_post_meta($post->ID, '_PO_disabled_plugins', $single=true);
			$children = get_post_meta($post->ID, '_PO_affect_children', $single=true);
			
			$secure=0;
			if (preg_match('/^.{1,5}:\/\//', get_post_meta($post->ID, '_PO_permalink', $single=true), $matches)) {
				switch ($matches[0]) {
					case "https://":
						$secure=1;
						break;
					default:
						$secure=0;
				}
			}
			
			$permalink = preg_replace('/^.{1,5}:\/\//', '', get_post_meta($post->ID, '_PO_permalink', $single=true));
			
			$splitPermalink = explode('?', $permalink);
			$permalinkNoArgs = $splitPermalink[0];

			$wpdb->insert($wpdb->prefix."PO_plugins", array("enabled_mobile_plugins"=>serialize($enabledMobilePlugins), "disabled_mobile_plugins"=>serialize($disabledMobilePlugins), "enabled_plugins"=>serialize($enabledPlugins), "disabled_plugins"=>serialize($disabledPlugins), "post_type"=>get_post_type($post->ID), "permalink"=>$permalink, "permalink_hash"=>md5($permalinkNoArgs), "permalink_hash_args"=>md5($permalink), "children"=>$children, "secure"=>$secure, "post_id"=>$post->ID));
		}
		update_option('PO_old_posts_moved', 1);
		

		delete_post_meta_by_key('_PO_affect_children');
		delete_post_meta_by_key('_PO_disabled_plugins');
		delete_post_meta_by_key('_PO_enabled_plugins');
		delete_post_meta_by_key('_PO_disabled_mobile_plugins');
		delete_post_meta_by_key('_PO_enabled_mobile_plugins');
		delete_post_meta_by_key('_PO_permalink');
	}

	function correct_group_members() {
		if (get_option('PO_group_members_corrected') != 1) {
			$plugins = get_plugins();
			$pluginList = array();
			foreach ($plugins as $key=>$plugin) {
				$pluginList[$plugin['Name']] = $key;
			}
			$groups = get_posts(array('post_type'=>'plugin_group', 'posts_per_page'=>-1));
			foreach($groups as $group) {
				$newGroupMembers = array();
				$groupMembers = get_post_meta($group->ID, '_PO_group_members', $single=true);
				foreach($groupMembers as $member) {
					if (array_key_exists($member, $pluginList)) {
						$newGroupMembers[] = $pluginList[$member];
					}
				}
				update_post_meta($group->ID, '_PO_group_members', $newGroupMembers);
			}
			update_option('PO_group_members_corrected', 1);
		}
	}
	
	function activate() {
		global $wpdb;
		$poPluginTableSQL = "CREATE TABLE ".$wpdb->prefix."PO_plugins (
			post_id bigint(20) unsigned NOT NULL,
			permalink longtext NOT NULL,
			permalink_hash varchar(32) NOT NULL default '',
			permalink_hash_args varchar(32) NOT NULL default '',
			post_type varchar(20) NOT NULL default '',
			status varchar(20) NOT NULL default 'publish',
			secure int(1) NOT NULL default 0,
			children int(1) NOT NULL default 0,
			disabled_plugins longtext NOT NULL,
			enabled_plugins longtext NOT NULL,
			disabled_mobile_plugins longtext NOT NULL,
			enabled_mobile_plugins longtext NOT NULL,
			disabled_groups longtext NOT NULL,
			enabled_groups longtext NOT NULL,
			disabled_mobile_groups longtext NOT NULL,
			enabled_mobile_groups longtext NOT NULL,
			PRIMARY KEY PO_post_id (post_id),
			KEY PO_permalink_hash (permalink_hash),
			KEY PO_permalink_hash_args (permalink_hash_args)
			);";
		if($wpdb->get_var("SHOW TABLES LIKE '".$wpdb->prefix."PO_plugins'") != $wpdb->prefix."PO_plugins") {
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($poPluginTableSQL);
		}

		//Add new columns to PO_plugins table
		$showColumnSql = "SHOW COLUMNS FROM ".$wpdb->prefix."PO_plugins";
		$showColumnResults = $wpdb->get_results($showColumnSql);
		$newColumns = array(
			'disabled_groups' => array(0, 'longtext NOT NULL'),
			'enabled_groups' => array(0, 'longtext NOT NULL'),
			'disabled_mobile_groups' => array(0, 'longtext NOT NULL'),
			'enabled_mobile_groups' => array(0, 'longtext NOT NULL')
		);
		foreach ($showColumnResults as $column) {
			if (array_key_exists($column->Field, $newColumns)) {
				$newColumns[$column->Field][0] = 1;
			}
		}

		foreach ($newColumns as $column=>$value) {
			if ($value[0] == 0) {
				$addColumnSql = "ALTER TABLE ".$wpdb->prefix."PO_plugins ADD COLUMN " . $column . " " . $value[1] . ";";
				$addColumnResult = $wpdb->query($addColumnSql);
			}
		}

		##Cleanup from previous versions
		delete_option('PO_old_posts_moved');
		delete_option('PO_old_urls_moved');
		delete_option('PO_old_groups_moved');
		
		$postTypeSupport = get_option("PO_custom_post_type_support");
		if (!is_array($postTypeSupport)) {
			$postTypeSupport = array('plugin_filter');
		} else {
			$postTypeSupport[] = 'plugin_filter';
		}
		
		$existingPosts = get_posts(array('posts_per_page' => -1, 'post_type'=>$postTypeSupport, 'meta_key'=>'_PO_permalink'));
		if (sizeof($existingPosts) > 0) {
			$this->move_old_posts($existingPosts);
		}
		
		if (!file_exists(WPMU_PLUGIN_DIR)) {
			@mkdir(WPMU_PLUGIN_DIR);
		}

		if (file_exists(WPMU_PLUGIN_DIR . "/PluginOrganizerMU.class.php")) {
			@unlink(WPMU_PLUGIN_DIR . "/PluginOrganizerMU.class.php");
		}
		
		if (file_exists(WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__)) . "/PluginOrganizerMU.class.php")) {
			@copy(WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__)) . "/PluginOrganizerMU.class.php", WPMU_PLUGIN_DIR . "/PluginOrganizerMU.class.php");
		}
		
		if (!is_array(get_option("PO_custom_post_type_support"))) {
			update_option("PO_custom_post_type_support", array("post", "page"));
		}
		
		if (get_option('PO_fuzzy_url_matching') == "") {
			update_option('PO_fuzzy_url_matching', "1");
		}
		
		if (get_option('PO_preserve_settings') == "") {
			update_option('PO_preserve_settings', "1");
		}
		
		if (get_option("PO_version_num") != "5.7.6") {
			update_option("PO_version_num", "5.7.6");
		}

		if (get_option('PO_mobile_user_agents') == '' || (is_array(get_option('PO_mobile_user_agents')) && sizeof(get_option('PO_mobile_user_agents')) == 0)) {
			update_option('PO_mobile_user_agents', array('mobile', 'bolt', 'palm', 'series60', 'symbian', 'fennec', 'nokia', 'kindle', 'minimo', 'netfront', 'opera mini', 'opera mobi', 'semc-browser', 'skyfire', 'teashark', 'uzard', 'android', 'blackberry', 'iphone', 'ipad'));
		}

		//Add capabilities to the administrator role
		$administrator = get_role( 'administrator' );
		if ( is_object($administrator) ) {			
			$administrator->add_cap('edit_plugin_filter');
			$administrator->add_cap('edit_plugin_filters');
			$administrator->add_cap('edit_private_plugin_filters');
			$administrator->add_cap('delete_plugin_filter');
			$administrator->add_cap('delete_plugin_filters');
			$administrator->add_cap('edit_others_plugin_filters');
			$administrator->add_cap('read_plugin_filters');
			$administrator->add_cap('read_private_plugin_filters');
			$administrator->add_cap('publish_plugin_filters');
			$administrator->add_cap('delete_others_plugin_filters');
			$administrator->add_cap('delete_published_plugin_filters');
			$administrator->add_cap('delete_private_plugin_filters');
			$administrator->add_cap('edit_filter_group');
			$administrator->add_cap('manage_filter_groups');
			

			$administrator->add_cap('edit_plugin_group');
			$administrator->add_cap('edit_plugin_groups');
			$administrator->add_cap('edit_private_plugin_groups');
			$administrator->add_cap('delete_plugin_group');
			$administrator->add_cap('delete_plugin_groups');
			$administrator->add_cap('edit_others_plugin_groups');
			$administrator->add_cap('read_plugin_groups');
			$administrator->add_cap('read_private_plugin_groups');
			$administrator->add_cap('publish_plugin_groups');
			$administrator->add_cap('delete_others_plugin_groups');
			$administrator->add_cap('delete_published_plugin_groups');
			$administrator->add_cap('delete_private_plugin_groups');
		}

		//Make sure all active plugins are valid
		$activePlugins = $this->get_active_plugins();
		$newActivePlugins = array();
		$pluginDisabled = 0;
		foreach ($activePlugins as $key=>$plugin) {
			if (file_exists(WP_PLUGIN_DIR . "/" . $plugin)) {
				$newActivePlugins[] = $plugin;
			} else {
				$pluginDisabled = 1;
			}
		}
		if ($pluginDisabled == 1) {
			update_option("active_plugins", $plugins);
		}
	}
	
	function deactivate() {
		global $wpdb;
		//Delete database tables and options if the option to preserve is set to 0.
		if (get_option("PO_preserve_settings") == "0") {
			$wpdb->query("DROP TABLE IF EXISTS `".$wpdb->prefix."PO_url_plugins");
			$wpdb->query("DROP TABLE IF EXISTS `".$wpdb->prefix."PO_post_plugins");
			$wpdb->query("DROP TABLE IF EXISTS `".$wpdb->prefix."PO_groups");
			$wpdb->query("DROP TABLE IF EXISTS `".$wpdb->prefix."PO_plugins");

			delete_option("PO_mobile_user_agents");
			delete_option("PO_disabled_plugins");
			delete_option("PO_disabled_mobile_plugins");
			delete_option("PO_disabled_groups");
			delete_option("PO_disabled_mobile_groups");
			delete_option("PO_ignore_arguments");
			delete_option("PO_ignore_protocol");
			delete_option("PO_plugin_order");
			delete_option("PO_default_group");
			delete_option("PO_preserve_settings");
			delete_option("PO_alternate_admin");
			delete_option("PO_fuzzy_url_matching");
			delete_option("PO_version_num");
			delete_option("PO_custom_post_type_support");
			delete_option("PO_disable_plugins");
			delete_option("PO_disable_mobile_plugins");
			delete_option("PO_admin_disable_plugins");
			
			delete_option("PO_enabled_search_plugins");
			delete_option("PO_disabled_search_plugins");
			delete_option("PO_enabled_mobile_search_plugins");
			delete_option("PO_disabled_mobile_search_plugins");
			delete_option("PO_enabled_search_groups");
			delete_option("PO_disabled_search_groups");
			delete_option("PO_enabled_mobile_search_groups");
			delete_option("PO_disabled_mobile_search_groups");
			
			$customPosts = get_posts(array('post_type'=>array('plugin_filter', 'plugin_group'), 'posts_per_page'=>-1));
			foreach($customPosts as $customPost) {
				wp_delete_post( $customPost->ID, true);
			}
		}
		if (file_exists(WPMU_PLUGIN_DIR . "/PluginOrganizerMU.class.php")) {
			@unlink(WPMU_PLUGIN_DIR . "/PluginOrganizerMU.class.php");
		}

		$administrator = get_role( 'administrator' );
		if ( is_object($administrator) ) {			
			$administrator->remove_cap('edit_plugin_filter');
			$administrator->remove_cap('edit_plugin_filters');
			$administrator->remove_cap('edit_private_plugin_filters');
			$administrator->remove_cap('delete_plugin_filter');
			$administrator->remove_cap('delete_plugin_filters');
			$administrator->remove_cap('edit_others_plugin_filters');
			$administrator->remove_cap('read_plugin_filters');
			$administrator->remove_cap('read_private_plugin_filters');
			$administrator->remove_cap('publish_plugin_filters');
			$administrator->remove_cap('delete_others_plugin_filters');
			$administrator->remove_cap('delete_published_plugin_filters');
			$administrator->remove_cap('delete_private_plugin_filters');
			$administrator->remove_cap('edit_filter_group');
			$administrator->remove_cap('manage_filter_groups');

			$administrator->remove_cap('edit_plugin_group');
			$administrator->remove_cap('edit_plugin_groups');
			$administrator->remove_cap('edit_private_plugin_groups');
			$administrator->remove_cap('delete_plugin_group');
			$administrator->remove_cap('delete_plugin_groups');
			$administrator->remove_cap('edit_others_plugin_groups');
			$administrator->remove_cap('read_plugin_groups');
			$administrator->remove_cap('read_private_plugin_groups');
			$administrator->remove_cap('publish_plugin_groups');
			$administrator->remove_cap('delete_others_plugin_groups');
			$administrator->remove_cap('delete_published_plugin_groups');
			$administrator->remove_cap('delete_private_plugin_groups');
		}
	}
	
	function create_default_group() {
		$post_id = wp_insert_post(array('post_title'=>"Default", 'post_type'=>'plugin_group', 'post_status'=>'publish'));
		if (!is_wp_error($post_id)) {
			update_post_meta($post_id, '_PO_group_members', array());
		}
		update_option("PO_default_group", $post_id);
	}
	
	function validate_field($fieldname) {
		if (isset($this->regex[$fieldname]) && preg_match($this->regex[$fieldname], $_POST[$fieldname])) {
			return true;
		} else if (preg_match($this->regex['default'], $_POST[$fieldname])) {
			return true;
		} else {
			return false;
		}
	}
	
	function check_plugin_order_access() {
		if (is_multisite() && get_option('PO_order_access_net_admin') == 1 && !current_user_can('manage_network')) {
			$this->pluginPageActions = "0";
		}
	}
	
	function setup_nonce() {
		$this->nonce = wp_create_nonce(plugin_basename(__FILE__));
	}
	
	function admin_menu() {
		$this->correct_group_members();
		if ( current_user_can( 'activate_plugins' ) ) {
			add_action('admin_head-plugins.php', array($this, 'plugin_page_js'));
			add_action('admin_head-plugins.php', array($this, 'make_draggable'));
			add_action('admin_head-plugins.php', array($this, 'admin_css'));
			add_action('admin_head-post-new.php', array($this, 'admin_css'));
			add_action('admin_head-post-new.php', array($this, 'common_js'));
			
			add_action('admin_head-post.php', array($this, 'admin_css'));
			add_action('admin_head-post.php', array($this, 'common_js'));
			
			$plugin_page=add_menu_page( 'Plugin Organizer', 'Plugin Organizer', 'activate_plugins', 'Plugin_Organizer', array($this, 'settings_page'), $this->urlPath . '/image/po-icon-16x16.png');
			
			$settings_page=add_submenu_page('Plugin_Organizer', 'Settings', 'Settings', 'activate_plugins', 'Plugin_Organizer', array($this, 'settings_page'));
			add_action('admin_head-'.$settings_page, array($this, 'admin_css'));
			add_action('admin_head-'.$settings_page, array($this, 'settings_page_js'));
			add_action('admin_head-'.$settings_page, array($this, 'common_js'));

			$plugin_page=add_submenu_page('Plugin_Organizer', 'Global Plugins', 'Global Plugins', 'activate_plugins', 'PO_global_plugins', array($this, 'global_plugins_page'));
			add_action('admin_head-'.$plugin_page, array($this, 'admin_css'));
			add_action('admin_head-'.$plugin_page, array($this, 'global_plugins_js'));
			add_action('admin_head-'.$plugin_page, array($this, 'common_js'));
			
			$plugin_page=add_submenu_page('Plugin_Organizer', 'Search Plugins', 'Search Plugins', 'activate_plugins', 'PO_search_plugins', array($this, 'search_plugins_page'));
			add_action('admin_head-'.$plugin_page, array($this, 'admin_css'));
			add_action('admin_head-'.$plugin_page, array($this, 'search_plugins_js'));
			add_action('admin_head-'.$plugin_page, array($this, 'common_js'));

			add_submenu_page('Plugin_Organizer', 'Filter Groups', 'Filter Groups', 'activate_plugins', 'edit-tags.php?taxonomy=filter_group&post_type=plugin_filter');
		}

	}

	function common_js() {
		require_once($this->absPath . "/tpl/common_js.php");
	}
	
	function plugin_page_js() {
		require_once($this->absPath . "/tpl/plugin_page_js.php");
	}

	function search_plugins_js() {
		require_once($this->absPath . "/tpl/search_plugins_js.php");
	}
	
	function global_plugins_js() {
		require_once($this->absPath . "/tpl/global_plugins_js.php");
	}

	function settings_page_js() {
		if (get_option('PO_disable_admin_notices') == 1) {
			add_action('admin_notices', array($this, 'admin_notices'));
		}
		require_once($this->absPath . "/tpl/settings_page_js.php");
	}

	function admin_css() {
		if (get_option('PO_disable_admin_notices') != 1) {
			add_action('admin_notices', array($this, 'admin_notices'));
		}
		require_once($this->absPath . "/tpl/admin_css.php");
	}
		
	function disable_admin_notices() {
		update_option('PO_disable_admin_notices', 1);
		die();
	}
	
	function admin_notices() {
		global $pagenow;
		$errMsg = $this->check_mu_plugin();
		
		if ($errMsg != '') {
			?>
			<div class="error" id="PO_admin_notices">
				<h2>Pugin Organizer is not set up correctly.</h2>
				<?php _e( $errMsg, 'plugin-organizer' ); ?>
				<a href="#" id="PO_disable_admin_notices">Disable admin notices</a> - You will still recieve admin notices when you visit the Plugin Organizer settings page.
			</div>
			<script type="text/javascript" language="javascript">
				jQuery('#PO_disable_admin_notices').click(function() {
					jQuery.post(encodeURI(ajaxurl + '?action=PO_disable_admin_notices'), {PO_nonce: '<?php print $this->nonce; ?>'}, function (result) {
						jQuery('#PO_admin_notices').remove();
					});
					return false;
				});
			</script>
			<?php
		}
	}
	
	function check_mu_plugin() {
		$muPlugins = get_mu_plugins();
		if (!isset($muPlugins['PluginOrganizerMU.class.php']['Version'])) {
			return "<p>You are missing the MU Plugin.  Please use the tool provided on the settings page to move the plugin into place or manually copy ".$this->absPath."/lib/PluginOrganizerMU.class.php to ".WPMU_PLUGIN_DIR."/PluginOrganizerMU.class.php.  If you don't do this the plugin will not work.  This message will disappear when everything is correct.</p>";
		} else if (isset($muPlugins['PluginOrganizerMU.class.php']['Version']) && $muPlugins['PluginOrganizerMU.class.php']['Version'] != get_option("PO_version_num")) {
			return "<p>You are running an old version of the MU Plugin.  Please use the tool provided on the settings page to move the updated version into place or manually copy ".$this->absPath."/lib/PluginOrganizerMU.class.php to ".WPMU_PLUGIN_DIR."/PluginOrganizerMU.class.php.  If you don't do this the plugin will not work.  This message will disappear when everything is correct.</p>";
		} else {
			return "";
		}
	}
	
	function settings_page() {
		if ( current_user_can( 'activate_plugins' ) ) {
			require_once($this->absPath . "/tpl/settings.php");
		} else {
			wp_die("You dont have permissions to access this page.");
		}
	}
	
	function search_plugins_page($post_id) {
		if ( current_user_can( 'activate_plugins' ) ) {
			$plugins = $this->reorder_plugins(get_plugins());
			$enabledPluginList = get_option('PO_enabled_search_plugins');
			$disabledPluginList = get_option('PO_disabled_search_plugins');
			$enabledMobilePluginList = get_option('PO_enabled_mobile_search_plugins');
			$disabledMobilePluginList = get_option('PO_disabled_mobile_search_plugins');
			$enabledGroupList = get_option('PO_enabled_search_groups');
			$disabledGroupList = get_option('PO_disabled_search_groups');
			$enabledMobileGroupList = get_option('PO_enabled_mobile_search_groups');
			$disabledMobileGroupList = get_option('PO_disabled_mobile_search_groups');
			$activePlugins = $this->get_active_plugins();
			$activeSitewidePlugins = array_keys((array) get_site_option('active_sitewide_plugins', array()));
			$groupList = get_posts(array('posts_per_page'=>-1, 'post_type'=>'plugin_group'));
			if (!is_array($enabledPluginList)) {
				$enabledPluginList = array();
			}
			if (!is_array($disabledPluginList)) {
				$disabledPluginList = array();
			}
			if (!is_array($enabledMobilePluginList)) {
				$enabledMobilePluginList = array();
			}
			if (!is_array($disabledMobilePluginList)) {
				$disabledMobilePluginList = array();
			}
			if (!is_array($enabledGroupList)) {
				$enabledGroupList = array();
			}
			if (!is_array($disabledGroupList)) {
				$disabledGroupList = array();
			}
			if (!is_array($enabledMobileGroupList)) {
				$enabledMobileGroupList = array();
			}
			if (!is_array($disabledMobileGroupList)) {
				$disabledMobileGroupList = array();
			}
			
			
			$globalPlugins = get_option('PO_disabled_plugins');
			if (!is_array($globalPlugins)) {
				$globalPlugins = array();
			}

			$globalMobilePlugins = get_option('PO_disabled_mobile_plugins');
			if (!is_array($globalMobilePlugins)) {
				$globalMobilePlugins = array();
			}

			$globalGroups = get_option('PO_disabled_groups');
			if (!is_array($globalGroups)) {
				$globalGroups = array();
			}

			$globalMobileGroups = get_option('PO_disabled_mobile_groups');
			if (!is_array($globalMobileGroups)) {
				$globalMobileGroups = array();
			}

			require_once($this->absPath . "/tpl/searchPlugins.php");
		}
	}
	
	function global_plugins_page($post_id) {
		if ( current_user_can( 'activate_plugins' ) ) {
			$plugins = $this->reorder_plugins(get_plugins());
			$disabledPlugins = get_option('PO_disabled_plugins');
			$disabledMobilePlugins = get_option('PO_disabled_mobile_plugins');
			$disabledGroups = get_option('PO_disabled_groups');
			$disabledMobileGroups = get_option('PO_disabled_mobile_groups');
			$activePlugins = $this->get_active_plugins();
			$activeSitewidePlugins = array_keys((array) get_site_option('active_sitewide_plugins', array()));
			$groupList = get_posts(array('posts_per_page'=>-1, 'post_type'=>'plugin_group'));
			if (!is_array($disabledPlugins)) {
				$disabledPlugins = array();
			}
			if (!is_array($disabledMobilePlugins)) {
				$disabledMobilePlugins = array();
			}
			if (!is_array($disabledGroups)) {
				$disabledGroups = array();
			}
			if (!is_array($disabledMobileGroups)) {
				$disabledMobileGroups = array();
			}
			require_once($this->absPath . "/tpl/globalPlugins.php");
		} else {
			wp_die("You dont have permissions to access this page.");
		}
	}	

	function add_hidden_start_order($pluginMeta, $pluginFile) {
		
		if ( current_user_can( 'activate_plugins' ) ) {
			$plugins = $this->get_active_plugins();
			if (array_search($pluginFile, $plugins) !== false) {
				$pluginMeta[0] .= "<input type=\"hidden\" class=\"start_order\" id=\"start_order_" . array_search($pluginFile, $plugins) . "\" value=\"" . array_search($pluginFile, $plugins) . "\">";
			}
		} else {
			wp_die("You dont have permissions to access this page.");
		}
		return $pluginMeta;
	}
	
	function add_group_views($views) {
		$groups = get_posts(array('post_type'=>'plugin_group', 'posts_per_page'=>-1));
		if (!array_key_exists('all', $views)) {
			$views = array_reverse($views, true);
			$views['all'] = '<a href="'.get_admin_url().'plugins.php?plugin_status=all">All <span class="count">('.count(get_plugins()).')</span></a>';
			$views = array_reverse($views, true);
		}
		foreach ($groups as $group) {
			$groupMembers = get_post_meta($group->ID, '_PO_group_members', $single=true);
			if (isset($groupMembers[0]) && $groupMembers[0] != 'EMPTY') {
				$groupCount = sizeof($groupMembers);
			} else {
				$groupCount = 0;
			}
			$groupName = $group->post_title;
			$loopCount = 0;
			while(array_key_exists($groupName, $views) && $loopCount < 10) {
				$groupName = $group->post_title.$loopCount;
				$loopCount++;
			}
			$views[$groupName] = '<a href="'.get_admin_url().'plugins.php?PO_group_view='.$group->ID.'">'.$group->post_title.' <span class="count">('.$groupCount.')</span></a> ';
		}
		return $views;
	}
	
	function make_draggable() {
		if ($this->pluginPageActions == '1' && !isset($_REQUEST['PO_group_view']) && (!isset($_REQUEST['plugin_status']) || $_REQUEST['plugin_status'] == 'all' || $_REQUEST['plugin_status'] == 'active')) {
			?>
			<script type="text/javascript" src="<?php print $this->urlPath.'/js/jquery.tablednd.js'; ?>"></script>
			<style type="text/css">
				tr.active .column-PO_draghandle {
					background-image:url('<?php print $this->urlPath; ?>/image/drag-16x16.png');
					background-repeat:no-repeat;
					background-position:center;
				}
			</style>
			<script type="text/javascript" language="javascript">
				function make_plugins_draggable() {
					//jQuery('tr.inactive .PO_draghandle').css('background', 'none');
					jQuery('tr.inactive').each(function () {
						jQuery(this).addClass('nodrag');
						jQuery(this).addClass('nodrop');
					});
					jQuery('#the-list').tableDnD({dragHandle: "column-PO_draghandle"});
				}
				jQuery(document).ready(function() {
					make_plugins_draggable();
				});
			</script>
			<?php
		}
	}
	

	function create_plugin_lists($pluginList, $pluginExludeList) {
		$returnArray = array(array(), array());
		if (is_array($pluginList)) {
			foreach ($pluginList as $plugin) {
				if (!in_array($plugin, $pluginExludeList)) {
					$returnArray[0][] = $plugin;
				}
			}

			foreach ($pluginExludeList as $plugin) {
				if (!in_array($plugin, $pluginList)) {
					$returnArray[1][] = $plugin;
				}
			}
		} else {
			foreach ($pluginExludeList as $plugin) {
				$returnArray[1][] = $plugin;
			}
		}
		return $returnArray;
	}
	
	function save_search_plugins() {
		if ( !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		
		$globalPlugins = get_option('PO_disabled_plugins');
		if (!is_array($globalPlugins)) {
			$globalPlugins = array();
		}
		$checkPluginList = (isset($_POST['disabledList'])) ? $_POST['disabledList'] : '';
		
		$tempPluginList = $this->create_plugin_lists($checkPluginList, $globalPlugins);
		$disabledPlugins = $tempPluginList[0];
		$enabledPlugins = $tempPluginList[1];
		

		### Mobile plugins
		if (get_option('PO_disable_mobile_plugins') == 1) {
			$globalMobilePlugins = get_option('PO_disabled_mobile_plugins');
			if (!is_array($globalMobilePlugins)) {
				$globalMobilePlugins = array();
			}
			$checkPluginList = (isset($_POST['disabledMobileList'])) ? $_POST['disabledMobileList'] : '';
			$tempPluginList = $this->create_plugin_lists($checkPluginList, $globalMobilePlugins);
			$disabledMobilePlugins = $tempPluginList[0];
			$enabledMobilePlugins = $tempPluginList[1];
		} else {
			$disabledMobilePlugins = array();
			$enabledMobilePlugins = array();
		}


		
		##Groups
		$globalGroups = get_option('PO_disabled_groups');
		if (!is_array($globalGroups)) {
			$globalGroups = array();
		}
		$checkPluginList = (isset($_POST['disabledGroupList'])) ? $_POST['disabledGroupList'] : '';
		
		$tempPluginList = $this->create_plugin_lists($checkPluginList, $globalGroups);
		$disabledGroups = $tempPluginList[0];
		$enabledGroups = $tempPluginList[1];


		##Mobile Groups
		if (get_option('PO_disable_mobile_plugins') == 1) {
			$globalMobileGroups = get_option('PO_disabled_mobile_groups');
			if (!is_array($globalMobileGroups)) {
				$globalMobileGroups = array();
			}
			$checkPluginList = (isset($_POST['disabledMobileGroupList'])) ? $_POST['disabledMobileGroupList'] : '';
		
			$tempPluginList = $this->create_plugin_lists($checkPluginList, $globalMobileGroups);
			$disabledMobileGroups = $tempPluginList[0];
			$enabledMobileGroups = $tempPluginList[1];
		} else {
			$disabledMobileGroups = array();
			$enabledMobileGroups = array();
		}
		
		update_option('PO_enabled_search_plugins', $enabledPlugins);
		update_option('PO_disabled_search_plugins', $disabledPlugins);
		update_option('PO_enabled_mobile_search_plugins', $enabledMobilePlugins);
		update_option('PO_disabled_mobile_search_plugins', $disabledMobilePlugins);
		update_option('PO_enabled_search_groups', $enabledGroups);
		update_option('PO_disabled_search_groups', $disabledGroups);
		update_option('PO_enabled_mobile_search_groups', $enabledMobileGroups);
		update_option('PO_disabled_mobile_search_groups', $disabledMobileGroups);
		
		print "Search plugins updated!";
		die();
	}
	
	function save_global_plugins() {
		if ( !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$returnStatus = "";
		if ( current_user_can( 'activate_plugins' ) ) {
			if (is_array($_POST['disabledList']) && $_POST['disabledList'][0] != 'EMPTY') {
				$disabledPlugins = $_POST['disabledList'];
				update_option("PO_disabled_plugins", $disabledPlugins);
				$returnStatus .= "Global plugin list has been saved.\n";
			} else {
				update_option("PO_disabled_plugins", array());
				$returnStatus .= "Global plugin list has been saved.\n";
			}
			
			if (is_array($_POST['disabledGroupList']) && $_POST['disabledGroupList'][0] != 'EMPTY') {
				$disabledGroups = $_POST['disabledGroupList'];
				update_option("PO_disabled_groups", $disabledGroups);
				$returnStatus .= "Global group list has been saved.\n";
			} else {
				update_option("PO_disabled_groups", array());
				$returnStatus .= "Global group list has been saved.\n";
			}
			
			if (get_option('PO_disable_mobile_plugins') == 1) {
				if (is_array($_POST['disabledMobileList']) && $_POST['disabledMobileList'][0] != 'EMPTY') {
					$disabledMobilePlugins = $_POST['disabledMobileList'];
					update_option("PO_disabled_mobile_plugins", $disabledMobilePlugins);
					$returnStatus .= "Global mobile plugin list has been saved.\n";
				} else {
					update_option("PO_disabled_mobile_plugins", array());
					$returnStatus .= "Global mobile plugin list has been saved.\n";
				}

				if (is_array($_POST['disabledMobileGroupList']) && $_POST['disabledMobileGroupList'][0] != 'EMPTY') {
					$disabledMobileGroups = $_POST['disabledMobileGroupList'];
					update_option("PO_disabled_mobile_groups", $disabledMobileGroups);
					$returnStatus .= "Global mobile group list has been saved.\n";
				} else {
					update_option("PO_disabled_mobile_groups", array());
					$returnStatus .= "Global mobile group list has been saved.\n";
				}
			}
		} else {
			$returnStatus .= "You dont have permissions to access this page.\n";
		}
		print $returnStatus;
		die();
	}
	
	function save_order() {
		if ( !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$returnStatus = "";
		if ( current_user_can( 'activate_plugins' ) ) {
			$plugins = $this->get_active_plugins();
			if (preg_match("/^(([0-9])+[,]*)*$/", implode(",", $_POST['orderList'])) && preg_match("/^(([0-9])+[,]*)*$/", implode(",", $_POST['startOrder']))) {
				$newPlugArray = $_POST['orderList'];
				$startOrderArray = $_POST['startOrder'];
				if (sizeof(array_unique($newPlugArray)) == sizeof($plugins) && sizeof(array_unique($startOrderArray)) == sizeof($plugins)) {
					array_multisort($startOrderArray, $newPlugArray);
					array_multisort($newPlugArray, $plugins);
					update_option("active_plugins", $plugins);
					update_option("PO_plugin_order", $plugins);
					$returnStatus = "The plugin load order has been changed.";
				} else {
					$returnStatus = "The order values were not unique so no changes were made.";
				}
			} else {
				$returnStatus = "Did not recieve the proper variables.  No changes made.";
			}
		} else {
			$returnStatus = "You dont have permissions to access this page.";
		}
		print $returnStatus;
		die();
	}

	function get_active_plugins() {
		global $PluginOrganizerMU;
		if (is_object($PluginOrganizerMU)) {
			remove_filter('option_active_plugins', array($PluginOrganizerMU, 'disable_plugins'), 10, 1);
		}
		
		$plugins = get_option("active_plugins");
		
		#print_r($plugins);
		$networkPlugins = get_site_option('active_sitewide_plugins');
		if (is_array($networkPlugins)) {
			$networkPluginMissing = 0;
			foreach($networkPlugins as $key=>$pluginFile) {
				if (array_search($key, $plugins) === FALSE && file_exists(WP_PLUGIN_DIR . "/" . $key)) {
					$plugins[] = $key;
					$networkPluginMissing = 1;
				}
			}
			#print_r($plugins);
			if ($networkPluginMissing == 1) {
				update_option("active_plugins", $plugins);
			}
		}
		
		if (is_object($PluginOrganizerMU)) {
			add_filter('option_active_plugins', array($PluginOrganizerMU, 'disable_plugins'), 10, 1);
		}
		
		return $plugins;
	}
	
	function reorder_plugins($allPluginList) {
		$plugins = $this->get_active_plugins();
		
		
		if (is_admin() && $this->pluginPageActions == 1 && (!isset($_REQUEST['PO_group_view']) || !is_numeric($_REQUEST['PO_group_view']))) {
			$perPage = get_user_option("plugins_per_page");
			if (!is_numeric($perPage)) {
				$perPage = 999;
			}
			if (sizeOf($plugins) > $perPage) {
				remove_action('all_plugins',  array($this, 'reorder_plugins'));
				$this->pluginPageActions = 0;
				return $allPluginList;
			}
		}
		$activePlugins = Array();
		$inactivePlugins = Array();
		$newPluginList = Array();
		$activePluginOrder = Array();
		
		$globalPlugins = get_option('PO_disabled_plugins');
		if (!is_array($globalPlugins)) {
			$globalPlugins = array();
		}
		
		if (isset($_REQUEST['PO_group_view']) && is_numeric($_REQUEST['PO_group_view'])) {
			$members = get_post_meta($_REQUEST['PO_group_view'], '_PO_group_members', $single=true);
			$members = stripslashes_deep($members);
			foreach ($allPluginList as $key=>$val) {
				if (is_array($members) && in_array($key, $members)) {
					$activePlugins[$key] = $val;
					$activePluginOrder[] = array_search($key, $plugins);
				}
			}
		} else {
			foreach ($allPluginList as $key=>$val) {
				if (in_array($key, $plugins)) {
					$activePlugins[$key] = $val;
					$activePluginOrder[] = array_search($key, $plugins);
				} else {
					$inactivePlugins[$key] = $val;
				}
			}
		}
		array_multisort($activePluginOrder, $activePlugins);
		
		$newPluginList = array_merge($activePlugins, $inactivePlugins);	
		return $newPluginList;
	}


	function get_pf_column_headers($columns) {
		$columns['PO_PF_permalink'] = __('Permalink');
		return $columns;
	}

	function set_pf_custom_column_values($column_name, $post_id ) {
		global $wpdb;
		switch ($column_name) {
			case 'PO_PF_permalink' :
				$postSettingsQuery = "SELECT * FROM ".$wpdb->prefix."PO_plugins WHERE post_id = %d";
				$postSettings = $wpdb->get_row($wpdb->prepare($postSettingsQuery, $post_id), ARRAY_A);
				if (isset($postSettings['permalink'])) {
					print $postSettings['permalink'];
				}
				break;
			default:
		}
	}
	
	
	function get_column_headers($columns) {
		$count = 0;
		$newColumns = array();
		if ($this->pluginPageActions == '1' && !isset($_REQUEST['PO_group_view']) && (!isset($_REQUEST['plugin_status']) || $_REQUEST['plugin_status'] == 'all' || $_REQUEST['plugin_status'] == 'active')) {
			foreach ($columns as $key=>$column) {
				if ($count==1) {
					$newColumns['PO_draghandle'] = __('Drag');
					$newColumns[$key]=$column;
				} else {
					$newColumns[$key]=$column;
				}
				$count++;
			}
		} else {
			$newColumns = $columns;
		}
		$newColumns['PO_groups'] = __('Groups');
		return $newColumns;
	}

	function set_custom_column_values($column_name, $pluginPath, $plugin ) {
		switch ($column_name) {
			case 'PO_groups' :
				$groups = get_posts(array('post_type'=>'plugin_group', 'posts_per_page'=>-1));
				$assignedGroups = "";
				foreach ($groups as $group) {
					$members = get_post_meta($group->ID, '_PO_group_members', $single=true);
					$members = stripslashes_deep($members);
					if (is_array($members) && array_search($pluginPath, $members) !== FALSE) {
						$assignedGroups .= '<a href="'.get_admin_url().'plugins.php?PO_group_view='.$group->ID.'">'.$group->post_title.'</a> ,';
					}
				}
				print rtrim($assignedGroups, ',');
				break;
			default:
		}
	}

	
	function change_page_title($translation, $original) {
		global $pagenow;
		if ($pagenow == "plugins.php" && $original == 'Plugins') {
			if (isset($_REQUEST['PO_group_view']) && is_numeric($_REQUEST['PO_group_view'])) {
				$group = get_posts(array('ID'=>$_REQUEST['PO_group_view'], 'post_type'=>'plugin_group'));
				if (is_array($group[0])) {
					return 'Plugin Group: '.$group[0]->post_title;
				}
			}
		}
		return $translation;
	}
	
	function save_group() {
		if ( !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$returnStatus = "";
		if ( current_user_can( 'activate_plugins' ) ) {
			$plugins = $this->get_active_plugins();
			if (is_array($_POST['groupList']) && is_numeric($_POST['PO_group']) && $this->validate_field("group_name")) {
				$post_id = wp_update_post(array('ID'=>$_POST['PO_group'], 'post_title'=>$_POST['group_name']));
				if ($post_id > 0) {
					update_post_meta($post_id, "_PO_group_members", $_POST['groupList']);
				}
				$returnStatus = "The plugin group has been updated.";
			} else if (is_array($_POST['groupList']) && $_POST['PO_group'] == "" && $this->validate_field("group_name")) {
				$post_id = wp_insert_post(array('post_title'=>$_POST['group_name'], 'post_type'=>'plugin_group', 'post_status'=>'publish'));
				if (!is_wp_error($post_id)) {
					update_post_meta($post_id, "_PO_group_members", $_POST['groupList']);
				}
					
				$returnStatus = "The plugin group has been created.";
			} else {
				$returnStatus = "Did not recieve the proper variables.  No changes made.";
			}
		} else {
			$returnStatus = "You dont have permissions to access this page.";
		}
		print $returnStatus;
		die();
	}

	function add_to_group() {
		if ( !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$returnStatus = "";
		if ( current_user_can( 'activate_plugins' ) ) {
			$plugins = $this->get_active_plugins();
			if (is_array($_POST['groupList']) && is_numeric($_POST['PO_group']) && $this->validate_field("group_name")) {
				$members = get_post_meta($_POST['PO_group'], '_PO_group_members', $single=true);
				$members = stripslashes_deep($members);
				if (!is_array($members)) {
					$members = array();
				}
				
				foreach($_POST['groupList'] as $newGroupMember) {
					#print $newGroupMember . " - " . array_search($newGroupMember, $members) . "\n";
					if (array_search($newGroupMember, $members) === FALSE) {
						$members[]=$newGroupMember;
					}
				}
				if ($members === get_post_meta($_POST['PO_group'], '_PO_group_members', $single=true)) {
					$returnStatus = "The selected plugins were not added to the group because they already belong to it.";
				} else {
					$post_id = wp_update_post(array('ID'=>$_POST['PO_group'], 'post_title'=>$_POST['group_name']));
					if ($post_id > 0) {
						update_post_meta($post_id, "_PO_group_members", $members);
					}
					$returnStatus = "The plugin group has been updated.";
				}
			} else if (is_array($_POST['groupList']) && $_POST['PO_group'] == "" && $this->validate_field("group_name")) {
				$post_id = wp_insert_post(array('post_title'=>$_POST['group_name'], 'post_type'=>'plugin_group', 'post_status'=>'publish'));
				if (!is_wp_error($post_id)) {
					update_post_meta($post_id, "_PO_group_members", $_POST['groupList']);
				}
				
				$returnStatus = "The plugin group has been created.";
			} else {
				$returnStatus = "Did not recieve the proper variables.  No changes made.";
			}
		} else {
			$returnStatus = "You dont have permissions to access this page.";
		}
		print $returnStatus;
		die();
	}

	function edit_plugin_group_name() {
		if ( !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) ) || !current_user_can( 'activate_plugins' ) ) {
			print "You dont have permissions to access this page.";
			die();
		}
		$returnStatus = "";
		if (is_numeric($_POST['PO_group_id']) && $_POST['PO_new_group_name'] != '') {
			$post_id = wp_update_post(array('ID'=>$_POST['PO_group_id'], 'post_title'=>$_POST['PO_new_group_name']));
			if ($post_id > 0) {
				$returnStatus = "The group was successfully updated.";
			} else {
				$returnStatus = "The group name was not updated.";
			}
		}
		print $returnStatus;
		die();
	}


	function delete_group() {
		if ( !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$returnStatus = "";
		if (current_user_can('activate_plugins') && is_numeric($_POST['PO_group'])) {
			$result = wp_delete_post($_POST['PO_group'], true);
			if ($result) {
				$returnStatus = "The plugin group has been deleted.";
			} else {
				$returnStatus = "There was a problem deleting the plugin group.";
			}
		}
		print $returnStatus;
		die();
	}

	function remove_plugins_from_group() {
		if ( !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$returnStatus = "";
		if (current_user_can('activate_plugins') && is_numeric($_POST['PO_group'])) {
			$members = get_post_meta($_POST['PO_group'], '_PO_group_members', $single=true);
			if (!is_array($members)) {
				$members = array();
			}
			foreach($_POST['groupList'] as $key=>$pluginToRemove) {
				if (array_search($pluginToRemove, $members) !== FALSE) {
					unset($members[array_search($pluginToRemove, $members)]);
				}
			}
			$members = array_values($members);
			if ($members === get_post_meta($_POST['PO_group'], '_PO_group_members', $single=true)) {
				$returnStatus = "The selected plugins were not found in the group.";
			} else {
				$result = update_post_meta($_POST['PO_group'], "_PO_group_members", $members);
				if ($result) {
					$returnStatus = "The selected plugins were removed from the group.";
				} else {
					$returnStatus = "There was a problem removing the plugins from the group.";
				}
			}
		}
		print $returnStatus;
		die();

	}


	function disable_plugin_box() {
		if ( current_user_can( 'activate_plugins' ) ) {
			$supportedPostTypes = get_option("PO_custom_post_type_support");
			$supportedPostTypes[] = 'plugin_filter';
			if (is_array($supportedPostTypes)) {
				foreach ($supportedPostTypes as $postType) {
					add_meta_box(
						'plugin_organizer',
						'Plugin Organizer',
						array($this, 'get_post_meta_box'),
						$postType,
						'normal'
					);
				}
			}
		}
	}

	function find_parent_plugins($currID, $permalink, $mobile, $secure) {
		global $wpdb;
		$fuzzyPlugins = array(
			'post_id'=>0,
			'plugins'=>array(
				'disabled_plugins'=>array(),
				'enabled_plugins'=>array(),
				'disabled_groups'=>array(),
				'enabled_groups'=>array()
			)
		);
		
		if (get_option('PO_ignore_arguments') == '1') {
			$permalink = preg_replace('/\?.*$/', '', $permalink);
			$permalinkSearchField = 'permalink_hash';
		} else {
			$permalinkSearchField = 'permalink_hash_args';
		}
		$endChar = (preg_match('/\/$/', get_option('permalink_structure')) || preg_match('/^'.preg_quote(get_admin_url(), '/').'/', $permalink))? '/':'';
		$lastUrl = $_SERVER['HTTP_HOST'].$endChar;
		
		//Dont allow an endless loop
		$loopCount = 0;
		$matchFound = 0;


		while ($loopCount < 25 && $matchFound == 0 && strlen($permalink) > strlen($lastUrl) && ($loopCount == 0 || $permalink = preg_replace('/\/[^\/]+\/?$/', $endChar, $permalink))) {
			$loopCount++;
			$permalinkHash = md5($permalink);
			if (get_option('PO_ignore_protocol') == '0') {
		
				$fuzzyPostQuery = "SELECT * FROM ".$wpdb->prefix."PO_plugins WHERE ".$permalinkSearchField." = %s AND status = 'publish' AND secure = %d AND children = 1 AND post_id != %d";
				$fuzzyPost = $wpdb->get_results($wpdb->prepare($fuzzyPostQuery, $permalinkHash, $secure, $currID), ARRAY_A);
				$matchFound = (sizeof($fuzzyPost) > 0)? 1:$matchFound;
				
			} else {
				$fuzzyPostQuery = "SELECT * FROM ".$wpdb->prefix."PO_plugins WHERE ".$permalinkSearchField." = %s AND status = 'publish' AND children = 1 AND post_id != %d";
				$fuzzyPost = $wpdb->get_results($wpdb->prepare($fuzzyPostQuery, $permalinkHash, $currID), ARRAY_A);
				
				$matchFound = (sizeof($fuzzyPost) > 0)? 1:$matchFound;
			}
		
		
			if ($matchFound > 0) {
				$matchFound = 0;
				if (!is_array($fuzzyPost)) {
					$fuzzyPost = array();
				} else if (sizeOf($fuzzyPost) > 0) {
					usort($fuzzyPost, array($this, 'sort_posts'));
				}

				foreach($fuzzyPost as $currPost) {
					if ($mobile == 0) {
						$disabledFuzzyPlugins = @unserialize($currPost['disabled_plugins']);
						$enabledFuzzyPlugins = @unserialize($currPost['enabled_plugins']);
						$disabledFuzzyGroups = @unserialize($currPost['disabled_groups']);
						$enabledFuzzyGroups = @unserialize($currPost['enabled_groups']);
					} else {
						$disabledFuzzyPlugins = @unserialize($currPost['disabled_mobile_plugins']);
						$enabledFuzzyPlugins = @unserialize($currPost['enabled_mobile_plugins']);
						$disabledFuzzyGroups = @unserialize($currPost['disabled_mobile_groups']);
						$enabledFuzzyGroups = @unserialize($currPost['enabled_mobile_groups']);
					}
					
					if ((is_array($disabledFuzzyPlugins) && sizeof($disabledFuzzyPlugins) > 0) || (is_array($enabledFuzzyPlugins) && sizeof($enabledFuzzyPlugins) > 0) || (is_array($disabledFuzzyGroups) && sizeof($disabledFuzzyGroups) > 0) || (is_array($enabledFuzzyGroups) && sizeof($enabledFuzzyGroups) > 0)) {
						$matchFound = 1;
						if (!is_array($disabledFuzzyPlugins)) {
							$disabledFuzzyPlugins = array();
						}

						if (!is_array($enabledFuzzyPlugins)) {
							$enabledFuzzyPlugins = array();
						}

						if (!is_array($disabledFuzzyGroups)) {
							$disabledFuzzyGroups = array();
						}

						if (!is_array($enabledFuzzyGroups)) {
							$enabledFuzzyGroups = array();
						}

						$fuzzyPlugins['plugins']['disabled_plugins'] = $disabledFuzzyPlugins;
						$fuzzyPlugins['plugins']['enabled_plugins'] = $enabledFuzzyPlugins;
						$fuzzyPlugins['plugins']['disabled_groups'] = $disabledFuzzyGroups;
						$fuzzyPlugins['plugins']['enabled_groups'] = $enabledFuzzyGroups;

						$fuzzyPlugins['post_id'] = $currPost['post_id'];
					}
				}
			}
		}
		return $fuzzyPlugins;
	}
	
	
	function find_duplicate_permalinks($postID, $permalink) {
		global $wpdb;
		$returnDup = array();
		$dupPostQuery = "SELECT post_id FROM ".$wpdb->prefix."PO_plugins WHERE permalink = %s and post_id != %d and post_type='plugin_filter' and status != 'trash'";
		$dupPosts = $wpdb->get_results($wpdb->prepare($dupPostQuery, $permalink, $postID), ARRAY_A);
		if (sizeOf($dupPosts) > 0) {
			foreach ($dupPosts as $dup) {
				$returnDup[] = $dup['post_id'];
			}
		}
		return $returnDup;
	}
	
	function get_post_meta_box($post) {
		global $wpdb;
		$errMsg = "";
		if ($post->ID != "" && is_numeric($post->ID)) {
			$filterName = $post->post_title;
			$postSettingsQuery = "SELECT * FROM ".$wpdb->prefix."PO_plugins WHERE post_id = %d";
			$postSettings = $wpdb->get_row($wpdb->prepare($postSettingsQuery, $post->ID), ARRAY_A);
			
			$affectChildren = $postSettings['children'];
			
			$disabledPluginList = @unserialize($postSettings['disabled_plugins']);
			if (!is_array($disabledPluginList)) {
				$disabledPluginList = array();
			}

			$enabledPluginList = @unserialize($postSettings['enabled_plugins']);
			if (!is_array($enabledPluginList)) {
				$enabledPluginList = array();
			}

			$disabledMobilePluginList = @unserialize($postSettings['disabled_mobile_plugins']);
			if (!is_array($disabledMobilePluginList)) {
				$disabledMobilePluginList = array();
			}

			$enabledMobilePluginList = @unserialize($postSettings['enabled_mobile_plugins']);
			if (!is_array($enabledMobilePluginList)) {
				$enabledMobilePluginList = array();
			}

			$disabledGroupList = @unserialize($postSettings['disabled_groups']);
			if (!is_array($disabledGroupList)) {
				$disabledGroupList = array();
			}

			$enabledGroupList = @unserialize($postSettings['enabled_groups']);
			if (!is_array($enabledGroupList)) {
				$enabledGroupList = array();
			}

			$disabledMobileGroupList = @unserialize($postSettings['disabled_mobile_groups']);
			if (!is_array($disabledMobileGroupList)) {
				$disabledMobileGroupList = array();
			}

			$enabledMobileGroupList = @unserialize($postSettings['enabled_mobile_groups']);
			if (!is_array($enabledMobileGroupList)) {
				$enabledMobileGroupList = array();
			}

			$permalinkFilter = $postSettings['permalink'];
			$secure = $postSettings['secure'];

			$postType = get_post_type($post->ID);
			$postTypeObject = get_post_type_object($postType);
			if (isset($postTypeObject->labels->name)) {
				$postTypeName = $postTypeObject->labels->singular_name;
			} else {
				$postTypeName = 'Post';
			}

			$duplicates = $this->find_duplicate_permalinks($post->ID, $postSettings['permalink']);
			if (sizeOf($duplicates) > 0) {
				foreach($duplicates as $dup) {
					$errMsg .= 'There is another Plugin Filter with the same permalink.  <a href="' . get_admin_url() . 'post.php?post=' . $dup . '&action=edit">Edit Duplicate</a><br />';
				}
			}
		} else {
			$filterName = "";
			$affectChildren = 0;
			$disabledPluginList = array();
			$enabledPluginList = array();
			$disabledMobilePluginList = array();
			$enabledMobilePluginList = array();
			$disabledGroupList = array();
			$enabledGroupList = array();
			$disabledMobileGroupList = array();
			$enabledMobileGroupList = array();
			$permalinkFilter = "";
			$secure=0;
			$postTypeName = 'Post';
		}
		
		if ($post->post_type == 'plugin_filter') {
			$fuzzyPermalink = $permalinkFilter;
		} else {
			$fuzzyPermalink = preg_replace('/^.{1,5}:\/\//', '', get_permalink($post->ID));
		}
		
		#Find and apply parent settings
		if ($fuzzyPermalink != '' && get_option("PO_fuzzy_url_matching") == "1" && sizeof($disabledPluginList) == 0 && sizeof($enabledPluginList) == 0 && sizeof($disabledGroupList) == 0 && sizeof($enabledGroupList) == 0) {
			$fuzzyPluginList = $this->find_parent_plugins($post->ID, $fuzzyPermalink, 0, $secure);
			$disabledPluginList = $fuzzyPluginList['plugins']['disabled_plugins'];
			$enabledPluginList = $fuzzyPluginList['plugins']['enabled_plugins'];
			$disabledGroupList = $fuzzyPluginList['plugins']['disabled_groups'];
			$enabledGroupList = $fuzzyPluginList['plugins']['enabled_groups'];
			if ($fuzzyPluginList['post_id'] > 0) {
				$errMsg .= 'There is a parent affecting the standard plugins on this '. $postTypeName . '.  To edit it click <a href="' . get_admin_url() . 'post.php?post=' . $fuzzyPluginList['post_id'] . '&action=edit">here</a>.<br />';
			}
		}



		#Find and apply parent settings to mobile plugins
		if ($fuzzyPermalink != '' && get_option('PO_disable_mobile_plugins') == '1' && get_option("PO_fuzzy_url_matching") == "1" && sizeof($disabledMobilePluginList) == 0 && sizeof($enabledMobilePluginList) == 0 && sizeof($disabledMobileGroupList) == 0 && sizeof($enabledMobileGroupList) == 0) {
			$fuzzyPluginList = $this->find_parent_plugins($post->ID, $fuzzyPermalink, 1, $secure);
			$disabledMobilePluginList = $fuzzyPluginList['plugins']['disabled_plugins'];
			$enabledMobilePluginList = $fuzzyPluginList['plugins']['enabled_plugins'];
			$disabledMobileGroupList = $fuzzyPluginList['plugins']['disabled_groups'];
			$enabledMobileGroupList = $fuzzyPluginList['plugins']['enabled_groups'];
			if ($fuzzyPluginList['post_id'] > 0) {
				$errMsg .= 'There is a parent affecting the mobile plugins on this '. $postTypeName . '.  To edit it click <a href="' . get_admin_url() . 'post.php?post=' . $fuzzyPluginList['post_id'] . '&action=edit">here</a>.<br />';
			}
		}




		
		$globalPlugins = get_option('PO_disabled_plugins');
		if (!is_array($globalPlugins)) {
			$globalPlugins = array();
		}

		$globalMobilePlugins = get_option('PO_disabled_mobile_plugins');
		if (!is_array($globalMobilePlugins)) {
			$globalMobilePlugins = array();
		}

		$globalGroups = get_option('PO_disabled_groups');
		if (!is_array($globalGroups)) {
			$globalGroups = array();
		}

		$globalMobileGroups = get_option('PO_disabled_mobile_groups');
		if (!is_array($globalMobileGroups)) {
			$globalMobileGroups = array();
		}
		
		
		$plugins = $this->reorder_plugins(get_plugins());
		
		$activePlugins = $this->get_active_plugins();
		$activeSitewidePlugins = array_keys((array) get_site_option('active_sitewide_plugins', array()));
		
		$groupList = get_posts(array('posts_per_page'=>-1, 'post_type'=>'plugin_group'));
		if (get_option("PO_disable_plugins") != 1) {
			$errMsg .= 'You currently have Selective Plugin Loading disabled.  None of the changes you make here will have any affect on what plugins are loaded until you enable it.  You can enable it by going to the <a href="' . get_admin_url() . 'admin.php?page=Plugin_Organizer">settings page</a> and clicking enable under Selective Plugin Loading.';
		}
		
		require_once($this->absPath . "/tpl/postMetaBox.php");
	}

	function change_plugin_filter_title($title) {
		global $post;
		$supportedPostTypes = get_option("PO_custom_post_type_support");
		$supportedPostTypes[] = 'plugin_filter';
		if ( is_object($post) && ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post->ID) || !current_user_can( 'edit_post', $post->ID ) || !current_user_can( 'activate_plugins' ) || !in_array(get_post_type($post->ID), $supportedPostTypes) || !isset($_POST['poSubmitPostMetaBox']))) {
			return $title;
		}
		
		if (is_object($post) && get_post_type($post->ID) == 'plugin_filter') {
			if (isset($_POST['filterName']) && $_POST['filterName'] != '') {
				return $_POST['filterName'];
			} else if (!isset($_POST['permalinkFilter']) || $_POST['permalinkFilter'] == '') {
				$randomTitle = "";
				for($i=0; $i<10; $i++) {
					$randomTitle .= chr(mt_rand(109,122));
				}
				return $randomTitle;
			} else {
				return $_POST['permalinkFilter'];
			}
		} else {
			return $title;
		}
	}
	
	function save_post_meta_box($post_id) {
		global $wp_rewrite, $wpdb;

		$supportedPostTypes = get_option("PO_custom_post_type_support");
		$supportedPostTypes[] = 'plugin_filter';
		if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id) || !current_user_can( 'edit_post', $post_id ) || !current_user_can( 'activate_plugins' ) || !in_array(get_post_type($post_id), $supportedPostTypes) || !isset($_POST['poSubmitPostMetaBox'])) {
			return $post_id;
		}

		$postExists = ($wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$wpdb->prefix."PO_plugins WHERE post_id=%d", $post_id)) > 0) ? 1 : 0;
		
		if (isset($_POST['affectChildren'])) {
			$affectChildren = 1;
		} else {
			$affectChildren = 0;
		}			
		
		$globalPlugins = get_option('PO_disabled_plugins');
		if (!is_array($globalPlugins)) {
			$globalPlugins = array();
		}
		$checkPluginList = (isset($_POST['pluginsList'])) ? $_POST['pluginsList'] : '';
		
		$tempPluginList = $this->create_plugin_lists($checkPluginList, $globalPlugins);
		$disabledPlugins = $tempPluginList[0];
		$enabledPlugins = $tempPluginList[1];
		

		### Mobile plugins
		if (get_option('PO_disable_mobile_plugins') == 1) {
			$globalMobilePlugins = get_option('PO_disabled_mobile_plugins');
			if (!is_array($globalMobilePlugins)) {
				$globalMobilePlugins = array();
			}
			$checkPluginList = (isset($_POST['mobilePluginsList'])) ? $_POST['mobilePluginsList'] : '';
			$tempPluginList = $this->create_plugin_lists($checkPluginList, $globalMobilePlugins);
			$disabledMobilePlugins = $tempPluginList[0];
			$enabledMobilePlugins = $tempPluginList[1];
		} else {
			$disabledMobilePlugins = array();
			$enabledMobilePlugins = array();
		}

		
		##Groups
		$globalGroups = get_option('PO_disabled_groups');
		if (!is_array($globalGroups)) {
			$globalGroups = array();
		}
		$checkPluginList = (isset($_POST['pluginGroupList'])) ? $_POST['pluginGroupList'] : '';
		
		$tempPluginList = $this->create_plugin_lists($checkPluginList, $globalGroups);
		$disabledGroups = $tempPluginList[0];
		$enabledGroups = $tempPluginList[1];


		##Mobile Groups
		if (get_option('PO_disable_mobile_plugins') == 1) {
			$globalMobileGroups = get_option('PO_disabled_mobile_groups');
			if (!is_array($globalMobileGroups)) {
				$globalMobileGroups = array();
			}
			$checkPluginList = (isset($_POST['mobilePluginGroupList'])) ? $_POST['mobilePluginGroupList'] : '';
		
			$tempPluginList = $this->create_plugin_lists($checkPluginList, $globalMobileGroups);
			$disabledMobileGroups = $tempPluginList[0];
			$enabledMobileGroups = $tempPluginList[1];
		} else {
			$disabledMobileGroups = array();
			$enabledMobileGroups = array();
		}
		
		
		$postStatus = get_post_status($post_id);
		if (!$postStatus) {
			$postStatus = 'publish';
		}

		if (get_post_type($post_id) != 'plugin_filter') {
			$permalink = get_permalink($post_id);
		} else {
			$permalink = $this->fix_trailng_slash($_POST['permalinkFilter']);
		}

		$secure=0;
		if (preg_match('/^.{1,5}:\/\//', $permalink, $matches)) {
			switch ($matches[0]) {
				case "https://":
					$secure=1;
					break;
				default:
					$secure=0;
			}
		}

		$permalink = preg_replace('/^.{1,5}:\/\//', '', $permalink);
		
		$permalinkNoArgs = preg_replace('/\?.*$/', '', $permalink);
		
		$disabledPluginsAfterParent = array();
		$enabledPluginsAfterParent = array();
		$disabledGroupsAfterParent = array();
		$enabledGroupsAfterParent = array();
		if ($permalink != '' && get_option("PO_fuzzy_url_matching") == "1") {
			$fuzzyPluginList = $this->find_parent_plugins($post_id, $permalink, 0, $secure);
			foreach ($disabledPlugins as $plugin) {
				if (!in_array($plugin, $fuzzyPluginList['plugins']['disabled_plugins'])) {
					$disabledPluginsAfterParent[] = $plugin;
				}
			}

			foreach ($enabledPlugins as $plugin) {
				if (!in_array($plugin, $fuzzyPluginList['plugins']['enabled_plugins'])) {
					$enabledPluginsAfterParent[] = $plugin;
				}
			}

			foreach ($disabledGroups as $group) {
				if (!in_array($group, $fuzzyPluginList['plugins']['disabled_groups'])) {
					$disabledGroupsAfterParent[] = $group;
				}
			}

			foreach ($enabledGroups as $group) {
				if (!in_array($group, $fuzzyPluginList['plugins']['enabled_groups'])) {
					$enabledGroupsAfterParent[] = $group;
				}
			}

			if (sizeof($disabledPluginsAfterParent) == 0 && sizeof($enabledPluginsAfterParent) == 0 && sizeof($disabledGroupsAfterParent) == 0 && sizeof($enabledGroupsAfterParent) == 0) {
				$disabledPlugins = array();
				$enabledPlugins = array();
				$disabledGroups = array();
				$enabledGroups = array();
			}

			$disabledMobilePluginsAfterParent = array();
			$enabledMobilePluginsAfterParent = array();
			$disabledMobileGroupsAfterParent = array();
			$enabledMobileGroupsAfterParent = array();
			$fuzzyMobilePluginList = $this->find_parent_plugins($post_id, $permalink, 1, $secure);
			foreach ($disabledMobilePlugins as $plugin) {
				if (!in_array($plugin, $fuzzyMobilePluginList['plugins']['disabled_plugins'])) {
					$disabledMobilePluginsAfterParent[] = $plugin;
				}
			}

			foreach ($enabledMobilePlugins as $plugin) {
				if (!in_array($plugin, $fuzzyMobilePluginList['plugins']['enabled_plugins'])) {
					$enabledMobilePluginsAfterParent[] = $plugin;
				}
			}

			foreach ($disabledMobileGroups as $group) {
				if (!in_array($group, $fuzzyMobilePluginList['plugins']['disabled_groups'])) {
					$disabledMobileGroupsAfterParent[] = $group;
				}
			}

			foreach ($enabledMobileGroups as $group) {
				if (!in_array($group, $fuzzyMobilePluginList['plugins']['enabled_groups'])) {
					$enabledMobileGroupsAfterParent[] = $group;
				}
			}

			if (sizeof($disabledMobilePluginsAfterParent) == 0 && sizeof($enabledMobilePluginsAfterParent) == 0 && sizeof($disabledMobileGroupsAfterParent) == 0 && sizeof($enabledMobileGroupsAfterParent) == 0) {
				$disabledMobilePlugins = array();
				$enabledMobilePlugins = array();
				$disabledMobileGroups = array();
				$enabledMobileGroups = array();
			}
		}
		
		
		
		if (sizeof($enabledPlugins) > 0 || sizeof($disabledPlugins) > 0 || sizeof($enabledMobilePlugins) > 0 || sizeof($disabledMobilePlugins) > 0 || sizeof($enabledGroups) > 0 || sizeof($disabledGroups) > 0 || sizeof($enabledMobileGroups) > 0 || sizeof($disabledMobileGroups) > 0 || get_post_type($post_id) == "plugin_filter") {
			if ($postExists == 1) {
				$wpdb->update($wpdb->prefix."PO_plugins", array("permalink"=>$permalink, "permalink_hash"=>md5($permalinkNoArgs), "permalink_hash_args"=>md5($permalink), "children"=>$affectChildren, "enabled_plugins"=>serialize($enabledPlugins), "disabled_plugins"=>serialize($disabledPlugins), "enabled_mobile_plugins"=>serialize($enabledMobilePlugins), "disabled_mobile_plugins"=>serialize($disabledMobilePlugins), "enabled_groups"=>serialize($enabledGroups), "disabled_groups"=>serialize($disabledGroups), "enabled_mobile_groups"=>serialize($enabledMobileGroups), "disabled_mobile_groups"=>serialize($disabledMobileGroups), "secure"=>$secure, "post_type"=>get_post_type($post_id), "status"=>$postStatus), array("post_id"=>$post_id));
			} else {
				$wpdb->insert($wpdb->prefix."PO_plugins", array("post_id"=>$post_id, "permalink"=>$permalink, "permalink_hash"=>md5($permalinkNoArgs), "permalink_hash_args"=>md5($permalink), "children"=>$affectChildren, "enabled_plugins"=>serialize($enabledPlugins), "disabled_plugins"=>serialize($disabledPlugins), "enabled_mobile_plugins"=>serialize($enabledMobilePlugins), "disabled_mobile_plugins"=>serialize($disabledMobilePlugins), "enabled_groups"=>serialize($enabledGroups), "disabled_groups"=>serialize($disabledGroups), "enabled_mobile_groups"=>serialize($enabledMobileGroups), "disabled_mobile_groups"=>serialize($disabledMobileGroups), "secure"=>$secure, "post_type"=>get_post_type($post_id), "status"=>$postStatus));
			}
		} else if ($postExists == 1) {
			$deletePluginQuery = "DELETE FROM ".$wpdb->prefix."PO_plugins WHERE post_id = %d";
			$wpdb->query($wpdb->prepare($deletePluginQuery, $post_id));
		}
	}
	
	function delete_plugin_lists($post_id) {
		global $wpdb;
		if ( !current_user_can( 'activate_plugins', $post_id ) ) {
			return $post_id;
		}
		if (is_numeric($post_id)) {
			$deletePluginQuery = "DELETE FROM ".$wpdb->prefix."PO_plugins WHERE post_id = %d";
			$wpdb->query($wpdb->prepare($deletePluginQuery, $post_id));
		}
	}
	
	function redo_permalinks() {
		global $wpdb;
		if (!empty($_POST['old_site_address'])) {
			$oldSiteAddress = preg_quote($this->fix_trailng_slash($_POST['old_site_address']), "/");
		} else {
			$oldSiteAddress = "";
		}

		if (!empty($_POST['new_site_address'])) {
			$newSiteAddress = $this->fix_trailng_slash($_POST['new_site_address']);
		} else {
			$newSiteAddress = "";
		}
		
		$failedCount = 0;
		$updatedCount = 0;
		$noUpdateCount = 0;
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$postIDsQuery = "SELECT post_id FROM ".$wpdb->prefix."PO_plugins WHERE post_type != 'plugin_filter'";
		$postIDs = $wpdb->get_results($postIDsQuery, ARRAY_A);
		foreach ($postIDs as $postID) {
			$post = get_post($postID['post_id']);
			if (!is_null($post)) {
				$secure=0;
				if (preg_match('/^.{1,5}:\/\//', get_permalink($post->ID), $matches)) {
					switch ($matches[0]) {
						case "https://":
							$secure=1;
							break;
						default:
							$secure=0;
					}
				}
				$permalink = preg_replace('/^.{1,5}:\/\//', '', get_permalink($post->ID));
				
				if ($permalink != $wpdb->get_var("SELECT permalink FROM ".$wpdb->prefix."PO_plugins WHERE post_id=".$post->ID)) {
					
					if ($wpdb->get_var("SELECT count(*) FROM ".$wpdb->prefix."PO_plugins WHERE post_id=".$post->ID) > 0) {
						if($wpdb->update($wpdb->prefix."PO_plugins", array('permalink'=>$permalink, 'permalink_hash'=>md5($permalink), 'permalink_hash_args'=>md5($permalink), 'secure'=>$secure), array("post_id"=>$post->ID))) {
							$updatedCount++;
						} else {
							$failedCount++;
						}
					} else {
						if ($wpdb->insert($wpdb->prefix."PO_plugins", array("enabled_mobile_plugins"=>serialize(array()), "disabled_mobile_plugins"=>serialize(array()), "enabled_plugins"=>serialize(array()), "disabled_plugins"=>serialize(array()), "post_type"=>get_post_type($post->ID), "permalink"=>$permalink, "permalink_hash"=>md5($permalink), "permalink_hash_args"=>md5($permalink), "children"=>0, "secure"=>$secure, "post_id"=>$post->ID))) {
							$updatedCount++;
						} else {
							$failedCount++;
						}
					}
				} else {
					$noUpdateCount++;
				}
			} else {
				$failedCount++;
			}
		}

		if ($oldSiteAddress != "" && $newSiteAddress != "") {
			$filterQuery = "SELECT * FROM ".$wpdb->prefix."PO_plugins WHERE post_type = 'plugin_filter'";
			$filters = $wpdb->get_results($filterQuery, ARRAY_A);
			foreach ($filters as $filter) {
				$filterObject = get_post($filter['post_id']);
				if (!is_null($filterObject)) {
					$permalink = preg_replace("/^".$oldSiteAddress."/", "".$newSiteAddress."", $filter['permalink']);
					if ($wpdb->update($wpdb->prefix."PO_plugins", array('permalink'=>$permalink, 'permalink_hash'=>md5($permalink), 'permalink_hash_args'=>md5($permalink)), array("post_id"=>$filter['post_id']))) {
						$updatedCount++;
					} else {
						$failedCount++;
					}
				}
			}
		} else {
			print "Plugin Filters were not updated since the new or old address was blank.\n\n";
		}

		if ($failedCount > 0) {
			print $failedCount . " permalinks failed to update!\n";
			print $updatedCount . " permalinks were updated successfully.\n";
			print $noUpdateCount . " permalinks were already up to date.";
		} else {
			print $updatedCount . " permalinks were updated successfully.\n";
			print $noUpdateCount . " permalinks were already up to date.";
		}
		die();
	}

	function add_custom_post_type_support() {
		$failedCount = 0;
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		if (isset($_POST['PO_cutom_post_type']) && is_array($_POST['PO_cutom_post_type'])) {
			$submittedPostTypes = $_POST['PO_cutom_post_type'];
		} else {
			$submittedPostTypes = array();
		}
		
		update_option("PO_custom_post_type_support", $submittedPostTypes);
		if (sizeof(array_diff(get_option("PO_custom_post_type_support"), $submittedPostTypes)) == 0) {
			print "Post types saved.";
		} else {
			print "Saving post types failed!";
		}
		die();
	}

	function reset_plugin_order() {
		$activePlugins = $this->get_active_plugins();
		usort($activePlugins, array($this, 'custom_sort_plugins'));
		update_option("active_plugins", $activePlugins);
		update_option("PO_plugin_order", $activePlugins);
		print "The order has been reset.";
		die();
	}

	function custom_sort_plugins($a, $b) { 
		$aData = get_plugin_data(WP_PLUGIN_DIR.'/'.$a);
		$bData = get_plugin_data(WP_PLUGIN_DIR.'/'.$b);
		return strcasecmp($aData['Name'], $bData['Name']);
	}
	
	function recreate_plugin_order() {
		$plugins = $this->get_active_plugins();
		$pluginOrder = get_option("PO_plugin_order");
		$newPlugArray = $plugins;
		$activePlugins = $plugins;
		if (is_array($pluginOrder) && sizeof(array_diff_assoc($plugins, $pluginOrder)) > 0) {
			$newPlugins = array_diff($plugins, $pluginOrder);
			foreach ($newPlugins as $newPlug) {
				$pluginOrder[] = $newPlug;
			}
			$pluginLoadOrder = Array();
			$activePlugins = array();
			foreach ($plugins as $val) {
				$activePlugins[] = $val;
				$pluginLoadOrder[] = array_search($val, $pluginOrder);
			}
			array_multisort($pluginLoadOrder, $activePlugins);
			update_option("active_plugins", $activePlugins);
			update_option("PO_plugin_order", $activePlugins);
		}
	}

	function manage_mu_plugin() {
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$result = "";
		if ($_POST['selected_action'] == 'delete') {
			if (file_exists(WPMU_PLUGIN_DIR . "/PluginOrganizerMU.class.php")) {
				if (@unlink(WPMU_PLUGIN_DIR . "/PluginOrganizerMU.class.php")) {
					$result = "The MU plugin component has been removed.";
				} else {
					$result = "There was an issue removing the MU plugin component!";
				}
			} else {
				$result = "There was an issue removing the MU plugin component!";
			}
		} else if ($_POST['selected_action'] == 'move') {
			if (!file_exists(WPMU_PLUGIN_DIR)) {
				@mkdir(WPMU_PLUGIN_DIR);
			}
			if (file_exists(WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__)) . "/PluginOrganizerMU.class.php")) {
				@unlink(WPMU_PLUGIN_DIR . "/PluginOrganizerMU.class.php");
				@copy(WP_PLUGIN_DIR . "/" . plugin_basename(dirname(__FILE__)) . "/PluginOrganizerMU.class.php", WPMU_PLUGIN_DIR . "/PluginOrganizerMU.class.php");
			}
			if (file_exists(WPMU_PLUGIN_DIR . "/PluginOrganizerMU.class.php")) {
				$result = "The MU plugin component has been moved to the mu-plugins folder.";
			} else {
				$result = "There was an issue moving the MU plugin component!";
			}
		}
		print $result;
		die();
	}

	function set_ignore_protocol() {
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$result = "";
		if (preg_match("/^(1|0)$/", $_POST['PO_ignore_protocol'])) {
			update_option("PO_ignore_protocol", $_POST['PO_ignore_protocol']);
			$result = "Update was successful.";
		} else {
			$result = "Update failed.";
		}
		print $result;
		die();
	}

	function set_ignore_arguments() {
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$result = "";
		if (preg_match("/^(1|0)$/", $_POST['PO_ignore_arguments'])) {
			update_option("PO_ignore_arguments", $_POST['PO_ignore_arguments']);
			$result = "Update was successful.";
		} else {
			$result = "Update failed.";
		}
		print $result;
		die();
	}



	function set_fuzzy_url_matching() {
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$result = "";
		if (preg_match("/^(1|0)$/", $_POST['PO_fuzzy_url_matching'])) {
			update_option("PO_fuzzy_url_matching", $_POST['PO_fuzzy_url_matching']);
			$result = "Update was successful.";
		} else {
			$result = "Update failed.";
		}
		print $result;
		die();
	}

	function set_disable_plugin_settings() {
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		
		$result = "";
		if (preg_match("/^(1|0)$/", $_POST['PO_disable_plugins'])) {
			update_option("PO_disable_plugins", $_POST['PO_disable_plugins']);
			$result .= "The selective plugin loading setting was saved successfully.\n";
		} else {
			$result .= "There was a problem saving the selective plugin loading setting.\n";
		}

		if (preg_match("/^(1|0)$/", $_POST['PO_disable_mobile_plugins'])) {
			update_option("PO_disable_mobile_plugins", $_POST['PO_disable_mobile_plugins']);
			$result .= "The selective mobile plugin loading setting was saved successfully.\n";
		} else {
			$result .= "There was a problem saving the selective mobile plugin loading setting.\n";
		}

		if (preg_match("/^(1|0)$/", $_POST['PO_admin_disable_plugins'])) {
			update_option("PO_admin_disable_plugins", $_POST['PO_admin_disable_plugins']);
			$result .= "The admin areas setting was saved successfully.\n";
		} else {
			$result .= "There was a problem saving the admin areas setting.\n";
		}
		print $result;
		die();
	}

	function set_preserve_settings() {
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		$result = "";
		if (preg_match("/^(1|0)$/", $_POST['PO_preserve_settings'])) {
			update_option('PO_preserve_settings', $_POST['PO_preserve_settings']);
			$result = "Update was successful.";
		} else {
			$result = "Update failed.";
		}
		print $result;
		die();
	}

	function save_mobile_user_agents() {
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		
		$userAgents = preg_replace("/\\r\\n/", "\n", $_POST['PO_mobile_user_agents']);
		$userAgents = explode("\n", $userAgents);
		if (!is_array($userAgents)) {
			$userAgents = array();
		}
		foreach ($userAgents as $key=>$agent) {
			if ($agent == '') {
				unset($userAgents[$key]);
			}
		}
		
		if (update_option('PO_mobile_user_agents', $userAgents)) {
			print "The user agents were saved.";
		}
		die();
	}
	
	function submit_order_access_net_admin() {
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		
		if (preg_match("/^(1|0)$/", $_POST['PO_order_access_net_admin'])) {
			update_option('PO_order_access_net_admin', $_POST['PO_order_access_net_admin']);
			$result = "Update was successful.";
		} else {
			$result = "Update failed.";
		}
		print $result;
		die();
	}

	function submit_admin_css_settings() {
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}
		
		if (preg_match("/^(#[0-9A-Za-z]{0,6}|)$/", $_POST['PO_network_active_plugins_color'])) {
			update_option('PO_network_active_plugins_color', $_POST['PO_network_active_plugins_color']);
			$result = "Update was successful.";
		} else {
			$result = "Update failed.";
		}
		print $result;
		die();
	}
		
	function reset_post_settings() {
		global $wpdb;
		if ( !current_user_can( 'activate_plugins' ) || !wp_verify_nonce( $_POST['PO_nonce'], plugin_basename(__FILE__) )) {
			print "You dont have permissions to access this page.";
			die();
		}

		if (is_numeric($_POST['postID'])) {
			if ($wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$wpdb->prefix."PO_plugins WHERE post_id=%d", $_POST['postID'])) > 0) {
				$deletePluginQuery = "DELETE FROM ".$wpdb->prefix."PO_plugins WHERE post_id = %d";
				print $wpdb->query($wpdb->prepare($deletePluginQuery, $_POST['postID']));
			} else {
				print -1;
			}
		}
		die();
	}
	
	function plugin_filter_sort($columns) {
		$custom = array(
			'taxonomy-filter_group' => 'taxonomy-filter_group',
			'PO_PF_permalink' => 'PO_PF_permalink'
		);
		return wp_parse_args($custom, $columns);
	}

	function register_taxonomy() {
		$labels = array(
			'name' => _x( 'Filter Groups', 'taxonomy general name' ),
			'singular_name' => _x( 'Filter Group', 'taxonomy singular name' ),
			'search_items' =>  __( 'Search Filter Groups' ),
			'all_items' => __( 'All Filter Groups' ),
			'parent_item' => __( 'Parent Filter Group' ),
			'parent_item_colon' => __( 'Parent Filter Group:' ),
			'edit_item' => __( 'Edit Filter Group' ),
			'update_item' => __( 'Update Filter Group' ),
			'add_new_item' => __( 'Add New Filter Group' ),
			'new_item_name' => __( 'New Filter Group Name' )
		);

		$settings = array(
			'hierarchical' => true,
			'public' => false,
			'capability_type' => 'filter_group',
			'show_admin_column' => true,
			'labels' => $labels,
			'show_ui' => true,
			'capabilities' => array('assign_terms'=>'edit_filter_group','manage_terms' => 'manage_filter_groups','edit_terms' => 'manage_filter_groups','delete_terms' => 'manage_filter_groups'),
			'rewrite' => array( 'slug' => 'filter_group' )
		);

		register_taxonomy('filter_group', array('plugin_filter'), $settings);
	}
	
	function register_type() {
		$labels = array(
			'name' => _x('Plugin Filters', 'post type general name'),
			'singular_name' => _x('Plugin Filter', 'post type singular name'),
			'add_new' => _x('Add Plugin Filter', 'neo_theme'),
			'add_new_item' => __('Add New Plugin Filter'),
			'edit_item' => __('Edit Plugin Filter'),
			'new_item' => __('New Plugin Filter'),
			'view_item' => __('View Plugin Filter'),
			'search_items' => __('Search Plugin Filter'),
			'not_found' =>  __('No Plugin Filters found'),
			'not_found_in_trash' => __('No Plugin Filters found in Trash'), 
			'parent_item_colon' => 'Parent Plugin Filter:',
			'parent' => 'Parent Plugin Filter'
		);
		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true, 
			'menu_icon' => $this->urlPath . '/image/po-icon-16x16.png', 		
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('custom-fields'),
			'capability_type' => 'plugin_filter',
			'show_in_menu' => 'Plugin_Organizer'
		); 
		register_post_type('plugin_filter',$args);
		
		$labels = array(
			'name' => _x('Plugin Groups', 'post type general name'),
			'singular_name' => _x('Plugin Group', 'post type singular name'),
			'add_new' => _x('Add Plugin Group', 'neo_theme'),
			'add_new_item' => __('Add New Plugin Group'),
			'edit_item' => __('Edit Plugin Group'),
			'new_item' => __('New Plugin Group'),
			'view_item' => __('View Plugin Group'),
			'search_items' => __('Search Plugin Group'),
			'not_found' =>  __('No PPlugin Groups found'),
			'not_found_in_trash' => __('No Plugin Groups found in Trash'), 
			'parent_item_colon' => 'Parent Plugin Group:',
			'parent' => 'Parent Plugin Group'
		);
		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false, 
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('custom-fields'),
			'capability_type' => 'plugin_group'
		); 
		register_post_type('plugin_group',$args);
	}
	
	function custom_updated_messages( $messages ) {
		global $post, $post_ID;
		$messages['plugin_filter'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __('Plugin Filter updated.'), esc_url( get_permalink($post_ID) ) ),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __('Plugin Filter updated.'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __('Plugin Filter restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Plugin Filter published.'), esc_url( get_permalink($post_ID) ) ),
			7 => __('theme saved.'),
			8 => sprintf( __('Plugin Filter submitted.'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __('Plugin Filter scheduled for: <strong>%1$s</strong>.'),
			  // translators: Publish box date format, see http://php.net/date
			  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Plugin Filter draft updated.'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);

		$messages['plugin_group'] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf( __('Plugin Group updated.'), esc_url( get_permalink($post_ID) ) ),
			2 => __('Custom field updated.'),
			3 => __('Custom field deleted.'),
			4 => __('Plugin Group updated.'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __('Plugin Group restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __('Plugin Group published.'), esc_url( get_permalink($post_ID) ) ),
			7 => __('theme saved.'),
			8 => sprintf( __('Plugin Group submitted.'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			9 => sprintf( __('Plugin Group scheduled for: <strong>%1$s</strong>.'),
			  // translators: Publish box date format, see http://php.net/date
			  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
			10 => sprintf( __('Plugin Group draft updated.'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);
		return $messages;
	}

	function deactivated_plugin($plugin, $networkWide = null) {
		global $wpdb;
		if ($networkWide != null) {
			$sites = $wpdb->get_results("SELECT blog_id FROM ".$wpdb->base_prefix."blogs");
			foreach ($sites as $site) {
				if (switch_to_blog($site->blog_id)) {
					$activePlugins = $this->get_active_plugins();
					$activePlugins = array_values(array_diff($activePlugins, array($plugin)));
					update_option('active_plugins', $activePlugins);
				}
			}
			restore_current_blog();
		}
	}

	function activated_plugin($plugin, $networkWide = null) {
		global $wpdb;
		if ($networkWide != null) {
			$sites = $wpdb->get_results("SELECT blog_id FROM ".$wpdb->base_prefix."blogs");
			foreach ($sites as $site) {
				if (switch_to_blog($site->blog_id)) {
					$activePlugins = $this->get_active_plugins();
					if (!in_array($plugin, $activePlugins)) {
						$activePlugins[] = $plugin;
						update_option('active_plugins', $activePlugins);
					}
				}
			}
			restore_current_blog();
		}
	}

	function update_post_status($newStatus, $oldStatus, $post) {
		global $wpdb;
		$wpdb->update($wpdb->prefix."PO_plugins", array("status"=>$newStatus), array("post_id"=>$post->ID));
	}

	function fix_trailng_slash($permalink) {
		global $wpdb;
		if ($permalink == '') { return $permalink; }
		
		$wpDomain = preg_replace(array('/^(https?:\/\/)?/', '/\/$/'), array('',''), get_bloginfo('url'));
		$wpAdminURL = preg_replace('/^(https?:\/\/)?/', '', admin_url());
		
		$permalinkNoProtocol = preg_replace('/^(https?:\/\/)?/', '', $permalink);
		$filePath = preg_replace('/^(https?:\/\/)?'.preg_quote($wpDomain, '/').'\/?/', '', $permalink);
		
		##get unfiltered siteurl value directly from database since wordpress won't let you have it any other way.  This includes the trailing slash if set in the options.
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'siteurl' ) );
		$realSiteUrl = '';
		if (is_object($row)) {
			$realSiteUrl = preg_replace('/^(https?:\/\/)?/', '', $row->option_value);
			if ($_SERVER['HTTP_HOST'] == preg_replace('/\/$/', '', $realSiteUrl)) {
				$realSiteUrl = trailingslashit($realSiteUrl);
			}
		}
		if (!is_file(get_home_path() . $filePath) && !preg_match('/^'.preg_quote($wpAdminURL, '/').'/', $permalinkNoProtocol) && strpos($permalink, "?") === FALSE) {
			if (preg_replace('/\/$/', '', $realSiteUrl) == preg_replace('/\/$/', '', $permalinkNoProtocol)) {
				if (preg_match('/\/$/', $realSiteUrl)) {
					$permalink = trailingslashit($permalink);
				} else {
					$permalink = untrailingslashit($permalink);
				}
			} else {
				$permalink = user_trailingslashit($permalink);
			}
		}
		return $permalink;
	}

	function check_for_missing_posts() {
		global $wpdb;
		if (false === get_site_transient('PO_delete_missing_posts')) {
			$allPostsQuery = "SELECT post_id FROM ".$wpdb->prefix."PO_plugins";
			$allPosts = $wpdb->get_results($allPostsQuery, ARRAY_A);
			foreach ($allPosts as $post) {
				if (false === get_post_status($post['post_id'])) {
					$deletePluginQuery = "DELETE FROM ".$wpdb->prefix."PO_plugins WHERE post_id = %d";
					$wpdb->query($wpdb->prepare($deletePluginQuery, $post['post_id']));
				}
			}
			set_site_transient('PO_delete_missing_posts', 1, 604800);
		}
	}
	
	function sort_posts($a, $b) {
		if ($a['post_type'] == 'plugin_filter' && $b['post_type'] != 'plugin_filter') {
			return 1;
		} else if($a['post_type'] != 'plugin_filter' && $b['post_type'] == 'plugin_filter') {
			return -1;
		} else {
			return 0;
		}
	}
}
?>