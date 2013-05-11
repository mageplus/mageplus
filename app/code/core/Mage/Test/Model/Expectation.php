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
// becuase Symfony component is not working propertly with nested
require_once 'Spyc/spyc.php';

class Mage_Test_Model_Expectation
    implements Mage_Test_Model_Expectation_Interface
{
    /**
     * List of created data object ids by path format
     *
     * @var array
     */
    protected $_dataObjectIds = array();

    /**
     * Loaded data from Yaml files
     *
     * @var Varien_Object
     */
    protected $_loadedData = null;

    /**
     * Data object used for managing
     * expectation data
     *
     * @var string
     */
    protected $_dataObjectClassAlias = 'mage_test/expectation_object';

    /**
     * Returns class alias for fixture data object
     *
     * @return string
     */
    public function getDataObjectClassAlias()
    {
        return $this->_dataObjectClassAlias;
    }

    /**
     * Retrieves data object for a particular path format
     *
     * @see Mage_Test_Model_Expectation_Interface::getDataObject()
     */
    public function getDataObject($pathFormat = null, $args = array())
    {
        if ($pathFormat === null) {
            return $this->_loadedData;
        }

        $argsHash = $pathFormat . '_' . md5(serialize($args));

        // Check already created objects by path
        if (!isset($this->_dataObjectIds[$argsHash])) {
            if ($args) {
               array_unshift($args, $pathFormat);
               $dataPath = call_user_func_array('sprintf', $args);
            } else {
               $dataPath = $pathFormat;
            }

            $data = $this->_loadedData->getData($dataPath);

            if (!is_array($data)) {
               throw new InvalidArgumentException(
                   'Argument values for specifying special scope of expectations should be presented '
                   . ' in expectation file and should be an associative list (path: "' . $dataPath . '")'
               );
            }

            $this->_dataObjectIds[$argsHash] = Mage::objects()->save(
                Mage::getModel($this->getDataObjectClassAlias(), $data)
            );
        }

        return Mage::objects($this->_dataObjectIds[$argsHash]);
    }

    /**
     * Applies loaded data
     *
     * @see Mage_Test_Model_Test_Loadable_Interface::apply()
     */
    public function apply()
    {
        // For now it does nothing :(
        return $this;
    }

    /**
     * Removes objects created in object cache
     * Clears loaded data property
     *
     * @see Mage_Test_Model_Test_Loadable_Interface::discard()
     */
    public function discard()
    {
        foreach ($this->_dataObjectIds as $objectId) {
            Mage::objects()->delete($objectId);
        }

        $this->_dataObjectIds = array();
        $this->_loadedData = null;

        return $this;
    }

    /**
     * Check that expectations is loaded
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->_loadedData !== null;
    }

    /**
     * Loads expected data from test case annotations
     *
     * @see Mage_Test_Model_Test_Loadable_Interface::loadByTestCase()
     */
    public function loadByTestCase(Mage_Test_Unit_Case $testCase)
    {
        $expectations = $testCase->getAnnotationByName('loadExpectation');

        if (!$expectations) {
            $expectations[] = null;
        }

        $expectationData = array();

        foreach ($expectations as $expectation) {
            if (empty($expectation)) {
                $expectation = null;
            }

            $expectationFile = $testCase->getYamlFilePath('expectations', $expectation);

            if (!$expectationFile) {
                $text = 'There was no expectation defined for current test case';
                if ($expectation) {
                    $text = sprintf('Cannot load expectation %s', $expectation);
                }
                throw new RuntimeException($text);
            }

            $expectationData = array_merge_recursive(
                $expectationData, Spyc::YAMLLoad($expectationFile)
            );
        }

        $this->_loadedData = new Varien_Object($expectationData);
        return $this;
    }
}