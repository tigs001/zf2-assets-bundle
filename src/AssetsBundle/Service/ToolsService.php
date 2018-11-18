<?php

namespace AssetsBundle\Service;

class ToolsService
{

    /**
     * @var \Zend\Console\Adapter\AdapterInterface
     */
    protected $console;

    /**
     * @var \Zend\Mvc\MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var \AssetsBundle\Service\Service
     */
    protected $assetsBundleService;

    /**
     * @return \AssetsBundle\Service\ToolsService
     */
    public function renderAllAssets()
    {

        //Initialize AssetsBundle service
        $oAssetsBundleService = $this->getAssetsBundleService();
        $oAssetsBundleService->getOptions()->setRenderer(new \Zend\View\Renderer\PhpRenderer());

        //Start process
        $oConsole = $this->getConsole();
        $oConsole->writeLine('');
        $oConsole->writeLine('======================================================================', \Zend\Console\ColorInterface::WHITE);
        $oConsole->writeLine('Render all assets for ' . ($oAssetsBundleService->getOptions()->isProduction() ? 'production' : 'development'), \Zend\Console\ColorInterface::GREEN);
        $oConsole->writeLine('======================================================================', \Zend\Console\ColorInterface::WHITE);
        $oConsole->writeLine('');

        //Empty cache directory
        $this->emptyCache();

        $oConsole->writeLine('');
        $oConsole->writeLine('Start rendering assets : ', \Zend\Console\ColorInterface::GREEN);
        $oConsole->writeLine('-------------------------', \Zend\Console\ColorInterface::WHITE);
        $oConsole->writeLine('');
        $aUnwantedKeys = array(
            \AssetsBundle\AssetFile\AssetFile::ASSET_CSS => true,
            \AssetsBundle\AssetFile\AssetFile::ASSET_LESS => true,
            \AssetsBundle\AssetFile\AssetFile::ASSET_JS => true,
            \AssetsBundle\AssetFile\AssetFile::ASSET_MEDIA => true
        );

        //Retrieve MvcEvent
        $oMvcEvent = $this->getMvcEvent();

        //Reset route match and request
        $oMvcEvent->setRouteMatch(new \Zend\Router\RouteMatch(array()))->setRequest(new \Zend\Http\Request());

        //Retrieve AssetsBundle service options
        $oOptions = $oAssetsBundleService->getOptions();

        $aAssetsConfiguration = $oOptions->getAssets();

        //Render all assets
        foreach (array_diff_key($aAssetsConfiguration, $aUnwantedKeys) as $sModuleName => $aModuleConfig) {
            //Render module assets
            $oOptions->setModuleName($sModuleName);

            //If module has global assets
            if (array_intersect_key($aModuleConfig, $aUnwantedKeys)) {
                $oConsole->write(' * ', \Zend\Console\ColorInterface::WHITE);
                $oConsole->write('[' . $sModuleName . ']', \Zend\Console\ColorInterface::LIGHT_CYAN);
                $oConsole->write('[No controller]', \Zend\Console\ColorInterface::LIGHT_BLUE);
                $oConsole->write('[No action]' . PHP_EOL, \Zend\Console\ColorInterface::LIGHT_WHITE);

                //Render assets for no_controller and no_action
                $oOptions->setControllerName(\AssetsBundle\Service\ServiceOptions::NO_CONTROLLER)
                        ->setActionName(\AssetsBundle\Service\ServiceOptions::NO_ACTION);
                $oAssetsBundleService->renderAssets($oMvcEvent);
            }

            foreach (array_diff_key($aAssetsConfiguration[$sModuleName], $aUnwantedKeys) as $sControllerName => $aControllerConfig) {
                //Render controller assets
                $oOptions->setControllerName($sControllerName);

                //If controller has global assets
                if (array_intersect_key($aControllerConfig, $aUnwantedKeys)) {
                    $oConsole->write(' * ', \Zend\Console\ColorInterface::WHITE);
                    $oConsole->write('[' . $sModuleName . ']', \Zend\Console\ColorInterface::LIGHT_CYAN);
                    $oConsole->write('[' . $sControllerName . ']', \Zend\Console\ColorInterface::LIGHT_BLUE);
                    $oConsole->write('[No action]' . PHP_EOL, \Zend\Console\ColorInterface::LIGHT_WHITE);

                    //Render assets for no_action
                    $oOptions->setActionName(\AssetsBundle\Service\ServiceOptions::NO_ACTION);
                    $oAssetsBundleService->renderAssets($oMvcEvent);
                }

                foreach (array_diff_key($aAssetsConfiguration[$sModuleName][$sControllerName], $aUnwantedKeys) as $sActionName => $aActionConfig) {
                    //Render assets for action
                    if (array_intersect_key($aActionConfig, $aUnwantedKeys)) {
                        $oConsole->write(' * ', \Zend\Console\ColorInterface::WHITE);
                        $oConsole->write('[' . $sModuleName . ']', \Zend\Console\ColorInterface::LIGHT_CYAN);
                        $oConsole->write('[' . $sControllerName . ']', \Zend\Console\ColorInterface::LIGHT_BLUE);
                        $oConsole->write('[' . $sActionName . ']' . PHP_EOL, \Zend\Console\ColorInterface::LIGHT_WHITE);

                        $oAssetsBundleService->getOptions()->setActionName($sActionName);
                        $oAssetsBundleService->renderAssets($oMvcEvent);
                    }
                }
            }
        }
        //Render global assets
        $oConsole->write(' * ', \Zend\Console\ColorInterface::WHITE);
        $oConsole->write('[No module]', \Zend\Console\ColorInterface::LIGHT_CYAN);
        $oConsole->write('[No controller]', \Zend\Console\ColorInterface::LIGHT_BLUE);
        $oConsole->write('[No action]' . PHP_EOL, \Zend\Console\ColorInterface::LIGHT_WHITE);
        $oAssetsBundleService->getOptions()
                ->setModuleName(\AssetsBundle\Service\ServiceOptions::NO_MODULE)
                ->setControllerName(\AssetsBundle\Service\ServiceOptions::NO_CONTROLLER)
                ->setActionName(\AssetsBundle\Service\ServiceOptions::NO_ACTION);
        $oAssetsBundleService->renderAssets($oMvcEvent);

        $oConsole->writeLine('');
        $oConsole->writeLine('---------------', \Zend\Console\ColorInterface::WHITE);
        $oConsole->writeLine('Assets rendered', \Zend\Console\ColorInterface::GREEN);
        $oConsole->writeLine('');

        return $this;
    }

