<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Varien
 * @package    Varien_Data
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tree node collection
 *
 * @category   Varien
 * @package    Varien_Data
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Varien_Data_Tree_Node_Collection implements ArrayAccess, IteratorAggregate
{
    private $_nodes;
    private $_container;
    
    /**
     * @todo
     *
     * @param $container
     * @return
     */
    public function __construct($container) 
    {
        $this->_nodes = array();
        $this->_container = $container;
    }
    
    /**
     * @todo
     *
     * @return
     */
    public function getNodes()
    {
        return $this->_nodes;
    }
    
    /**
    * Implementation of IteratorAggregate::getIterator()
    */
    public function getIterator()
    {
        return new ArrayIterator($this->_nodes);
    }

    /**
     * Implementation of ArrayAccess:offsetSet()
     *
     * @param $key
     * @param $value
     * @return
     */
    public function offsetSet($key, $value)
    {
        $this->_nodes[$key] = $value;
    }
    
    /**
    * Implementation of ArrayAccess:offsetGet()
    *
    *  @param $key
    *  @return
    */
    public function offsetGet($key)
    {
        return $this->_nodes[$key];
    }
    
    /**
    * Implementation of ArrayAccess:offsetUnset()
    *
    * @param $key
    * @return
    */
    public function offsetUnset($key)
    {
        unset($this->_nodes[$key]);
    }
    
    /**
    * Implementation of ArrayAccess:offsetExists()
    *
    * @param $key
    * @return
    */
    public function offsetExists($key)
    {
        return isset($this->_nodes[$key]);
    }
    
    /**
    * Adds a node to this node
    *
    * @param Varien_Data_Triee_Node $node
    * @return
    */
    public function add(Varien_Data_Tree_Node $node)
    {
        $node->setParent($this->_container);

        // Set the Tree for the node
        if ($this->_container->getTree() instanceof Varien_Data_Tree) {
            $node->setTree($this->_container->getTree());
        }

        $this->_nodes[$node->getId()] = $node;

        return $node;
    }
    
    /**
     * @todo
     *
     * @param $node
     * @return
     */
    public function delete($node)
    {
        if (isset($this->_nodes[$node->getId()])) {
            unset($this->_nodes[$node->getId()]);
        }
        return $this;
    }
    
    /**
     * @todo
     *
     * @return
     */
    public function count()
    {
        return count($this->_nodes);
    }

    /**
     * @todo
     *
     * @return
     */
    public function lastNode()
    {
        return !empty($this->_nodes) ? $this->_nodes[count($this->_nodes) - 1] : null;
    }

    /**
     * @todo
     *
     * @param $nodeId
     * @return
     */
    public function searchById($nodeId)
    {
        if (isset($this->_nodes[$nodeId])) {
            return $this->_nodes[$nodeId];
        }
        return null;
    }
}
