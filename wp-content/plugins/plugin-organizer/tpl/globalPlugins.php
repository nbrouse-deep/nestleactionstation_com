<div id="wrap">
    <div class="po-setting-icon" id="icon-po-global"> <br /> </div>

    <h2 class="po-setting-title">Global Plugins</h2>
    <div style="clear: both;"></div>
	<p>Select the plugins you would like to disable site wide.  This will allow you to not load the plugin on any page unless it is specifically allowed in the post or page.</p>
	<div id="poststuff" class="metabox-holder">
      <div id="post-body">
        <div id="post-body-content">
	      <div id="pluginListdiv" class="stuffbox" style="width: 98%">
              <div id="pluginContainer" class="metaBoxContent">
					<h3 class="pluginListHead">Plugins</h3>
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
								if (in_array($key, $disabledPlugins)) {
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
									if (in_array($key, $disabledMobilePlugins)) {
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
				<div style="clear: both;"></div>
              </div>


              <div id="groupContainer" class="metaBoxContent">
					<h3 class="pluginListHead">Plugin Groups</h3>
					<div class="groupWrap">
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
						<div class="groupWrap activePluginGroupWrap">
							<div class="toggleContainer">
								<?php 
								if (in_array($group->ID, $disabledGroups)) {
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
									if (in_array($group->ID, $disabledMobileGroups)) {
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
				<div style="clear: both;"></div>
              </div>

			  <input type=button name=submit value="Save" onmousedown="PO_submit_global_plugins();">
			</div>
	    </div>
      </div>
    </div>
</div>

