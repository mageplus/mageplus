<?php
class Mage_Xml_Parser
{
    protected $_dom = null;
    protected $_currentDom;
    protected $_content = array();

    public function __construct()
    {
        $this->_dom = new DOMDocument;
        $this->_currentDom = $this->_dom;
        return $this;
    }

    /**
     * @todo
     *
     * @return
     */
    public function getDom()
    {
        return $this->_dom;
    }

    /**
     * @todo
     *
     * @return
     */
    protected function _getCurrentDom()
    {
        return $this->_currentDom;
    }

    /**
     * @todo
     *
     * @param $node
     * @return
     */
    protected function _setCurrentDom($node)
    {
        $this->_currentDom = $node;
        return $this;
    }

    /**
     * @todo
     *
     * @return
     */
    public function xmlToArray()
    {
        $this->_content = $this->_xmlToArray();
        return $this->_content;
    }

    /**
     * @todo
     *
     * @param $currentNode
     * @return
     */
    protected function _xmlToArray($currentNode=false)
    {
        if (!$currentNode) {
            $currentNode = $this->getDom();
        }
        $content = array();
        foreach ($currentNode->childNodes as $node) {
            switch ($node->nodeType) {
                case XML_ELEMENT_NODE:

                    $value = null;
                    if ($node->hasChildNodes()) {
                        $value = $this->_xmlToArray($node);
                    }
                    $attributes = array();
                    if ($node->hasAttributes()) {
                        foreach($node->attributes as $attribute) {
                            $attributes += array($attribute->name=>$attribute->value);
                        }
                        $value = array('_value'=>$value, '_attribute'=>$attributes);
                    }
                    if (isset($content[$node->nodeName])) {
                        if (!isset($content[$node->nodeName][0]) || !is_array($content[$node->nodeName][0])) {
                            $oldValue = $content[$node->nodeName];
                            $content[$node->nodeName] = array();
                            $content[$node->nodeName][] = $oldValue;
                        }
                        $content[$node->nodeName][] = $value;
                    } else {
                        $content[$node->nodeName] = $value;
                    }
                    break;
                case XML_TEXT_NODE:
                    if (trim($node->nodeValue)) {
                        $content = $node->nodeValue;
                    }
                    break;
            }
        }
        return $content;
    }

    /**
     * @todo
     *
     * @param $file
     * @return
     */
    public function load($file)
    {
        $this->getDom()->load($file);
        return $this;
    }

    /**
     * @todo
     *
     * @param $string
     * @return
     */
    public function loadXML($string)
    {
        $this->getDom()->loadXML($string);
        return $this;
    }
}