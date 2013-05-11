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
 * Setup resources configuration constraint
 *
 */
class Mage_PHPUnit_Constraint_Config_Resource_Script
    extends Mage_PHPUnit_Constraint_Config_Abstract
{
    const XML_PATH_RESOURCES_NODE = 'global/resources';

    const TYPE_SCRIPT_SCHEME = 'script_scheme';
    const TYPE_SCRIPT_DATA = 'script_data';

    const FILE_INSTALL_SCHEME = '/^(mysql4-install|install)-([\\d\\.]+)$/';
    const FILE_UPGRADE_SCHEME = '/^(mysql4-upgrade|upgrade)-([\\d\\.]+)-([\\d\\.]+)$/';

    const FILE_INSTALL_DATA = '/^(mysql4-data-install|data-install)-([\\d\\.]+)$/';
    const FILE_UPGRADE_DATA = '/^(mysql4-data-upgrade|data-upgrade)-([\\d\\.]+)-([\\d\\.]+)$/';

    /**
     * Name of the module for constraint
     *
     * @var string
     */
    protected $_moduleName = null;
    
    /**
     * The module directory for constraint
     *
     * @var string
     */
    protected $_moduleDirectory = null;
    
    /**
     * Resource name where to look for scripts
     * 
     * @var string
     */
    protected $_resourceName = null;

    /**
     * Contraint for evaluation of module config node
     *
     * @param string $nodePath
     * @param string $type
     * @param string $moduleDirectory
     * @param mixed $expectedValue
     */
    public function __construct($moduleName, $type, $moduleDirectory, $resourceName = null, $expectedVersions = null)
    {
        $this->_typesWithDiff[] = self::TYPE_SCRIPT_SCHEME;
        $this->_typesWithDiff[] = self::TYPE_SCRIPT_DATA;

        parent::__construct(
            self::XML_PATH_RESOURCES_NODE,
            $type,
            $expectedVersions
        );

        $this->_moduleName = $moduleName;
        $this->_moduleDirectory = $moduleDirectory;
        $this->_resourceName = $resourceName;

        if (!is_dir($moduleDirectory)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(3, 'real directory', $moduleDirectory);
        }
    }
    
    /**
     * Returns setup resource for module setup scripts
     * 
     * @param Varien_Simplexml_Element $xml
     * @return string
     */
    protected function getResourceName(Varien_Simplexml_Element $xml)
    {
        foreach ($xml->children() as $resourceNode) {
            if (isset($resourceNode->setup->module) 
                && (string)$resourceNode->setup->module === $this->_moduleName) {
                return $resourceNode->getName();
            }
        }
        
        return false;
    }
    
    /**
     * Returns list of scripts that are presented in resource directory
     *
     * @param string|array $directory
     * @param string $fromVersion
     */
    protected function parseVersions($directory)
    {
        if (is_array($directory)) {
            // For multiple directories merge result together
            $result = array();

            foreach ($directory as $entry) {
                $result = array_merge_recursive($result, $this->parseVersions($entry));
            }

            // Sort install scripts by version
            usort($result['install'], array($this, 'compareVersions'));
            // Sort upgrade scripts by version
            usort($result['upgrade'], array($this, 'compareVersions'));
            return $result;
        }

        
        $versions = array(
            'scheme' => array(
                'install' => array(),
                'upgrade' => array()
            ),
            'data' => array(
                'install' => array(),
                'upgrade' => array()
            )
        );


        if (!is_dir($directory)) {
            return $versions;
        }

        $directoryIterator = new DirectoryIterator($directory);

        $matchMap = array(
            self::FILE_INSTALL_SCHEME => array('scheme', 'install'),
            self::FILE_UPGRADE_SCHEME => array('scheme', 'upgrade'),
            self::FILE_INSTALL_DATA => array('data', 'install'),
            self::FILE_UPGRADE_DATA => array('data', 'upgrade')
        );

        foreach ($directoryIterator as $entry) {
            /* @var $entry SplFileInfo */
            // We do not support scheme upgrade scripts with .sql
            // file extension, since it is not the best practice.
            if ($entry->isFile() && substr($entry->getBasename(), -4) === '.php') {
                foreach ($matchMap as $pattern => $target) {
                    // Fulfill versions array
                    if (preg_match($pattern, $entry->getBasename('.php'), $match)) {
                        $versions[$target[0]][$target[1]][] = array(
                            'filename' => $entry->getBasename(),
                            'prefix' => $match[1],
                            'from' => $match[2],
                            'to' => isset($match[3]) ? $match[3] : null
                        );
                    }
                }
            }
        }

        return $versions;
    }

    /**
     * Compares two versions array
     *
     * @param array $first
     * @param array $next
     * @return int
     */
    protected function compareVersions($first, $next)
    {
        $result = version_compare($first['from'], $next['from']);

        if ($result === 0) {
            /* if file with type specified, it has more priority */
            $result = (strpos($first['prefix'], 'mysql4') ? 1 : -1);
        }

        return $result;
    }

    /**
     * Returns list of version scripts including expected and actual information
     *
     * @param array $versions
     * @param string $from
     * @param string $to
     * @param string $scriptPrefix
     * @return array with keys expected and actual
     */
    protected function getVersionScriptsDiff($versions, $from = null, $to = null, $scriptPrefix = '')
    {
        if ($from === null && end($versions['install'])) {
            $version = end($versions['install']);
            $from = $version['from'];
            reset($versions['install']);
        } elseif ($from === null && reset($versions['upgrade'])) {
            $version = reset($versions['upgrade']);
            $from = $version['from'];
        }

        if ($to === null && end($versions['upgrade'])) {
            $version = end($versions['upgrade']);
            $to = $version['to'];
        } elseif ($to === null) {
            $to = $from;
        }

        $actualVersions = array();
        $expectedVersions = array();

        $latestVersionFound = null;
        if (empty($versions['install']) && $from !== null) {
            $expectedVersions[] = sprintf('install-%s.php', $scriptPrefix, $from);
            $latestVersionFound = $from;
        } elseif ($from !== null) {
            foreach ($versions['install'] as $index=>$version) {
                if (version_compare($version['from'], $from) <= 0
                    && (!isset($versions['install'][$index+1]['from'])
                        || version_compare($versions['install'][$index+1]['from'], $from) > 0)) {
                    $latestVersionFound = $version['from'];
                    $actualVersions[] = $version['filename'];
                    $expectedVersions[] = $version['filename'];
                    break;
                }
            }
        } elseif (!empty($versions['install'])) {
            $version = current($versions['install']);
            $latestVersionFound = $version['from'];
            $actualVersions[] = $version['filename'];
            $expectedVersions[] = $version['filename'];
        } else {
            $expectedVersions[] = sprintf('%sinstall-%s.php', $scriptPrefix, $to);
            $latestVersionFound = $to;
        }

        foreach ($versions['upgrade'] as $version) {
            $fromCompare = version_compare($version['from'], $latestVersionFound);
            if ($fromCompare < 0) {
                continue;
            }

            if ($fromCompare > 0) {
                $expectedVersions[] = sprintf('%supgrade-%s-%s.php', $scriptPrefix, $latestVersionFound, $version['from']);
            }

            $actualVersions[] = $version['filename'];
            $expectedVersions[] = $version['filename'];
            $latestVersionFound = $version['to'];
        }

        if ($to !== null && version_compare($latestVersionFound, $to) === -1) {
            $expectedVersions[] = sprintf('%supgrade-%s-%s.php', $scriptPrefix, $latestVersionFound, $to);
        } elseif ($to !== null && version_compare($latestVersionFound, $to) === 1 && $expectedVersions) {
            array_pop($expectedVersions);
        }

        return array(
            'actual' => $actualVersions,
            'expected' => $expectedVersions
        );
    }

    /**
     * Checks structure of the schme setup scripts for a module
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateScriptScheme($other)
    {
        if ($this->_resourceName === null) {
            $this->_resourceName = $this->getResourceName($other);
        }

        $from = isset($this->_expectedValue[0]) ? $this->_expectedValue[0] : null;
        $to =  isset($this->_expectedValue[1]) ? $this->_expectedValue[1] : null;

        $versions = $this->parseVersions(
            $this->_moduleDirectory . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR .  $this->_resourceName
        );

        $diff = $this->getVersionScriptsDiff($versions['scheme'], $from, $to);

        $this->setActualValue($diff['actual']);
        $this->_expectedValue = $diff['expected'];
        return $this->_actualValue == $this->_expectedValue;
    }

    /**
     * Text represetnation of scheme setup scripts versions chain
     *
     * @return
     */
    public function textScriptScheme()
    {
        return 'scheme setup scripts are created in correct version chain order';
    }

    /**
     * Checks structure of the data setup scripts for a module
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateScriptData($other)
    {
        if ($this->_resourceName === null) {
            $this->_resourceName = $this->getResourceName($other);
        }

        $from = isset($this->_expectedValue[0]) ? $this->_expectedValue[0] : null;
        $to =  isset($this->_expectedValue[1]) ? $this->_expectedValue[1] : null;

        $versions = $this->parseVersions(
            array(
                $this->_moduleDirectory . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $this->_resourceName,
                $this->_moduleDirectory . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . $this->_resourceName
            )
        );

        $diff = $this->getVersionScriptsDiff($versions['data'], $from, $to);

        $this->setActualValue($diff['actual']);
        $this->_expectedValue = $diff['expected'];
        return $this->_actualValue == $this->_expectedValue;
    }

    /**
     * Text represetnation of scheme setup scripts versions chain
     *
     * @return
     */
    public function textScriptData()
    {
        return 'data setup scripts are created in correct version chain order';
    }

    /**
     * Custom failure description for showing config related errors
     * (non-PHPdoc)
     * @see PHPUnit_Framework_Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf(
            'Failed asserting that setup resources %s.',
            $this->toString()
        );
    }
}