    /**
     * @param boolean $bDisplayConsoleMessage
     * @return \AssetsBundle\Service\ToolsService
     */
    public function emptyCache($bDisplayConsoleMessage = true)
    {
        if ($bDisplayConsoleMessage) {
            $oConsole = $this->getConsole();
            $oConsole->writeLine('');
            $oConsole->writeLine('========================', \Zend\Console\ColorInterface::WHITE);
            $oConsole->writeLine('Empty cache', \Zend\Console\ColorInterface::GREEN);
            $oConsole->writeLine('========================', \Zend\Console\ColorInterface::WHITE);
            $oConsole->writeLine('');
        }

        // Initialize AssetsBundle service
        $oAssetsBundleService = $this->getAssetsBundleService();
        /* @var $oAssetsBundleService \AssetsBundle\Service\Service */

        // Empty cache directory except .gitignore
        $cachepath = $oAssetsBundleService->getOptions()->getCachePath();
        $cacheiterator = new \RecursiveIteratorIterator(
        							new \RecursiveDirectoryIterator($cachepath, \RecursiveDirectoryIterator::SKIP_DOTS),
        							\RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($cacheiterator as $oFileinfo) {
            if ($oFileinfo->isDir()) {
                rmdir($oFileinfo->getRealPath());
            } elseif ($oFileinfo->getBasename() !== '.gitignore') {
                unlink($oFileinfo->getRealPath());
            }
        }
        if ($bDisplayConsoleMessage) {
            $oConsole->writeLine(' * Cache directory is empty', \Zend\Console\ColorInterface::WHITE);
        }

        // Retrieve Asset File Filters Manager
        $oAssetFileFiltersManager = $oAssetsBundleService->getAssetFilesManager()->getAssetFileFiltersManager();
        // Empty asset file filters cache directory except .gitignore
        foreach ($oAssetFileFiltersManager->getRegisteredServices() as $sFilter => $thefilter) {
            $oFilter = $oAssetFileFiltersManager->get($sFilter);
            if (is_dir($sAssetFileFilterProcessedDirPath = $oFilter->getAssetFileFilterProcessedDirPath())) {
                foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sAssetFileFilterProcessedDirPath, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST
                ) as $oFileinfo) {
                    if ($oFileinfo->isDir()) {
                        rmdir($oFileinfo->getRealPath());
                    } elseif ($oFileinfo->getBasename() !== '.gitignore') {
                        unlink($oFileinfo->getRealPath());
                    }
                }
                if ($bDisplayConsoleMessage) {
                    $oConsole->writeLine(' * "' . $oFilter->getAssetFileFilterName() . '" filter cache directory is empty', \Zend\Console\ColorInterface::WHITE);
                }
            }
        }

