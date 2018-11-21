<?php

namespace AssetsBundle\Factory;

use Interop\Container\ContainerInterface;

/*
 * Implementations should update to implement only Zend\ServiceManager\Factory\FactoryInterface.
 *
 * If upgrading from v2, take the following steps:
 *
 * - rename the method `createService()` to `__invoke()`, and:
 *   - rename the `$serviceLocator` argument to `$container`, and change the
 *     typehint to `Interop\Container\ContainerInterface`
 *   - add the `$requestedName` as a second argument
 *   - add the optional `array $options = null` argument as a final argument
 * - create a `createService()` method as defined in this interface, and have it
 *   proxy to `__invoke()`.
 *
 * Once you have tested your code, you can then update your class to only implement
 * Zend\ServiceManager\Factory\FactoryInterface, and remove the `createService()`
 * method.
 */


class ToolsServiceFactory implements \Zend\ServiceManager\FactoryInterface
{

    /**
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
	 *
     * @return \AssetsBundle\Service\ToolsService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $oToolsService = new \AssetsBundle\Service\ToolsService();
        $oAssetsService = $container->get('AssetsBundleService');
        $oToolsService->setAssetsBundleService($oAssetsService);
        $oToolsService->setMvcEvent(($oMvcEvent = $container->get('Application')->getMvcEvent()) ? clone $oMvcEvent : new \Zend\Mvc\MvcEvent());

        if ($container->has('console') && ($oConsole = $container->get('console')) instanceof \Zend\Console\Adapter\AdapterInterface) {
            $oToolsService->setConsole($oConsole);
        }
        return $oToolsService;
    }


    /**
     *
     * {@inheritDoc}
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator)
    {
    	return $this->invoke($serviceLocator);
    }


}

