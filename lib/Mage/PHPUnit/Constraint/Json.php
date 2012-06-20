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
 * Constraint for checking JSON values
 *
 */
class Mage_PHPUnit_Constraint_Json extends Mage_PHPUnit_Constraint_Abstract
{
    const TYPE_VALID = 'valid';
    const TYPE_MATCH = 'match';

    const MATCH_AND = 'and';
    const MATCH_OR = 'or';
    const MATCH_EXACT = 'exact';

    /**
     * Match type for evaluation
     *
     * @var string
     */
    protected $_matchType = self::MATCH_AND;

    /**
     * Constraint for checking JSON values
     *
     *
     * @param string $type
     * @param array $expectedValue expected json in array format
     * @param string $matchType
     */
    public function __construct($type, $expectedValue = null, $matchType = self::MATCH_AND)
    {
        if ($expectedValue !== null && (empty($matchType) || !is_string($matchType))) {
            PHPUnit_Util_InvalidArgumentHelper::factory(3, 'string', $matchType);
        }

        $this->_expectedValueValidation += array(
            self::TYPE_MATCH => array(true, 'is_array', 'array')
        );

        $this->_typesWithDiff[] = self::TYPE_MATCH;

        parent::__construct($type, $expectedValue);
        $this->_matchType = $matchType;
    }

    /**
     * Evaluate that string is valid JSON
     *
     * @param string $other
     * @return boolean
     */
    protected function evaluateValid($other)
    {
        try {
            $decodedJson = Zend_Json::decode($other);
            $this->setActualValue($decodedJson);
        } catch (Zend_Json_Exception $e) {
            $this->setActualValue(
                PHPUnit_Util_Type::shortenedString($other)
                . "\n" . $e->__toString()
            );
            return false;
        }

        return true;
    }

    /**
     * Text representation of JSON string is valid assertion
     *
     * @return string
     */
    protected function textValid()
    {
        return 'is valid JSON';
    }

    /**
     * Evaluate that string is valid JSON
     *
     * @param string $other
     * @return boolean
     */
    protected function evaluateMatch($other)
    {
        $decodedJson = Zend_Json::decode($other);
        $this->setActualValue($decodedJson);

        $intersection = array_intersect_assoc(
            $this->_actualValue,
            $this->_expectedValue
        );

        switch ($this->_matchType) {
            case self::MATCH_OR:
                $matched = !empty($intersection);
                break;
            case self::MATCH_EXACT:
                $matched = count($intersection) === count($decodedJson);
                break;
            case self::MATCH_AND:
            default:
                $matched = count($intersection) === count($this->_expectedValue);
                break;
        }

        return $matched;
    }

    /**
     * Text representation of matching evaluation
     *
     * @return string
     */
    protected function textMatch()
    {
        $string = 'matches expected JSON structure ';

        switch ($this->_matchType) {
            case self::MATCH_OR:
                $string .= 'at least in one element';
                break;
            case self::MATCH_EXACT:
                $string .= 'exactly';
                break;
            case self::MATCH_AND:
            default:
                $string .= 'in all expected elements';
                break;
        }

        return $string;
    }

    /**
     * Custom failure description for showing json related errors
     * (non-PHPdoc)
     * @see PHPUnit_Framework_Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf(
            'Failed asserting that string value %s.',
            $this->toString()
        );
    }
}
