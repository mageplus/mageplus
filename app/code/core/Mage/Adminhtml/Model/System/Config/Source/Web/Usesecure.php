<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Axel Helmert <ah@luka.de>
 * @copyright Copyright (c) 2012 LUKA netconsult GmbH (www.luka.de)
 * @license   $license$
 * @version   $Id$
 */

class Mage_Adminhtml_Model_System_Config_Source_Web_Usesecure
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Mage_Core_Model_Store::WEB_FRONTEND_INSECURE, 'label'=>Mage::helper('adminhtml')->__('Disabled')),
            array('value' => Mage_Core_Model_Store::WEB_FRONTEND_SECURE_PARTIALLY, 'label'=>Mage::helper('adminhtml')->__('Enabled for pages consuming sensitive user data')),
            array('value' => Mage_Core_Model_Store::WEB_FRONTEND_SECURE_ALL, 'label'=>Mage::helper('adminhtml')->__('Enabled for all Pages')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Mage_Core_Model_Store::WEB_FRONTEND_INSECURE => Mage::helper('adminhtml')->__('Disabled'),
            Mage_Core_Model_Store::WEB_FRONTEND_SECURE_PARTIALLY => Mage::helper('adminhtml')->__('Enabled for pages consuming sensitive user data'),
            Mage_Core_Model_Store::WEB_FRONTEND_SECURE_ALL => Mage::helper('adminhtml')->__('Enabled for all Pages'),
        );
    }
}
