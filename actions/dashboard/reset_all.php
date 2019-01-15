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
	
//Loop on users
$count = 0;
foreach ($users as $user) {
    // Update widget manager fixed widgets
    if (elgg_is_active_plugin('widget_manager') && function_exists('widget_manager_update_fixed_widgets')) {
	$fixed_ts = elgg_get_plugin_setting($context . '_fixed_ts', 'widget_manager');
	if (empty($fixed_ts)) {
		// there should always be a fixed ts, so fix it now. This situation only occurs after activating widget_manager the first time.
		$fixed_ts = time();
		elgg_set_plugin_setting($context . '_fixed_ts', $fixed_ts, 'widget_manager');
	}

	// get the ts of the profile/dashboard you are viewing
	$user_fixed_ts = elgg_get_plugin_user_setting($context . '_fixed_ts', $user->getGUID(), 'widget_manager');
	if ($user_fixed_ts < $fixed_ts) {
		widget_manager_update_fixed_widgets($context, $user->getGUID());
	}
    }

    if (dashboard_reset_widgets($user->getGUID(), $context) === false) {
	register_error(elgg_echo('dashboard_reset:all:failure'));
	forward(REFERER);
    } else {
	$count++;
    }
}

system_message(elgg_echo('dashboard_reset:all:success', [$count]));
forward(REFERER);