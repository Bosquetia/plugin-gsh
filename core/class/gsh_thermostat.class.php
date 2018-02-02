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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class gsh_thermostat {

	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	public static function buildDevice($_device) {
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return 'deviceNotFound';
		}
		$return = array();
		$return['id'] = $eqLogic->getId();
		$return['type'] = $_device->getType();
		$return['name'] = array('name' => $eqLogic->getHumanName(), 'nicknames' => array($eqLogic->getName()));
		$return['traits'] = array('action.devices.traits.TemperatureSetting');
		$return['willReportState'] = true;
		$return['attributes'] = array('availableThermostatModes' => 'on,off,heat,cool', 'thermostatTemperatureUnit' => 'C');

		return $return;
	}

	public static function query($_device) {
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return array('status' => 'ERROR');
		}
		return self::getState($_device);
	}

	public static function exec($_device, $_executions, $_infos) {
		$return = array('status' => 'ERROR');
		$eqLogic = $_device->getLink();
		if (!is_object($eqLogic)) {
			return $return;
		}
		foreach ($_executions as $execution) {
			try {
				switch ($execution['command']) {
					case 'action.devices.commands.ThermostatTemperatureSetpoint':
						$cmd = $_device->getCmdByGenericType(array('THERMOSTAT_SET_SETPOINT'));
						if (is_object($cmd)) {
							$cmd->execCmd(array('slider' => $execution['params']['thermostatTemperatureSetpoint']));
							$return = array('status' => 'SUCCESS');
						}
						break;
					case 'action.devices.commands.ThermostatSetMode':
						$cmds = $_device->getCmdByGenericType(array('THERMOSTAT_SET_MODE'));
						if ($cmds == null) {
							break;
						}
						if (is_array($cmds)) {
							$cmds = array($cmds);
						}
						foreach ($cmds as $cmd) {
							if ($execution['params']['thermostatMode'] == $cmd->getName()) {
								$cmd->execCmd();
							}
						}
						break;
				}
			} catch (Exception $e) {
				$return = array('status' => 'ERROR');
			}
		}
		$return['states'] = self::getState($_device);
		return $return;
	}

	public static function getState($_device) {
		$return = array();
		$return['online'] = true;
		$cmd = $_device->getCmdByGenericType(array('THERMOSTAT_STATE'));
		if ($cmd != null) {
			$return['thermostatMode'] = ($cmd->execCmd()) ? 'on' : 'off';
		}
		$cmd = $_device->getCmdByGenericType(array('THERMOSTAT_STATE_NAME'));
		if ($cmd != null && $return['thermostatMode'] == 'on') {
			$value = $cmd->execCmd();
			if ($value == __('Chauffage', __FILE__)) {
				$return['thermostatMode'] = 'heat';
			}
			if ($value == __('Climatisation', __FILE__)) {
				$return['thermostatMode'] = 'cool';
			}
		}
		$cmd = $_device->getCmdByGenericType(array('THERMOSTAT_SETPOINT'));
		if ($cmd != null) {
			$return['thermostatTemperatureSetpoint'] = $cmd->execCmd();
		}
		$cmd = $_device->getCmdByGenericType(array('THERMOSTAT_TEMPERATURE'));
		if ($cmd != null) {
			$return['thermostatTemperatureAmbient'] = $cmd->execCmd();
		}
		return $return;
	}

	/*     * *********************Méthodes d'instance************************* */

	/*     * **********************Getteur Setteur*************************** */

}