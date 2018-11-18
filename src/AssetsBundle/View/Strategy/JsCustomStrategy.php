<?php

namespace AssetsBundle\View\Strategy;

class JsCustomStrategy implements \Zend\EventManager\ListenerAggregateInterface {

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var \Zend\Router\RouteInterface
     */
    protected $oRouter;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @var \AssetsBundle\View\Renderer\JsCustomRenderer
     */
    protected $renderer;

    /**
     * @param \AssetsBundle\View\Renderer\JsCustomRenderer $oRenderer
     * @return \AssetsBundle\View\Strategy\JsCustomStrategy
     */
    public function setRenderer(\AssetsBundle\View\Renderer\JsCustomRenderer $oRenderer) {
        $this->renderer = $oRenderer;
        return $this;
    }

    /**
     * @throws \LogicException
     * @return \AssetsBundle\View\Renderer\JsCustomRenderer
     */
    public function getRenderer() {
        if ($this->renderer instanceof \AssetsBundle\View\Renderer\JsCustomRenderer) {
            return $this->renderer;
        }
        throw new \LogicException('Renderer is undefined');
    }

    public function setRouter(\Zend\Router\RouteInterface $oRouter){
        $this->oRouter = $oRouter;
        return $this;
    }
    public function getRouter(){
        if ($this->oRouter instanceof \Zend\Router\RouteInterface) {
            return $this->oRouter;
        }
        throw new \LogicException('Router is undefined');
    }

    /**
     * Attach the aggregate to the specified event manager
     * @param \Zend\EventManager\EventManagerInterface $oEvents
     * @param int $iPriority
     * @return void
     */
    public function attach(\Zend\EventManager\EventManagerInterface $oEvents, $iPriority = 1) {
        $this->listeners[] = $oEvents->attach(\Zend\View\ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $iPriority);
        $this->listeners[] = $oEvents->attach(\Zend\View\ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $iPriority);
    }

    /**
     * Detach aggregate listeners from the specified event manager
     * @param \Zend\EventManager\EventManagerInterface $oEvents
     * @return void
     */
    public function detach(\Zend\EventManager\EventManagerInterface $oEvents) {
        foreach ($this->listeners as $iIndex => $oListener) {
        	// detach() no longer returns a result.  So we cannot test its success or not.
            $oEvents->detach($oListener);
            unset($this->listeners[$iIndex]);
        }
    }

    /**
     * Check if JsCustomStrategy has to be used (MVC action = \AssetsBundle\Mvc\Controller\AbstractActionController::JS_CUSTOM_ACTION)
     * @param \Zend\View\ViewEvent $oEvent
     * @throws \LogicException
     * @return void|\AssetsBundle\View\Renderer\JsRenderer
     */
    public function selectRenderer(\Zend\View\ViewEvent $oEvent) {
        $oRouter = $this->getRouter();
        if (  //Retrieve request
                ($oRequest = $oEvent->getRequest()) instanceof \Zend\Http\Request
                //Retrieve route match
                && ($oRouteMatch = $oRouter->match($oRequest)) instanceof \Zend\Router\RouteMatch && $oRouteMatch->getParam('action') === \AssetsBundle\Mvc\Controller\AbstractActionController::JS_CUSTOM_ACTION
        ) {
            if (!($oViewModel = $oEvent->getModel()) instanceof \Zend\View\Model\ViewModel) {
                throw new \UnexpectedValueException(sprintf(
                        'Event model expects an instance of "Zend\View\Model\ViewModel", "%s" given', is_object($oViewModel) ? get_class($oViewModel) : gettype($oViewModel)
                ));
            } elseif (($oException = $oViewModel->getVariable('exception')) instanceof \Exception) {
                return;
            }

            //jsCustomFiles is empty
            if (!is_array($aJsCustomFiles = $oEvent->getModel()->getVariable('jsCustomFiles'))) {
                $oEvent->getModel()->setVariable('jsCustomFiles', array());
            }

            return $this->getRenderer();
        }
    }

    /**
     * @param \Zend\View\ViewEvent $oEvent
     * @throws \UnexpectedValueException
     */
    public function injectResponse(\Zend\View\ViewEvent $oEvent) {
        if ($oEvent->getRenderer() !== $this->getRenderer()) {
            return;
        }
        if (!is_string($sResult = $oEvent->getResult())) {
            throw new \UnexpectedValueException('Result expects string, "' . gettype($sResult) . '" given');
        }
        //jsCustomFiles is empty
        if (!is_array($aJsCustomFiles = $oEvent->getModel()->getVariable('jsCustomFiles'))) {
            throw new \UnexpectedValueException('"jsCustomFiles" view\'s variable expects an array, "' . gettype($aJsCustomFiles) . '" given');
        }

        $sResponseContent = '';
        foreach ($aJsCustomFiles as $oAssetFile) {
            if ($oAssetFile instanceof \AssetsBundle\AssetFile\AssetFile) {
                $sResponseContent .= $oAssetFile->getAssetFileContents() . PHP_EOL;
            } else {
                throw new \UnexpectedValueException('"jsCustomFiles" view\'s variable must contains instance of \AssetsBundle\AssetFile\AssetFile, "' . (is_object($oAssetFile) ? get_class($oAssetFile) : gettype($oAssetFile)) . '" given');
            }
        }
        //Inject javascript in the response
        $oEvent->getResponse()->setContent($sResponseContent)->getHeaders()->addHeaderLine('content-type', 'text/javascript');
    }



    /**
     * getListeners() - Return the current array of listeners that have been
     * added to the Event Manager.
     *
     * This is added for testing purposes as of ZF3 we can no longer extract
     * the set of attached listeners from the Event Manager.
     *
     * @return \Zend\Stdlib\CallbackHandler[]
     */
    public function getListeners()
    {
    	return $this->listeners;
    }
}
