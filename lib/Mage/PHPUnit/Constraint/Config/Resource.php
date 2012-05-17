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
 * @package    Mage_PHPUnit
 * @copyright  Copyright (c) 2012 EcomDev BV (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Ivan Chepurnyi <ivan.chepurnyi@ecomdev.org>
 */

/**
 * Setup resources configuration constraint
 *
 */
class Mage_PHPUnit_Constraint_Config_Resource
    extends Mage_PHPUnit_Constraint_Config_Abstract
{
    const XML_PATH_RESOURCES_NODE = 'global/resources';

    const TYPE_SETUP_DEFINED = 'setup_defined';
    const TYPE_SETUP_SCHEME_EXISTS = 'setup_scheme_exists';
    const TYPE_SETUP_DATA_EXISTS = 'setup_data_exists';

    /**
     * Name of the module for constraint
     *
     * @var string
     */
    protected $_moduleName = null;
    
    /**
     * The module directory for constraint
     *
     * @var string
     */
    protected $_moduleDirectory = null;

    /**
     * Constraint for evaluation of module config node
     *
     * @param string $nodePath
     * @param string $type
     * @param string $moduleDirectory
     * @param mixed $expectedValue
     */
    public function __construct($moduleName, $type, $moduleDirectory = null, $expectedValue = null)
    {
        $this->_expectedValueValidation += array(
            self::TYPE_SETUP_DEFINED => array(false, 'is_string', 'string'),
            self::TYPE_SETUP_SCHEME_EXISTS => array(false, 'is_string', 'string'),
            self::TYPE_SETUP_DATA_EXISTS => array(false, 'is_string', 'string'),
        );

        $this->_typesWithDiff[] = self::TYPE_SETUP_DEFINED;
        $this->_typesWithDiff[] = self::TYPE_SETUP_SCHEME_EXISTS;
        $this->_typesWithDiff[] = self::TYPE_SETUP_DATA_EXISTS;

        parent::__construct(
            self::XML_PATH_RESOURCES_NODE,
            $type,
            $expectedValue
        );

        $this->_moduleName = $moduleName;
        $this->_moduleDirectory = $moduleDirectory;
        
        if (($this->_type === self::TYPE_SETUP_SCHEME_EXISTS || $this->_type === self::TYPE_SETUP_DATA_EXISTS)
            && !is_dir($moduleDirectory)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(3, 'real directory', $moduleDirectory);
        }
    }
    
    /**
     * Returns list of module setup resources
     * 
     * @param Varien_Simplexml_Element $xml
     * @return array
     */
    protected function getModuleSetupResources(Varien_Simplexml_Element $xml)
    {
        $resourcesForModule = array(); 
        foreach ($xml->children() as $resourceNode) {
            if (isset($resourceNode->setup->module) 
                && (string)$resourceNode->setup->module === $this->_moduleName) {
                $resourcesForModule[] = $resourceNode->getName();
            }
        }
        
        return $resourcesForModule;
    }
    
    /**
     * Checks definition of expected resource name
     *
     * @param Varien_Simplexml_Element $other
     */
    protected function evaluateSetupDefined($other)
    {
        $moduleResources = $this->getModuleSetupResources($other);
        
        if ($this->_expectedValue === null) {
            $this->_expectedValue = empty($moduleResources) ? 
                                    strtolower($this->_moduleName) . '_setup' : 
                                    current($moduleResources);
        }
        
        $this->setActualValue($moduleResources);
        
        return in_array($this->_expectedValue, $this->_actualValue);
    }
    
    /**
     * Represents constraint for definition of setup resources
     * 
     * @return string
     */
    public function textSetupDefined()
    {
        return sprintf('contains resource definition for %s module with %s name',
                       $this->_moduleName, $this->_expectedValue);
    }
    
    /**
     * Set actual value for comparison from module sql/data directories
     * 
     * @param string $type
     * @return Mage_PHPUnit_Constraint_Config_Resource
     */
    protected function setActualValueFromResourceDirectories($type = 'sql')
    {
        if (!is_dir($this->_moduleDirectory . DIRECTORY_SEPARATOR . $type)) {
            $this->setActualValue(array());
            return $this;
        }
        
        $dirIterator = new DirectoryIterator($this->_moduleDirectory . DIRECTORY_SEPARATOR . $type);
        
        $resourceDirectories = array();

        foreach ($dirIterator as $entry) {
            /* @var $entry DirectoryIterator */
            if ($entry->isDir() && !$entry->isDot()) {
                $resourceDirectories[] = $entry->getBasename();
            }
        }
        
        $this->setActualValue($resourceDirectories);
        
        return $this;
    }
    
    /**
     * Checks existence and definition of expected resource name schema directory
     *
     * @param Varien_Simplexml_Element $other
     */
    protected function evaluateSetupSchemeExists($other)
    {
        $moduleResources = $this->getModuleSetupResources($other);
        
        if ($this->_expectedValue === null) {
            $this->_expectedValue = empty($moduleResources) ? 
                                    strtolower($this->_moduleName) . '_setup' : 
                                    current($moduleResources);
        }
        
        $this->setActualValueFromResourceDirectories('sql');
        
        return in_array($this->_expectedValue, $moduleResources) 
               && in_array($this->_expectedValue, $this->_actualValue);
    }
    
    /**
     * Represents constraint for definition of setup resources
     * 
     * @return string
     */
    public function textSetupSchemeExists()
    {
        return sprintf(' schema directory is created for %s module with %s name',
                       $this->_moduleName, $this->_expectedValue);
    }
    
    /**
     * Checks existence and definition of expected resource name data directory
     *
     * @param Varien_Simplexml_Element $other
     */
    protected function evaluateSetupDataExists($other)
    {
        $moduleResources = $this->getModuleSetupResources($other);
        
        if ($this->_expectedValue === null) {
            $this->_expectedValue = empty($moduleResources) ? 
                                    strtolower($this->_moduleName) . '_setup' : 
                                    current($moduleResources);
        }
        
        $this->setActualValueFromResourceDirectories('data');
        
        return in_array($this->_expectedValue, $moduleResources) 
               && in_array($this->_expectedValue, $this->_actualValue);
    }
    
    /**
     * Represents constraint for definition of setup resources
     * 
     * @return string
     */
    public function textSetupDataExists()
    {
        return sprintf(' data directory is created for %s module with %s name', $this->_moduleName, $this->_expectedValue);
    }
    
    /**
     * Custom failure description for showing config related errors
     * (non-PHPdoc)
     * @see PHPUnit_Framework_Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf(
            'Failed asserting that setup resources %s.',
            $this->toString()
        );
    }
}
