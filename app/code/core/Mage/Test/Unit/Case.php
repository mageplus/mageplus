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
// becuase Symfony component is not working propertly with nested associations
require_once 'Spyc/spyc.php';

/**
 * Basic test case class
 *
 *
 */
abstract class Mage_Test_Unit_Case extends PHPUnit_Framework_TestCase
{
    const XML_PATH_DEFAULT_FIXTURE_MODEL = 'phpunit/suite/fixture/model';
    const XML_PATH_DEFAULT_EXPECTATION_MODEL = 'phpunit/suite/expectation/model';

    /**
     * List of system registry values replaced by test case
     *
     * @var array
     */
    protected $_replacedRegistry = array();

    /**
     * The expectations for current test are loaded here
     *
     * @var Varien_Object|null
     * @deprecated since 0.2.0
     */
    protected $_expectations = null;

    /**
     * Original store kept for tearDown,
     * if was set in test method
     *
     * @var Mage_Core_Model_Store
     */
    protected $_originalStore = null;

    /**
     * Returns app for test case, created for type hinting
     * in the test case code
     *
     * @return Mage_Test_Model_App
     */
    public static function app()
    {
        return Mage::app();
    }

    /**
     * Asserts that event was dispatched at least once
     *
     * @param string|array $event
     * @param string $message
     */
    public static function assertEventDispatched($eventName)
    {
        if (is_array($eventName)) {
            foreach ($eventName as $singleEventName) {
                self::assertEventDispatched($singleEventName);
            }
            return;
        }

        $actual = self::app()->getDispatchedEventCount($eventName);
        $message = sprintf('%s event was not dispatched', $eventName);
        self::assertGreaterThanOrEqual(1, $actual, $message);
    }

    /**
     * Asserts that event was not dispatched
     *
     * @param string|array $event
     * @param string $message
     */
    public static function assertEventNotDispatched($eventName)
    {
        if (is_array($eventName)) {
            foreach ($eventName as $singleEventName) {
                self::assertEventNotDispatched($singleEventName);
            }
            return;
        }

        $actual = self::app()->getDispatchedEventCount($eventName);
        $message = sprintf('%s event was dispatched', $eventName);
        self::assertEquals(0, $actual, $message);
    }

    /**
     * Assert that event was dispatched exactly $times
     *
     * @param string $eventName
     * @param int
     */
    public static function assertEventDispatchedExactly($eventName, $times)
    {
        $actual = self::app()->getDispatchedEventCount($eventName);
        $message = sprintf(
            '%s event was dispatched only %d times, but expected to be dispatched %d times',
            $eventName, $actual, $times
        );

        self::assertEquals($times, $actual, $message);
    }

    /**
     * Assert that event was dispatched at least $times
     *
     * @param string $eventName
     * @param int
     */
    public static function assertEventDispatchedAtLeast($eventName, $times)
    {
        $actual = self::app()->getDispatchedEventCount($eventName);
        $message = sprintf(
            '%s event was dispatched only %d times, but expected to be dispatched at least %d times',
            $eventName, $actual, $times
        );

        self::assertGreaterThanOrEqual($times, $actual, $message);
    }

    /**
     * Creates a constraint for checking that string is valid JSON
     *
     * @return Mage_Test_Constraint_Json
     */
    public static function isJson()
    {
        return new Mage_Test_Constraint_Json(
            Mage_Test_Constraint_Json::TYPE_VALID
        );
    }

    /**
     * Creates a constraint for checking that string
     * is matched expected JSON structure
     *
     * @param array $expectedValue
     * @param strign $matchType
     * @return Mage_Test_Constraint_Json
     */
    public static function matchesJson(array $expectedValue, $matchType = Mage_Test_Constraint_Json::MATCH_AND)
    {
        return new Mage_Test_Constraint_Json(
            Mage_Test_Constraint_Json::TYPE_MATCH,
            $expectedValue,
            $matchType
        );
    }

