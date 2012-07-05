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
 * @category    Magento
 * @package     Mage_XmlConnect
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_XmlConnect_Test_Helper_Catalog_Image extends Mage_Test_Unit_Case
{
    /**
     * @param string $name
     * @dataProvider getPlaceholderDataProvider
     */
    public function testGetPlaceholder($name)
    {
        $helper = new Mage_XmlConnect_Helper_Catalog_Category_Image;
        $helper->initialize(new Mage_Catalog_Model_Product, $name);
        //$this->assertFileExists(Mage::getDesign()->getSkinFile($helper->getPlaceholder()));
    }

    /**
     * @return array
     */
    public function getPlaceholderDataProvider()
    {
        return array(
            array('image'),
            array('small_image'),
            array('thumbnail'),
        );
    }
}
