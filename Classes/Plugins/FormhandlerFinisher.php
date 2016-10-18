<?php
namespace Mediatis\Formrelay\Plugins;

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
use Mediatis\Formrelay\Service\FormrelayManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FormhandlerFinisher extends \Typoheads\Formhandler\Finisher\AbstractFinisher
{
    protected $gp = array();

    public function process()
    {
        GeneralUtility::devLog('FormhandlerFinisher:process $this->gp', __CLASS__, 0, $this->gp);

        $formrelayManager = GeneralUtility::makeInstance(FormrelayManager::class);
        $formrelayManager->process($this->gp);
        return $this->gp;
    }

    /**
     * Method to set GET/POST for this class and load the configuration
     *
     * @param array The GET/POST values
     * @param array The TypoScript configuration
     * @return void
     */
    public function init($gp, $tsConfig)
    {
        GeneralUtility::devLog('FormhandlerFinisher:init gp', __CLASS__, 0, $gp);
        $this->gp = $gp;
        $this->settings = $tsConfig;
    }
}
