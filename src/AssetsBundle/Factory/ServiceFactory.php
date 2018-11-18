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


class ServiceFactory implements \Zend\ServiceManager\FactoryInterface
{

    /**
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     *
     * @throws \UnexpectedValueException
     *
     * @return \AssetsBundle\Service\Service
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $aConfiguration = $container->get('Config');
        if (!isset($aConfiguration['assets_bundle'])) {
            throw new \UnexpectedValueException('AssetsBundle configuration is undefined');
        }

        /*
         * Create our classes that we need to inject into
         * the Service.
         */
        $options = $container->get('AssetsBundleServiceOptions');

        /*
         * Create the AssetFileFiltersManager.
         */
        $affiltermanager = $container->get('AssetsBundle\AssetFile\AssetFileFiltersManager'); 	/* @var $affiltermanager \AssetsBundle\AssetFile\AssetFileFiltersManager */
		$affiltermanager->setOptions($options);


		/*
		 * Create the Asset Files Configuration.
		 */
		$afconfig = $container->get('AssetsBundle\AssetFile\AssetFilesConfiguration'); 	/* @var $afconfig \AssetsBundle\AssetFile\AssetFilesConfiguration */
		$afconfig->setOptions($options);

		/*
		 * Create the Asset Files Cache Manager.
		 */
		$afcachemgr = $container->get('AssetsBundle\AssetFile\AssetFilesCacheManager'); 	/* @var $afconfig \AssetsBundle\AssetFile\AssetFilesCacheManager */
		$afcachemgr->setOptions($options);


        /*
         * Create the AssetFilesManager.
         * Then inject the AssetFileFiltersManager we created as
         * well as the AssetFilesConfiguration we created and
         * the AssetFilesCacheManager we created.
         */
        $afilesmanager = $container->get('AssetsBundle\AssetFile\AssetFilesManager'); 	/* @var $afilesmanager \AssetsBundle\AssetFile\AssetFilesManager */
		$afilesmanager->setOptions($options);
        $afilesmanager->setAssetFileFiltersManager($affiltermanager);
        $afilesmanager->setAssetFilesConfiguration($afconfig);
        $afilesmanager->setAssetFilesCacheManager($afcachemgr);


        /*
         * Initialize AssetsBundle service with options
         * Then inject the options and AssetFilesManager we created.
         */
        $oAssetsBundleService = new \AssetsBundle\Service\Service();
        $oAssetsBundleService->setOptions($options);
        $oAssetsBundleService->setAssetFilesManager($afilesmanager);


        //Retrieve filters
        if (isset($aConfiguration['assets_bundle']['filters'])) {
            $aFilters = $aConfiguration['assets_bundle']['filters'];
            if ($aFilters instanceof \Traversable) {
                $aFilters = \Zend\Stdlib\ArrayUtils::iteratorToArray($aFilters);
            } elseif (!is_array($aFilters)) {
                throw new \InvalidArgumentException('Assets bundle "filters" option expects an array or Traversable object; received "' . (is_object($aFilters) ? get_class($aFilters) : gettype($aFilters)) . '"');
            }

            $oAssetFileFiltersManager = $oAssetsBundleService->getAssetFilesManager()->getAssetFileFiltersManager();
            foreach ($aFilters as $sFilterAliasName => $oFilter) {
                if ($oFilter === null) {
                    continue;
                }
                if ($oFilter instanceof \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface) {
                    $oAssetFileFiltersManager->setService($oFilter->getFilterName(), $oFilter);
                    continue;
                }
                if (is_string($oFilter)) {
                    $sFilterName = $oFilter;
                    $oFilter = array();
                } else {
                    if ($oFilter instanceof \Traversable) {
                        $oFilter = \Zend\Stdlib\ArrayUtils::iteratorToArray($oFilter);
                    }
                    if (is_array($oFilter)) {
                        if (isset($oFilter['filter_name'])) {
                            $sFilterName = $oFilter['filter_name'];
                            unset($oFilter['filter_name']);
                        }
                    } elseif (!is_array($oFilter)) {
                        throw new \InvalidArgumentException('Filter expect expects a string, an array or Traversable object; received "' . (is_object($oFilter) ? get_class($oFilter) : gettype($oFilter)) . '"');
                    }
                }

                //Retrieve filter
                if ($container->has($sFilterName)) {
                    $oFilter = $container->get($sFilterName);
                } elseif (class_exists($sFilterName)) {
                    $oFilter = new $sFilterName($oFilter);
                } else {
                    throw new \InvalidArgumentException('Filter "' . $sFilterName . '" is not an available service or an existing class');
                }

                if ($oFilter instanceof \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface) {
                	// Add the filter name service if it does not already exist.
                	$sAssetFileFilterName = $oFilter->getAssetFileFilterName();
                	if ( ! $oAssetFileFiltersManager->has($sAssetFileFilterName))
                    	$oAssetFileFiltersManager->setService($sAssetFileFilterName, $oFilter);
                	// Add an alias to the service if it does not already exist.
                    if (!$oAssetFileFiltersManager->has($sFilterAliasName)) {
                        $oAssetFileFiltersManager->setAlias($sFilterAliasName, $sAssetFileFilterName);
                    }
                } else {
                    throw new \InvalidArgumentException('Filter expects an instance of \AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface, "' . get_class($oFilter) . '" given');
                }
            }
        }
        return $oAssetsBundleService;
    }


    /**
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * @param \Zend\ServiceManager\ServiceLocatorInterface $oServiceLocator
     * @throws \UnexpectedValueException
     * @return \AssetsBundle\Service\Service
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $oServiceLocator)
    {
    	return $this->__invoke($oServiceLocator, 'AssetsBundleService');
    }
}
