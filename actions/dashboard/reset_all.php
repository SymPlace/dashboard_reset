<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$context = get_input('context');

// Check inputs
if (is_null($context) || ($context != 'dashboard')) {
    elgg_log("Missing or invalid context input:'$context' !", 'ERROR');
    register_error(elgg_echo('dashboard_reset:all:failure'));
    forward(REFERER);
}

$site = elgg_get_site_entity();
$dbprefix = elgg_get_config('dbprefix');
if (!$site || !$dbprefix) {
    elgg_log("Cannot get site entity or 'dbprefix' config!", 'ERROR');
    register_error(elgg_echo('dashboard_reset:all:failure'));
    forward(REFERER);    
}

// this could take a while
set_time_limit(0);

// Request users who are:
// - 'member_of_site'
// - enabled (users are enabled after their email address validation)
// - not banned
// - last logged in the last 3 years (users can log in if they have not been validated by an admin)
//   otherwise we need to check following user metadata: 'admin_validated'
// - with a non empty password_hash (anonymous and anonymized accounts have an empty password_hash)
//   otherwise we need to check following user metadata: 'AU_anonymous_comments' and 'member_selfdelete'
$last_login = strtotime('-3 years');
$users = $site->getEntities([
	'types' => 'user',
	'joins' => array("join {$dbprefix}users_entity ue on e.guid = ue.guid"),
	'wheres' => array("e.enabled = 'yes'", 
			  "ue.banned = 'no' AND ue.last_login > $last_login AND ue.password_hash != '' "),
	'limit' => 0,
	'batch' => true,
]);

//HACK: Disable widget_manager 'createFixedParentMetadata' event handler during reset_all action
elgg_unregister_event_handler('all', 'object', '\ColdTrick\WidgetManager\Widgets::createFixedParentMetadata');

//Loop on users
$count = 0;
foreach ($users as $user) {
    elgg_log("Reset '$user->name' dashboard:", 'NOTICE');
    if (dashboard_reset_widgets($user->getGUID(), $context) === false) {
	register_error(elgg_echo('dashboard_reset:all:failure'));
	forward(REFERER);
    } else {
	$count++;
    }
}

elgg_log("Successfully reset $count user dashboards !", 'NOTICE');

system_message(elgg_echo('dashboard_reset:all:success', [$count]));
forward(REFERER);