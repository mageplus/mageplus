<?php
/**
 * PHP Unit test suite for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Mage
 * @package    Mage_Test
 * @copyright  Copyright (c) 2012 EcomDev BV (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Ivan Chepurnyi <ivan.chepurnyi@ecomdev.org>
 */

class Mage_Test_Model_Design_Package
    extends Mage_Core_Model_Design_Package
    implements Mage_PHPUnit_Design_Package_Interface,
               Mage_PHPUnit_Isolation_Interface

{
    /**
     * Asserts layout file existance in design packages,
     * and returns actual and expected filenames as result
     *
     * @param string $fileName
     * @param string $area
     * @param string|null $designPackage if not specified any theme will be used
     * @param string|null $theme if not specified any theme will be used
     * @return array of 'expected' and 'actual' file names
     */
    public function getLayoutFileAssertion($fileName, $area, $designPackage = null, $theme = null)
    {
        $this->reset();
        $this->setArea($area);

        if ($area == Mage_Test_Model_App::AREA_ADMINHTML) {
            $this->setStore(Mage_Test_Model_App::ADMIN_STORE_CODE);
        } else {
            $this->setStore(Mage::app()->getAnyStoreView()->getCode());
        }

        $this->setPackageName($designPackage);
        $this->setTheme($theme);

        $params = array(
            '_area' => $area,
            '_package' => $designPackage,
            '_theme' => $theme,
        );
        $actualFileName = $this->getLayoutFilename($fileName, $params);

        if ($theme !== null || $designPackage !== null) {
            $expectedTheme = $this->getTheme('layout');
            $expectedDesignPackage = $this->getPackageName();
            $params = array(
                '_type' => 'layout',
                '_theme' => $expectedTheme,
                '_package' => $expectedDesignPackage
            );

            $expectedFileName = $this->_renderFilename($fileName, $params);
        } else {
            $expectedFileName = $actualFileName;
            if (!file_exists($actualFileName)) {
                $actualFileName = false;
            }
        }

        return array(
            'actual' => $actualFileName,
            'expected' => $expectedFileName
        );
    }

    /**
     * Resets design data
     *
     * @return Mage_Test_Model_Design_Package
     */
    public function reset()
    {
        $this->_store = null;
        $this->_area = null;
        $this->_name = null;
        $this->_theme = null;
        $this->_config = null;
        $this->_rootDir = null;
        $this->_callbackFileDir = null;

        return $this;
    }
}
