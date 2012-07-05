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
 * Data form
 *
 * @category   Varien
 * @package    Varien_Data
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Varien_Data_Form extends Varien_Data_Form_Abstract
{
    /**
     * All form elements collection
     *
     * @var Varien_Data_Form_Element_Collection
     */
    protected $_allElements;

    /**
     * form elements index
     *
     * @var array
     */
    protected $_elementsIndex;

    static protected $_defaultElementRenderer;
    static protected $_defaultFieldsetRenderer;
    static protected $_defaultFieldsetElementRenderer;

    /**
     * @todo
     *
     * @param array $attributes
     * @return
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->_allElements = new Varien_Data_Form_Element_Collection($this);
    }

    /**
     * @todo
     *
     * @param Varien_Data_Form_Element_Renderer_Interface $renderer
     * @return
     */
    public static function setElementRenderer(Varien_Data_Form_Element_Renderer_Interface $renderer)
    {
        self::$_defaultElementRenderer = $renderer;
    }

    /**
     * @todo
     *
     * @param Varien_Data_Form_Element_Renderer_Interface $renderer
     * @return
     */
    public static function setFieldsetRenderer(Varien_Data_Form_Element_Renderer_Interface $renderer)
    {
        self::$_defaultFieldsetRenderer = $renderer;
    }

    /**
     * @todo
     *
     * @param Varien_Data_Form_Element_Renderer_Interface $renderer
     * @return
     */
    public static function setFieldsetElementRenderer(Varien_Data_Form_Element_Renderer_Interface $renderer)
    {
        self::$_defaultFieldsetElementRenderer = $renderer;
    }

    /**
     * @todo
     *
     * @return
     */
    public static function getElementRenderer()
    {
        return self::$_defaultElementRenderer;
    }

    /**
     * @todo
     *
     * @return
     */
    public static function getFieldsetRenderer()
    {
        return self::$_defaultFieldsetRenderer;
    }

    /**
     * @todo
     *
     * @return
     */
    public static function getFieldsetElementRenderer()
    {
        return self::$_defaultFieldsetElementRenderer;
    }

    /**
     * Return allowed HTML form attributes
     * @return array
     */
    public function getHtmlAttributes()
    {
        return array('id', 'name', 'method', 'action', 'enctype', 'class', 'onsubmit');
    }

    /**
     * Add form element
     *
     * @param   Varien_Data_Form_Element_Abstract $element
     * @return  Varien_Data_Form
     */
    public function addElement(Varien_Data_Form_Element_Abstract $element, $after=false)
    {
        $this->checkElementId($element->getId());
        parent::addElement($element, $after);
        $this->addElementToCollection($element);
        return $this;
    }

    /**
     * Check existing element
     *
     * @param   string $elementId
     * @return  boolean
     */
    protected function _elementIdExists($elementId)
    {
        return isset($this->_elementsIndex[$elementId]);
    }

    /**
     * @todo
     *
     * @param $element
     * @return
     */
    public function addElementToCollection($element)
    {
        $this->_elementsIndex[$element->getId()] = $element;
        $this->_allElements->add($element);
        return $this;
    }

    /**
     * @todo
     *
     * @param $elementId
     * @return
     */
    public function checkElementId($elementId)
    {
        if ($this->_elementIdExists($elementId)) {
            throw new Exception('Element with id "'.$elementId.'" already exists');
        }
        return true;
    }

    /**
     * @todo
     *
     * @return
     */
    public function getForm()
    {
        return $this;
    }

    /**
     * @todo
     *
     * @param $elementId
     * @return
     */
    public function getElement($elementId)
    {
        if ($this->_elementIdExists($elementId)) {
            return $this->_elementsIndex[$elementId];
        }
        return null;
    }

    /**
     * @todo
     *
     * @param $values
     * @return
     */
    public function setValues($values)
    {
        foreach ($this->_allElements as $element) {
            if (isset($values[$element->getId()])) {
                $element->setValue($values[$element->getId()]);
            }
            else {
                $element->setValue(null);
            }
        }
        return $this;
    }

    /**
     * @todo
     *
     * @param $values
     * @return
     */
    public function addValues($values)
    {
        if (!is_array($values)) {
            return $this;
        }
        foreach ($values as $elementId=>$value) {
            if ($element = $this->getElement($elementId)) {
                $element->setValue($value);
            }
        }
        return $this;
    }

    /**
     * Add suffix to name of all elements
     *
     * @param string $suffix
     * @return Varien_Data_Form
     */
    public function addFieldNameSuffix($suffix)
    {
        foreach ($this->_allElements as $element) {
            $name = $element->getName();
            if ($name) {
                $element->setName($this->addSuffixToName($name, $suffix));
            }
        }
        return $this;
    }

    /**
     * @todo
     *
     * @param $name
     * @param $suffix
     * @return
     */
    public function addSuffixToName($name, $suffix)
    {
        if (!$name) {
            return $suffix;
        }
        $vars = explode('[', $name);
        $newName = $suffix;
        foreach ($vars as $index=>$value) {
            $newName.= '['.$value;
            if ($index==0) {
                $newName.= ']';
            }
        }
        return $newName;
    }

    /**
     * @todo
     *
     * @param $elementId
     * @return
     */
    public function removeField($elementId)
    {
        if ($this->_elementIdExists($elementId)) {
            unset($this->_elementsIndex[$elementId]);
        }
        return $this;
    }

    /**
     * @todo
     *
     * @param $prefix
     * @return
     */
    public function setFieldContainerIdPrefix($prefix)
    {
        $this->setData('field_container_id_prefix', $prefix);
        return $this;
    }

    /**
     * @todo
     *
     * @return
     */
    public function getFieldContainerIdPrefix()
    {
        return $this->getData('field_container_id_prefix');
    }

    /**
     * @todo
     *
     * @return
     */
    public function toHtml()
    {
        Varien_Profiler::start('form/toHtml');
        $html = '';
        if ($useContainer = $this->getUseContainer()) {
            $html .= '<form '.$this->serialize($this->getHtmlAttributes()).'>';
            $html .= '<div>';
            if (strtolower($this->getData('method')) == 'post') {
                $html .= '<input name="form_key" type="hidden" value="'.Mage::getSingleton('core/session')->getFormKey().'" />';
            }
            $html .= '</div>';
        }

        foreach ($this->getElements() as $element) {
            $html.= $element->toHtml();
        }

        if ($useContainer) {
            $html.= '</form>';
        }
        Varien_Profiler::stop('form/toHtml');
        return $html;
    }

    /**
     * @todo
     *
     * @return
     */
    public function getHtml()
    {
        return $this->toHtml();
    }
}
