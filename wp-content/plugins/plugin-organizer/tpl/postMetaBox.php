<?php
if ($errMsg != "") {
	?>
	<h3 style="color: #CC0066;"><?php print $errMsg; ?></h3>
	<?php
}
?>
<?php if (!isset($_GET['page']) || $_GET['page'] != 'PO_search_plugins') { ?>
	<?php if(get_post_type($post->ID) == 'plugin_filter') { ?>
		<div class="metaBoxLabel">
			Name
		</div>
		<div class="metaBoxContent">
			<input type="text" class="poFilterNameInput" size="25" name="filterName" value="<?php print $filterName; ?>">
		</div>
		<div class="metaBoxLabel">
			Permalink
		</div>
		<div class="metaBoxContent">
			<input type="text" class="poPermalinkInput" size="25" name="permalinkFilter" value="<?php print ($permalinkFilter != "") ? (($secure == 1)? "https://":"http://") . $permalinkFilter : ""; ?>">
		</div>
	<?php } ?>

	<div class="metaBoxLabel">
		Settings
	</div>
	<div class="metaBoxContent">
		NOTE:  By checking this box the plugins disabled or enabled for this page will be used for its children if they have nothing set.
		<hr>
		<input type="checkbox" id="affectChildren" name="affectChildren" value="1" <?php print ($affectChildren == "1")? 'checked="checked"':''; ?>>Also Affect Children
		<hr>
		<input type="button" class="button" style="margin: 5px;" id="resetPostSettings" value="Reset settings for this post" onclick="PO_reset_post_settings(<?php print $post->ID; ?>);">
	</div>
<?php } ?>
<div class="metaBoxLabel">
	Plugins
