<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
header('Content-type: application/json');
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['apikey']) || !jeedom::apiAccess($data['apikey'], 'gsh')) {
	echo json_encode(array(
		'status' => 'ERROR',
		'errorCode' => 'authFailure',
	));
	die();
}
$plugin = plugin::byId('gsh');
if (!$plugin->isActive()) {
	echo json_encode(array(
		'status' => 'ERROR',
		'errorCode' => 'authFailure',
	));
	die();
}
log::add('gsh', 'debug', json_encode($data));
if ($data['action'] == 'exec') {
	echo json_encode(gsh::exec($data));
	die();
}

if ($data['action'] == 'query') {
	echo json_encode(gsh::query($data));
	die();
}

echo json_encode(array(
	'status' => 'SUCCESS',
));
die();
