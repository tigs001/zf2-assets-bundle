<?php
namespace AssetsBundleTest;
class ModuleTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @var \AssetsBundle\Module
	 */
	protected $module;

	/**
	 * @var \Zend\Mvc\MvcEvent
	 */
	protected $event;

	public function setUp(){
		$this->module = new \AssetsBundle\Module();
		$aConfiguration = \AssetsBundleTest\Bootstrap::getServiceManager()->get('Config');
		$this->event = new \Zend\Mvc\MvcEvent();
		$this->event
		->setViewModel(new \Zend\View\Model\ViewModel())
		->setApplication(\AssetsBundleTest\Bootstrap::getServiceManager()->get('Application'))
		->setRouter(\Zend\Router\Http\TreeRouteStack::factory(isset($aConfiguration['router'])?$aConfiguration['router']:array()))
		->setRouteMatch(new \Zend\Router\RouteMatch(array('controller' => 'test-module','action' => 'test-module\index-controller')));
	}

	public function testGetConsoleUsager() {
		$oConsoleService = \AssetsBundleTest\Bootstrap::getServiceManager()->get('console');
		$this->assertTrue(is_array($this->module->getConsoleUsage($oConsoleService)));
	}

	public function testGetAutoloaderConfig(){
        $this->assertEquals(
        	array('Zend\Loader\ClassMapAutoloader' => array(realpath(getcwd().'/../autoload_classmap.php'))),
        	$this->module->getAutoloaderConfig()
        );
    }

    public function testGetConfig(){
    	$this->assertTrue(is_array($this->module->getConfig()));
    }
}