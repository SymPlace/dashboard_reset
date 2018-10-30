<?php
/**
 * A user dashboard reset ability for users and admins
 */

elgg_register_event_handler('init', 'system', 'dashboard_reset_init');

function dashboard_reset_init() {
    // reset buttons
    elgg_extend_view('page/layouts/widgets/add_button', 'dashboard_reset/widgets/reset_button', 400);
    if (!elgg_is_active_plugin('widget_manager')) {
	elgg_extend_view('css/elgg', 'css/dashboard_reset/global.css');
	elgg_extend_view('css/admin', 'css/dashboard_reset/global.css');
    }
    
    // reset actions
    elgg_register_action('dashboard/reset_one', dirname(__FILE__) . '/actions/dashboard/reset_one.php');
    elgg_register_action('dashboard/reset_all', dirname(__FILE__) . '/actions/dashboard/reset_all.php', 'admin');
}

/* SQL Request:
SELECT * from elgg_entities e
LEFT JOIN elgg_entity_subtypes es ON es.id = e.subtype
LEFT JOIN elgg_private_settings ps ON ps.entity_guid = e.guid AND ps.name = 'context' AND ps.value = 'dashboard'
WHERE e.type = 'object' AND es.subtype = 'widget' AND e.owner_guid != 1 AND ps.id IS NOT NULL
 */
function dashboard_reset_widgets($owner_guid, $context) {
    // Request user dashboard widgets
    $options = array(
	    'type' => 'object',
	    'subtype' => 'widget',
	    'owner_guid' => $owner_guid,
	    'private_setting_name' => 'context',
	    'private_setting_value' => $context,
	    'limit' => 0,
    );
    $widgets = elgg_get_entities_from_private_settings($options);

    // Delete user dashboard widgets
    foreach ($widgets as $widget) {
	$layout_owner_guid = $widget->getContainerGUID();
	elgg_set_page_owner_guid($layout_owner_guid);
	if (!elgg_can_edit_widget_layout($widget->context) || !$widget->delete()) {
	    $current_user_guid = elgg_get_logged_in_user_guid();
	    elgg_log("Current user (guid:'{$current_user_guid}') does not have the right to edit current widget (guid:'{$widget->getGUID()}') layout ('{$widget->context}') !", 'ERROR');
	    return false;
	}
    }

    // Create default user dashboard widgets by simulating a user account creation
    if (!function_exists('_elgg_create_default_widgets')) {
	elgg_log('Internal core function "_elgg_create_default_widgets" does not exist any more !', 'ERROR');
	return false;
    }
    _elgg_create_default_widgets('create', 'user', get_entity($owner_guid));
}