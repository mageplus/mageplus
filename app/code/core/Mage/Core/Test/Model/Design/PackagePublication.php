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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Test_Model_Design_PackagePublication extends Mage_Test_Unit_Case
{
    /**
     * Path to skin directory of Magento installation
     * @var string
     */
    protected static $_skinPublicDir;

    /**
     * Path for temporary fixture files. Used to test publishing changed files.
     * @var string
     */
    protected static $_fixtureTmpDir;

    protected static $_cssFiles = array(
        'css/file.css',
        'recursive.css',
        'recursive.gif',
        'css/deep/recursive.css',
        'recursive2.gif',
        'css/body.gif',
        'css/1.gif',
        'h1.gif',
        'images/h2.gif',
        'Namespace_Module/absolute_valid_module.gif',
        'Mage_Page/favicon.ico', // non-fixture file from real module
    );

    /**
     * @var Mage_Core_Model_Design_Package
     */
    protected $_model;

    public static function setUpBeforeClass()
    {
        self::$_skinPublicDir = Mage::app()->getConfig()->getOptions()->getMediaDir() . '/skin';
        //self::$_fixtureTmpDir = Magento_Test_Bootstrap::getInstance()->getTmpDir() . '/publication';
    }

    protected function setUp()
    {
        Mage::app()->getConfig()->getOptions()->setDesignDir(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'design'
        );
        $this->_model = new Mage_Core_Model_Design_Package();
        //$this->_model->setDesignTheme('test/default/default', 'frontend');
    }

    protected function tearDown()
    {
        Varien_Io_File::rmdirRecursive(self::$_skinPublicDir);
        Varien_Io_File::rmdirRecursive(self::$_fixtureTmpDir);
    }

    /**
     * @dataProvider getSkinUrlDataProvider
     */
    public function testGetSkinUrl($file, $expectedName, $locale = null)
    {
        Mage::app()->getLocale()->setLocale($locale);
        $url = $this->_model->getSkinUrl($file);
        $this->assertStringEndsWith($expectedName, $url);
        //$skinFile = $this->_model->getSkinFile($file);
        //$this->assertFileExists($skinFile);
    }

    /**
     * @return array
     */
    public function getSkinUrlDataProvider()
    {
        return array(
            'theme file' => array(
                'css/styles.css',
                'skin/frontend/default/default/css/styles.css',
            ),
            'theme localized file' => array(
                'logo.gif',
                'skin/frontend/default/default/logo.gif',
                'fr_FR',
            ),
            'modular file' => array(
                'Module::favicon.ico',
                'skin/frontend/default/default/Module/favicon.ico',
            ),
            'lib file' => array(
                'varien/product.js',
                'http://localhost/pub/js/varien/product.js',
            ),
            'lib folder' => array(
                'varien',
                'http://localhost/pub/js/varien',
            )
        );
    }

    /**
     * @magentoConfigFixture default/design/theme/allow_skin_files_duplication 0
     * @dataProvider testGetSkinUrlNoFilesDuplicationDataProvider
     */
    public function testGetSkinUrlNoFilesDuplication($file, $expectedName, $locale = null)
    {
        $this->testGetSkinUrl($file, $expectedName, $locale);
    }

    /**
     * @return array
     */
    public function testGetSkinUrlNoFilesDuplicationDataProvider()
    {
        return array(
            'theme css file' => array(
                'css/styles.css',
                'skin/frontend/default/default/css/styles.css',
            ),
            'theme file' => array(
                'images/logo.gif',
                'skin/frontend/test/default/skin/default/images/logo.gif',
            ),
            'theme localized file' => array(
                'logo.gif',
                'skin/frontend/test/default/skin/default/locale/fr_FR/logo.gif',
                'fr_FR',
            )
        );
    }

    /**
     * @magentoConfigFixture default/design/theme/allow_skin_files_duplication 0
     */
    public function testGetSkinUrlNoFilesDuplicationWithCaching()
    {
        Mage::app()->getLocale()->setLocale('en_US');
        $skinParams = array('_package' => 'test', '_theme' => 'default', '_skin' => 'default');
        $cacheKey = 'frontend/test/default/default/en_US';
        Mage::app()->cleanCache();

        $skinFile = 'images/logo.gif';
        $this->_model->getSkinUrl($skinFile, $skinParams);
        $map = unserialize(Mage::app()->loadCache($cacheKey));
        $this->assertTrue(count($map) == 1);
        $this->assertStringEndsWith('logo.gif', (string)array_pop($map));

        $skinFile = 'images/logo_email.gif';
        $this->_model->getSkinUrl($skinFile, $skinParams);
        $map = unserialize(Mage::app()->loadCache($cacheKey));
        $this->assertTrue(count($map) == 2);
        $this->assertStringEndsWith('logo_email.gif', (string)array_pop($map));
    }

    /**
     * @param string $file
     * @expectedException Magento_Exception
     * @dataProvider getSkinUrlDataExceptionProvider
     */
    public function testGetSkinUrlException($file)
    {
        $this->_model->getSkinUrl($file);
    }

    /**
     * @return array
     */
    public function getSkinUrlDataExceptionProvider()
    {
        return array(
            'non-existing theme file'  => array('path/to/nonexisting-file.ext'),
            'non-existing module file' => array('Some_Module::path/to/nonexisting-file.ext'),
        );
    }


    /**
     * @param string $file
     * @expectedException Magento_Exception
     * @dataProvider findFileExceptionDataProvider
     */
    public function testFindFileException($file)
    {
        $this->_model->getTemplateFilename($file);
    }

    public function findFileExceptionDataProvider()
    {
        return array(
            array('::no_scope.ext'),
            array('./file.ext'),
            array('../file.ext'),
            array('dir/./file.ext'),
            array('dir/../file.ext'),
        );
    }

    /**
     * Publication of skin files in development mode
     *
     * @param string $application
     * @param string $package
     * @param string $theme
     * @param string $skin
     * @param string $file
     * @dataProvider publishSkinFileDataProvider
     */
    public function testPublishSkinFile($application, $package, $theme, $skin, $file)
    {
        // determine path where a published file is to be expected
        $expectedModule = false;
        $targetFile   = $file;
        if (false !== strpos($file, '::')) {
            $targetFile = explode('::', $file);
            $expectedModule = $targetFile[0];
            $targetFile = $targetFile[1];
        }
        $path = array(Mage::getBaseDir('media'), 'skin', $application, $package, $theme, $skin, 'en_US');
        if ($expectedModule) {
            $path[] = $expectedModule;
        }
        $path[] = $targetFile;
        $targetFile = implode(DIRECTORY_SEPARATOR, $path);

        // test doesn't make sense if the original file doesn't exist or the target file already exists
        $params = array(
            '_area'    => $application,
            '_package' => $package,
            '_theme'   => $theme,
            '_skin'    => $skin,
        );
        //$originalFile = $this->_model->getSkinFile($file, $params);
        $this->assertFileExists($originalFile);

        // getSkinUrl() will trigger publication in development mode
        $this->assertFileNotExists($targetFile, 'Please verify isolation from previous test(s).');
        $this->_model->getSkinUrl($file, $params);
        $this->assertFileExists($targetFile);

        // as soon as the files are published, they must have the same mtime as originals
        $this->assertEquals(filemtime($originalFile), filemtime($targetFile),
            "These files mtime must be equal: {$originalFile} / {$targetFile}"
        );
    }

    /**
     * @return array
     */
    public function publishSkinFileDataProvider()
    {
        return array(
            array('frontend', 'test', 'default', 'default', 'images/logo_email.gif'),
            array('frontend', 'test', 'default', 'default', 'Mage_Page::favicon.ico'),
        );
    }

    /**
     * Publication of CSS files located in the theme (development mode)
     */
    public function testPublishCssFileFromTheme()
    {
        $publishedDir = self::$_skinPublicDir . '/frontend/package/default/theme/en_US';
        $this->assertFileNotExists($publishedDir, 'Please verify isolation from previous test(s).');
        $this->_model->getSkinUrl('css/file.css', array(
            '_package' => 'package',
            '_skin' => 'theme',
        ));
        foreach (self::$_cssFiles as $file) {
            $this->assertFileExists("{$publishedDir}/{$file}");
        }
        $this->assertFileNotExists("{$publishedDir}/absolute.gif");
        $this->assertFileNotExists(dirname($publishedDir) . '/access_violation.php');
    }

    /**
     * Publication of CSS files located in the module
     * @dataProvider publishCssFileFromModuleDataProvider
     */
    public function testPublishCssFileFromModule(
        $cssSkinFile, $designParams, $expectedCssFile, $expectedCssContent, $expectedRelatedFiles
    ) {
        $this->_model->getSkinUrl($cssSkinFile, $designParams);

        $expectedCssFile = self::$_skinPublicDir . '/' . $expectedCssFile;
        $this->assertFileExists($expectedCssFile);
        $actualCssContent = file_get_contents($expectedCssFile);

        $this->assertNotRegExp(
            '/url\(.*?' . Mage_Core_Model_Design_Package::SCOPE_SEPARATOR . '.*?\)/',
            $actualCssContent,
            'Published CSS file must not contain scope separators in URLs.'
        );

        foreach ($expectedCssContent as $expectedCssSubstring) {
            $this->assertContains($expectedCssSubstring, $actualCssContent);
        }

        foreach ($expectedRelatedFiles as $expectedFile) {
            $expectedFile = self::$_skinPublicDir . '/' . $expectedFile;
            $this->assertFileExists($expectedFile);
        }
    }

    public function publishCssFileFromModuleDataProvider()
    {
        return array(
            'frontend' => array(
                'widgets.css',
                array(
                    '_area'    => 'frontend',
                    '_package' => 'default',
                    '_skin'    => 'default',
                    '_module'  => 'Mage_Reports',
                ),
                'frontend/default/default/default/en_US/Mage_Reports/widgets.css',
                array(
                    'url(../Mage_Catalog/images/i_block-list.gif)',
                ),
                array(
                    'frontend/default/default/default/en_US/Mage_Catalog/images/i_block-list.gif',
                ),
            ),
            'adminhtml' => array(
                'Mage_Paypal::boxes.css',
                array(
                    '_area'    => 'adminhtml',
                    '_package' => 'package',
                    '_theme'   => 'test',
                    '_skin'    => 'default',
                    '_module'  => false,
                ),
                'adminhtml/package/test/default/en_US/Mage_Paypal/boxes.css',
                array(
                    'url(logo.gif)',
                    'url(section.png)',
                ),
                array(
                    'adminhtml/package/test/default/en_US/Mage_Paypal/logo.gif',
                    'adminhtml/package/test/default/en_US/Mage_Paypal/section.png',
                ),
            ),
        );
    }


    /**
     * Test that modified CSS file and changed resources are re-published
     */
    public function testPublishResourcesAndCssWhenChangedCss()
    {
        $fixtureSkinPath = self::$_fixtureTmpDir . '/frontend/test/default/skin/default/';
        $publishedPath = self::$_skinPublicDir . '/frontend/test/default/default/en_US/';

        // Prepare temporary fixture directory and publish files from it
        $this->_copyFixtureSkinToTmpDir($fixtureSkinPath);
        $this->_model->getSkinUrl('style.css');

        // Change main file and referenced files - everything changed and referenced must appear
        file_put_contents(
            $fixtureSkinPath . 'style.css',
            'div {background: url(images/rectangle.gif);}',
            FILE_APPEND
        );
        file_put_contents(
            $fixtureSkinPath . 'sub.css',
            '.sub2 {border: 1px solid magenta}',
            FILE_APPEND
        );
        $this->_model->getSkinUrl('style.css');

        $this->assertFileEquals($fixtureSkinPath . 'style.css', $publishedPath . 'style.css');
        $this->assertFileEquals($fixtureSkinPath . 'sub.css', $publishedPath . 'sub.css');
        $this->assertFileEquals($fixtureSkinPath . 'images/rectangle.gif', $publishedPath . 'images/rectangle.gif');
    }

    /**
     * Test changed resources, referenced in non-modified CSS file, are re-published
     * @magentoAppIsolation enabled
     */
    public function testPublishChangedResourcesWhenUnchangedCss()
    {
        $fixtureSkinPath = self::$_fixtureTmpDir . '/frontend/test/default/skin/default/';
        $publishedPath = self::$_skinPublicDir . '/frontend/test/default/default/en_US/';

        // Prepare temporary fixture directory and publish files from it
        $this->_copyFixtureSkinToTmpDir($fixtureSkinPath);
        $this->_model->getSkinUrl('style.css');

        // Change referenced files
        copy($fixtureSkinPath . 'images/rectangle.gif', $fixtureSkinPath . 'images/square.gif');
        touch($fixtureSkinPath . 'images/square.gif');
        file_put_contents(
            $fixtureSkinPath . 'sub.css',
            '.sub2 {border: 1px solid magenta}',
            FILE_APPEND
        );

        // Without developer mode nothing must be re-published
        Mage::setIsDeveloperMode(false);
        $this->_model->getSkinUrl('style.css');

        $this->assertFileNotEquals($fixtureSkinPath . 'sub.css', $publishedPath . 'sub.css');
        $this->assertFileNotEquals($fixtureSkinPath . 'images/rectangle.gif', $publishedPath . 'images/square.gif');

        // With developer mode all changed files must be re-published
        Mage::setIsDeveloperMode(true);
        $this->_model->getSkinUrl('style.css');

        $this->assertFileEquals($fixtureSkinPath . 'sub.css', $publishedPath . 'sub.css');
        $this->assertFileEquals($fixtureSkinPath . 'images/rectangle.gif', $publishedPath . 'images/square.gif');
    }

    /**
     * Prepare design directory with initial css and resources
     *
     * @param string $fixtureSkinPath
     */
    protected function _copyFixtureSkinToTmpDir($fixtureSkinPath)
    {
        Mage::app()->getConfig()->getOptions()->setDesignDir(self::$_fixtureTmpDir);
        mkdir($fixtureSkinPath . '/images', 0777, true);

        // Copy all files to fixture location
        $mTime = time() - 10; // To ensure that all files, changed later in test, will be recognized for publication
        $sourcePath = dirname(__DIR__) . '/_files/design/frontend/test/publication/skin/default/';
        $files = array('../../theme.xml', 'style.css', 'sub.css', 'images/square.gif', 'images/rectangle.gif');
        foreach ($files as $file) {
            copy($sourcePath . $file, $fixtureSkinPath . $file);
            touch($fixtureSkinPath . $file, $mTime);
        }
    }
}
