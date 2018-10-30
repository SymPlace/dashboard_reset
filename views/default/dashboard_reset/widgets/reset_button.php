<?php
/**
 * Button area for reseting the dashboard widgets
 */

$context = elgg_get_context();
if ($context != 'widgets' || !elgg_in_context('dashboard')) {
    return;
}
$widget_context = 'dashboard';

$owner_guid = elgg_get_page_owner_guid();
if(!$owner_guid) {
    return;
}

$href = "action/dashboard/reset_one?owner_guid=$owner_guid&context=$widget_context";
$text = elgg_echo('dashboard_reset:one:button');
$onclick = "return confirm(elgg.echo('dashboard_reset:one:confirm'));";

if (elgg_in_context('admin') && elgg_in_context('default_widgets')) {
	$href = "action/dashboard/reset_all?context=$widget_context";
	$text = elgg_echo('dashboard_reset:all:button');
	$onclick = "return confirm(elgg.echo('dashboard_reset:all:confirm'));";
}

if (elgg_is_active_plugin('widget_manager')) {
	elgg_register_menu_item('title', [
	    'name' => 'widgets:reset',
	    'text' => $text,
	    'href' => $href,
	    'link_class' => 'elgg-button elgg-button-action',
	    'is_trusted' => true,
	    'is_action' => true,
	    'onclick' => $onclick,
	]);
} else {
?>
<div class="elgg-widget-reset-control">
<?php
	echo elgg_view('output/url', array(
		'href' => $href,
		'text' => $text,
		'class' => 'elgg-button elgg-button-action',
		'is_trusted' => true,
		'is_action' => true,
		'onclick' => $onclick,
	));
?>
</div>
<?php
}
?>