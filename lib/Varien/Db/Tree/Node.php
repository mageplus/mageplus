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
 * @package    Varien_Db
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


require_once 'Varien/Db/Tree/Node/Exception.php';

class Varien_Db_Tree_Node
{
    private $left;
    private $right;
    private $id;
    private $pid;
    private $level;
    private $title;
    private $data;

    public $hasChild = false;
    public $numChild = 0;

    /**
     * @todo
     *
     * @param array $nodeData
     * @param $keys
     * @return
     */
    function __construct($nodeData = array(), $keys)
    {
        if (empty($nodeData)) {
            throw new Varien_Db_Tree_Node_Exception('Empty array of node information');
        }
        if (empty($keys)) {
            throw new Varien_Db_Tree_Node_Exception('Empty keys array');
        }

        $this->id    = $nodeData[$keys['id']];
        $this->pid   = $nodeData[$keys['pid']];
        $this->left  = $nodeData[$keys['left']];
        $this->right = $nodeData[$keys['right']];
        $this->level = $nodeData[$keys['level']];

        $this->data  = $nodeData;
        $a = $this->right - $this->left;
        if ($a > 1) {
            $this->hasChild = true;
            $this->numChild = ($a - 1) / 2;
        }
        return $this;
    }

    /**
     * @todo
     *
     * @param $name
     * @return
     */
    function getData($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    /**
     * @todo
     *
     * @return
     */
    function getLevel()
    {
        return $this->level;
    }

    /**
     * @todo
     *
     * @return
     */
    function getLeft()
    {
        return $this->left;
    }

    /**
     * @todo
     *
     * @return
     */
    function getRight()
    {
        return $this->right;
    }

    /**
     * @todo
     *
     * @return
     */
    function getPid()
    {
        return $this->pid;
    }

    /**
     * @todo
     *
     * @return
     */
    function getId()
    {
        return $this->id;
    }
    
    /**
     * Return true if node have chield
     *
     * @return boolean
     */
    function isParent()
    {
        if ($this->right - $this->left > 1) {
            return true;
        } else {
            return false;
        }
    }
}