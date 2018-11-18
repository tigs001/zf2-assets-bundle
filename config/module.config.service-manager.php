<?php

//Service manager module config
return array(
    'factories' => array(
        'LesscAssetFileFilter' => '\AssetsBundle\Factory\AssetFileFilter\LesscAssetFileFilterFactory',
        'LessphpAssetFileFilter' => '\AssetsBundle\Factory\AssetFileFilter\LessphpAssetFileFilterFactory',
        'CssAssetFileFilter' => '\AssetsBundle\Factory\AssetFileFilter\CssAssetFileFilterFactory',
        'JsMinAssetFileFilter' => '\AssetsBundle\Factory\AssetFileFilter\JsMinAssetFileFilterFactory',
        'JShrinkAssetFileFilter' => '\AssetsBundle\Factory\AssetFileFilter\JShrinkAssetFileFilterFactory',
        'PngAssetFileFilter' => '\AssetsBundle\Factory\AssetFileFilter\PngAssetFileFilterFactory',
        'JpegAssetFileFilter' => '\AssetsBundle\Factory\AssetFileFilter\JpegAssetFileFilterFactory',
        'GifAssetFileFilter' => '\AssetsBundle\Factory\AssetFileFilter\GifAssetFileFilterFactory',
        'AssetsBundleService' => '\AssetsBundle\Factory\ServiceFactory',
        'AssetsBundleServiceOptions' => '\AssetsBundle\Factory\ServiceOptionsFactory',
        'AssetsBundleToolsService' => '\AssetsBundle\Factory\ToolsServiceFactory',
        'JsCustomStrategy' => '\AssetsBundle\Factory\JsCustomStrategyFactory',
        AssetsBundle\AssetFile\AssetFileFiltersManager::class 	=> AssetsBundle\Factory\AssetFileFiltersManagerFactory::class,
        AssetsBundle\AssetFile\AssetFilesManager::class 		=> AssetsBundle\Factory\AssetFilesManagerFactory::class,
        AssetsBundle\AssetFile\AssetFilesConfiguration::class 	=> AssetsBundle\Factory\AssetFilesConfigurationFactory::class,
        AssetsBundle\AssetFile\AssetFilesCacheManager::class 	=> AssetsBundle\Factory\AssetFilesCacheManagerFactory::class,
    	AssetsBundle\Service\Service::class 					=> AssetsBundle\Factory\ServiceFactory::class,
    	AssetsBundle\Service\ToolsService::class 				=> AssetsBundle\Factory\ToolsServiceFactory::class,
    ),
    'invokables' => array(
        'JsCustomRenderer' => '\AssetsBundle\View\Renderer\JsCustomRenderer'
    ),
);
