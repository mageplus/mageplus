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
 * Layout configuration constraint
 *
 */
class Mage_PHPUnit_Constraint_Config_Layout
    extends Mage_PHPUnit_Constraint_Config_Abstract
{
    const XML_PATH_LAYOUT = '%s/layout/updates';

    const TYPE_LAYOUT_DEFINITION = 'layout_definition';
    const TYPE_LAYOUT_FILE = 'layout_file';

    /**
     * Design area (frontend, adminhtml)
     *
     * @var string
     */
    protected $_area = null;

    /**
     * Name of layout update,
     * if specified, constraint
     * will be additionally checked by this parameter
     *
     * @var string
     */
    protected $_layoutUpdate = null;

    /**
     * Restriction by theme name
     *
     * @var string
     */
    protected $_theme = null;

    /**
     * Restriction by design package name
     *
     * @var string
     */
    protected $_designPackage = null;

    /**
     * Model for assertion of the data
     *
     * @var Mage_PHPUnit_Constraint_Config_Design_Package_Interface
     */
    protected static $_designPackageModel = null;

    /**
     * Configuration constraint for cheking the existance of
     * layout file in configuration and a particular theme as well
     *
     * @param string $area design area (frontend|adminhtml)
     * @param string $expectedFile layout file name that should be checked
     * @param string $type type of assertion
     * @param string|null $layoutUpdate additional check for layout update name for assertion of configuration
     * @param string|null $theme additional check for layout file existance in a particular theme
     * @param string|null $designPackage additional check for layout file existance in a particular theme
     */
    public function __construct($area, $expectedFile, $type, $layoutUpdate = null,
        $theme = null, $designPackage = null)
    {
        $this->_area = $area;
        $this->_layoutUpdate = $layoutUpdate;
        $this->_designPackage = $designPackage;
        $this->_theme = $theme;

        $this->_expectedValueValidation += array(
            self::TYPE_LAYOUT_FILE => array(true, 'is_string', 'string'),
            self::TYPE_LAYOUT_DEFINITION => array(true, 'is_string', 'string')
        );

        $this->_typesWithDiff[] = self::TYPE_LAYOUT_FILE;

        $nodePath = sprintf(self::XML_PATH_LAYOUT, $area);

        parent::__construct($nodePath, $type, $expectedFile);
    }

    /**
     * Sets design package model for assertions
     *
     * @param Mage_PHPUnit_Design_Package_Interface $model
     */
    public static function setDesignPackageModel(Mage_PHPUnit_Design_Package_Interface $model)
    {
        self::$_designPackageModel = $model;
    }

    /**
     * Retrieves design package model that was set before
     *
     * @return Mage_PHPUnit_Design_Package_Interface
     */
    public static function getDesignPackageModel()
    {
        return self::$_designPackageModel;
    }

    /**
     * Checks layout definititions for expected file defined in the configuration
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateLayoutDefinition($other)
    {
        foreach ($other->children() as $layoutUpdate) {
            if ((string)$layoutUpdate->file === $this->_expectedValue
                && ($this->_layoutUpdate === null || $this->_layoutUpdate === $layoutUpdate->getName())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Layout defition assertion text
     *
     * @return string
     */
    protected function textLayoutDefinition()
    {
        $text = sprintf('file "%s" is defined in configuration for %s area', $this->_expectedValue, $this->_area);

        if ($this->_layoutUpdate !== null) {
            $text .= sprintf(' in "%s" layout update', $this->_layoutUpdate);
        }

        return $text;
    }

    /**
     * Evaluates layout file existance
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateLayoutFile($other)
    {
        $assertion = self::getDesignPackageModel()
            ->getLayoutFileAssertion($this->_expectedValue, $this->_area, $this->_designPackage, $this->_theme);

        $this->setActualValue($assertion['actual']);
        $this->_expectedValue = $assertion['expected'];

        return $this->_actualValue === $this->_expectedValue;
    }

    /**
     * Text representation of layout file existance constraint
     *
     * @return string
     */
    protected function textLayoutFile()
    {
        return 'file is the same as expected and exists';
    }

    /**
     * Custom failure description for showing config related errors
     * (non-PHPdoc)
     * @see PHPUnit_Framework_Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf(
            'Failed asserting that layout %s.',
            $this->toString()
        );
    }
}
