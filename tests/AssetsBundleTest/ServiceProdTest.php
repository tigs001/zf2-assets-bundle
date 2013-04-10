<?php
namespace AssetsBundleTest;
class ServiceProdTest extends \PHPUnit_Framework_TestCase{
	/**
	 * @var array
	 */
	private $configuration = array(
		'asset_bundle' => array(
			'production' => true,
			'recursiveSearch' => true,
			'basePath' => '/',
			'cachePath' => '@zfRootPath/AssetsBundleTest/_files/cache',
			'assetsPath' => '@zfRootPath/AssetsBundleTest/_files/assets',
			'assets' => array(
				'css' => array('css/test.css'),
				'less' => array('less/test.less'),
				'js' => array('js/test.js'),
				'index' => array(
					'test-media' => array(
						'css' => array('css/test-media.css'),
						'less' => array('less/test-media.less'),
						'media' => array(
							'@zfRootPath/AssetsBundleTest/_files/fonts',
							'@zfRootPath/AssetsBundleTest/_files/images'
						)
					),
					'test-mixins' => array(
						'less' => array(
							'less/test-mixins.less',
							'less/test-mixins-use.less'
						)
					)
				)
			)
		)
	);

	/**
	 * @var \AssetsBundle\Service\Service
	 */
	private $service;

	/**
	 * @var \Zend\Mvc\Router\RouteMatch
	 */
	private $routeMatch;