</div>
<div id="pluginContainer" class="metaBoxContent">
	<div class="pluginWrap">
		<div class="toggleContainer">
			Standard<br />
			<input type="checkbox" id="toggleAllPlugins" name="toggleAllPlugins" value="">
			<img src="<?php print $this->urlPath; ?>/image/toggle-on-button.png" class="pluginsButton pluginsButtonOn" alt="On" id="toggleAllPluginsButton" onclick="PO_toggle_on_off('toggleAllPluginsButton', 'toggleAllPlugins', 'toggle-');PO_toggle_all('toggleAllPlugins', 'pluginsList', 'pluginsButton_', 'plugins_');" />
		</div>
		<?php if (get_option('PO_disable_mobile_plugins') == 1) { ?>
			<div class="toggleContainer">
				Mobile<br />
				<input type="checkbox" id="toggleAllMobilePlugins" name="toggleAllMobilePlugins" value="">
				<img src="<?php print $this->urlPath; ?>/image/toggle-on-button.png" class="pluginsButton pluginsButtonOn" alt="On" id="toggleAllMobilePluginsButton" onclick="PO_toggle_on_off('toggleAllMobilePluginsButton', 'toggleAllMobilePlugins', 'toggle-');PO_toggle_all('toggleAllMobilePlugins', 'mobilePluginsList', 'mobilePluginsButton_', 'mobilePlugins_');" />
			</div>
		<?php } ?>
		<div style="clear: both;"></div>
	</div>
	<?php
	$count = 0;
	foreach ($plugins as $key=>$plugin) {
		$count++;
		?>
		<div class="pluginWrap <?php print (in_array($key, $activeSitewidePlugins) || in_array($key, $activePlugins))? "activePluginWrap" : "inactivePluginWrap"; ?>">
			<div class="toggleContainer">
				<?php 
				if ((in_array($key, $globalPlugins) && !in_array($key, $enabledPluginList)) || in_array($key, $disabledPluginList)) {
					?>
					<input type="checkbox" class="pluginsList" id="plugins_<?php print $count; ?>" name="pluginsList[]" value="<?php print $key; ?>" checked="checked" />
					<img src="<?php print $this->urlPath; ?>/image/off-button.png" class="pluginsButton pluginsButtonOff" alt="Off" id="pluginsButton_<?php print $count; ?>" onclick="PO_toggle_on_off('pluginsButton_<?php print $count; ?>', 'plugins_<?php print $count; ?>', '');" />
					<?php
				} else {
					?>
					<input type="checkbox" class="pluginsList" id="plugins_<?php print $count; ?>" name="pluginsList[]" value="<?php print $key; ?>" />
					<img src="<?php print $this->urlPath; ?>/image/on-button.png" class="pluginsButton pluginsButtonOn" alt="On" id="pluginsButton_<?php print $count; ?>" onclick="PO_toggle_on_off('pluginsButton_<?php print $count; ?>', 'plugins_<?php print $count; ?>', '');" />
					<?php
				}
				?>
			</div>
			<?php if (get_option('PO_disable_mobile_plugins') == 1) { ?>
				<div class="toggleContainer">
					<?php 
					if ((in_array($key, $globalMobilePlugins) && !in_array($key, $enabledMobilePluginList)) || in_array($key, $disabledMobilePluginList)) {
						?>
						<input type="checkbox" class="mobilePluginsList" id="mobilePlugins_<?php print $count; ?>" name="mobilePluginsList[]" value="<?php print $key; ?>" checked="checked" />
						<img src="<?php print $this->urlPath; ?>/image/off-button.png" class="pluginsButton pluginsButtonOff" alt="Off" id="mobilePluginsButton_<?php print $count; ?>" onclick="PO_toggle_on_off('mobilePluginsButton_<?php print $count; ?>', 'mobilePlugins_<?php print $count; ?>', '');" />
						<?php
					} else {
						?>
						<input type="checkbox" class="mobilePluginsList" id="mobilePlugins_<?php print $count; ?>" name="mobilePluginsList[]" value="<?php print $key; ?>" />
						<img src="<?php print $this->urlPath; ?>/image/on-button.png" class="pluginsButton pluginsButtonOn" alt="On" id="mobilePluginsButton_<?php print $count; ?>" onclick="PO_toggle_on_off('mobilePluginsButton_<?php print $count; ?>', 'mobilePlugins_<?php print $count; ?>', '');" />
						<?php
					}
					?>
				</div>
			<?php } ?>
			<div class="pluginLabel">
				<?php print $plugin['Name']; ?>
			</div>
			<div style="clear: both;"></div>
		</div>
		<?php
	}
	?>
</div>
<div style="clear: both;"></div>

<div class="metaBoxLabel">
	Plugin Groups
