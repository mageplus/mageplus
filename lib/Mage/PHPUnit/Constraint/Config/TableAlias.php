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
 * Table alias constraint
 *
 */
class Mage_PHPUnit_Constraint_Config_TableAlias
    extends Mage_PHPUnit_Constraint_Config_Abstract
{
    const XML_PATH_MODELS = 'global/models';

    const TYPE_TABLE_ALIAS = 'table_alias';

    /**
     * Table alias name, e.g. the name of the table within resource
     *
     * @var string
     */
    protected $_tableAliasName = null;

    /**
     * Table alias prefix,
     * e.g. the prefix for tables of a particular resource model
     *
     * @var string
     */
    protected $_tableAliasPrefix = null;

    /**
     * Constraint for evaluation of table alias
     *
     * @param string $tableAlias
     * @param string $expectedTableName
     * @param string $type
     */
    public function __construct($tableAlias, $expectedTableName, $type = self::TYPE_TABLE_ALIAS)
    {
        if (!strpos($tableAlias, '/')) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'table alias', $tableAlias);
        }

        list($this->_tableAliasPrefix, $this->_tableAliasName) = explode('/', $tableAlias, 2);

        $this->_expectedValueValidation += array(
            self::TYPE_TABLE_ALIAS => array(true, 'is_string', 'string')
        );

        $this->_typesWithDiff[] = self::TYPE_TABLE_ALIAS;

        parent::__construct(self::XML_PATH_MODELS, $type, $expectedTableName);
    }

    /**
     * Evaluates table alias is mapped to expected table name
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateTableAlias($other)
    {
        if (!isset($other->{$this->_tableAliasPrefix})) {
            $this->setActualValue(false);
            return false;
        }
        
        $modelNode = $other->{$this->_tableAliasPrefix};
        
        if (!isset($modelNode->entities) && isset($other->{(string)$modelNode->resourceModel})) {
            $modelNode = $other->{(string)$modelNode->resourceModel};
        }

        if (isset($modelNode->entities->{$this->_tableAliasName}->table)) {
            $tableName = (string)$modelNode->entities->{$this->_tableAliasName}->table;
        } else {
            $tableName = false;
        }

        $this->setActualValue($tableName);
        return $this->_actualValue === $this->_expectedValue;
    }

    /**
     * Text representation of table alias constaint
     *
     * @return string
     */
    protected function textTableAlias()
    {
        return 'is mapped to table name';
    }

    /**
     * Custom failure description for showing config related errors
     * (non-PHPdoc)
     * @see PHPUnit_Framework_Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf(
            'Failed asserting that table alias "%s/%s" %s.',
            $this->_tableAliasPrefix, $this->_tableAliasName,
            $this->toString()
        );
    }
}
