<?php

namespace AssetsBundleTest\View\Strategy;

class JsCustomStrategyTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \AssetsBundle\View\Strategy\JsCustomStrategy
     */
    protected $jsCustomStrategy;

    /**
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {

        // Empty cache and processed directories
        $oToolsService = \AssetsBundleTest\Bootstrap::getServiceManager()->get('AssetsBundleToolsService');
        $oToolsService->emptyCache(false);
        $this->jsCustomStrategy = new \AssetsBundle\View\Strategy\JsCustomStrategy();
    }

    /**
     * @expectedException LogicException
     */
    public function testGetRendererUnset()
    {
        $this->jsCustomStrategy->getRenderer();
    }

    public function testAttachDetach()
    {
        $oEventManager = \AssetsBundleTest\Bootstrap::getServiceManager()->get('EventManager'); 	/* @var $oEventManager \Zend\EventManager\EventManager */

        /*
         * We no longer can inspect the EventManager for
         * its attached events.  See
         * https://docs.zendframework.com/zend-eventmanager/migration/removed/#eventmanagerinterfacegetevents-and-getlisteners
         *
         * So I am going to remove
         * them one at a time to test for their existence.
         *
         * We are expecting two listners:  'renderer', and 'response'
         */
        $this->jsCustomStrategy->attach($oEventManager);

        /*
         * Now remove these two and hope that's all we have.
         * When we detach directly from the event manager,
         * it will not remove them from the array of listeners
         * managed by the jsCustomStrategy.
         */
        $listeners = $this->jsCustomStrategy->getListeners();
        $this->assertEquals(2, count($listeners), 'The number of listeners in the jsCustomStrategy array is incorrect.  Expecting 2');
        foreach ($listeners as $l)
        {
        	/*
        	 * This does not really test anything.
        	 * The only time an invalid argument will be
        	 * called is if $l is an invalid type.  It does
        	 * not verify that the Listeners are attached.
        	 */
        	try
        	{
        		$oEventManager->detach($l);
        	}
        	catch (\Zend\EventManager\Exception\InvalidArgumentException $ex)
        	{
				$this->assertTrue(false, 'There was an exception detaching the listener ' . print_r($l, true));
        	}

        }


        /*
         * This will detach again, but will also
         * reduce the array of listeners mantained
         * by the jsCustomStrategy.
         */
        $this->jsCustomStrategy->detach($oEventManager);
        $listeners = $this->jsCustomStrategy->getListeners();
        $this->assertEquals(0, count($listeners), 'The number of listeners in the jsCustomStrategy array is incorrect.  Expecting 0');
    }

    /**
     * @expectedException LogicException
     */
    public function testGetRouterUnset()
    {
        $this->jsCustomStrategy->getRouter();
    }

    public function testSelectRenderer()
    {
        $this->jsCustomStrategy->setRouter(\AssetsBundleTest\Bootstrap::getServiceManager()->get('router'))->selectRenderer(new \Zend\View\ViewEvent());

        $this->assertTrue(true, 'This is here to suppress warnings from PHPUnit about it being a risky test.  If we threw an exception, we failed.');
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testSelectRendererWithWrongModel()
    {

        //Reset server datas
        $_SESSION = array();
        $_GET = array();
        $_POST = array();
        $_COOKIE = array();

        //Reset singleton
        // \Zend\EventManager\StaticEventManager::resetInstance();

        //Do not cache module config on testing environment
        $aApplicationConfig = \AssetsBundleTest\Bootstrap::getConfig();
        if (isset($aApplicationConfig['module_listener_options']['config_cache_enabled'])) {
            $aApplicationConfig['module_listener_options']['config_cache_enabled'] = false;
        }
        \Zend\Console\Console::overrideIsConsole(false);
        $oApplication = \Zend\Mvc\Application::init($aApplicationConfig);
        // $oEventManager = $oApplication->getEventManager();
        // $oEventManager->detach($oApplication->getServiceManager()->get('SendResponseListener'));

        $oRequest = $oApplication->getRequest();
        $oUri = new \Zend\Uri\Http('/jscustom/AssetsBundleTest\\Controller\\Test/test');

        $oRequest->setMethod(\Zend\Http\Request::METHOD_GET)
                ->setUri($oUri)
                ->setRequestUri($oUri->getPath());

        $oApplication->run();

        $oViewEvent = new \Zend\View\ViewEvent();
        $this->jsCustomStrategy
                ->setRouter($oApplication->getServiceManager()->get('router'))
                ->selectRenderer($oViewEvent->setRequest($oRequest));
    }

    public function tearDown()
    {
        //Empty cache and processed directories
        \AssetsBundleTest\Bootstrap::getServiceManager()->get('AssetsBundleToolsService')->emptyCache(false);
        parent::tearDown();
    }
}
