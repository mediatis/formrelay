<?php
namespace Mediatis\Formrelay\Service;

/***************************************************************
*  Copyright notice
*
*  (c) 2016 Michael Vöhringer (Mediatis AG) <voehringer@mediatis.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Mediatis\Formrelay\Utility\FormrelayUtility;

class FormrelayManager
{

	/**
	 * @var array
	 */
	protected $settings;


	public function __construct(){
		// Todo:: use ConfigutationManager to load settings
		//
		$typoScript = FormrelayUtility::loadPluginTS('tx_formrelay');
		$this->settings = $typoScript['.settings'];
		// GeneralUtility::devLog('MailFormPostProcessor:process settings', __CLASS__, 0, $typoScript);
	}

	public function process($data)
	{
		$this->getAdditionalData($data);
		$this->logData($data);
		$this->callPlugins($data);
		// GeneralUtility::devLog('MailFormPostProcessor:process data', __CLASS__, 0, $data);
	}

	private function callPlugins(&$data)
	{
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProcessor'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProcessor'] as $classReference) {
				$dataHook = GeneralUtility::getUserObj($classReference);

				if ($dataHook instanceof \Mediatis\Formrelay\DataProcessorInterface) {

					$dataHook->processData($data);

				} else {
					throw new \InvalidArgumentException(
						'Error detector "' . $classReference . '" must implement interface Mediatis\Formrelay\DataProcessorInterface.',
						1359156192
					);
				}
			}
		}
	}

	private function getAdditionalData(&$data){
		// Add Additional Data
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'] as $classReference) {
				$dataProvider = GeneralUtility::getUserObj($classReference);

				if ($dataProvider instanceof \Mediatis\Formrelay\DataProviderInterface) {

					$dataProvider->addData($data);

				} else {
					throw new \InvalidArgumentException(
						'Error detector "' . $classReference . '" must implement interface Mediatis\Formrelay\DataProviderInterface.',
						1359156192
					);
				}
			}
		}
	}


	private function logData()
	{
		GeneralUtility::devLog('TODO LogData', __CLASS__, 0);
	}
}
?>