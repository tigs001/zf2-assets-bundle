<?php

namespace AssetsBundle\AssetFile\AssetFileFilter;

abstract class AbstractAssetFileFilter extends \Zend\Stdlib\AbstractOptions implements \ AssetsBundle\AssetFile\AssetFileFilter\AssetFileFilterInterface
{

    /**
     * @var string
     */
    protected $assetFileFilterName;

    /**
     * @var \AssetsBundle\Service\ServiceOptions
     */
    protected $options;

    /**
     * @var string
     */
    protected $assetFileFilterProcessedDirPath;

    /**
     * @param string $sAssetFileFilterName
     * @return \AssetsBundle\Service\Filter\AbstractFilter
     * @throws \InvalidArgumentException
     */
    public function setAssetFileFilterName($sAssetFileFilterName)
    {
        if (empty($sAssetFileFilterName)) {
            throw new \InvalidArgumentException('Filter name is empty');
        }

        if (!is_string($sAssetFileFilterName)) {
            throw new \InvalidArgumentException('Filter name expects string, "' . gettype($sAssetFileFilterName) . '" given');
        }

        $this->assetFileFilterName = $sAssetFileFilterName;

        return $this;
    }

    /**
     * @return string
     * @throws \LogicException
     */
    public function getAssetFileFilterName()
    {
        if (is_string($this->assetFileFilterName) && !empty($this->assetFileFilterName)) {
            return $this->assetFileFilterName;
        }
        throw new \LogicException('Filter name is undefined');
    }

    /**
     * @param \AssetsBundle\Service\ServiceOptions $oOptions
     * @return \AssetsBundle\Service\Service
     */
    public function setOptions(\AssetsBundle\Service\ServiceOptions $oOptions)
    {
        $this->options = $oOptions;
        return $this;
    }

    /**
     * @return \AssetsBundle\Service\ServiceOptions
     */
    public function getOptions()
    {
        if (!($this->options instanceof \AssetsBundle\Service\ServiceOptions)) {
        	throw new \LogicException('As of ZF3, you must inject the AssetsBundleServiceOptions from the Service factory.');
        }
        return $this->options;
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return string
     */
    public function getCachedFilteredContentFilePath(\AssetsBundle\AssetFile\AssetFile $oAssetFile)
    {
        return $this->getAssetFileFilterProcessedDirPath() . DIRECTORY_SEPARATOR . md5($sAssetFilePath = $oAssetFile->getAssetFilePath());
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @return boolean|string
     */
    public function getCachedFilteredContent(\AssetsBundle\AssetFile\AssetFile $oAssetFile)
    {
        if (file_exists($sCachedFilteredContentFilePath = $this->getCachedFilteredContentFilePath($oAssetFile))) {
            $oFilteredAssetFile = new \AssetsBundle\AssetFile\AssetFile(array(
                'assetFilePath' => $sCachedFilteredContentFilePath,
                'assetFileType' => $oAssetFile->getAssetFileType()
            ));
            if (
            //Retrieve cached filtered asset file last modified timestamp
                    ($iFilteredAssetFileLastModified = $oFilteredAssetFile->getAssetFileLastModified())
                    //Retrieve asset file last modified timestamp
                    && ($iAssetFileLastModified = $oAssetFile->getAssetFileLastModified())
                    //If  cached filtered asset file is updated
                    && $iFilteredAssetFileLastModified >= $iAssetFileLastModified
            ) {
                return $oFilteredAssetFile->getAssetFileContents();
            }
        }
        return false;
    }

    /**
     * @param \AssetsBundle\AssetFile\AssetFile $oAssetFile
     * @param string $sFilteredContent
     * @return \AssetsBundle\AssetFile\AssetFileFilter\AbstractAssetFileFilter
     * @throws \InvalidArgumentException
     */
    public function cacheFilteredAssetFileContent(\AssetsBundle\AssetFile\AssetFile $oAssetFile, $sFilteredContent)
    {
        if (is_string($sFilteredContent)) {
            $sCachedFilteredContentFilePath = $this->getCachedFilteredContentFilePath($oAssetFile);
            $bFileExists = file_exists($sCachedFilteredContentFilePath);

            \Zend\Stdlib\ErrorHandler::start();
            file_put_contents($sCachedFilteredContentFilePath, $sFilteredContent);
            \Zend\Stdlib\ErrorHandler::stop(true);

            if (!$bFileExists) {
                \Zend\Stdlib\ErrorHandler::start();
                chmod($sCachedFilteredContentFilePath, $this->getOptions()->getFilesPermissions());
                \Zend\Stdlib\ErrorHandler::stop(true);
            }
            return $this;
        }
        throw new \InvalidArgumentException('Filtered content expects string, "' . gettype($sFilteredContent) . '" given');
    }

    /**
     * @return string
     */
    public function getAssetFileFilterProcessedDirPath()
    {
        if (!is_dir($this->assetFileFilterProcessedDirPath)) {
            $this->assetFileFilterProcessedDirPath = $this->getOptions()->getProcessedDirPath() . DIRECTORY_SEPARATOR . strtolower(str_replace(
                                    array('/', '<', '>', '?', '*', '"', '|'), '_', $this->getAssetFileFilterName()
            ));
            if (!is_dir($this->assetFileFilterProcessedDirPath)) {
                \Zend\Stdlib\ErrorHandler::start();
                mkdir($this->assetFileFilterProcessedDirPath, $this->getOptions()->getDirectoriesPermissions());
                \Zend\Stdlib\ErrorHandler::stop(true);
            }
        }
        return $this->assetFileFilterProcessedDirPath;
    }
}
