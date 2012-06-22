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
interface Mage_Test_Model_Fixture_Interface extends Mage_Test_Model_Test_Loadable_Interface
{
    const SCOPE_LOCAL = 'local';
    const SCOPE_SHARED = 'shared';

    /**
     * Sets fixture options
     *
     * @param array $options
     * @return Mage_Test_Model_Fixture_Interface
     */
    public function setOptions(array $options);

    /**
     * Sets storage for fixtures
     *
     * @param Varien_Object $storage
     * @return Mage_Test_Model_Fixture_Interface
     */
    public function setStorage(Varien_Object $storage);

    /**
     * Retrieve fixture storage
     *
     * @return Varien_Object
     */
    public function getStorage();

    /**
     * Retrieves storage data for a particular fixture scope
     *
     * @param string $key
     * @param string|null $scope
     */
    public function getStorageData($key, $scope = null);

    /**
     * Sets storage data for a particular fixture scope
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $scope
     */
    public function setStorageData($key, $value, $scope = null);

    /**
     * Returns current fixture scope
     *
     * @return string
     */
    public function getScope();

    /**
     * Sets current fixture scope
     *
     *
     * @param string $scope Mage_Test_Model_Fixture_Interface::SCOPE_LOCAL|Mage_PHPUnit_Model_Fixture_Interface::SCOPE_SHARED
     */
    public function setScope($scope);

    /**
     * Check that current fixture scope is equal to SCOPE_SHARED
     *
     * @return boolean
     */
    public function isScopeShared();

    /**
     * Check that current fixture scope is equal to SCOPE_LOCAL
     *
     * @return boolean
     */
    public function isScopeLocal();

    /**
     * Loads fixture files from test class annotations
     *
     * @param string $className
     * @return Mage_Test_Model_Fixture_Interface
     */
    public function loadForClass($className);
}
