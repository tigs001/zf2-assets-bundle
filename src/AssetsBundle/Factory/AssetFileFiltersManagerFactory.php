<?php
namespace AssetsBundle\Factory;

use Interop\Container\ContainerInterface;

use AssetsBundle\AssetFile\AssetFileFiltersManager;



class AssetFileFiltersManagerFactory
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

       	return new AssetFileFiltersManager($container);
    }
}