    /**
     * Assert that string is a valid JSON
     *
     * @param string $string
     * @param string $message
     */
    public static function assertJson($string, $message = '')
    {
        self::assertThat($string, self::isJson(), $message);
    }

    /**
     * Assert that string is not a valid JSON
     *
     * @param string $string
     * @param string $message
     */
    public static function assertNotJson($string, $message = '')
    {
        self::assertThat($string, self::logicalNot(self::isJson()), $message);
    }

    /**
     * Assert that JSON string matches expected value,
     * Can accept different match type for matching logic.
     *
     * @param string $string
     * @param array $expectedValue
     * @param string $message
     * @param strign $matchType
     */
    public static function assertJsonMatch($string, array $expectedValue, $message = '',
        $matchType = Mage_Test_Constraint_Json::MATCH_AND)
    {
        self::assertThat(
            $string,
            self::matchesJson($expectedValue, $matchType),
            $message
        );
    }

    /**
     * Assert that JSON string doesn't matches expected value,
     * Can accept different match type for matching logic.
     *
     * @param string $string
     * @param array $expectedValue
     * @param string $message
     * @param strign $matchType
     */
    public static function assertJsonNotMatch($string, array $expectedValue, $message = '',
        $matchType = Mage_Test_Constraint_Json::MATCH_AND)
    {
        self::assertThat(
            $string,
            self::logicalNot(
                self::matchesJson($expectedValue, $matchType)
            ),
            $message
        );
    }

    /**
     * Retrieves the module name for current test case
     *
     * @return string
     * @throws RuntimeException if module name was not found for the passed class name
     */
    public function getModuleName()
    {
        return $this->app()->getModuleNameByClassName($this);
    }

    /**
     * Retrieves module name from call stack objects
     *
     * @return string
     * @throws RuntimeException if assertion is called in not from Mage_PHPUnit_Test_Case
     */
    protected static function getModuleNameFromCallStack()
    {
        $backTrace = debug_backtrace(true);
        foreach ($backTrace as $call) {
            if (isset($call['object']) && $call['object'] instanceof Mage_Test_Unit_Case) {
                return $call['object']->getModuleName();
            }
        }

        throw new RuntimeException('Unable to retrieve module name from call stack, because assertion is not called from Mage_PHPUnit_Test_Case based class method');
    }

    /**
     * Retrieves annotation by its name from different sources (class, method)
     *
     *
     * @param string $name
     * @param array|string $sources
     * @return array
     */
    public function getAnnotationByName($name, $sources = 'method')
    {
        return self::getAnnotationByNameFromClass(get_class($this), $name, $sources, $this->getName(false));
    }

    /**
     * Retrieves annotation by its name from different sources (class, method) based on meta information
     *
     * @param string $className
     * @param string $name annotation name
     * @param array|string $sources
     * @param string $testName test method name
     */
    public static function getAnnotationByNameFromClass($className, $name, $sources = 'class', $testName = '')
    {
        if (is_string($sources)) {
            $sources = array($sources);
        }

        $allAnnotations =  PHPUnit_Util_Test::parseTestMethodAnnotations(
          $className, $testName
        );

        $annotation = array();

        // Walkthrough sources for annotation retrieval
        foreach ($sources as $source) {
            if (isset($allAnnotations[$source][$name])) {
                $annotation = array_merge(
                    $allAnnotations[$source][$name],
                    $annotation
                );
            }
        }

        return $annotation;
    }

    /**
     * Loads expectations for current test case
     *
     * @throws RuntimeException if no expectation was found
     * @return Varien_Object
     * @deprecated since 0.2.0, use self::expected() instead.
     */
    protected function _getExpectations()
    {
        $arguments = func_get_args();

        return $this->expected($arguments);
    }

