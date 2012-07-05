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
 * Interface that should be implemented in layout model for
 * making possible asserting layout actions
 *
 */
interface Mage_PHPUnit_Constraint_Layout_Logger_Interface
{
    const ACTION_HANDLE_LOADED = 'handle_loaded';
    const ACTION_BLOCK_CREATED = 'block_created';
    const ACTION_BLOCK_RENDERED = 'block_rendered';
    const ACTION_BLOCK_REMOVED = 'block_removed';
    const ACTION_BLOCK_ACTION = 'block_action';

    const ACTION_RENDER = 'rendered';

    const SEARCH_TYPE_OR = 'or';
    const SEARCH_TYPE_AND = 'and';
    const SEARCH_TYPE_EXACT = 'exact';

    /**
     * Records a particular target action
     *
     * @param string $action
     * @param string $target
     * @param array $parameters
     * @return Mage_PHPUnit_Constraint_Layout_Logger_Interface
     */
    public function record($action, $target, array $parameters = array());

    /**
     * Returns all actions performed on the target
     * or if target is null returns actions for all targets
     *
     * @param string $action
     * @param string|null $target
     * @return array
     */
    public function findAll($action, $target = null);

    /**
     * Returns all actions targets
     *
     * @param string $action
     * @return array
     */
    public function findAllTargets($action);

    /**
     * Returns first action that was recorded for target
     *
     * @param string $action
     * @param string $target
     * @return array|false
     */
    public function findFirst($action, $target);

    /**
     * Returns target action records by specified parameters
     *
     *
     * @param string $action
     * @param string $target
     * @param array $parameters
     * @param string $searchType (or, and, exact)
     * @return array|false
     */
    public function findByParameters($action, $target, array $parameters, $searchType = self::SEARCH_TYPE_AND);

    /**
     * Returns block position information in the parent subling.
     * Returned array contains two keys "before" and "after"
     * which are list of block names in this positions
     *
     * @param string $block
     * @return array
     */
    public function getBlockPosition($block);

    /**
     * Returns block parent name
     *
     * @param string $block
     * @return string|boolean
     */
    public function getBlockParent($block);

    /**
     * Returns block property, even by getter
     *
     * @param string $block
     * @return mixed
     */
    public function getBlockProperty($block, $property);

    /**
     * Retuns a boolean flag for layout load status
     *
     * @return boolean
     */
    public function isLoaded();
}