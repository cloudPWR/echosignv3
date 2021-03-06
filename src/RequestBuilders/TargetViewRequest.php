<?php
namespace Echosign\RequestBuilders;

use Echosign\Interfaces\RequestBuilder;

/**
 * Class TargetViewRequest
 * @package Echosign\RequestBuilders
 */
class TargetViewRequest implements RequestBuilder
{
    /**
     * @var bool
     */
    protected $noChrome;

    /**
     * @var string
     */
    protected $targetView;

    /**
     * @var bool
     */
    protected $autoLogin;

    /**
     * @param $targetView
     * @param bool $noChrome
     * @param bool $autoLogin
     */
    public function __construct( $targetView, $noChrome = false, $autoLogin = false )
    {
        $this->targetView = $targetView;
        $this->noChrome   = (bool) $noChrome;
        $this->autoLogin  = (bool) $autoLogin;
    }

    /**
     * @return mixed
     */
    public function getAutoLogin()
    {
        return $this->autoLogin;
    }

    /**
     * @param $autoLogin
     * @return $this
     */
    public function setAutoLogin( $autoLogin )
    {
        $this->autoLogin = $autoLogin;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNoChrome()
    {
        return $this->noChrome;
    }

    /**
     * @param $noChrome
     * @return $this
     */
    public function setNoChrome( $noChrome )
    {
        $this->noChrome = $noChrome;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTargetView()
    {
        return $this->targetView;
    }

    /**
     * @param $targetView
     * @return $this
     */
    public function setTargetView( $targetView )
    {
        $this->targetView = $targetView;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'noChrome'   => $this->noChrome,
            'targetView' => $this->targetView,
            'autoLogin'  => $this->autoLogin,
        ];
    }
}