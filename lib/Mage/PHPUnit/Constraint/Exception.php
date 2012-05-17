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
 * Exception for failed constraints execution
 *
 *
 */
class Mage_PHPUnit_Constraint_Exception extends PHPUnit_Framework_ExpectationFailedException
{
    protected $diff = null;

    public function __construct($description, $diff = '', $message = '')
    {
        if (!$diff instanceof PHPUnit_Framework_ComparisonFailure) {
            if (!is_scalar($diff)) {
                $diff = print_r($diff, true);
            }

            $this->diff = $diff;
            $diff = null;
        }

        parent::__construct($description, $diff);
    }

    public function toString()
    {
        $result = parent::toString();

        if (!empty($this->diff)) {
            $result .= "\n" . $this->diff;
        }

        return $result;
    }
}