    /**
     * Replaces Magento resource by mock object
     *
     *
     * @param string $type
     * @param string $classAlias
     * @param PHPUnit_Framework_MockObject_MockObject|PHPUnit_Framework_MockObject_MockBuilder $mock
     * @return Mage_Test_Unit_Case
     */
    protected function replaceByMock($type, $classAlias, $mock)
    {
        if ($mock instanceof PHPUnit_Framework_MockObject_MockBuilder) {
            $mock = $mock->getMock();
        } elseif (!$mock instanceof PHPUnit_Framework_MockObject_MockObject) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
              1, 'PHPUnit_Framework_MockObject_MockObject'
            );
        }


        if ($type == 'helper' && strpos($classAlias, '/') === false) {
            $classAlias .= '/data';
        }

        if (in_array($type, array('model', 'resource_model'))) {
            $this->app()->getConfig()->replaceInstanceCreation($type, $classAlias, $mock);
            $type = str_replace('model', 'singleton', $type);
        } elseif ($type == 'block') {
            $this->app()->getLayout()->replaceBlockCreation($classAlias, $mock);
        }

        if (in_array($type, array('singleton', 'resource_singleton', 'helper'))) {
            $registryPath = '_' . $type . '/' . $classAlias;
            $this->replaceRegistry($registryPath, $mock);
        }

        return $this;
    }

    /**
     * Replaces value in Magento system registry
     *
     * @param string $key
     * @param mixed $value
     */
    protected function replaceRegistry($key, $value)
    {
        $oldValue = Mage::registry($key);

        $this->app()->replaceRegistry($key, $value);

        $this->_replacedRegistry[$key] = $oldValue;
        return $this;
    }

    /**
     * Shortcut for expectation data object retrieval
     * Can be called with arguments array or in usual method
     *
     * @param string|array $pathFormat
     * @param mixed $arg1
     * @param mixed $arg2 ...
     * @return Varien_Object
     */
    protected function expected($firstArgument = null)
    {
        if (!$this->getExpectation()->isLoaded()) {
            $this->getExpectation()->loadByTestCase($this);
            $this->getExpectation()->apply();
        }

        if (!is_array($firstArgument)) {
            $arguments = func_get_args();
        } else {
            $arguments = $firstArgument;
        }

        $pathFormat = null;
        if ($arguments) {
            $pathFormat = array_shift($arguments);
        }

        return $this->getExpectation()
            ->getDataObject($pathFormat, $arguments);
    }

    /**
     * Retrieve mock builder for grouped class alias
     *
     * @param string $type block|model|helper
     * @param string $classAlias
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function getGroupedClassMockBuilder($type, $classAlias)
    {
        $className = $this->getGroupedClassName($type, $classAlias);

        return $this->getMockBuilder($className);
    }

    /**
     * Retrieves a mock builder for a block class alias
     *
     * @param string $classAlias
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function getBlockMockBuilder($classAlias)
    {
        return $this->getGroupedClassMockBuilder('block', $classAlias);
    }

    /**
     * Retrieves a mock builder for a model class alias
     *
     * @param string $classAlias
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function getModelMockBuilder($classAlias)
    {
        return $this->getGroupedClassMockBuilder('model', $classAlias);
    }

    /**
     * Retrieves a mock builder for a resource model class alias
     *
     * @param string $classAlias
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function getResourceModelMockBuilder($classAlias)
    {
        return $this->getGroupedClassMockBuilder('resource_model', $classAlias);
    }

    /**
     * Retrieves a mock builder for a helper class alias
     *
     * @param string $classAlias
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function getHelperMockBuilder($classAlias)
    {
        return $this->getGroupedClassMockBuilder('helper', $classAlias);
    }

    /**
     * Retrieves a mock object for the specified model class alias.
     *
     * @param  string  $classAlias
     * @param  array   $methods
     * @param  boolean $isAbstract
     * @param  array   $constructorArguments
     * @param  string  $mockClassAlias
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getModelMock($classAlias, $methods = array(), $isAbstract = false,
                                 array $constructorArguments = array(),
                                 $mockClassAlias = '',  $callOriginalConstructor = true,
                                 $callOriginalClone = true, $callAutoload = true)
    {
        return $this->getGroupedClassMock('model', $classAlias, $methods, $isAbstract,
                                   $constructorArguments, $mockClassAlias,
                                   $callOriginalConstructor, $callOriginalClone,
                                   $callAutoload);
    }

    /**
     * Retrieves a mock object for the specified resource model class alias.
     *
     * @param  string  $classAlias
     * @param  array   $methods
     * @param  boolean $isAbstract
     * @param  array   $constructorArguments
     * @param  string  $mockClassAlias
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getResourceModelMock($classAlias, $methods = array(), $isAbstract = false,
                                 array $constructorArguments = array(),
                                 $mockClassAlias = '',  $callOriginalConstructor = true,
                                 $callOriginalClone = true, $callAutoload = true)
    {
        return $this->getGroupedClassMock('resource_model', $classAlias, $methods, $isAbstract,
                                   $constructorArguments, $mockClassAlias,
                                   $callOriginalConstructor, $callOriginalClone,
                                   $callAutoload);
    }

    /**
     * Retrieves a mock object for the specified helper class alias.
     *
     * @param  string  $classAlias
     * @param  array   $methods
     * @param  boolean $isAbstract
     * @param  array   $constructorArguments
     * @param  string  $mockClassAlias
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getHelperMock($classAlias, $methods = array(), $isAbstract = false,
                                 array $constructorArguments = array(),
                                 $mockClassAlias = '',  $callOriginalConstructor = true,
                                 $callOriginalClone = true, $callAutoload = true)
    {
        return $this->getGroupedClassMock('helper', $classAlias, $methods, $isAbstract,
                                   $constructorArguments, $mockClassAlias,
                                   $callOriginalConstructor, $callOriginalClone,
                                   $callAutoload);
    }

    /**
     * Retrieves a mock object for the specified helper class alias.
     *
     * @param  string  $classAlias
     * @param  array   $methods
     * @param  boolean $isAbstract
     * @param  array   $constructorArguments
     * @param  string  $mockClassAlias
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getBlockMock($classAlias, $methods = array(), $isAbstract = false,
                                 array $constructorArguments = array(),
                                 $mockClassAlias = '',  $callOriginalConstructor = true,
                                 $callOriginalClone = true, $callAutoload = true)
    {
        return $this->getGroupedClassMock('block', $classAlias, $methods, $isAbstract,
                                   $constructorArguments, $mockClassAlias,
                                   $callOriginalConstructor, $callOriginalClone,
                                   $callAutoload);
    }

    /**
     * Returns class name by grouped class alias
     *
     * @param string $type block/model/helper/resource_model
     * @param string $classAlias
     */
    protected function getGroupedClassName($type, $classAlias)
    {
        if ($type === 'resource_model') {
            return $this->app()->getConfig()->getResourceModelClassName($classAlias);
        }

        return $this->app()->getConfig()->getGroupedClassName($type, $classAlias);
    }

    /**
     * Retrieves a mock object for the specified grouped class alias.
     *
     * @param  string  $type
     * @param  string  $classAlias
     * @param  array   $methods
     * @param  boolean $isAbstract
     * @param  array   $constructorArguments
     * @param  string  $mockClassAlias
     * @param  boolean $callOriginalConstructor
     * @param  boolean $callOriginalClone
     * @param  boolean $callAutoload
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getGroupedClassMock($type, $classAlias, array $methods = array(), $isAbstract = false,
                                        array $constructorArguments = array(),
                                        $mockClassAlias = '',  $callOriginalConstructor = true,
                                        $callOriginalClone = true, $callAutoload = true)
    {
        if (!empty($mockClassAlias)) {
            $mockClassName = $this->getGroupedClassName($type, $mockClassAlias);
        } else {
            $mockClassName = '';
        }

        $mockBuilder = $this->getGroupedClassMockBuilder($type, $classAlias);

        if ($callOriginalConstructor === false) {
            $mockBuilder->disableOriginalConstructor();
        }

        if ($callOriginalClone === false) {
            $mockBuilder->disableOriginalClone();
        }

        if ($callAutoload === false) {
            $mockBuilder->disableAutoload();
        }

        $mockBuilder->setMethods($methods);
        $mockBuilder->setConstructorArgs($constructorArguments);
        $mockBuilder->setMockClassName($mockClassName);

        if ($isAbstract) {
            return $mockBuilder->getMockForAbstractClass();
        }

        return $mockBuilder->getMock();
    }

    /**
     * Retrieves fixture model singleton
     *
     * @return Mage_Test_Model_Fixture
     * @deprecated since 0.2.0 use getFixture() method instead
     */
    protected function _getFixture()
    {
        return $this->getFixture();
    }

    /**
     * Retrieves fixture model singleton
     *
     * @return Mage_Test_Model_Fixture
     */
    protected static function getFixture()
    {
        $fixture = Mage::getSingleton(
            self::getLoadableClassAlias(
                'fixture',
                self::XML_PATH_DEFAULT_FIXTURE_MODEL
            )
        );

        if (!$fixture instanceof Mage_Test_Model_Fixture_Interface) {
            throw new RuntimeException('Fixture model should implement Mage_Test_Model_Fixture_Interface interface');
        }

        $storage = Mage::registry(Mage_Test_Model_App::REGISTRY_PATH_SHARED_STORAGE);

        if (!$storage instanceof Varien_Object) {
            throw new RuntimeException('Fixture storage object was not initialized during test application setup');
        }

        $fixture->setStorage(
            Mage::registry(Mage_Test_Model_App::REGISTRY_PATH_SHARED_STORAGE)
        );

        return $fixture;
    }

    /**
     * Returns expectation model singleton
     *
     * @return Mage_Test_Model_Expectation
     */
    protected function getExpectation()
    {
        return Mage::getSingleton(
            self::getLoadableClassAlias(
                'expectation',
                self::XML_PATH_DEFAULT_EXPECTATION_MODEL
            )
        );
    }

    /**
     * Retrieves loadable class alias from annotation or configuration node
     * E.g. class alias for fixture model can be specified via @fixtureModel annotation
     *
     * @param string $type
     * @param string $configPath
     * @return string
     */
    protected static function getLoadableClassAlias($type, $configPath)
    {
        $annotationValue = self::getAnnotationByNameFromClass(
            get_called_class(),
            $type .'Model'
        );

        if (current($annotationValue)) {
            $classAlias = current($annotationValue);
        } else {
            $classAlias = self::app()->getConfig()->getNode($configPath);
        }

        return $classAlias;
    }

    /**
     * Protected wrapper for _getYamlFilePath method. Backward campatibility.
     *
     * @see Mage_Test_Unit_Case::getYamlFilePath()
     *
     * @param string $type type of YAML data (fixtures,expectations,dataproviders)
     * @param string|null $name the file name for loading, if equals to null,
     *                          the current test name will be used
     * @return string|boolean
     * @deprecated since 0.2.0
     */
    protected function _getYamlFilePath($type, $name = null)
    {
        return $this->getYamlFilePath($type, $name);
    }

    /**
     * Loads YAML file from directory inside of the unit test class
     *
     * @param string $type type of YAML data (fixtures,expectations,dataproviders)
     * @param string|null $name the file name for loading, if equals to null,
     *                          the current test name will be used
     * @return string|boolean
     */
    public function getYamlFilePath($type, $name = null)
    {
        if ($name === null) {
            $name = $this->getName(false);
        }

        return self::getYamlFilePathByClass(get_called_class(), $type, $name);
    }

    /**
     * Loads YAML file from directory inside of the unit test class or
     * the directory inside the module directory if name is prefixed with ~/
     * or from another module if name is prefixed with ~My_Module/
     *
     * @param string $className class name for looking fixture files
     * @param string $type type of YAML data (fixtures,expectations,dataproviders)
     * @param string $name the file name for loading
     * @return string|boolean
     */
    public static function getYamlFilePathByClass($className, $type, $name)
    {
        if (strrpos($name, '.yaml') !== strlen($name) - 5) {
            $name .= '.yaml';
        }

        $classFileObject = new SplFileInfo(
            Mage_Utils_Reflection::getRelflection($className)->getFileName()
        );

        // When prefixed with ~/ or ~My_Module/, load from the module's Test/<type> directory
        if (preg_match('#^~(?<module>[^/]*)/(?<path>.*)$#', $name, $matches)) {
            $name = $matches['path'];
            if( ! empty($matches['module'])) {
              $moduleName = $matches['module'];
            } else {
              $moduleName = substr($className, 0, strpos($className, '_Test_'));;
            }
            $filePath = Mage::getModuleDir('', $moduleName) . DS . 'Test' . DS;
        }
        // Otherwise load from the Class/<type> directory
        else {
            $filePath = $classFileObject->getPath() . DS
                      . $classFileObject->getBasename('.php') . DS;
        }
        $filePath .= $type . DS . $name;

        if (file_exists($filePath)) {
            return $filePath;
        }

        return false;
    }

    /**
     * Initializes a particular test environment
     *
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        self::getFixture()
            ->setScope(Mage_Test_Model_Fixture_Interface::SCOPE_LOCAL)
            ->loadByTestCase($this);
        $annotations = $this->getAnnotations();
        self::getFixture()
            ->setOptions($annotations['method'])
            ->apply();
        $this->app()->resetDispatchedEvents();
        parent::setUp();
    }

    /**
     * Initializes test environment for subset of tests
     *
     */
    public static function setUpBeforeClass()
    {
        self::getFixture()
            ->setScope(Mage_Test_Model_Fixture_Interface::SCOPE_SHARED)
            ->loadForClass(get_called_class());

        $annotations = PHPUnit_Util_Test::parseTestMethodAnnotations(
            get_called_class()
        );

        self::getFixture()
            ->setOptions($annotations['class'])
            ->apply();

        parent::setUpBeforeClass();
    }

    /**
     * Implements default data provider functionality,
     * returns array data loaded from Yaml file with the same name as test method
     *
     * @param string $testName
     * @return array
     */
    public function dataProvider($testName)
    {
        $this->setName($testName);
        $filePath = $this->getYamlFilePath('providers');
        $this->setName(null);

        if (!$filePath) {
            throw new RuntimeException('Unable to load data provider for the current test');
        }

        return Spyc::YAMLLoad($filePath);
    }

    /**
     * Set current store scope for test
     *
     * @param int|string|Mage_Core_Model_Store $store
     * @return Mage_Test_Unit_Case
     */
    public function setCurrentStore($store)
    {
        if (!$this->_originalStore) {
            $this->_originalStore = $this->app()->getStore();
        }

        $this->app()->setCurrentStore(
            $this->app()->getStore($store)
        );
        return $this;
    }

    /**
     * Performs a clean up after a particular test was run
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    protected function tearDown()
    {
        if ($this->_originalStore) { // Remove store scope, that was set in test
            $this->app()->setCurrentStore($this->_originalStore);
            $this->_originalStore = null;
        }

        if ($this->getExpectation()->isLoaded()) {
            $this->getExpectation()->discard();
        }

        $this->app()->getConfig()->flushReplaceInstanceCreation();
        $this->app()->getLayout()->flushReplaceBlockCreation();

        foreach ($this->_replacedRegistry as $registryPath => $originalValue) {
            $this->app()->replaceRegistry($registryPath, $originalValue);
        }

        self::getFixture()
            ->setScope(Mage_Test_Model_Fixture_Interface::SCOPE_LOCAL)
            ->discard(); // Clear applied fixture
        parent::tearDown();
    }

    /**
     * Clean up all the shared fixture data
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        self::getFixture()
            ->setScope(Mage_Test_Model_Fixture_Interface::SCOPE_SHARED)
            ->discard();

        parent::tearDownAfterClass();
    }
}
