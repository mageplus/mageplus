<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mage
 * @package   Mage_Adminhtml
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