</div>
<div id="pluginGroupContainer" class="metaBoxContent">
	<div class="pluginWrap">
		<div class="toggleContainer">
			Standard<br />
			<input type="checkbox" id="toggleAllPluginGroups" name="toggleAllPluginGroups" value="">
			<img src="<?php print $this->urlPath; ?>/image/toggle-on-button.png" class="pluginsButton pluginsButtonOn" alt="On" id="toggleAllPluginGroupsButton" onclick="PO_toggle_on_off('toggleAllPluginGroupsButton', 'toggleAllPluginGroups', 'toggle-');PO_toggle_all('toggleAllPluginGroups', 'pluginGroupList', 'pluginGroupButton_', 'pluginGroup_');" />
		</div>
		<?php if (get_option('PO_disable_mobile_plugins') == 1) { ?>
			<div class="toggleContainer">
				Mobile<br />
				<input type="checkbox" id="toggleAllMobilePluginGroups" name="toggleAllMobilePluginGroups" value="">
				<img src="<?php print $this->urlPath; ?>/image/toggle-on-button.png" class="pluginsButton pluginsButtonOn" alt="On" id="toggleAllMobilePluginGroupsButton" onclick="PO_toggle_on_off('toggleAllMobilePluginGroupsButton', 'toggleAllMobilePluginGroups', 'toggle-');PO_toggle_all('toggleAllMobilePluginGroups', 'mobilePluginGroupList', 'mobilePluginGroupButton_', 'mobilePluginGroup_');" />
			</div>
		<?php } ?>
		<div style="clear: both;"></div>
	</div>
	<?php
	$count = 0;
	foreach ($groupList as $key=>$group) {
		$count++;
		?>
		<div class="pluginWrap activePluginGroupWrap">
			<div class="toggleContainer">
				<?php 
				if ((in_array($group->ID, $globalGroups) && !in_array($group->ID, $enabledGroupList)) || in_array($group->ID, $disabledGroupList)) {
					?>
					<input type="checkbox" class="pluginGroupList" id="pluginGroup_<?php print $count; ?>" name="pluginGroupList[]" value="<?php print $group->ID; ?>" checked="checked" />
					<img src="<?php print $this->urlPath; ?>/image/off-button.png" class="pluginsButton pluginsButtonOff" alt="Off" id="pluginGroupButton_<?php print $count; ?>" onclick="PO_toggle_on_off('pluginGroupButton_<?php print $count; ?>', 'pluginGroup_<?php print $count; ?>', '');" />
					<?php
				} else {
					?>
					<input type="checkbox" class="pluginGroupList" id="pluginGroup_<?php print $count; ?>" name="pluginGroupList[]" value="<?php print $group->ID; ?>" />
					<img src="<?php print $this->urlPath; ?>/image/on-button.png" class="pluginsButton pluginsButtonOn" alt="On" id="pluginGroupButton_<?php print $count; ?>" onclick="PO_toggle_on_off('pluginGroupButton_<?php print $count; ?>', 'pluginGroup_<?php print $count; ?>', '');" />
					<?php
				}
				?>
			</div>
			<?php if (get_option('PO_disable_mobile_plugins') == 1) { ?>
				<div class="toggleContainer">
					<?php 
					if ((in_array($group->ID, $globalMobileGroups) && !in_array($group->ID, $enabledMobileGroupList)) || in_array($group->ID, $disabledMobileGroupList)) {
						?>
						<input type="checkbox" class="mobilePluginGroupList" id="mobilePluginGroup_<?php print $count; ?>" name="mobilePluginGroupList[]" value="<?php print $group->ID; ?>" checked="checked" />
						<img src="<?php print $this->urlPath; ?>/image/off-button.png" class="pluginsButton pluginsButtonOff" alt="Off" id="mobilePluginGroupButton_<?php print $count; ?>" onclick="PO_toggle_on_off('mobilePluginGroupButton_<?php print $count; ?>', 'mobilePluginGroup_<?php print $count; ?>', '');" />
						<?php
					} else {
						?>
						<input type="checkbox" class="mobilePluginGroupList" id="mobilePluginGroup_<?php print $count; ?>" name="mobilePluginGroupList[]" value="<?php print $group->ID; ?>" />
						<img src="<?php print $this->urlPath; ?>/image/on-button.png" class="pluginsButton pluginsButtonOn" alt="On" id="mobilePluginGroupButton_<?php print $count; ?>" onclick="PO_toggle_on_off('mobilePluginGroupButton_<?php print $count; ?>', 'mobilePluginGroup_<?php print $count; ?>', '');" />
						<?php
					}
					?>
				</div>
			<?php } ?>
			<div class="pluginLabel">
				<?php print $group->post_title; ?>
				<?php 
				$groupMembers = get_post_meta($group->ID, "_PO_group_members", $single=true);
				if (is_array($groupMembers)) {
					print '<select style="margin-left: 10px;" name=""><option disabled="disabled">-- Plugin List --</option>';
					foreach($groupMembers as $plugin) {
						print '<option disabled="disabled">'.$plugins[$plugin]['Name'].'</option>';
					}
					print '</select>';
				}
				?>
			</div>
			<div style="clear: both;"></div>
		</div>
		<?php
	}
	?>
</div>
<div style="clear: both;"></div>
<input type="hidden" name="poSubmitPostMetaBox" value="1" />