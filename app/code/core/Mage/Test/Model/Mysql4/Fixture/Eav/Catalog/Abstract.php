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
 * Base Catalog EAV fixture loader
 *
 *
 */
abstract class Mage_Test_Model_Mysql4_Fixture_Eav_Catalog_Abstract extends Mage_Test_Model_Mysql4_Fixture_Eav_Abstract
{
    const SCOPE_TYPE_STORE = 'stores';
    const SCOPE_TYPE_WEBSITE = 'websites';

    /**
     * Overriden to add GWS implementation for attribute records
     *
     * @param array $row
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param array $tableColumns
     * @return array
     * (non-PHPdoc)
     * @see Mage_Test_Model_Mysql4_Fixture_Eav_Abstract::_getAttributeRecords()
     */
    protected function _getAttributeRecords($row, $attribute, $tableColumns)
    {
        $records = parent::_getAttributeRecords($row, $attribute, $tableColumns);

        // If the attribute is not global,
        // then walk over all websites and stores scopes for attribute value
        if ($attribute->isScopeStore() || $attribute->isScopeWebsite()) {
            // Search for website values and fullfil data per website's store
            $storeValues = array();
            foreach ($this->_getGwsCodes($row, self::SCOPE_TYPE_WEBSITE) as $websiteCode) {
                $website = Mage::app()->getWebsite($websiteCode);

                $value = $this->_getGwsValue($row, $attribute, $websiteCode, self::SCOPE_TYPE_WEBSITE);
                if ($value !== null) {
                    foreach ($website->getStoreIds() as $storeId) {
                        $storeValues[$storeId] = $value;
                    }
                }
            }

            // If attribute has store scope, then override website values by store ones
            if ($attribute->isScopeStore()) {
                foreach ($this->_getGwsCodes($row, self::SCOPE_TYPE_STORE) as $storeCode) {
                    $store = Mage::app()->getStore($storeCode);
                    $value = $this->_getGwsValue($row, $attribute, $storeCode, self::SCOPE_TYPE_STORE);
                    if ($value !== null) {
                        $storeValues[$store->getId()] = $value;
                    }
                }
            }

            // Apply collected values
            $valueInfo = $this->_getAttributeValueInfo($row, $attribute);
            foreach ($storeValues as $storeId => $value) {
                $valueInfo['store_id'] = $storeId;
                $valueInfo['value'] = $value;
                $records[] = $this->_getTableRecord($valueInfo, $tableColumns);
            }
        }

        return $records;
    }

    /**
     * Check is available store/website values
     *
     * @param array $row
     * @param string $scopeType
     * @return boolean
     */
    protected function _hasGwsValues($row, $scopeType = self::SCOPE_TYPE_STORE)
    {
        return isset($row['/' . $scopeType]);
    }

    /**
     * Retrieves list of websites/stores codes
     *
     * @param array $row
     * @param string $scopeType
     */
    protected function _getGwsCodes($row, $scopeType = self::SCOPE_TYPE_STORE)
    {
        if (!$this->_hasGwsValues($row, $scopeType)) {
            return array();
        }

        return array_keys($row['/' . $scopeType]);
    }

    /**
     * Retrieves scope dependent value from fixture value, i.e,
     * store view or
     * website attribute value
     *
     *
     * @param array $row
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @param string $scopeCode
     * @param string $scopeType
     * @return mixed|null
     */
    protected function _getGwsValue($row, $attribute, $scopeCode,  $scopeType = self::SCOPE_TYPE_STORE)
    {
        if (!isset($row['/' . $scopeType][$scopeCode])
            || !is_array($row['/' . $scopeType][$scopeCode])
            || !isset($row['/' . $scopeType][$scopeCode][$attribute->getAttributeCode()])) {
            return null;
        }

        return $this->_getAttributeValue($row['/' . $scopeType][$scopeCode], $attribute);
    }

    /**
     * Overriden to add default store id
     * (non-PHPdoc)
     * @see Mage_Test_Model_Mysql4_Fixture_Eav_Abstract::_getAttributeValueInfo()
     */
    protected function _getAttributeValueInfo($row, $attribute)
    {
        $info = parent::_getAttributeValueInfo($row, $attribute);
        $info['store_id'] = 0;
        return $info;
    }
}
