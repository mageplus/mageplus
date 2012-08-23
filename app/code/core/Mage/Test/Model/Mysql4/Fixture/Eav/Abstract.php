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
 * Base implementation of EAV fixtures loader
 *
 */
abstract class Mage_Test_Model_Mysql4_Fixture_Eav_Abstract extends Mage_Test_Model_Mysql4_Fixture
{
    /**
     * List of indexers required to build
     *
     * @var array
     */
    protected $_requiredIndexers = array();

    /**
     * Fixture options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Fixture model
     *
     * @var Mage_Test_Model_Fixture_Interface
     */
    protected $_fixture = null;

    /**
     * Retrieve required indexers for re-building
     *
     * @var array
     */
    public function getRequiredIndexers()
    {
        return $this->_requiredIndexers;
    }

    /**
     * Sets fixture model to EAV loader
     *
     * @param Mage_Test_Model_Fixture_Interface $fixture
     */
    public function setFixture($fixture)
    {
        $this->_fixture = $fixture;
        return $this;
    }

    /**
     * Set fixture options
     *
     * @param array $options
     * @return Mage_Test_Model_Mysql4_Fixture_Eav_Abstract
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * Add indexer by specific code to required indexers list
     *
     * @param string $code
     * @return Mage_Test_Model_Mysql4_Fixture_Eav_Abstract
     */
    public function addRequiredIndexer($code)
    {
        if (!in_array($code, $this->_requiredIndexers)) {
            $this->_requiredIndexers[] = $code;
        }
        return $this;
    }

    /**
     * Clean entity data table
     *
     * @param string $entityType
     * @return Mage_Test_Model_Mysql4_Fixture_Eav_Abstract
     */
    public function cleanEntity($entityType)
    {
        $entityTypeModel = Mage::getSingleton('eav/config')->getEntityType($entityType);
        $this->cleanTable($entityTypeModel->getEntityTable());
        return $this;
    }

    /**
     * Loads EAV data into DB tables
     *
     * @param string $entityType
     * @param array $values
     * @return Mage_Test_Model_Mysql4_Fixture_Eav_Abstract
     * @throws RuntimeException
     */
    public function loadEntity($entityType, $values)
    {
        $originalRequiredIndexers = $this->_requiredIndexers;
        if (!empty($this->_options['addRequiredIndex'])) {
            foreach ($this->_options['addRequiredIndex'] as $data) {
                if (preg_match('/^([a-z0-9_\\-])+\\s+([a-z0-9_\\-])\s*$/i', $data, $match)
                    && $match[1] == $entityType) {
                    $this->_requiredIndexers[] = $match[2];
                }
            }
        }

        $entityTypeModel = Mage::getSingleton('eav/config')->getEntityType($entityType);


        $entityTableColumns = $this->_getWriteAdapter()->describeTable(
            $this->getTable($entityTypeModel->getEntityTable())
        );

        $attributeTableColumns = $this->_getAttributeTablesColumnList($entityTypeModel);


        $entities = array();
        $entityValues = array();

        // Custom values array is used for
        // inserting custom entity data in custom tables.
        // It is an associative array with table name as key,
        // and rows list as value
        // See getCustomTableRecords
        $customValues = array();

        foreach ($values as $index => &$row) {
            if (!isset($row[$this->_getEntityIdField($entityTypeModel)])) {
                throw new RuntimeException('Entity Id should be specified in EAV fixture');
            }

            // Fulfill necessary information
            $row['entity_type_id'] = $entityTypeModel->getEntityTypeId();
            if (!isset($row['attribute_set_id'])) {
                $row['attribute_set_id'] = $entityTypeModel->getDefaultAttributeSetId();
            }

            // Preparing entity table record
            $entity = $this->_getTableRecord($row, $entityTableColumns);
            $entities[] = $entity;

            // Preparing simple attributes records
            foreach ($entityTypeModel->getAttributeCollection() as $attribute) {
                $attributeBackendTable = $attribute->getBackendTable();
                if (!$attribute->isStatic()
                    && $attributeBackendTable
                    && isset($attributeTableColumns[$attributeBackendTable])) {

                    // Prepearing data for insert per attribute table
                    $attributeRecords = $this->_getAttributeRecords(
                        $row,
                        $attribute,
                        $attributeTableColumns[$attributeBackendTable]
                    );

                    if ($attributeRecords) {
                        if (!isset($entityValues[$attributeBackendTable])) {
                            $entityValues[$attributeBackendTable] = array();
                        }

                        $entityValues[$attributeBackendTable] = array_merge(
                            $entityValues[$attributeBackendTable],
                            $attributeRecords
                        );
                    }
                }
            }

            // Processing custom entity values
            $customValues = array_merge_recursive(
                $customValues,
                $this->_getCustomTableRecords($row, $entityTypeModel)
            );
        }

        $this->_getWriteAdapter()->insertOnDuplicate(
            $this->getTable($entityTypeModel->getEntityTable()),
            $entities
        );

        foreach ($entityValues as $tableName => $records) {
            $this->_getWriteAdapter()->insertOnDuplicate(
                $tableName,
                $records
            );
        }

        foreach ($customValues as $tableName => $records) {
            $this->_getWriteAdapter()->insertOnDuplicate(
                (strpos($tableName, '/') !== false ? $this->getTable($tableName) : $tableName),
                $records
            );
        }

        foreach ($entities as $entity) {
            $this->_customEntityAction($entity, $entityTypeModel);
        }

        if (empty($this->_options['doNotIndexAll'])) {
            $indexer = Mage::getSingleton('index/indexer');
            foreach ($this->getRequiredIndexers() as $indexerCode) {
                if (empty($this->_options['doNotIndex'])
                    || !in_array($indexerCode, $this->_options['doNotIndex'])) {
                    $indexer->getProcessByCode($indexerCode)
                        ->reindexAll();
                }
            }
        }

        // Restoring original required indexers for making tests isolated
        $this->_requiredIndexers = $originalRequiredIndexers;
        return $this;
    }

