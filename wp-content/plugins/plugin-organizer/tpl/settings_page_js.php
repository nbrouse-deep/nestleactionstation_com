<?php
if ( current_user_can( 'activate_plugins' ) ) {
	?>
	<script type="text/javascript" language="javascript">
		function PO_submit_mobile_user_agents() {
			var mobileUserAgents = jQuery('#PO_mobile_user_agents').val();
			PO_toggle_loading('PO-browser-string-div');
			jQuery.post(encodeURI(ajaxurl + '?action=PO_submit_mobile_user_agents'), { 'PO_mobile_user_agents': mobileUserAgents, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
				alert(result);
				PO_toggle_loading('PO-browser-string-div');
			});
		}
	
		function PO_submit_disable_settings() {
			var disable_plugins = 0;
			var disable_mobile_plugins = 0;
			var admin_disable_plugins = 0;
			jQuery('.PO-disable-plugins').each(function() {
				if (this.checked) {
					disable_plugins = this.value;
				}
			});

			jQuery('.PO-disable-mobile-plugins').each(function() {
				if (this.checked) {
					disable_mobile_plugins = this.value;
				}
			});

			jQuery('.PO-admin-disable-plugins').each(function() {
				if (this.checked) {
					admin_disable_plugins = this.value;
				}
			});

			PO_toggle_loading('PO-disable-settings-div');
			jQuery.post(encodeURI(ajaxurl + '?action=PO_submit_disable_plugin_settings'), { 'PO_disable_plugins': disable_plugins, 'PO_disable_mobile_plugins': disable_mobile_plugins, 'PO_admin_disable_plugins': admin_disable_plugins, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
				alert(result);
				PO_toggle_loading('PO-disable-settings-div');
			});
		}
		
		function PO_submit_fuzzy_url_matching() {
			var fuzzy_url_matching = 0;
			jQuery('.PO-fuzzy-url-radio').each(function() {
				if (this.checked) {
					fuzzy_url_matching = this.value;
				}
			});
			PO_toggle_loading('PO-fuzzy-url-matching-div');
			jQuery.post(encodeURI(ajaxurl + '?action=PO_submit_fuzzy_url_matching'), { 'PO_fuzzy_url_matching': fuzzy_url_matching, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
				alert(result);
				PO_toggle_loading('PO-fuzzy-url-matching-div');
			});
		}
		
		function PO_submit_ignore_protocol() {
			var ignore_protocol = 0;
			jQuery('.PO-ignore-protocol-radio').each(function() {
				if (this.checked) {
					ignore_protocol = this.value;
				}
			});
			PO_toggle_loading('PO-ignore-protocol-div');
			jQuery.post(encodeURI(ajaxurl + '?action=PO_submit_ignore_protocol'), { 'PO_ignore_protocol': ignore_protocol, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
				alert(result);
				PO_toggle_loading('PO-ignore-protocol-div');
			});
		}

		function PO_submit_ignore_arguments() {
			var ignore_arguments = 1;
			jQuery('.PO-ignore-arguments-radio').each(function() {
				if (this.checked) {
					ignore_arguments = this.value;
				}
			});
			PO_toggle_loading('PO-ignore-arguments-div');
			jQuery.post(encodeURI(ajaxurl + '?action=PO_submit_ignore_arguments'), { 'PO_ignore_arguments': ignore_arguments, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
				alert(result);
				PO_toggle_loading('PO-ignore-arguments-div');
			});
		}
		
		function PO_submit_redo_permalinks() {
			var old_site_address = jQuery('#PO-old-site-address').val();
			var new_site_address = jQuery('#PO-new-site-address').val();
			PO_toggle_loading('redo-permalinks-div');
			jQuery.post(encodeURI(ajaxurl + '?action=PO_redo_permalinks'), { PO_nonce: '<?php print $this->nonce; ?>', 'old_site_address': old_site_address, 'new_site_address': new_site_address }, function (result) {
				alert(result);
				PO_toggle_loading('redo-permalinks-div');
			});
		}

		function PO_submit_post_type_support() {
			var PO_cutom_post_type = new Array();
			jQuery('.PO_cutom_post_type').each(function() {
				if (this.checked) {
					PO_cutom_post_type[PO_cutom_post_type.length] = this.value;
				}
			});
			PO_toggle_loading('PO-custom-post-type-div');
			jQuery.post(encodeURI(ajaxurl + '?action=PO_post_type_support'), { 'PO_cutom_post_type[]': PO_cutom_post_type, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
				alert(result);
				PO_toggle_loading('PO-custom-post-type-div');
			});
		}

		function PO_manage_mu_plugin_file() {
			var selected_action = '';
			jQuery('.PO-manage-mu-radio').each(function() {
				if (this.checked) {
					selected_action = this.value;
				}
			});
			if (selected_action != '') {
				PO_toggle_loading('PO-manage-mu-div');
				jQuery.post(encodeURI(ajaxurl + '?action=PO_manage_mu_plugin'), { 'selected_action': selected_action, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
					alert(result);
					PO_toggle_loading('PO-manage-mu-div');
				});
			}
		}

		function PO_submit_preserve_settings() {
			var preserve_settings = '';
			jQuery('.PO-preserve-settings-radio').each(function() {
				if (this.checked) {
					preserve_settings = this.value;
				}
			});
			PO_toggle_loading('PO-preserve-settings-div');
			jQuery.post(encodeURI(ajaxurl + '?action=PO_submit_preserve_settings'), { 'PO_preserve_settings': preserve_settings, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
				alert(result);
				PO_toggle_loading('PO-preserve-settings-div');
			});
		}

		function PO_submit_order_access_net_admin() {
			var order_access_net_admin = '';
			jQuery('.PO-order-access-net-admin-radio').each(function() {
				if (this.checked) {
					order_access_net_admin = this.value;
				}
			});
			PO_toggle_loading('PO-order-access-net-admin-div');
			jQuery.post(encodeURI(ajaxurl + '?action=PO_submit_order_access_net_admin'), { 'PO_order_access_net_admin': order_access_net_admin, PO_nonce: '<?php print $this->nonce; ?>' }, function (result) {
				alert(result);
				PO_toggle_loading('PO-order-access-net-admin-div');
			});
		}

		function PO_submit_admin_css_settings() {
			var postObject = {
				'PO_network_active_plugins_color': jQuery('#PO_network_active_plugins_color').val(),
				'PO_nonce': '<?php print $this->nonce; ?>'
			};
			
			PO_toggle_loading('PO-manage-css-div');
			jQuery.post(encodeURI(ajaxurl + '?action=PO_submit_admin_css_settings'), postObject, function (result) {
				alert(result);
				PO_toggle_loading('PO-manage-css-div');
			});
		}
	</script>
	<?php
}
?>