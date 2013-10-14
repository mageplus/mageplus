<?php
/**
 * PHP Unit test suite for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Test
 * @copyright  Copyright (c) 2012 Oggetto Web ltd. (http://www.oggettoweb.com/)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * api.xml config test case
 *
 * @category   Mage
 * @package    Mage_Test
 * @author     Valentin Sushkov <vsushkov@oggettoweb.com>
 */
class Mage_Test_Unit_Case_Config_Api extends Mage_Test_Unit_Case_Config
{
    /**
     * Executes configuration constraint
     *
     * @param Mage_PHPUnit_Constraint_Config $constraint
     * @param string $message
     */
    public static function assertThatConfig(Mage_PHPUnit_Constraint_Config $constraint, $message)
    {
        self::assertThat(Mage::getSingleton('api/config'), $constraint, $message);
    }
}
