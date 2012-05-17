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

/**
 * Interface for fixture model
 * Can be used for creation of
 * absolutely different implementation of fixture,
 * then current one.
 *
 */
interface Mage_Test_Model_Expectation_Interface extends Mage_Test_Model_Test_Loadable_Interface
{
    /**
     * Returns data object with expectations
     *
     * @param string $pathFormat
     * @param array $args arguments for format function
     * @return Mage_Test_Model_Expectation_Object
     */
    public function getDataObject($pathFormat = null, $args = array());

    /**
     * Check is expectation loaded
     *
     * @return boolean
     */
    public function isLoaded();
}