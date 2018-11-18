<?php
namespace AssetsBundle\Factory;

use Interop\Container\ContainerInterface;

use AssetsBundle\AssetFile\AssetFilesManager;



class AssetFilesManagerFactory
{
	/**
	 * __invoke() - Called to invoke the factory.  Here we inject our dependencies.
	 *
	 * @param \Interop\Container\ContainerInterface $container
	 *
	 *
	 * @return \AssetsBundle\AssetFile\AssetFileFiltersManager
	 */
	public function __invoke(ContainerInterface $container)
    {
    	/*
    	 *
    	 */
        $options = $container->get('AssetsBundleServiceOptions');

       	return new AssetFilesManager($options);
    }
}

