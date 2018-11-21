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


class ServiceOptionsFactory implements \Zend\ServiceManager\FactoryInterface {

    /**
     * @see Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     *
     * @throws \UnexpectedValueException
     *
     * @return \AssetsBundle\Service\ServiceOptions
     */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        $aConfiguration = $container->get('Config');
        if (!isset($aConfiguration['assets_bundle'])) {
            throw new \UnexpectedValueException('AssetsBundle configuration is undefined');
        }

        $aOptions = $aConfiguration['assets_bundle'];
        if ($aOptions instanceof \Traversable) {
            $aOptions = \Zend\Stdlib\ArrayUtils::iteratorToArray($aOptions);
        } elseif (!is_array($aOptions)) {
            throw new \InvalidArgumentException('"assets_bundle" configuration expects an array or Traversable object; received "' . (is_object($aOptions) ? get_class($aOptions) : gettype($aOptions)) . '"');
        }

        //Define base URL of the application
        if (!isset($aOptions['baseUrl'])) {
            if (($oRequest = $container->get('request')) instanceof \Zend\Http\PhpEnvironment\Request) {
                $aOptions['baseUrl'] = $oRequest->getBaseUrl();
            } else {
                $oRequest = new \Zend\Http\PhpEnvironment\Request();
                $aOptions['baseUrl'] = $oRequest->getBaseUrl();
            }
        }

        //Retrieve filters
        if (isset($aOptions['view_helper_plugins'])) {
            $aViewHelperPlugins = $aOptions['view_helper_plugins'];
            if ($aViewHelperPlugins instanceof \Traversable) {
                $aViewHelperPlugins = \Zend\Stdlib\ArrayUtils::iteratorToArray($aOptions);
            } elseif (!is_array($aViewHelperPlugins)) {
                throw new \InvalidArgumentException('Assets bundle "filters" option expects an array or Traversable object; received "' . (is_object($aViewHelperPlugins) ? get_class($aViewHelperPlugins) : gettype($aViewHelperPlugins)) . '"');
            }

            $oViewHelperPluginManager = $container->get('ViewHelperManager');

            foreach ($aViewHelperPlugins as $sAssetFileType => $oViewHelperPlugin) {
                if (!\AssetsBundle\AssetFile\AssetFile::assetFileTypeExists($sAssetFileType)) {
                    throw new \LogicException('Asset file type "' . $sAssetFileType . '" is not valid');
                }
                if (is_string($oViewHelperPlugin)) {
                    if ($oViewHelperPluginManager->has($oViewHelperPlugin)) {
                        $oViewHelperPlugin = $oViewHelperPluginManager->get($oViewHelperPlugin);
                    } elseif (class_exists($oViewHelperPlugin)) {
                        $oViewHelperPlugin = new $oViewHelperPlugin();
                    } else {
                        throw new \LogicException('View helper plugin "' . $oViewHelperPlugin . '" is not a registered service or an existing class');
                    }

                    if ($oViewHelperPlugin instanceof \Zend\View\Helper\HelperInterface) {
                        $aViewHelperPlugins[$sAssetFileType] = $oViewHelperPlugin;
                    } else {
                        throw new \LogicException('View helper plugin expects an instance of "\Zend\View\Helper\HelperInterface", "' . get_class($oViewHelperPlugin) . '" given');
                    }
                }
            }
            $aOptions['view_helper_plugins'] = $aViewHelperPlugins;
        }

        //Unset filters
        unset($aOptions['filters']);
        return new \AssetsBundle\Service\ServiceOptions($aOptions);
    }




    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @param \Zend\ServiceManager\ServiceLocatorInterface $oServiceLocator
     * @throws \UnexpectedValueException
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $oServiceLocator)
    {
    	return $this->__invoke($oServiceLocator);
    }

}
