<?php 
namespace Mediatis\Formrelay\DataProvider;

class LanguageCode implements DataProvider 
{
	public function addData(&$dataArray)
	{
		$dataArray['language'] = $GLOBALS['TSFE']->tmpl->setup['config.']['language'];
	}
}
?>