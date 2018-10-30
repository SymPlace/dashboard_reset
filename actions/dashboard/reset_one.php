<?php
/**
 * Elgg widget add action
 *
 * @package Elgg.Core
 * @subpackage Widgets.Management
 */

$owner_guid = get_input('owner_guid');
$context = get_input('context');

// Check inputs
if (is_null($owner_guid) || is_null($context)) {
    elgg_log("Missing owner_guid input:'$owner_guid' or context input:'$context' !", 'ERROR');
    register_error(elgg_echo('dashboard_reset:one:failure'));
    forward(REFERER);
}

$owner_entity = get_entity($owner_guid);
if (!$owner_entity || !elgg_instanceof($owner_entity, 'user') || ($context != 'dashboard')) {
    elgg_log("Owner entity (guid:'$owner_guid') is not a 'user' entity or context ('$context') is not 'dashboard' !", 'ERROR');
    register_error(elgg_echo('dashboard_reset:one:failure'));
    forward(REFERER);
}

if (dashboard_reset_widgets($owner_guid, $context) === false) {
    register_error(elgg_echo('dashboard_reset:one:failure'));
    forward(REFERER);
}

system_message(elgg_echo('dashboard_reset:one:success'));
forward(REFERER);