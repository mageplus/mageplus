<?php

require 'app/Mage.php';

if (version_compare(PHP_VERSION, '5.3', '<')) {
    exit('Magento Unit Tests can be run only on PHP version over 5.3');
}

if (!Mage::isInstalled()) {
    exit('Magento Unit Tests can be run only after Magento is installed');
}

/* Replace server variables for proper file naming */
$_SERVER['SCRIPT_NAME'] = dirname(__FILE__) . DS . 'index.php';
$_SERVER['SCRIPT_FILENAME'] = dirname(__FILE__) . DS . 'index.php';

Mage::app('admin');
Mage::getConfig()->init();

class UnitTests extends Mage_Test_Unit_Suite
{

}
