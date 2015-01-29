<?php
/**
 * Annual Checklist Interface
 *
 * Abstract class ACI_Form_Dojo_Abstract
 * Single form dojo-enabled form model
 *
 * @category    ACI
 * @package     application
 * @subpackage  forms
 *
 */

abstract class ACI_Form_Dojo_Abstract extends Zend_Dojo_Form
{
    protected function _moduleEnabled ($module)
    {
        return Bootstrap::instance()->getOption('module.' . $module);
    }

    protected function _getCookie ($name, $default = 0)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

}
