<?php
if ( current_user_can( 'activate_plugins' ) ) {
	$plugins = get_plugins();
	foreach($plugins as $key=>$plugin) {
		$plugins[$key]=$plugin['Name'];
	}
	
	$activePlugins = $this->get_active_plugins();
	$hiddenPlugins = array();
	$lastPluginName = '';
	foreach($activePlugins as $key=>$plugin) {
		if (is_plugin_active_for_network($plugin)) {
			$pluginID = sanitize_title($plugins[$plugin]);
			$lastPluginID = (isset($activePlugins[$key-1]))? sanitize_title($plugins[$activePlugins[$key-1]]) : 'first item in the list';
			$hiddenPlugins[] = array($plugin, $pluginID, $lastPluginID, $plugins[$plugin], array_search($plugin, $activePlugins));
			
		}
	}
	
	$groups = get_posts(array('post_type'=>'plugin_group', 'posts_per_page'=>-1));
	?>
	<style type="text/css">
		<?php 
		$styleLoops = sizeof($groups)/8;
		$cssLoopCount = 0;
		while ($cssLoopCount < $styleLoops) {
			$cssLoopCount++;
			?>
			.subsubsub li:nth-child(<?php print $cssLoopCount*8; ?>):before { content:"\A"; white-space:pre; }
			<?php
		}
		?>
		.column-PO_groups {
			width: 150px;
		}

		.column-PO_draghandle {
			width: 35px;
		}
			
	</style>
	<script type="text/javascript" language="javascript">
		var pluginList = <?php print json_encode($plugins); ?>;
		var hiddenPlugins = <?php print ($this->pluginPageActions == '1')? json_encode($hiddenPlugins) : json_encode(array()) ; ?>;
		function PO_save_draggable_plugin_order() {
			var orderList = new Array();
			var startOrderList = new Array();
			var count=0;
			jQuery('tr.active').each(function () {
				orderList[orderList.length] = count;
				startOrderList[startOrderList.length] = jQuery(this).find('.start_order').val();
				count++;
			});
			var load_element = jQuery('#the-list');
			var revertHtml = load_element.html();
			load_element.html('<tr><td colspan=2 style="width: 100%;text-align: center;"><img src="<?php print $this->urlPath . "/image/ajax-loader.gif"; ?>"></td></tr>');

			jQuery.post(encodeURI(ajaxurl + '?action=PO_plugin_organizer'), { 'orderList[]': orderList, 'startOrder[]': startOrderList, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
				alert(result);
				load_element.html(revertHtml);
				if (result == "The plugin load order has been changed.") {
					var count=0;
					jQuery('tr.active').each(function () {
						jQuery(this).find('.start_order').val(count);
						count++;
					});
				}
				make_plugins_draggable();
			});
			return false;
		}
		
		function PO_add_plugins_to_group(selectedGroup) {
			var groupList = new Array();
			var group_id = jQuery(selectedGroup).val();
			var group_name = jQuery(selectedGroup).text();
			if (group_name == "-- New Group --") {
				group_name = jQuery('input[name=PO_new_group_name]:first').val();
				group_id = '';
			}
			jQuery('tr.active input:checkbox[name*=checked], tr.inactive input:checkbox[name*=checked]').each(function() {
				if (this.checked) {
					groupList[groupList.length] = jQuery(this).val();
				}
			});
			if (group_name == '' || group_name == '-- Select Group --') {
				alert('You must enter a group name for your new group.');
			} else if (groupList.length == 0) {
				alert('You must select at least one plugin to add to the group.');
			} else {
				load_element = jQuery('#the-list');
				load_element.html('<tr><td colspan=2 style="width: 100%;text-align: center;"><img src="<?php print $this->urlPath . "/image/ajax-loader.gif"; ?>"></td></tr>');
				jQuery.post(encodeURI(ajaxurl + '?action=PO_add_to_group'), { 'groupList[]': groupList, PO_group: group_id, PO_nonce: '<?php print $this->nonce; ?>', group_name: group_name }, function (result) {
					alert(result);
					location.reload(true);
				});
			}
			return false;
		}

		function PO_save_plugins_to_group(selectedGroup) {
			var groupList = new Array();
			var group_id = jQuery(selectedGroup).val();
			var group_name = jQuery(selectedGroup).text();
			if (group_name == "-- New Group --") {
				group_name = jQuery('input[name=PO_new_group_name]:first').val();
				group_id = '';
			}
			jQuery('tr.active input:checkbox[name*=checked], tr.inactive input:checkbox[name*=checked]').each(function() {
				if (this.checked) {
					groupList[groupList.length] = jQuery(this).val();
				}
			});
			if (group_name == '' || group_name == '-- Select Group --') {
				alert('You must enter a group name for your new group.');
			} else if (groupList.length == 0) {
				alert('You must select at least one plugin to add to the group.');
			} else {
				load_element = jQuery('#the-list');
				load_element.html('<tr><td colspan=2 style="width: 100%;text-align: center;"><img src="<?php print $this->urlPath . "/image/ajax-loader.gif"; ?>"></td></tr>');
				jQuery.post(encodeURI(ajaxurl + '?action=PO_save_group'), { 'groupList[]': groupList, PO_group: group_id, PO_nonce: '<?php print $this->nonce; ?>', group_name: group_name }, function (result) {
					alert(result);
					location.reload(true);
				});
			}
			return false;
		}

		function PO_remove_plugins_from_group(selectedGroup) {
			var group_id = jQuery(selectedGroup).val();
			var group_name = jQuery(selectedGroup).text();
			if (group_name == '' || group_name == '-- Select Group --' || group_name == '-- New Group --') {
				alert('You must select a group to remove plugins from it.');
			} else {
				var groupList = new Array();
				jQuery('tr.active input:checkbox[name*=checked], tr.inactive input:checkbox[name*=checked]').each(function() {
					if (this.checked) {
						groupList[groupList.length] = jQuery(this).val();
					}
				});
				
				if (groupList.length == 0) {
					alert('You must select at least one plugin to remove from the group.');
				} else {
					if (confirm('Are you sure you wish to remove the selected plugins from group "'+group_name+'"?')) {
						load_element = jQuery('#the-list');
						load_element.html('<tr><td colspan=2 style="width: 100%;text-align: center;"><img src="<?php print $this->urlPath . "/image/ajax-loader.gif"; ?>"></td></tr>');
						jQuery.post(encodeURI(ajaxurl + '?action=PO_remove_plugins_from_group'), { 'groupList[]': groupList, PO_group: group_id, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
							alert(result);
							location.reload(true);
						});
					}
				}
			}
			return false;
		}
		
		function PO_delete_plugin_group(selectedGroup) {
			var group_id = jQuery(selectedGroup).val();
			var group_name = jQuery(selectedGroup).text();
			if (group_name == '' || group_name == '-- Select Group --' || group_name == '-- New Group --') {
				alert('You must select a group to delete.');
			} else {
				if (confirm('Are you sure you wish to delete group "'+group_name+'"?')) {
					load_element = jQuery('#the-list');
					load_element.html('<tr><td colspan=2 style="width: 100%;text-align: center;"><img src="<?php print $this->urlPath . "/image/ajax-loader.gif"; ?>"></td></tr>');
					jQuery.post(encodeURI(ajaxurl + '?action=PO_delete_group'), { PO_group: group_id, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
						alert(result);
						location.reload(true);
					});
				}
			}
			return false;
		}

		function PO_reset_to_default_order() {
			if (confirm('Are you sure you want to reset the plugin load order back to default?')) {
				load_element = jQuery('#the-list');
				load_element.html('<tr><td colspan=2 style="width: 100%;text-align: center;"><img src="<?php print $this->urlPath . "/image/ajax-loader.gif"; ?>"></td></tr>');
				jQuery.post(encodeURI(ajaxurl + '?action=PO_reset_to_default_order'), { PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
					alert(result);
					location.reload(true);
				});
			}
			return false;
		}

		function PO_edit_plugin_group_name(selectedGroup) {
			new_group_name = jQuery('input[name=PO_new_group_name]:first').val();
			var group_id = jQuery(selectedGroup).val();
			if (group_id == '' || group_id == '-- New Group --') {
				alert('The group you have selected can\'t be edited.');
			} else if (new_group_name == '') {
				alert('You must enter a new name for the group if you want to change it.');
			} else {
				load_element = jQuery('#the-list');
				revertHtml = load_element.html();
				load_element.html('<tr><td colspan=2 style="width: 100%;text-align: center;"><img src="<?php print $this->urlPath . "/image/ajax-loader.gif"; ?>"></td></tr>');
				jQuery.post(encodeURI(ajaxurl + '?action=PO_edit_plugin_group_name'), { PO_nonce: '<?php print $this->nonce; ?>', PO_group_id: group_id, PO_new_group_name: new_group_name }, function (result) {
					alert(result);
					location.reload(true);
				});
			}
			return false;
		}

		function PO_submit_plugin_action(actionButton) {
			var returnStatus = true;
			var selectedAction = '';
			var groupElement = '';
			if (actionButton.id == 'doaction') {
				selectedAction = jQuery('.tablenav.top .actions select[name=action]').val();
				selectedGroup = jQuery('.tablenav.top .actions select[name=PO_group_name_select] option:selected');
			} else {
				selectedAction = jQuery('.tablenav.bottom .actions select[name=action2]').val();
				selectedGroup = jQuery('.tablenav.bottom .actions select[name=PO_group_name_select2] option:selected');
			}

			if (selectedAction == "save_load_order") {
				returnStatus = PO_save_draggable_plugin_order();
			} else if (selectedAction == "save_plugin_group") {
				returnStatus = PO_save_plugins_to_group(selectedGroup);
			} else if (selectedAction == "add_to_plugin_group") {
				returnStatus = PO_add_plugins_to_group(selectedGroup);
			} else if (selectedAction == "edit_plugin_group_name") {
				returnStatus = PO_edit_plugin_group_name(selectedGroup);
			} else if (selectedAction == "delete_plugin_group") {
				returnStatus = PO_delete_plugin_group(selectedGroup);
			} else if (selectedAction == "remove_plugins_from_group") {
				returnStatus = PO_remove_plugins_from_group(selectedGroup);
			} else if (selectedAction == "reset_to_default_order") {
				returnStatus = PO_reset_to_default_order();
			}
			return returnStatus;
		}
		

		jQuery(function () {
			var colspanCount = jQuery('#the-list tr:first td').length - 2;
			
			for(var i=0; i<hiddenPlugins.length; i++) {
				if (hiddenPlugins[i][2] == "first item in the list") {
					jQuery('#the-list').prepend('<tr class="active network-active" id="'+hiddenPlugins[i][1]+'" style="cursor: move;"><th class="check-column"></th><td class="PO_draghandle column-PO_draghandle"></td><td class="plugin-title"><strong>'+hiddenPlugins[i][3]+'</strong></td><td class="column-description desc" colspan="'+colspanCount+'"><div class="plugin-description"><input type="hidden" value="'+hiddenPlugins[i][4]+'" id="start_order_'+hiddenPlugins[i][4]+'" class="start_order"><p>This is a network activated plugin.  It is only here to let you change its load order for this site.  Go to the network dashboard to activate/deactivate it.</p></div></td></tr>');
				} else {
					jQuery('#'+hiddenPlugins[i][2]).after('<tr class="active network-active" id="'+hiddenPlugins[i][1]+'" style="cursor: move;"><th class="check-column"></th><td class="PO_draghandle column-PO_draghandle"></td><td class="plugin-title"><strong>'+hiddenPlugins[i][3]+'</strong></td><td class="column-description desc" colspan="'+colspanCount+'"><div class="plugin-description"><input type="hidden" value="'+hiddenPlugins[i][4]+'" id="start_order_'+hiddenPlugins[i][4]+'" class="start_order"><p>This is a network activated plugin.  It is only here to let you change its load order for this site.  Go to the network dashboard to activate/deactivate it.</p></div></td></tr>');
				}
			}
			
			var pluginGroups = '';
			pluginGroups += '<option value="">-- Select Group --</option>';
			pluginGroups += '<option value="-- New Group --">-- New Group --</option>';
			<?php
			foreach ($groups as $group) {
				?>
				pluginGroups += '<option value="<?php print $group->ID; ?>"><?php print preg_replace("/'/", "\'", $group->post_title); ?></option>';
				<?php
			}
			?>
			pluginGroups += '</select><div class="PO_group_name_field" style="margin: 0px 10px;display: none;float: left;">Group Name:&nbsp;<input type="text" name="PO_new_group_name"></div>';
			
			var bulkActionList = new Array();
			jQuery('.tablenav.top .actions select[name=action] option').each(function() {
				bulkActionList[bulkActionList.length] = new Array(jQuery(this).val(), jQuery(this).text());
			});
			var bulkListReplacement = '';
			for (var i=0; i<bulkActionList.length; i++) {
				bulkListReplacement += '<option value="'+bulkActionList[i][0]+'">'+bulkActionList[i][1]+'</option>';
			}
			bulkListReplacement += '<option value="" disabled>-- PO Actions --</option>';
			bulkListReplacement += '<option value="remove_plugins_from_group">Remove From Group</option>';
			bulkListReplacement += '<option value="add_to_plugin_group">Add To Group</option>';
			bulkListReplacement += '<option value="edit_plugin_group_name">Edit Group Name</option>';
			bulkListReplacement += '<option value="save_plugin_group">Save Group</option>';
			bulkListReplacement += '<option value="delete_plugin_group">Delete Group</option>';
			<?php 
			if ($this->pluginPageActions == '1' && !isset($_REQUEST['PO_group_view']) && (!isset($_REQUEST['plugin_status']) || $_REQUEST['plugin_status'] == 'all' || $_REQUEST['plugin_status'] == 'active')) {
				?>
				bulkListReplacement += '<option value="reset_to_default_order">Reset To Default Order</option>';
				bulkListReplacement += '<option value="save_load_order">Save plugin load order</option>';
				<?php
			}
			?>
			bulkListReplacement += '</select>';
			jQuery('.tablenav.top .actions select[name=action]').remove();
			jQuery('.tablenav.bottom .actions select[name=action2]').remove();
			jQuery('.tablenav.top .actions:first').html('<select name="action">'+bulkListReplacement+jQuery('.tablenav.top .actions').html()+' <div style="float: left;margin: 0px 10px;padding-top: 5px;">Groups: </div><select name="PO_group_name_select">'+pluginGroups);
			jQuery('.tablenav.bottom .actions:first').html('<select name="action2">'+bulkListReplacement+jQuery('.tablenav.bottom .actions').html()+' <div style="float: left;margin: 0px 10px;padding-top: 5px;">Groups: </div><select name="PO_group_name_select2">'+pluginGroups);
			jQuery('#doaction, #doaction2').css('float', 'right');
			jQuery('#doaction, #doaction2').click(function() {
				return PO_submit_plugin_action(this);
			});

			jQuery('input[name=PO_new_group_name]').keyup(function() {
				jQuery('input[name=PO_new_group_name]').val(jQuery(this).val());
			});
			
			jQuery('.tablenav.top .actions select[name=action], .tablenav.bottom .actions select[name=action2]').change(function() {
				var selectedVal = jQuery(this).val();
				if (jQuery(this).val() == 'edit_plugin_group_name') {
					jQuery('.PO_group_name_field').css('display', '');
					jQuery('.tablenav.top .actions select[name=PO_group_name_select] option, .tablenav.bottom .actions select[name=PO_group_name_select2] option').each(function() {
						if (jQuery(this).val() == '-- New Group --' || jQuery(this).val() == '') {
							jQuery(this).prop('disabled', true);
						} else {
							jQuery(this).prop('selected', true);
						}
					});
				} else {
					jQuery('.PO_group_name_field').css('display', 'none');
					jQuery('.tablenav.top .actions select[name=PO_group_name_select] option, .tablenav.bottom .actions select[name=PO_group_name_select2] option').each(function() {
						if (jQuery(this).val() == '-- New Group --' || jQuery(this).val() == '') {
							jQuery(this).prop('disabled', false);
						}
					});
				}
			});

			jQuery('.tablenav.top .actions select[name=PO_group_name_select], .tablenav.bottom .actions select[name=PO_group_name_select2]').change(function() {
				var selectedVal = jQuery(this).val();
				jQuery('.tablenav.top .actions select[name=PO_group_name_select], .tablenav.bottom .actions select[name=PO_group_name_select2]').each(function() {
					jQuery(this).val(selectedVal);
				});
				if (jQuery(this).val() == '-- New Group --' || jQuery('.tablenav.top .actions select[name=action]').val() == 'edit_plugin_group_name') {
					jQuery('.PO_group_name_field').css('display', '');
				} else {
					jQuery('.PO_group_name_field').css('display', 'none');
				}
			});
		});
		
	</script>
	<?php
}
if (isset($_REQUEST['PO_group_view'])) {
	?>
	<script type="text/javascript" language="javascript">
		jQuery(function() {
			jQuery('.subsubsub .count').remove();
		});
	</script>
	<?php
}
?>