<?php

namespace AssetsBundleTest\Factory;

class ServiceFactoryTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var \AssetsBundle\Factory\ServiceFactory
     */
    protected $serviceFactory;

    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp() {
        $this->serviceFactory = new \AssetsBundle\Factory\ServiceFactory();
        $this->configuration = \AssetsBundleTest\Bootstrap::getServiceManager()->get('Config');
    }

    public function testCreateServiceWithoutBaseUrl() {
        $aConfiguration = $this->configuration;
        unset($aConfiguration['assets_bundle']['baseUrl']);

        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();
        $bAllowOverride = $oServiceManager->getAllowOverride();
        if (!$bAllowOverride) {
            $oServiceManager->setAllowOverride(true);
        }
        $oServiceManager->setService('Config', $aConfiguration);
        $oServiceManager->setAllowOverride($bAllowOverride);

        $this->serviceFactory->createService(\AssetsBundleTest\Bootstrap::getServiceManager());

        $this->assertTrue(true, 'This is here to suppress warnings from PHPUnit about it being a risky test.  If we threw an exception, we failed.');
    }

    public function testCreateServiceWithClassnameFilter() {
        $aConfiguration = $this->configuration;
        $aConfiguration['assets_bundle']['filters']['css'] = 'AssetsBundle\AssetFile\AssetFileFilter\CssAssetFileFilter';

        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();
        $bAllowOverride = $oServiceManager->getAllowOverride();
        if (!$bAllowOverride) {
            $oServiceManager->setAllowOverride(true);
        }
        $oServiceManager->setService('Config', $aConfiguration);
        $oServiceManager->setAllowOverride($bAllowOverride);

        $this->serviceFactory->createService(\AssetsBundleTest\Bootstrap::getServiceManager());

        $this->assertTrue(true, 'This is here to suppress warnings from PHPUnit about it being a risky test.  If we threw an exception, we failed.');
    }

    public function testCreateServiceWithClassnameRendererToStrategy() {
        $aConfiguration = $this->configuration;
        $aConfiguration['assets_bundle']['rendererToStrategy']['zendviewrendererphprenderer'] = '\AssetsBundle\View\Strategy\ViewHelperStrategy';

        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();
        $bAllowOverride = $oServiceManager->getAllowOverride();
        if (!$bAllowOverride) {
            $oServiceManager->setAllowOverride(true);
        }
        $oServiceManager->setService('Config', $aConfiguration);
        $oServiceManager->setAllowOverride($bAllowOverride);

        $this->serviceFactory->createService(\AssetsBundleTest\Bootstrap::getServiceManager());

        $this->assertTrue(true, 'This is here to suppress warnings from PHPUnit about it being a risky test.  If we threw an exception, we failed.');
    }

    public function testCreateServiceWithoutAssetsPath() {
        $aConfiguration = $this->configuration;
        unset($aConfiguration['assets_bundle']['assetsPath']);

        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();
        $bAllowOverride = $oServiceManager->getAllowOverride();
        if (!$bAllowOverride) {
            $oServiceManager->setAllowOverride(true);
        }
        $oServiceManager->setService('Config', $aConfiguration);
        $oServiceManager->setAllowOverride($bAllowOverride);

        $this->serviceFactory->createService(\AssetsBundleTest\Bootstrap::getServiceManager());

        $this->assertTrue(true, 'This is here to suppress warnings from PHPUnit about it being a risky test.  If we threw an exception, we failed.');
    }

    public function tearDown() {
        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();
        $bAllowOverride = $oServiceManager->getAllowOverride();
        if (!$bAllowOverride) {
            $oServiceManager->setAllowOverride(true);
        }
        $oServiceManager->setService('Config', $this->configuration);
        $oServiceManager->setAllowOverride($bAllowOverride);
    }

}
