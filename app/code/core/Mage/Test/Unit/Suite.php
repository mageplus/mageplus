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
 * Test suite for Magento
 *
 * It discovers all test cases in modules
 * if they were added to 'phpunit/suite/modules' configuration node
 *
 */
class Mage_Test_Unit_Suite extends PHPUnit_Framework_TestSuite
{
    // Configuration path constants
    const XML_PATH_UNIT_TEST_GROUPS = 'phpunit/suite/groups';
    const XML_PATH_UNIT_TEST_MODULES = 'phpunit/suite/modules';
    const XML_PATH_UNIT_TEST_APP = 'phpunit/suite/app/class';
    const XML_PATH_UNIT_TEST_SUITE = 'phpunit/suite/test_suite';
    const CACHE_TAG = 'MAGE_TEST';
    const CACHE_TYPE = 'mage_test';

    /**
     * Setting up test scope for Magento
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestSuite::setUp()
     */
    protected function setUp()
    {
        $appClass = (string) Mage::getConfig()->getNode(self::XML_PATH_UNIT_TEST_APP);
        $reflectionClass = Mage_Utils_Reflection::getRelflection($appClass);

        if ($reflectionClass->hasMethod('applyTestScope')) {
            $reflectionClass->getMethod('applyTestScope')->invoke(null);
        }
    }

    /**
     * Returning Magento to the state before suite was run
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestSuite::tearDown()
     */
    protected function tearDown()
    {
        $appClass = (string) Mage::getConfig()->getNode(self::XML_PATH_UNIT_TEST_APP);
        $reflectionClass = Mage_Utils_Reflection::getRelflection($appClass);

        if ($reflectionClass->hasMethod('discardTestScope')) {
            $reflectionClass->getMethod('discardTestScope')->invoke(null);
        }
    }

    /**
     * This method loads all available test suites for PHPUnit
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $groups = Mage::getConfig()->getNode(self::XML_PATH_UNIT_TEST_GROUPS);
        $modules = Mage::getConfig()->getNode(self::XML_PATH_UNIT_TEST_MODULES);
        $testSuiteClass = Mage_Utils_Reflection::getRelflection((string) Mage::getConfig()->getNode(self::XML_PATH_UNIT_TEST_SUITE));

        if (!$testSuiteClass->isSubclassOf('Mage_Test_Unit_Suite_Group')) {
            new RuntimeException('Test Suite class should be extended from Mage_Test_Unit_Suite_Group');
        }

        $suite = new self('Mage+ Test Suite');

        // Walk through different groups in modules for finding test cases
        foreach ($groups->children() as $group) {
            foreach ($modules->children() as $module) {
                $realModule = Mage::getConfig()->getNode('modules/' . $module->getName());
                if (!$realModule || !$realModule->is('active')) {
                    $suite->addTest(self::warning('There is no module with name: ' . $module->getName()));
                    continue;
                }

                $moduleCodeDir = Mage::getBaseDir('code') . DS . (string) $realModule->codePool;
                $searchPath = Mage::getModuleDir('', $module->getName()) . DS . 'Test' . DS . (string) $group;

                if (!is_dir($searchPath)) {
                    continue;
                }

                $currentGroups = array(
                    $group->getName(),
                    $module->getName()
                );
            
                $testCases = self::_loadTestCases($searchPath, $moduleCodeDir);
               
                foreach ($testCases as $className) {
                    $suite->addTest($testSuiteClass->newInstance($className, $currentGroups));
                }
            }
        }

        if (!$suite->count()) {
            $suite->addTest(self::warning('There were no test cases for the current run'));
        }

        return $suite;
    }

    /**
     * Loads test cases from search path,
     * Will return cached result
     *
     * @param string $searchPath path for searching files with tests
     * @param string $moduleCodeDir path where the module files are placed (e.g. app/code/local),
     *                              used for determining the class name
     */
    protected static function _loadTestCases($searchPath, $moduleCodeDir)
    {
        if (Mage::app()->useCache(self::CACHE_TYPE)) {
            $cachedTestCases = Mage::app()->loadCache(
                self::CACHE_TYPE . md5($searchPath)
            );
            if ($cachedTestCases) {
                return unserialize($cachedTestCases);
            }
        }

        $testCases = array();

        $directoryIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($searchPath)
        );
        
        foreach ($directoryIterator as $fileObject) {
            /* @var $fileObject SplFileObject */
            // Skip entry if it is not a php file
            if ((!$fileObject->isFile() && !$fileObject->isLink()) || $fileObject->getBasename('.php') === $fileObject->getBasename()) {
                continue;
            }

            $classPath = substr($fileObject->getPath() . DS . $fileObject->getBasename('.php'), strlen($moduleCodeDir));
            $className = uc_words(ltrim($classPath, DS), '_', DS);

            // Add unit test case only
            // if it is a valid class extended from Mage_PHPUnit_Unit_Case
            if (class_exists($className, true)) {
                $reflectionClass = Mage_Utils_Reflection::getRelflection($className);
                if (!$reflectionClass->isSubclassOf('Mage_Test_Unit_Case')
                    || $reflectionClass->isAbstract()) {
                    continue;
                }
                $testCases[] = $className;
            }
        }

        if (Mage::app()->useCache(self::CACHE_TYPE)) {
            Mage::app()->saveCache(
                serialize($testCases),
                self::CACHE_TYPE . md5($searchPath),
                array(self::CACHE_TAG)
            );
        }

        return $testCases;
    }
}