	/**
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
    protected function setUp(){
        $oServiceManager = \AssetsBundleTest\Bootstrap::getServiceManager();

        $aConfiguration = $oServiceManager->get('Config');
        unset($aConfiguration['asset_bundle']['assets']);

        $this->configuration = \Zend\Stdlib\ArrayUtils::merge($aConfiguration,$this->configuration);
        $bAllowOverride = $oServiceManager->getAllowOverride();
        if(!$bAllowOverride)$oServiceManager->setAllowOverride(true);
        $oServiceManager->setService('Config',$this->configuration)->setAllowOverride($bAllowOverride);

        //Define service
        $oServiceFactory = new \AssetsBundle\Factory\ServiceFactory();
        $this->routeMatch = new \Zend\Mvc\Router\RouteMatch(array('controller' => 'index','action' => 'index'));
        $this->service = $oServiceFactory->createService($oServiceManager)
        ->setRenderer(new \Zend\View\Renderer\PhpRenderer())
        ->setControllerName($this->routeMatch->getParam('controller'))
        ->setActionName($this->routeMatch->getParam('action'));
    }

    public function testService(){
    	//Test service instance
    	$this->assertInstanceOf('AssetsBundle\Service\Service',$this->service);

    	//Test cache path
    	$this->assertEquals(realpath(__DIR__.'/_files/cache').DIRECTORY_SEPARATOR, $this->service->getCachePath());

    	//Test assets configuration
    	$this->assertTrue($this->service->controllerHasAssetConfiguration('index'));
    	$this->assertFalse($this->service->controllerHasAssetConfiguration('wrong-controller'));

    	$this->assertTrue($this->service->actionHasAssetConfiguration('test-media'));
    	$this->assertFalse($this->service->actionHasAssetConfiguration('wrong-action'));
    }

    public function testSetRoute(){
    	//Controller
    	$this->assertInstanceOf('AssetsBundle\Service\Service',$this->service->setControllerName($this->routeMatch->getParam('controller')));
    	$this->assertEquals('index', $this->service->getControllerName());

    	//Action
    	$this->assertInstanceOf('AssetsBundle\Service\Service',$this->service->setActionName($this->routeMatch->getParam('action')));
    	$this->assertEquals('index', $this->service->getActionName());

    	//Cache file name
    	$this->assertEquals($this->service->getCacheFileName(), md5($this->routeMatch->getParam('controller').\AssetsBundle\Service\Service::NO_ACTION));
    }

    public function testRenderSimpleAssets(){
		$sCacheExpectedPath = __DIR__.'/_files/prod-cache-expected';
		$sCacheName = $this->service->getCacheFileName();

		//Cache file name
		$this->assertEquals($sCacheName, md5($this->routeMatch->getParam('controller').\AssetsBundle\Service\Service::NO_ACTION));

		$sCssFile = $sCacheName.'.css';
		$sLessFile = $sCacheName.'.less';
		$sJsFile = $sCacheName.'.js';

		//Empty cache directory
		$this->emptyCacheDirectory();

		//Render assets
		$this->assertInstanceOf('AssetsBundle\Service\Service',$this->service->renderAssets());

		//Css cache file
		$this->assertFileExists($this->service->getCachePath().$sCssFile);
		$this->assertEquals(
			file_get_contents($this->service->getCachePath().$sCssFile),
			file_get_contents($sCacheExpectedPath.'/'.$sCssFile)
		);

		//Less cache file
		$this->assertFileExists($this->service->getCachePath().$sLessFile);
		$this->assertEquals(
			file_get_contents($this->service->getCachePath().$sLessFile),
			file_get_contents($sCacheExpectedPath.'/'.$sLessFile)
		);

		//Js cache file
		$this->assertFileExists($this->service->getCachePath().$sJsFile);
		$this->assertEquals(
			file_get_contents($this->service->getCachePath().$sJsFile),
			file_get_contents($sCacheExpectedPath.'/'.$sJsFile)
		);

		//Empty cache directory
		$this->emptyCacheDirectory();
    }

	public function testRenderAssetsWithMedias(){
		$sCacheExpectedPath = __DIR__.'/_files/prod-cache-expected';

		$this->assertInstanceOf('AssetsBundle\Service\Service',$this->service->setActionName('test-media'));
		$this->assertEquals('test-media', $this->service->getActionName());

		//Test Cache file name
		$this->assertEquals($this->service->getCacheFileName(), md5($this->routeMatch->getParam('controller').'test-media'));

		$sCacheName = $this->service->getCacheFileName();

		$sCssFile = $sCacheName.'.css';
		$sLessFile = $sCacheName.'.less';

		//Empty cache directory
		$this->emptyCacheDirectory();

		//Render assets
		$this->assertInstanceOf('AssetsBundle\Service\Service',$this->service->renderAssets());

		//Css cache file
		$this->assertFileExists($this->service->getCachePath().$sCssFile);
		$this->assertEquals(
			file_get_contents($this->service->getCachePath().$sCssFile),
			file_get_contents($sCacheExpectedPath.'/'.$sCssFile)
		);

		//Less cache file
		$this->assertFileExists($this->service->getCachePath().'/'.$sLessFile);
		$this->assertEquals(
			file_get_contents($this->service->getCachePath().'/'.$sLessFile),
			file_get_contents($sCacheExpectedPath.'/'.$sLessFile)
		);

		//Media cache files

		#Fonts
		$this->assertFileExists($this->service->getCachePath().'/AssetsBundleTest/_files/fonts/fontawesome-webfont.eot');
		$this->assertFileExists($this->service->getCachePath().'/AssetsBundleTest/_files/fonts/fontawesome-webfont.ttf');
		$this->assertFileExists($this->service->getCachePath().'/AssetsBundleTest/_files/fonts/fontawesome-webfont.woff');

		#Images
		$this->assertFileExists($this->service->getCachePath().'/AssetsBundleTest/_files/images/test-media.gif');
		$this->assertFileExists($this->service->getCachePath().'/AssetsBundleTest/_files/images/test-media.png');

		#Subfolders
		$this->assertFileExists($this->service->getCachePath().'/AssetsBundleTest/_files/images//subfolder/test-sub-media.jpg');

		//Check optimisation

		//Gd2 compression
		if(function_exists('imagecreatefromstring')){
			//Sizes
			$this->assertGreaterThan(filesize($this->service->getCachePath().'/AssetsBundleTest/_files/images/test-media.png'),filesize(__DIR__.'/_files/images/test-media.png'));
			$this->assertGreaterThan(filesize($this->service->getCachePath().'/AssetsBundleTest/_files/images/test-media.jpg'),filesize(__DIR__.'/_files/images/test-media.jpg'));
			$this->assertGreaterThan(filesize($this->service->getCachePath().'/AssetsBundleTest/_files/images/test-media.gif'),filesize(__DIR__.'/_files/images/test-media.gif'));
		}

		//Empty cache directory
		$this->emptyCacheDirectory();
    }

    public function testRenderMixins(){
    	$sCacheExpectedPath = __DIR__.'/_files/prod-cache-expected';

    	$this->assertInstanceOf('AssetsBundle\Service\Service',$this->service->setActionName('test-mixins'));
    	$this->assertEquals('test-mixins', $this->service->getActionName());

    	//Test Cache file name
    	$this->assertEquals($this->service->getCacheFileName(), md5($this->routeMatch->getParam('controller').'test-mixins'));

    	$sCacheName = $this->service->getCacheFileName();

    	$sLessFile = $sCacheName.'.less';

    	//Empty cache directory
    	$this->emptyCacheDirectory();

    	//Render assets
    	$this->assertInstanceOf('AssetsBundle\Service\Service',$this->service->renderAssets());

    	//Less cache file
    	$this->assertFileExists($this->service->getCachePath().$sLessFile);

    	$this->assertEquals(
    		file_get_contents($this->service->getCachePath().$sLessFile),
    		file_get_contents($sCacheExpectedPath.'/'.$sLessFile)
    	);

    	//Empty cache directory
    	$this->emptyCacheDirectory();
    }

    protected function emptyCacheDirectory(){
    	//Empty cache directory except .gitignore
    	foreach(new \RecursiveIteratorIterator(
    		new \RecursiveDirectoryIterator($this->service->getCachePath(), \RecursiveDirectoryIterator::SKIP_DOTS),
    		\RecursiveIteratorIterator::CHILD_FIRST
    	) as $oFileinfo){
    		if($oFileinfo->isDir())rmdir($oFileinfo->getRealPath());
    		elseif($oFileinfo->getBasename() !== '.gitignore')unlink($oFileinfo->getRealPath());
    	}
    }
}