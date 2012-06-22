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
 * Layout model that adds additional functionality
 * for testing the layout itself
 *
 */
class Mage_Test_Model_Layout
    extends Mage_Core_Model_Layout
    implements Mage_PHPUnit_Constraint_Layout_Logger_Interface,
               Mage_PHPUnit_Isolation_Interface
{
    /**
     * List of replaced blocks creation
     *
     * @return array
     */
    protected $_replaceBlockCreation = array();

    /**
     * Records for gethering information about all,
     * the actions that was performed
     *
     *
     * @var array
     */
    protected $_records = array();

    /**
     * List of collected args for action call
     *
     * @var array
     */
    protected $_collectedArgs = null;

    /**
     * Collected block during block creation
     *
     * @var Mage_Core_Block_Abstract
     */
    protected $_collectedBlock = null;

    /**
     * Replaces creation of some block by mock object
     *
     * @param string $classAlias
     * @param PHPUnit_Framework_MockObject_MockObject|PHPUnit_Framework_MockObject_MockBuilder $mock
     * @return Mage_Test_Model_Layout
     */
    public function replaceBlockCreation($classAlias, $mock)
    {
        $this->_replaceBlockCreation[$classAlias] = $mock;
        return $this;
    }

    /**
     * Flushes instance creation instruction list
     *
     * @return Mage_Test_Model_Layout
     */
    public function flushReplaceBlockCreation()
    {
        $this->_replaceBlockCreation = array();
        return $this;
    }

    /**
     * Overriden for possibility of replacing a block by mock object
     * (non-PHPdoc)
     * @see Mage_Core_Model_Layout::_getBlockInstance()
     */
    protected function _getBlockInstance($block, array $attributes=array())
    {
        if (!isset($this->_replaceBlockCreation[$block])) {
            return parent::_getBlockInstance($block, $attributes);
        }

        return $this->_replaceBlockCreation[$block];
    }

    /**
     * Resets layout instance properties
     *
     * @return Mage_Test_Model_Layout
     */
    public function reset()
    {
        $this->setXml(simplexml_load_string('<layout/>', $this->_elementClass));
        $this->_update = Mage::getModel('core/layout_update');
        $this->_area = null;
        $this->_helpers = array();
        $this->_directOutput = false;
        $this->_output = array();
        $this->_records = array();

        foreach ($this->_blocks as $block) {
            // Remove references between blocks
            $block->setParentBlock(null);
            $block->setMessageBlock(null);
            $block->unsetChildren();
        }

        $this->_blocks = array();
        return $this;
    }

    /**
     * Returns all the recorded actions
     *
     * @return array
     */
    public function getRecords()
    {
        return $this->_records;
    }

     /**
     * Returns all actions performed on the target
     * or if target is null returns actions for all targets
     *
     * @param string $action
     * @param string|null $target
     * @return array
     */
    public function findAll($action, $target = null)
    {
        if ($target !== null && isset($this->_records[$action][$target])) {
            return $this->_records[$action][$target];
        } elseif ($target !== null) {
            return array();
        } elseif (!isset($this->_records[$action])) {
            return array();
        }

        $result = array();
        foreach ($this->_records[$action] as $target => $records) {
            $record['target'] = $target;
            $result = array_merge($result, $records);
        }

        return $result;
    }

    /**
     * Returns all actions targets
     *
     * @param string $action
     * @return array
     */
    public function findAllTargets($action)
    {
        if (isset($this->_records[$action])) {
            return array_keys($this->_records[$action]);
        }

        return array();
    }

    /**
     * Returns a single target action record by specified parameters
     *
     * @param string $action
     * @param string $target
     * @param array $parameters
     * @return boolean
     */
    public function findByParameters($action, $target, array $parameters, $searchType = self::SEARCH_TYPE_AND)
    {
        if (!isset($this->_records[$action][$target])) {
            return array();
        }

        $records = array();
        $arrayValues = false;

        // If it is a numeric array, then actual parameters should transformed as well
        if (count(array_filter(array_keys($parameters), 'is_int')) === count($parameters)) {
            $arrayValues = true;
        }


        foreach ($this->_records[$action][$target] as $actualParameters) {
            if ($arrayValues) {
                $actualParameters = array_values($actualParameters);
            }

            $intersection = array_intersect_assoc($actualParameters, $parameters);
            switch ($searchType) {
                case self::SEARCH_TYPE_OR:
                    $match = !empty($intersection);
                    break;
                case self::SEARCH_TYPE_EXACT:
                    $match = count($intersection) === count($actualParameters);
                    break;
                case self::SEARCH_TYPE_AND:
                default:
                    $match = count($intersection) === count($parameters);
                    break;
            }

            if ($match) {
                $records[] = $actualParameters;
            }
        }

        return $records;
    }

    /**
     * Returns first action that was recorded for target
     *
     * @param string $action
     * @param string $target
     * @return array
     */
    public function findFirst($action, $target)
    {
        if (!isset($this->_records[$action][$target])) {
            return false;
        }

        reset($this->_records[$action][$target]);

        return current($this->_records[$action][$target]);
    }

    /**
     * Records a particular target action
     *
     * @param string $action
     * @param string|null $target
     * @param array $parameters
     * @return Mage_Test_Model_Layout
     */
    public function record($action, $target = null, array $parameters = array())
    {
        $this->_records[$action][$target][] = $parameters;
        return $this;
    }

    /**
     * Observes a system event that is triggered on block render process start
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Test_Model_Layout
     */
    public function recordBlockRender(Varien_Event_Observer $observer)
    {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();

        $this->record(
            self::ACTION_BLOCK_RENDERED,
            $block->getNameInLayout(),
            array('content' => $transport->getHtml())
        );
    }

    /**
     * Records action call
     * (non-PHPdoc)
     * @see Mage_Core_Model_Layout::_generateAction()
     */
    protected function _generateAction($node, $parent)
    {
        $this->_collectedArgs = null;
        parent::_generateAction($node, $parent);
        if ($this->_collectedArgs !== null) {
            $method = (string)$node['method'];
            if (!empty($node['block'])) {
                $parentName = (string)$node['block'];
            } else {
                $parentName = $parent->getBlockName();
            }

            $target = $parentName . '::' . $method;
            $this->record(self::ACTION_BLOCK_ACTION, $target, $this->_collectedArgs);
        }
        return $this;
    }

    /**
     * Collects arguments if was not collected before
     * (non-PHPdoc)
     * @see Mage_Core_Model_Layout::_translateLayoutNode()
     */
    protected function _translateLayoutNode($node, $args)
    {
        parent::_translateLayoutNode($node, $args);
        if ($this->_collectedArgs === null) {
            $this->_collectedArgs = $args;
        }
    }

    /**
     * Records information about new block creation
     * (non-PHPdoc)
     * @see Mage_Core_Model_Layout::_generateBlock()
     */
    protected function _generateBlock($node, $parent)
    {
        $this->_collectedBlock = null;
        parent::_generateBlock($node, $parent);
        if ($this->_collectedBlock !== null) {
            $target = $this->_collectedBlock->getNameInLayout();
            $params = array();
            if (isset($node['as'])) {
                $params['alias'] = (string)$node['as'];
            } else {
                $params['alias'] = $target;
            }

            if (isset($node['class'])) {
                $params['type'] = (string)$node['class'];
            } elseif (isset($node['type'])) {
                $params['type'] = (string)$node['type'];
            }

            $params['class'] = get_class($this->_collectedBlock);

            $params['is_root'] = isset($node['output']);
            $this->record(self::ACTION_BLOCK_CREATED, $target, $params);

            if (isset($node['template'])) {
                $this->record(self::ACTION_BLOCK_ACTION, $target . '::setTemplate',
                              array('template' => (string)$node['template']));
            }
        }
        return $this;
    }

    /**
     * Collects block creation
     * (non-PHPdoc)
     * @see Mage_Core_Model_Layout::addBlock()
     */
    public function addBlock($block, $blockName)
    {
        $block = parent::addBlock($block, $blockName);

        if ($this->_collectedBlock === null) {
            $this->_collectedBlock = $block;
        }

        return $block;
    }

    /**
     * Records information about blocks removal and loaded layout handles
     * (non-PHPdoc)
     * @see Mage_Core_Model_Layout::generateXml()
     */
    public function generateXml()
    {
        $loadedHandles = $this->getUpdate()->getHandles();
        foreach ($loadedHandles as $key => $handle) {
            $params = array();
            if ($key > 0) {
                $params['after'] = array_slice($loadedHandles, 0, $key);
            } else {
                $params['after'] = array();
            }

            if ($key < count($loadedHandles)) {
                $params['before'] = array_slice($loadedHandles, $key + 1);
            } else {
                $params['before'] = array();
            }

            $this->record(self::ACTION_HANDLE_LOADED, $handle, $params);
        }

        parent::generateXml();

        $removedBlocks = $this->_xml->xpath('//block[@ignore]');

        if (is_array($removedBlocks)) {
            foreach ($removedBlocks as $block) {
                $this->record(self::ACTION_BLOCK_REMOVED, $block->getBlockName());
            }
        }

        return $this;
    }

    /**
     * Returns block position information in the parent subling.
     * Returned array contains two keys "before" and "after"
     * which are list of block names in this positions
     *
     * @param string $block
     * @return array
     */
    public function getBlockPosition($block)
    {
        $result = array(
            'before' => array(),
            'after' => array()
        );

        $block = $this->getBlock($block);
        if (!$block || !$block->getParentBlock()) {
            return $result;
        }

        $sortedBlockNames = $block->getParentBlock()->getSortedChildren();
        $key = 'before';
        foreach ($sortedBlockNames as $blockName) {
            if ($blockName == $block->getNameInLayout()) {
                $key = 'after';
                continue;
            }
            $result[$key][] = $blockName;
        }

        return $result;
    }

    /**
     * Returns block parent
     *
     * @param string $block
     * @return srting|boolean
     */
    public function getBlockParent($block)
    {
        $block = $this->getBlock($block);
        if (!$block || !$block->getParentBlock()) {
            return false;
        }

        return $block->getParentBlock()->getNameInLayout();
    }

    /**
     * Returns block property by getter
     *
     * @param string $block
     * @return mixed
     */
    public function getBlockProperty($block, $property)
    {
        $block = $this->getBlock($block);

        if (!$block) {
            throw new RuntimeException('Received a call to block, that does not exist');
        }

        return $block->getDataUsingMethod($property);
    }

    /**
     * Retuns a boolean flag for layout load status
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->_xml->hasChildren();
    }

    /**
     * Records that layout was rendered
     * (non-PHPdoc)
     * @see Mage_Core_Model_Layout::getOutput()
     */
    public function getOutput()
    {
        $this->record(self::ACTION_RENDER, 'layout');
        return parent::getOutput();
    }
}
