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

// Loading Spyc yaml parser,
// because Symfony component is not working properly with nested structures
require_once 'Spyc/spyc.php';

/**
 * Fixture model for Magento unit tests
 *
 * Created for operations with different fixture types
 *
 */
class Mage_Test_Model_Fixture
    extends Mage_Core_Model_Abstract
    implements Mage_Test_Model_Fixture_Interface
{
    // Configuration path for eav loaders
    const XML_PATH_FIXTURE_EAV_LOADERS = 'phpunit/suite/fixture/eav';

    // Default eav loader class node in loaders configuration
    const DEFAULT_EAV_LOADER_NODE = 'default';

    // Default shared fixture name
    const DEFAULT_SHARED_FIXTURE_NAME = 'default';

    // Default eav loader class alias
    const DEFAULT_EAV_LOADER_CLASS = 'mage_test/fixture_eav_default';

    // Key for storing fixture data into storage
    const STORAGE_KEY_FIXTURE = 'fixture';

    // Key for loaded tables into database
    const STORAGE_KEY_TABLES = 'tables';

    // Key for loaded entities by EAV loaders
    const STORAGE_KEY_ENTITIES = 'entities';

    // Key for loaded cache options 
    const STORAGE_KEY_CACHE_OPTIONS = 'cache_options';
    
    // Key for created scope models
    const STORAGE_KEY_SCOPE = 'scope';

    /**
     * Fixtures array, contains config,
     * table and eav keys.
     * Each of them loads data into its area.
     *
     * @example
     * array(
     *    'config' => array(
     *        'node/path' => 'value'
     *    ),
     *    'table' => array(
     *        'tablename' => array(
     *            array(
     *                'column1' => 'value'
     *                'column2' => 'value'
     *                'column3' => 'value'
     *            ), // row 1
     *           array(
     *                'column1' => 'value'
     *                'column2' => 'value'
     *                'column3' => 'value'
     *            ) // row 2
     *        )
     *    )
     *
     * )
     *
     * @var array
     */
    protected $_fixture = array();

    /**
     * Storage object, for storing data between tests
     *
     * @var Varien_Object
     */
    protected $_storage = null;

    /**
     * Scope of the fixture,
     * used for different logic depending on
     *
     * @var string
     */
    protected $_scope = self::SCOPE_LOCAL;

    /**
     * Fixture options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * List of scope model aliases by scope type
     *
     * @var array
     */
    protected static $_scopeModelByType = array(
        'store' => 'core/store',
        'group' => 'core/store_group',
        'website' => 'core/website'
    );

    /**
     * Associative array of configuration nodes xml that was changed by fixture,
     * it is used to preserve
     * @deprecated since 0.2.1
     *
     * @var array
     */
    protected $_originalConfigurationXml = array();

    /**
     * Hash of current scope instances (store, website, group)
     *
     * @deprecated since 0.2.1
     * @return array
     */
    protected $_currentScope = array();

    /**
     * Model constructor, just defines which resource model to use
     * (non-PHPdoc)
     * @see Varien_Object::_construct()
     */
    protected function _construct()
    {
        $this->_init('mage_test/fixture');
    }

    /**
     * Set fixture options
     *
     * @param array $options
     * @return Mage_Test_Model_Fixture
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
        return $this;
    }

    /**
     * Sets storage for fixtures
     *
     * @param Varien_Object $storage
     * @return Mage_Test_Model_Fixture
     */
    public function setStorage(Varien_Object $storage)
    {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * Retrieve fixture storage
     *
     * @return Varien_Object
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * Retrieves storage data for a particular fixture scope
     *
     * @param string $key
     * @param string|null $scope
     * @return mixed
     */
    public function getStorageData($key, $scope = null)
    {
        if ($scope === null) {
            $scope = $this->getScope();
        }

        $dataKey = sprintf('%s_%s', $scope, $key);

        return $this->getStorage()->getData($dataKey);
    }

    /**
     * Sets storage data for a particular fixture scope
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $scope
     * @return Mage_Test_Model_Fixture
     */
    public function setStorageData($key, $value, $scope = null)
    {
        if ($scope === null) {
            $scope = $this->getScope();
        }

        $dataKey = sprintf('%s_%s', $scope, $key);

        $this->getStorage()->setData($dataKey, $value);

        return $this;
    }

    /**
     * Returns current fixture scope
     *
     * @return string
     */
    public function getScope()
    {
        return $this->_scope;
    }

    /**
     * Sets current fixture scope
     *
     * @param string $scope Mage_Test_Model_Fixture_Interface::SCOPE_LOCAL|Mage_Test_Model_Fixture_Interface::SCOPE_SHARED
     * @return Mage_Test_Model_Fixture
     */
    public function setScope($scope)
    {
        $this->_scope = $scope;
        return $this;
    }

    /**
     * Check that current fixture scope is equal to SCOPE_SHARED
     *
     * @return boolean
     */
    public function isScopeShared()
    {
        return $this->getScope() === self::SCOPE_SHARED;
    }

    /**
     * Check that current fixture scope is equal to SCOPE_LOCAL
     *
     * @return boolean
     */
    public function isScopeLocal()
    {
        return $this->getScope() === self::SCOPE_LOCAL;
    }

    /**
     * Loads fixture files from test case annotations
     *
     * @param Mage_Test_Unit_Case $testCase
     * @return Mage_Test_Model_Fixture
     */
    public function loadByTestCase(Mage_Test_Unit_Case $testCase)
    {
        $fixtures = $testCase->getAnnotationByName(
            'loadFixture',
            array('class', 'method')
        );
        
        
        $cacheOptions = $testCase->getAnnotationByName('cache', 'method');
        
        $this->_parseCacheOptions($cacheOptions);

        $this->_loadFixtureFiles($fixtures, $testCase);

        return $this;
    }

    /**
     * Loads fixture files from test class annotations
     *
     * @param string $className
     * @return Mage_Test_Model_Fixture
     */
    public function loadForClass($className)
    {
        $reflection = Mage_Utils_Reflection::getRelflection($className);

        $method = $reflection->getMethod('getAnnotationByNameFromClass');

        if (!$method instanceof ReflectionMethod) {
            throw new RuntimeException('Unable to read class annotations, because it is not extended from Mage_Test_Unit_Case');
        }

        $fixtures = $method->invokeArgs(
            null, array($className, 'loadSharedFixture', 'class')
        );

        $cacheOptions = $method->invokeArgs(
            null, array($className, 'cache', 'class')
        );
        
        $this->_parseCacheOptions($cacheOptions);
        
        $this->_loadFixtureFiles($fixtures, $className);
        return $this;
    }
    
    /**
     * Loads test case cache on off annotations
     * 
     * @param array $annotations
     * @return Mage_Test_Model_Fixture
     */
    protected function _parseCacheOptions($annotations)
    {
        $cacheOptions = array();
        foreach ($annotations as $annotation) {
            list($action, $cacheType) = preg_split('/\s+/', trim($annotation));
            $flag = ($action === 'off' ? 0 : 1);
            if ($cacheType === 'all') {
                foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
                    $cacheOptions[$type->getId()] = $flag;
                }
            } else {
                $cacheOptions[$cacheType] = $flag;
            }
            
        }
        
        if ($cacheOptions) {
            $this->_fixture['cache_options'] = $cacheOptions;
        }
    }

    /**
     * Loads fixture files
     *
     * @param array $fixtures
     * @param string|Mage_Test_Unit_Case $classOrInstance
     * @return Mage_Test_Model_Fixture
     */
    protected function _loadFixtureFiles(array $fixtures, $classOrInstance)
    {
        $isShared = ($this->isScopeShared() || !$classOrInstance instanceof Mage_Test_Unit_Case);
        foreach ($fixtures as $fixture) {
            if (empty($fixture) && $isShared) {
                $fixture = self::DEFAULT_SHARED_FIXTURE_NAME;
            } elseif (empty($fixture)) {
                $fixture = null;
            }

            $filePath = false;

            if ($isShared) {
                $reflection = Mage_Utils_Reflection::getRelflection($classOrInstance);
                $method = $reflection->getMethod('getYamlFilePathByClass');
                if ($method instanceof ReflectionMethod) {
                    $filePath = $method->invokeArgs(null, array($classOrInstance, 'fixtures', $fixture));
                }
            } else {
                $filePath = $classOrInstance->getYamlFilePath('fixtures', $fixture);
            }

            if (!$filePath) {
                throw new RuntimeException('Unable to load fixture for test');
            }

            $this->loadYaml($filePath);
        }

        return $this;
    }

    /**
     * Load YAML file
     *
     * @param string $filePath
     * @return Mage_Test_Model_Fixture
     * @throws InvalidArgumentException if file is not a valid YAML file
     */
    public function loadYaml($filePath)
    {
        $data = Spyc::YAMLLoad($filePath);

        if (empty($this->_fixture)) {
            $this->_fixture = $data;
        } else {
            $this->_fixture = array_merge_recursive($this->_fixture, $data);
        }

        return $this;
    }

    /**
     * Applies loaded fixture
     *
     * @return Mage_Test_Model_Fixture
     */
    public function apply()
    {
        $this->setStorageData(self::STORAGE_KEY_FIXTURE, $this->_fixture);
        $reflection = Mage_Utils_Reflection::getRelflection($this);

        foreach ($this->_fixture as $part => $data) {
            $method = '_apply' . uc_words($part, '', '_');
            if ($reflection->hasMethod($method)) {
                $this->$method($data);
            }
        }

        // Clear fixture for getting rid of duoble processing
        $this->_fixture = array();
        return $this;
    }

    /**
     * Reverts environment to previous state
     *
     * @return Mage_Test_Model_Fixture
     */
    public function discard()
    {
        $fixture = $this->getStorageData(self::STORAGE_KEY_FIXTURE);

        if (!is_array($fixture)) {
            $fixture = array();
        }

        $this->_fixture = $fixture;
        $this->setStorageData(self::STORAGE_KEY_FIXTURE, null);
        $reflection = Mage_Utils_Reflection::getRelflection($this);
        foreach ($this->_fixture as $part => $data) {
            $method = '_discard' . uc_words($part, '', '_');
            if ($reflection->hasMethod($method)) {
                $this->$method($data);
            }
        }

        $this->_fixture = array();
    }
    
    /**
     * Applies cache options for current test or test case
     * 
     * @param array $options
     * @return Mage_Test_Model_Fixture
     */
    protected function _applyCacheOptions($options)
    {
        $originalOptions = Mage::app()->getCacheOptions();
        $this->setStorageData(self::STORAGE_KEY_CACHE_OPTIONS, $originalOptions);
        
        $options += $originalOptions;
        Mage::app()->setCacheOptions($options);

        return $this;
    }
    
    /**
     * Discards changes that were made to Magento cache
     * 
     * @return Mage_Test_Model_Fixture
     */
    protected function _discardCacheOptions()
    {
        Mage::app()->setCacheOptions(
            $this->getStorageData(self::STORAGE_KEY_CACHE_OPTIONS)
        );
        return $this;
    }

    /**
     * Applies fixture configuration values into Mage_Core_Model_Config
     *
     * @param array $configuration
     * @return Mage_Test_Model_Fixture
     */
    protected function _applyConfig($configuration)
    {
        if (!is_array($configuration)) {
            throw new InvalidArgumentException('Configuration part should be an associative list');
        }

        Mage::getConfig()->loadScopeSnapshot();

        foreach ($configuration as $path => $value) {
            $this->_setConfigNodeValue($path, $value);
        }

        Mage::getConfig()->loadDb();
        return $this;
    }

    /**
     * Applies raw xml data to config node
     *
     * @param array $configuration
     * @return Mage_Test_Model_Fixture
     */
    protected function _applyConfigXml($configuration)
    {
        if (!is_array($configuration)) {
            throw new InvalidArgumentException('Configuration part should be an associative list');
        }

        foreach ($configuration as $path => $value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException('Configuration value should be a valid xml string');
            }
            try {
                $xmlElement = new Varien_Simplexml_Element($value);
            } catch (Exception $e) {
                throw new InvalidArgumentException('Configuration value should be a valid xml string', 0, $e);
            }

            $node = Mage::getConfig()->getNode($path);

            if (!$node) {
                throw new InvalidArgumentException('Configuration value should be a valid xml string');
            }

            $node->extend($xmlElement, true);
        }

        return $this;
    }

    /**
     * Restores config to a previous configuration scope
     *
     * @return Mage_Test_Model_Fixture
     */
    protected function _restoreConfig()
    {
        Mage::getConfig()->loadScopeSnapshot();
        Mage::getConfig()->loadDb();
        return $this;
    }

    /**
     * Reverts fixture configuration values in Mage_Core_Model_Config
     *
     * @return Mage_Test_Model_Fixture
     */
    protected function _discardConfig()
    {
        $this->_restoreConfig();
        return $this;
    }

    /**
     * Reverts fixture configuration xml values in Mage_Core_Model_Config
     *
     * @return Mage_Test_Model_Fixture
     */
    protected function _discardConfigXml()
    {
        if (!isset($this->_fixture['config'])) {
            $this->_resetConfig();
        }
        return $this;
    }

    /**
     * Applies table data into test database
     *
     * @param array $tables
     * @return Mage_Test_Model_Fixture
     */
    protected function _applyTables($tables)
    {
        if (!is_array($tables)) {
            throw new InvalidArgumentException(
                'Tables part should be an associative list with keys as table entity and values as list of associative rows'
            );
        }

        $ignoreCleanUp = array();

        // Ignore cleaning of tables if shared fixture loaded something
        if ($this->isScopeLocal() && $this->getStorageData(self::STORAGE_KEY_TABLES, self::SCOPE_SHARED)) {
            $ignoreCleanUp = array_keys($this->getStorageData(self::STORAGE_KEY_TABLES, self::SCOPE_SHARED));
        }

        $this->getResource()->beginTransaction();
        foreach ($tables as $tableEntity => $data) {
            if (!in_array($tableEntity, $ignoreCleanUp)) {
                $this->getResource()->cleanTable($tableEntity);
            }

            if (!empty($data)) {
                $this->getResource()->loadTableData($tableEntity, $data);
            }
        }
        $this->getResource()->commit();
        $this->setStorageData(self::STORAGE_KEY_TABLES, $tables);
    }

    /**
     * Removes table data from test data base
     *
     * @param array $tables
     * @return Mage_Test_Model_Fixture
     */
    protected function _discardTables($tables)
    {
        if (!is_array($tables)) {
            throw new InvalidArgumentException(
                'Tables part should be an associative list with keys as table entity and values as list of associative rows'
            );
        }

        $restoreTableData = array();

        // Data for tables used in shared fixture
        if ($this->isScopeLocal() && $this->getStorageData(self::STORAGE_KEY_TABLES, self::SCOPE_SHARED)) {
            $restoreTableData = $this->getStorageData(self::STORAGE_KEY_TABLES, self::SCOPE_SHARED);
        }
        $this->getResource()->beginTransaction();

        foreach (array_keys($tables) as $tableEntity) {
            $this->getResource()->cleanTable($tableEntity);

            if (isset($restoreTableData[$tableEntity])) {
                 $this->getResource()->loadTableData($tableEntity, $restoreTableData[$tableEntity]);
            }
        }

        $this->getResource()->commit();
        $this->setStorageData(self::STORAGE_KEY_TABLES, null);
    }

    /**
     * Setting config value with applying the values to stores and websites
     *
     * @param string $path
     * @param string $value
     * @return Mage_Test_Model_Fixture
     */
    protected function _setConfigNodeValue($path, $value)
    {
        $pathArray = explode('/', $path);

        $scope = array_shift($pathArray);

        switch ($scope) {
            case 'stores':
                $storeCode = array_shift($pathArray);
                Mage::app()->getStore($storeCode)->setConfig(
                    implode('/', $pathArray), $value
                );
                break;

            case 'websites':
                $websiteCode = array_shift($pathArray);
                $website = Mage::app()->getWebsite($websiteCode);
                Mage_Utils_Reflection::setRestrictedPropertyValue(
                    $website, '_configCache', array()
                );
                // Should change value

            default:
                Mage::getConfig()->setNode($path, $value);
                break;
        }

        return $this;
    }

    /**
     * Retrieves eav loader for a particular entity type
     *
     * @param string $entityType
     * @return Mage_Test_Model_Mysql4_Fixture_Eav_Abstract
     */
    protected function _getEavLoader($entityType)
    {
        $loaders = Mage::getConfig()->getNode(self::XML_PATH_FIXTURE_EAV_LOADERS);

        if (isset($loaders->$entityType)) {
            $classAlias = (string)$loaders->$entityType;
        } elseif (isset($loaders->{self::DEFAULT_EAV_LOADER_NODE})) {
            $classAlias = (string)$loaders->{self::DEFAULT_EAV_LOADER_NODE};
        } else {
            $classAlias = self::DEFAULT_EAV_LOADER_CLASS;
        }

        return Mage::getResourceSingleton($classAlias);
    }

    /**
     * Applies fixture EAV values
     *
     * @param array $entities
     * @return Mage_Test_Model_Fixture
     */
    protected function _applyEav($entities)
    {
        if (!is_array($entities)) {
            throw new InvalidArgumentException('EAV part should be an associative list with rows as value and entity type as key');
        }
        
        $this->getResource()->beginTransaction();

        foreach ($entities as $entityType => $values) {
            $this->_getEavLoader($entityType)
                ->setFixture($this)
                ->setOptions($this->_options)
                ->loadEntity($entityType, $values);
        }

        $this->getResource()->commit();

        $this->setStorageData(self::STORAGE_KEY_ENTITIES, array_keys($entities));

        return $this;
    }

    /**
     * Clean applied eav data
     *
     * @param array $entities
     * @return Mage_Test_Model_Fixture
     */
    protected function _discardEav($entities)
    {
        $ignoreCleanUp = array();

        // Ignore cleaning of entities if shared fixture loaded something for them
        if ($this->isScopeLocal() && $this->getStorageData(self::STORAGE_KEY_ENTITIES, self::SCOPE_SHARED)) {
            $ignoreCleanUp = $this->getStorageData(self::STORAGE_KEY_ENTITIES, self::SCOPE_SHARED);
        }

        $this->getResource()->beginTransaction();
        foreach (array_keys($entities) as $entityType) {
            if (in_array($entityType, $ignoreCleanUp)) {
                continue;
            }
            $this->_getEavLoader($entityType)
                ->cleanEntity($entityType);
        }
        
        $this->getResource()->commit();

        return $this;
    }

    /**
     * Applies scope fixture,
     * i.e., website, store, store group
     *
     * @param array $types
     * @return Mage_Test_Model_Fixture
     */
    protected function _applyScope($types)
    {
        Mage::app()->disableEvents();
        // Validate received fixture data
        $this->_validateScope($types);

        if ($this->getStorageData(self::STORAGE_KEY_SCOPE) !== null) {
            throw new RuntimeException('Scope data was not cleared after previous test');
        }

        $scopeModels = array();

        foreach ($types as $type => $rows) {
            foreach ($rows as $row) {
                $model = $this->_handleScopeRow($type, $row);
                if ($model) {
                    $scopeModels[$type][$model->getId()] = $model;
                }
            }
        }

        $this->setStorageData(self::STORAGE_KEY_SCOPE, $scopeModels);

        Mage::app()->enableEvents();
        Mage::app()->reinitStores();
        return $this;
    }

    /**
     * Handle scope row data
     *
     * @param string $type
     * @param array $row
     * @return boolean|Mage_Core_Model_Abstract
     */
    protected function _handleScopeRow($type, $row)
    {
        $previousScope = array();

        if ($this->isScopeLocal() && $this->getStorageData(self::STORAGE_KEY_SCOPE, self::SCOPE_SHARED) !== null) {
            $previousScope = $this->getStorageData(self::STORAGE_KEY_SCOPE, self::SCOPE_SHARED);
        }

        if (isset($previousScope[$type][$row[$type . '_id']])) {
            return false;
        }

        $scopeModel = Mage::getModel(self::$_scopeModelByType[$type]);
        $scopeModel->setData($row);
        // Change property for saving new objects with specified ids
        Mage_Utils_Reflection::setRestrictedPropertyValues(
            $scopeModel->getResource(),
            array(
                '_useIsObjectNew' => true,
                '_isPkAutoIncrement' => false
            )
        );

        $scopeModel->isObjectNew(true);
        $scopeModel->save();
        // Revert changed property
        Mage_Utils_Reflection::setRestrictedPropertyValues(
            $scopeModel->getResource(),
            array(
                '_useIsObjectNew' => false,
                '_isPkAutoIncrement' => true
            )
        );

        return $scopeModel;
    }

    /**
     * Validate scope data
     *
     * @param array $types
     * @return Mage_Test_Model_Fixture
     */
    protected function _validateScope($types)
    {
        foreach ($types as $type => $rows) {
            if (!isset(self::$_scopeModelByType[$type])) {
                throw new RuntimeException(sprintf('Unknown "%s" scope type specified', $type));
            }

            foreach ($rows as $rowNumber => $row) {
                if (!isset($row[$type . '_id'])) {
                    throw new RuntimeException(sprintf('Missing primary key for "%s" scope entity at #%d row', $type, $rowNumber + 1));
                }
            }
        }

        return $this;
    }

    /**
     * Removes scope fixture changes,
     * i.e., website, store, store group
     *
     * @return Mage_Test_Model_Fixture
     */
    protected function _discardScope()
    {
        if ($this->getStorageData(self::STORAGE_KEY_SCOPE) === null) {
            return $this;
        }

        Mage::app()->disableEvents();
        $scope = array_reverse($this->getStorageData(self::STORAGE_KEY_SCOPE));
        foreach ($scope as $models) {
            foreach ($models as $model) {
                $model->delete();
            }
        }

        $this->setStorageData(self::STORAGE_KEY_SCOPE, null);

        Mage::app()->getCache()->clean(
            Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            array(
                Mage_Core_Model_Store::CACHE_TAG,
                Mage_Core_Model_Store_Group::CACHE_TAG,
                Mage_Core_Model_Website::CACHE_TAG
            )
        );

        Mage::app()->enableEvents();
        Mage::app()->reinitStores();
        return $this;
    }
}
