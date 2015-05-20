<style type="text/css">
	#icon-po-settings {
		background: url("<?php print $this->urlPath; ?>/image/po-icon-32x32.png") no-repeat scroll 0px 0px transparent;
	}
	#icon-po-global {
		background: url("<?php print $this->urlPath; ?>/image/po-global-32x32.png") no-repeat scroll 0px 0px transparent;
	}

	#icon-po-search {
		background: url("<?php print $this->urlPath; ?>/image/po-search-32x32.png") no-repeat scroll 0px 0px transparent;
	}

	.po-setting-icon {
		float: left;
		width: 32px;
		height: 32px;
		margin: 8px 5px 0px 0px;
	}

	.po-setting-title {
		float: left;
	}

	.activePlugin {
		color: #990033;
	}

	.badInputLabel {
		color: #990033;
		font-weight: bold;
	}
	.badInput {
		background-color: #990033;
	}
	.metaBoxLabel {
		font-size: 20px;
		line-height: 22px;
		padding: 5px;
	}
	.metaBoxContent {
		border: 2px outset #000000;
		margin-bottom: 20px;
	}
	.metaBoxContent input[type="checkbox"], .plugin-organizer_page_PO_global_plugins input[type="checkbox"], .plugin-organizer_page_PO_global_plugins input[type="radio"], .toplevel_page_Plugin_Organizer input[type="checkbox"], .toplevel_page_Plugin_Organizer input[type="radio"], .plugin-organizer_page_PO_url_admin input[type="checkbox"], .plugin-organizer_page_PO_url_admin input[type="radio"] {
		margin: 0px 3px 0px 3px !important;
	}

	.poPermalinkInput, .poFilterNameInput {
		width: 90%;
		margin: 10px;
	}

	.pluginWrap, .groupWrap {
		padding: 0px;
		border-bottom: 1px solid #cccccc;
		line-height: 26px;
	}

	.pluginsList, .mobilePluginsList, .pluginGroupList, .mobilePluginGroupList, #toggleAllPlugins, #toggleAllMobilePlugins, #toggleAllPluginGroups, #toggleAllMobilePluginGroups, #toggleAllGroups, #toggleAllMobileGroups {
		display: none !important;
	}
	
	.pluginsButton, .pluginGroupButton {
		vertical-align: middle;
		margin: 0px 3px;
	}

	.inactivePluginWrap, .inactivePluginGroupWrap {
		background-color: #cccccc;
		border-bottom: 1px solid #ffffff;
	}

	.toggleContainer {
		width: 80px;
		float: left;
		text-align: center;
		padding: 10px 5px;
		border-right: 1px solid #000000;
	}
	
	.pluginLabel {
		padding: 10px 0px 10px 10px;
		float: left;
	}

	.pluginListHead {
		font-size: 18px !important;
		border-bottom: 2px solid #000000;
	}
	.network-active th, .network-active td {
		background-color: <?php print (get_option('PO_network_active_plugins_color') != '')? get_option('PO_network_active_plugins_color') : '#D7DF9E'; ?> !important;
	}

	.PO-settings-left-column {
		width: 150px;
		float: left;
		line-height: 26px;
		font-size: 13px;
	}

	.PO-settings-right-column {
		width: 500px;
		float: left;
	}

	.PO-loading-container {
		display: none;
	}

	.PO-loading-container div {
		width: 100%;
		text-align: center;
	}
</style>