<?php
namespace AssetsBundle\Factory;

use Interop\Container\ContainerInterface;

use AssetsBundle\AssetFile\AssetFilesCacheManager;



class AssetFilesCacheManagerFactory
{
	/**
	 * __invoke() - Called to invoke the factory.  Here we inject our dependencies.
	 *
	 * @param \Interop\Container\ContainerInterface $container
	 *
	 *
	 * @return \AssetsBundle\AssetFile\AssetFilesCacheManager
	 */
	public function __invoke(ContainerInterface $container)
    {
    	/*
    	 *
    	 */
        $options = $container->get('AssetsBundleServiceOptions');

       	return new AssetFilesCacheManager($options);
    }
}

