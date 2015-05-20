<?php
global $wpdb;
if ( current_user_can( 'activate_plugins' ) ) {
	?>
	<script type="text/javascript" language="javascript">
		function PO_submit_global_plugins(){
			var disabledList = new Array();
			var disabledMobileList = new Array();
			var disabledGroupList = new Array();
			var disabledMobileGroupList = new Array();
			jQuery('.pluginsList').each(function() {
				if (this.checked) {
					disabledList[disabledList.length] = jQuery(this).val();
				}
			});

			jQuery('.mobilePluginsList').each(function() {
				if (this.checked) {
					disabledMobileList[disabledMobileList.length] = jQuery(this).val();
				}
			});

			jQuery('.pluginGroupList').each(function() {
				if (this.checked) {
					disabledGroupList[disabledGroupList.length] = jQuery(this).val();
				}
			});

			jQuery('.mobilePluginGroupList').each(function() {
				if (this.checked) {
					disabledMobileGroupList[disabledMobileGroupList.length] = jQuery(this).val();
				}
			});
			
			var revertHtml = jQuery('#pluginListdiv').html();
			jQuery('#pluginListdiv').html('<div style="width: 100%;text-align: center;"><img src="<?php print $this->urlPath . "/image/ajax-loader.gif"; ?>"></div>');
			
			jQuery.post(encodeURI(ajaxurl + '?action=PO_save_global_plugins'), { 'disabledList[]': disabledList, 'disabledMobileList[]': disabledMobileList, 'disabledGroupList[]': disabledGroupList, 'disabledMobileGroupList[]': disabledMobileGroupList, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
				alert(result);
				jQuery('#pluginListdiv').html(revertHtml);
				jQuery('.pluginsList').each(function() {
					if (disabledList.indexOf(this.value) != -1) {
						jQuery(this).attr('checked', true);
					} else {
						jQuery(this).attr('checked', false);
					}
				});
			});
		}
	</script>
	<?php
}
?>