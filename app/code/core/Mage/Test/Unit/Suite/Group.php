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
 * Test suite for a group of tests (e.g. tests from the same class)
 *
 */
class Mage_Test_Unit_Suite_Group extends PHPUnit_Framework_TestSuite
{
    const NO_GROUP_KEYWORD = '__nogroup__';

    /**
     * Name of suite that will be printed in tap/testdox format
     *
     * @var string
     */
    protected $suiteName = null;

    /**
     * Contructor adds test groups defined on global level
     * and adds additional logic for test names retrieval
     *
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestSuite::__construct()
     */
    public function __construct($theClass = '', $groups = array())
    {
        if (!$theClass instanceof ReflectionClass) {
            $theClass = Mage_Utils_Reflection::getRelflection($theClass);
        }

        // Check annotations for test case name
        $annotations = PHPUnit_Util_Test::parseTestMethodAnnotations(
            $theClass->getName()
        );

        if (isset($annotations['name'])) {
            $this->suiteName = $annotations['name'];
        }

        // Creates all test instances
        parent::__construct($theClass);

        // Just sort-out them by our internal groups
        foreach ($groups as $group) {
            $this->groups[$group] = $this->tests();
        }

        foreach ($this->tests() as $test) {
            if ($test instanceof PHPUnit_Framework_TestSuite) {
                /* @todo
                 * Post an issue into PHPUnit bugtracker for
                 * impossiblity for specifying group by parent test case
                 * Becuase it is a very dirty hack :(
                 **/
                $testGroups = array();
                foreach ($groups as $group) {
                    $testGroups[$group] = $test->tests();
                }

                Mage_Utils_Reflection::setRestrictedPropertyValue(
                    $test, 'groups', $testGroups
                );
            }
        }

        // Remove ungrouped tests group, if it exists
        if (isset($this->groups[self::NO_GROUP_KEYWORD])) {
            unset($this->groups[self::NO_GROUP_KEYWORD]);
        }
    }

    /**
     * Outputs test suite name from annotations
     *
     * (non-PHPdoc)
     * @see PHPUnit_Framework_TestSuite::toString()
     */
    public function toString()
    {
        return $this->suiteName !== null ?  $this->suiteName : $this->name;
    }
}