<script language="javascript" src="<?php print $this->urlPath; ?>/js/validation.js"></script>
<script language="javascript" type="text/javascript">
	function PO_toggle_loading(containerID) {
		if (jQuery('#'+containerID+' .inside').css('display') == 'none') {
			jQuery('#'+containerID+' .PO-loading-container').css('display', 'none');
			jQuery('#'+containerID+' .inside').css('display', 'block');
		} else {
			jQuery('#'+containerID+' .inside').css('display', 'none');
			jQuery('#'+containerID+' .PO-loading-container').css('display', 'block');
		}
	}
	
	function PO_toggle_all(toggleCheckbox, itemClass, buttonID, itemID) {
		var toggle = jQuery("#"+toggleCheckbox).attr('checked');
		jQuery("."+itemClass).each(function() {  
			var splitID = this.id.split('_');
			if (toggle) {
				PO_set_on_off(buttonID+splitID[1], itemID+splitID[1], 0, '');
			} else {
				PO_set_on_off(buttonID+splitID[1], itemID+splitID[1], 1, '');
			}
		});  
	}

	function PO_toggle_on_off(buttonID, checkboxID, buttonPrefix) {
		if (jQuery('#'+buttonID).hasClass('pluginsButtonOff')) {
			PO_set_on_off(buttonID, checkboxID, 1, buttonPrefix);
		} else {
			PO_set_on_off(buttonID, checkboxID, 0, buttonPrefix);
		}
	}
	
	function PO_set_on_off(buttonID, checkboxID, onOff, buttonPrefix) {
		if (onOff == 1) {
			jQuery('#'+checkboxID).attr('checked', false);
			jQuery('#'+buttonID).attr('src', '<?php print $this->urlPath; ?>/image/'+buttonPrefix+'on-button.png');
			jQuery('#'+buttonID).attr('alt', 'On');
			jQuery('#'+buttonID).removeClass('pluginsButtonOff');
			jQuery('#'+buttonID).addClass('pluginsButtonOn');
		} else {
			jQuery('#'+checkboxID).attr('checked', true);
			jQuery('#'+buttonID).attr('src', '<?php print $this->urlPath; ?>/image/'+buttonPrefix+'off-button.png');
			jQuery('#'+buttonID).attr('alt', 'Off');
			jQuery('#'+buttonID).removeClass('pluginsButtonOn');
			jQuery('#'+buttonID).addClass('pluginsButtonOff');
		}
	}
	
	function PO_reset_post_settings(postID) {
		jQuery.post(encodeURI(ajaxurl + '?action=PO_reset_post_settings'), { 'postID': postID, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
			if (result == '1') {
				alert('The settings were successfully reset.');
				location.reload(true);
			} else if (result == '-1') {
				alert('There were no settings found in the database.');
			} else {
				alert('There was an issue removing the settings.');
			}
		});
	}
	
	
	<?php
	print "var regex = new Array();\n";
	foreach ($this->regex as $key=>$val) {
		print "regex['$key'] = $val;\n";
	}
	?>
</script>