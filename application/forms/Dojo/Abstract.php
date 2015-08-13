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
/*
    protected function _getCookie ($name, $default = 0)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    protected function _getOrSetCookie ($name, $default = 0)
    {
        $config = Zend_Registry::get('config');
        if (!isset($_COOKIE[$name]) || $_COOKIE[$name] === false) {
            setcookie(
                $name,
                $config->default->fossils,
                time() + $config->advanced->cookie_expiration,
                '/',
                ''
            );
            return $config->default->fossils;
        }
        return $_COOKIE[$name];
    }
*/
    protected function _getTreeExtinct ()
    {
        if (isset($_SESSION['treeExtinct'])) {
            return $_SESSION['treeExtinct'];
        } else if (isset($_COOKIE['treeExtinct'])) {
            return $_COOKIE['treeExtinct'];
        }
        $config = Zend_Registry::get('config');
        return $config->default->fossils;
    }
}
