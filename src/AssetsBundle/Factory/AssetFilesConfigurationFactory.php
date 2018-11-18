<?php
namespace AssetsBundle\Factory;

use Interop\Container\ContainerInterface;

use AssetsBundle\AssetFile\AssetFilesConfiguration;



class AssetFilesConfigurationFactory
{
	/**
	 * __invoke() - Called to invoke the factory.  Here we inject our dependencies.
	 *
	 * @param \Interop\Container\ContainerInterface $container
	 *
	 *
	 * @return \AssetsBundle\AssetFile\AssetFilesConfiguration
	 */
	public function __invoke(ContainerInterface $container)
    {
    	/*
    	 *
    	 */

       	return new AssetFilesConfiguration();
    }
}

