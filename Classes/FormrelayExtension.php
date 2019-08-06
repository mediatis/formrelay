<?php

namespace Mediatis\Formrelay;

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

/**
 * Abstract class for sub-extensions as entry point
 *
 * @author  Stephan Ude (mediatis AG) <ude@mediatis.de>
 * @package TYPO3
 * @subpackage  formrelay
 */
abstract class FormrelayExtension implements FormrelayExtensionInterface
{
    abstract protected function getExtensionKey();

    public function registerExtension(array &$extensionList)
    {
        array_push($extensionList, $this->getExtensionKey());
        return [$extensionList];
    }

    protected function proceed($extKey)
    {
        return $this->getExtensionKey() === $extKey;
    }

    public function beforePermissionCheck($extKey, $index, $conf, $data, $attachments, $result)
    {
        if ($this->proceed($extKey)) {
            return $this->runBeforePermissionCheck($extKey, $index, $conf, $data, $attachments, $result);
        }
        return ['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result];
    }

    public function afterPermissionCheck($extKey, $index, $conf, $data, $attachments, $result)
    {
        if ($this->proceed($extKey)) {
            return $this->runAfterPermissionCheck($extKey, $index, $conf, $data, $attachments, $result);
        }
        return ['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result];
    }

    public function beforeDataMapping($extKey, $index, $conf, $data, $attachments, $result)
    {
        if ($this->proceed($extKey)) {
            return $this->runBeforeDataMapping($extKey, $index, $conf, $data, $attachments, $result);
        }
        return ['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result];
    }

    public function afterDataMapping($extKey, $index, $conf, $data, $attachments, $result)
    {
        if ($this->proceed($extKey)) {
            return $this->runAfterDataMapping($extKey, $index, $conf, $data, $attachments, $result);
        }
        return ['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result];
    }

    public function dispatch($extKey, $index, $conf, $data, $attachments, $result)
    {
        if ($this->proceed($extKey)) {
            return $this->runDispatch($extKey, $index, $conf, $data, $attachments, $result);
        }
        return ['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result];
    }

    protected function runBeforePermissionCheck($extKey, $index, $conf, $data, $attachments, $result)
    {
        return ['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result];
    }

    protected function runAfterPermissionCheck($extKey, $index, $conf, $data, $attachments, $result)
    {
        return ['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result];
    }

    protected function runBeforeDataMapping($extKey, $index, $conf, $data, $attachments, $result)
    {
        return ['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result];
    }

    protected function runAfterDataMapping($extKey, $index, $conf, $data, $attachments, $result)
    {
        return ['extKey' => $extKey, 'index' => $index, 'conf' => $conf, 'data' => $data, 'attachments' => $attachments, 'result' => $result];
    }

    abstract protected function runDispatch($extKey, $index, $conf, $data, $attachments, $result);
}