    /**
     * Performs custom action on entity
     *
     * @param array $entity
     * @param Mage_Eav_Model_Entity_Type $entityTypeModel
     * @return Mage_Test_Model_Mysql4_Fixture_Eav_Abstract
     */
    protected function _customEntityAction($entity, $entityTypeModel)
    {
        return $this;
    }

    /**
     * If you have some custom EAV tables,
     * this method will help you to insert
     * them on fixture processing step
     * It should return an associative array, where an entry key
     * is the table name and its value is a list of table rows
     *
     * @example
     * return array(
     *    'some/table' => array(
     *        array(
     *            'field' => 'value'
     *        )
     *    )
     * )
     *
     * @param array $row
     * @param Mage_Eav_Model_Entity_Type $entityTypeModel
     * @return array
     */
    protected function _getCustomTableRecords($row, $entityTypeModel)
    {
        return array();
    }

    /**
     * Retrieves associative list of attribute tables and their columns
     *
     * @param Mage_Eav_Model_Entity_Type $entityTypeModel
     * @return array
     */
    protected function _getAttributeTablesColumnList($entityTypeModel)
    {
        $tableNames = array_unique(
            $entityTypeModel->getAttributeCollection()
                ->walk('getBackendTable')
        );

        $columnsByTable = array();

        foreach ($tableNames as $table) {
            if ($table) {
                $columnsByTable[$table] = $this->_getWriteAdapter()
                    ->describeTable(
                        $table
                    );
            }
        }

        return $columnsByTable;
    }

    /**
     * Retrieves attribute records for single entity
     *
     * @param array $row
     * @param array $attribute
     * @param Mage_Eav_Model_Entity_Type $entityTypeModel
     */
    protected function _getAttributeRecords($row, $attribute, $tableColumns)
    {
        $records = array();

        $value = $this->_getAttributeValue($row, $attribute);

        if ($value !== null) {
            $valueInfo = $this->_getAttributeValueInfo($row, $attribute);
            $valueInfo['value'] = $value;
            $records[] = $this->_getTableRecord($valueInfo, $tableColumns);
        }

        return $records;
    }

    /**
     * Returns attribute meta info for record,
     * e.g. entity_type_id, attribute_id, etc
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @return array
     */
    protected function _getAttributeValueInfo($row, $attribute)
    {
        return array(
            'attribute_id' => $attribute->getId(),
            'entity_type_id' => $attribute->getEntityTypeId(),
            $this->_getEntityIdField($attribute) => $row[$this->_getEntityIdField($attribute)]
        );
    }

    /**
     * Retrieves attribute value
     *
     * @param array $row
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @return mixed|null
     */
    protected function _getAttributeValue($row, $attribute)
    {
        if (isset($row[$attribute->getAttributeCode()]) && !is_array($row[$attribute->getAttributeCode()])) {
            $value = $row[$attribute->getAttributeCode()];
        } elseif ($attribute->getIsRequired()
                  && $attribute->getDefaultValue() !== null
                  && $attribute->getDefaultValue() !== ''
                  && !is_array($attribute->getDefaultValue())) {
            $value = $attribute->getDefaultValue();
        } else {
            $value = null;
        }

        if ($attribute->usesSource() && $value !== null) {
            if ($attribute->getSource() instanceof Mage_Eav_Model_Entity_Attribute_Source_Abstract) {
                $value = $attribute->getSource()->getOptionId($value);
            } else {
                $value = $this->_getOptionIdNonAttributeSource($attribute->getSource()->getAllOptions(), $value);
            }
        }

        return $value;
    }

    /**
     * Get option id if attribute source model doesn't support eav attribute interface
     *
     *
     * @param array $options
     * @param mixed $value
     */
    protected function _getOptionIdNonAttributeSource($options, $value)
    {
        foreach ($options as $option) {
            if (strcasecmp($option['label'], $value)==0 || $option['value'] == $value) {
                return $option['value'];
            }
        }

        return null;
    }

    /**
     * Retrieves entity id field, based on entity configuration
     *
     * @param Mage_Eav_Model_Entity_Type|Mage_Eav_Model_Entity_Attribute $entityTypeModel
     * @return string
     */
    protected function _getEntityIdField($entityTypeModel)
    {
        if ($entityTypeModel->getEntityIdField()) {
            return $entityTypeModel->getEntityIdField();
        }
        return Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD;
    }
}
