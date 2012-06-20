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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Test_Model_Design_Package extends Mage_Test_Unit_Case
{
    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        $fixtureDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files';
        Mage::app()->getConfig()->getOptions()->setDesignDir($fixtureDir . DIRECTORY_SEPARATOR . 'design');
        Varien_Io_File::rmdirRecursive(Mage::app()->getConfig()->getOptions()->getMediaDir() . '/skin');
    }

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Design_Package();
        //$this->_model->setDesignTheme('test/default/default', 'frontend');
    }

    public function testSetGetArea()
    {
        $this->assertEquals(Mage_Core_Model_Design_Package::DEFAULT_AREA, $this->_model->getArea());
        $this->_model->setArea('test');
        $this->assertEquals('test', $this->_model->getArea());
    }

    public function testGetPackageName()
    {
        $this->assertEquals('test', $this->_model->getPackageName());
    }

    public function testGetTheme()
    {
        $this->assertEquals('default', $this->_model->getTheme());
    }

    public function testSetDesignTheme()
    {
        //$this->_model->setDesignTheme('test/test/test', 'test');
        $this->assertEquals('test', $this->_model->getArea());
        $this->assertEquals('test', $this->_model->getPackageName());
        $this->assertEquals('test', $this->_model->getSkin());
        $this->assertEquals('test', $this->_model->getSkin());
    }

    /**
     * @dataProvider getFilenameDataProvider
     */
    public function testGetFilename($file, $params)
    {
        $this->assertFileExists($this->_model->getFilename($file, $params));
    }

    /**
     * @return array
     */
    public function getFilenameDataProvider()
    {
        return array(
            array('theme_file.txt', array('_module' => 'Mage_Catalog')),
            array('Mage_Catalog::theme_file.txt', array()),
            array('Mage_Catalog::theme_file_with_2_dots..txt', array()),
            array('Mage_Catalog::theme_file.txt', array('_module' => 'Overriden_Module')),
        );
    }

    public function testGetLocaleFileName()
    {
        $this->assertFileExists($this->_model->getLocaleFileName('translate.csv'));
        $this->assertFileExists($this->_model->getLocaleFileName('fallback.csv', array(
            '_package' => 'package', '_theme' => 'custom_theme'
        )));
    }

    public function getOptimalJsUrlsMergedDataProvider()
    {
        return array(
            array(
                array('js/tabs.js', 'calendar/calendar.js'),
                array('http://localhost/pub/media/skin/_merged/c5a9f4afba4ff0ff979445892214fc8b.js',)
            ),
            array(
                array('calendar/calendar.js'),
                array('http://localhost/pub/js/calendar/calendar.js',)
            ),
        );
    }
}
