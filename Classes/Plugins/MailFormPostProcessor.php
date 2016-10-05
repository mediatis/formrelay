<?php
namespace Mediatis\Formrelay\Plugins;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Franz Geiger <mail_at_fx-g.de>
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

use TYPO3\CMS\Form\Utility\FormUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\PostProcess as Form;
use Mediatis\Formrelay;

class MailFormPostProcessor extends Form\AbstractPostProcessor implements Form\PostProcessorInterface
{

	/**
	 * @var \TYPO3\CMS\Form\Domain\Model\Element
	 */
	protected $form;

	/**
	 * @var array
	 */
	protected $typoScript;

	/**
	 * Constructor
	 *
	 * @param \TYPO3\CMS\Form\Domain\Model\Element $form Form domain model
	 * @param array $typoScript Post processor TypoScript settings
	 */
	public function __construct(\TYPO3\CMS\Form\Domain\Model\Element $form, array $typoScript)
	{
		GeneralUtility::devLog('MailFormPostProcessor:__construct', __CLASS__, 0);
		$this->form = $form;
		$this->typoScript = $typoScript;
	}


	/**
	 * The main method called by the post processor
	 *
	 * Configures the mail message
	 *
	 * @return string HTML message from this processor
	 */
	public function process()
	{
		GeneralUtility::devLog('MailFormPostProcessor:process', __CLASS__, 0);

		$data = $this->getFormData();
		$this->getAdditionalData($data);
		$this->logData($data);
		$this->callPlugins($data);
	}

	private function callPlugins($data)
	{
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['formSend'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['formSend'] as $classReference) {
				$dataHook = GeneralUtility::getUserObj($classReference);

				if ($dataHook instanceof Hook) {

					$dataHook->processData($data);

				} else {
					throw new \InvalidArgumentException(
						'Error detector "' . $classReference . '" must implement interface Mediatis\Formrelay\Hook.',
						1359156192
					);
				}
			}
		}
	}

	private function getFormData()
	{
		$data = array();

		// Get Form data
		foreach ($this->form as $key => $value) {
			$data[$key] = $value;
		}

		return $data;
	}

	private function getAdditionalData(&$data){
		// Add Additional Data
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['formrelay']['dataProvider'] as $classReference) {
				$dataProvider = GeneralUtility::getUserObj($classReference);

				if ($dataProvider instanceof DataProvider) {

					$dataProvider->addData($data);

				} else {
					throw new \InvalidArgumentException(
						'Error detector "' . $classReference . '" must implement interface Mediatis\Formrelay\DataProvider.',
						1359156192
					);
				}
			}
		}
	}


	private function logData()
	{

	}

}
?>