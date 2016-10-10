<?php
namespace Mediatis\Formrelay\Service;

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