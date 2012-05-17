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
 * Class alias constraint
 *
 */
class Mage_PHPUnit_Constraint_Config_ClassAlias
    extends Mage_PHPUnit_Constraint_Config_Abstract
{
    const XML_PATH_CLASS_ALIAS = 'global/%s/%s';

    const GROUP_BLOCK = 'blocks';
    const GROUP_MODEL = 'models';
    const GROUP_HELPER = 'helpers';

    const TYPE_CLASS_ALIAS = 'class_alias';

    /**
     * Mapping of the text that represents
     * class alias group in fail message
     *
     * @var array
     */
    protected $_textByGroup = array(
        self::GROUP_BLOCK => 'block alias',
        self::GROUP_MODEL => 'model alias',
        self::GROUP_HELPER => 'helper alias'
    );

    /**
     * Class alias name, e.g. the name of the model
     *
     * @var string
     */
    protected $_classAliasName = null;

    /**
     * Class alias prefix,
     * e.g. the prefix for models of a particular module
     *
     * @var string
     */
    protected $_classAliasPrefix = null;

    /**
     * Class alias group
     *
     * @var string
     */
    protected $_group = null;

    /**
     * Constraint for evaluation of grouped class alias (block, model, helper)
     *
     * @param string $group
     * @param string $classAlias
     * @param string $expectedClassName
     * @param string $type
     */
    public function __construct($group, $classAlias, $expectedClassName, $type = self::TYPE_CLASS_ALIAS)
    {
        if (!isset($this->_textByGroup[$group])) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                1,
                implode(
                    '|',
                    array_keys($this->_textByGroup)
                ),
                $group
            );
        }

        $this->_group = $group;

        if ($group === self::GROUP_HELPER && strpos($classAlias, '/') === false) {
            $classAlias .= '/data';
        }

        if (!strpos($classAlias, '/')) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'class/alias', $classAlias);
        }

        list($this->_classAliasPrefix, $this->_classAliasName) = explode('/', $classAlias, 2);

        $nodePath = sprintf(self::XML_PATH_CLASS_ALIAS, $group, $this->_classAliasPrefix);

        $this->_expectedValueValidation += array(
            self::TYPE_CLASS_ALIAS => array(true, 'is_string', 'string')
        );

        $this->_typesWithDiff[] = self::TYPE_CLASS_ALIAS;

        parent::__construct($nodePath, $type, $expectedClassName);
    }

    /**
     * Evaluates class alias is mapped to expected class name
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateClassAlias($other)
    {
        $classPrefix = $other->class;

        if (isset($other->rewrite->{$this->_classAliasName})) {
            $className = (string)$other->rewrite->{$this->_classAliasName};
        } else {
            $className = $classPrefix . '_' . uc_words($this->_classAliasName);
        }

        $this->setActualValue($className);
        return $this->_actualValue === $this->_expectedValue;
    }

    /**
     * Text representation of class alias constaint
     *
     * @return string
     */
    protected function textClassAlias()
    {
        return 'is mapped to expected class name';
    }

    /**
     * Custom failure description for showing config related errors
     * (non-PHPdoc)
     * @see PHPUnit_Framework_Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf(
            'Failed asserting that %s "%s/%s" %s.',
            $this->_textByGroup[$this->_group],
            $this->_classAliasPrefix, $this->_classAliasName,
            $this->toString()
        );
    }
}