        // Empty config directory except .gitignore
        $sConfigurationFileDirectoryPath = dirname($oAssetsBundleService->getAssetFilesManager()->getAssetFilesConfiguration()->getConfigurationFilePath());
        if(is_dir($sConfigurationFileDirectoryPath)){
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sConfigurationFileDirectoryPath, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST
            ) as $oFileinfo) {
                if ($oFileinfo->isDir()) {
                    rmdir($oFileinfo->getRealPath());
                } elseif ($oFileinfo->getBasename() !== '.gitignore') {
                    unlink($oFileinfo->getRealPath());
                }
            }
            if ($bDisplayConsoleMessage) {
                $oConsole->writeLine(' * Config cache directory is empty', \Zend\Console\ColorInterface::WHITE);
                $oConsole->writeLine('');
            }
        }

        return $this;
    }

    /**
     * @return \Zend\Console\Adapter\AdapterInterface
     * @throws \LogicException
     */
    public function getConsole()
    {
        if ($this->console instanceof \Zend\Console\Adapter\AdapterInterface) {
            return $this->console;
        }
        throw new \LogicException('Console is undefined');
    }

    /**
     * @param \Zend\Console\Adapter\AdapterInterface $oConsole
     * @return \AssetsBundle\Service\ToolsService
     */
    public function setConsole(\Zend\Console\Adapter\AdapterInterface $oConsole)
    {
        $this->console = $oConsole;
        return $this;
    }

    /**
     * @return \Zend\Mvc\MvcEvent
     * @throws \LogicException
     */
    public function getMvcEvent()
    {
        if ($this->mvcEvent instanceof \Zend\Mvc\MvcEvent) {
            return $this->mvcEvent;
        }
        throw new \LogicException('Mvc event is undefined');
    }

    /**
     * @param \Zend\Mvc\MvcEvent $oMvcEvent
     * @return \AssetsBundle\Service\ToolsService
     */
    public function setMvcEvent(\Zend\Mvc\MvcEvent $oMvcEvent)
    {
        $this->mvcEvent = $oMvcEvent;
        return $this;
    }

    /**
     * @return \AssetsBundle\Service\Service
     * @throws \LogicException
     */
    public function getAssetsBundleService()
    {
        if ($this->assetsBundleService instanceof \AssetsBundle\Service\Service) {
            return $this->assetsBundleService;
        }
        throw new \LogicException('AssetsBundle service is undefined');
    }

    /**
     * @param \AssetsBundle\Service\Service $oAssetsBundleService
     * @return \AssetsBundle\Service\ToolsService
     */
    public function setAssetsBundleService(\AssetsBundle\Service\Service $oAssetsBundleService)
    {
        $this->assetsBundleService = $oAssetsBundleService;
        return $this;
    }
}
