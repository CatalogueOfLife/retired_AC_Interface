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
}
