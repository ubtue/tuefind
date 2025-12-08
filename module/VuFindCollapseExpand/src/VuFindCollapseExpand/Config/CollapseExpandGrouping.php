<?php

/*
 * Copyright 2025 (C) Universitaet Tuebingen, Germany
 *
 */

namespace VuFindCollapseExpand\Config;

use Laminas\Http\Header\SetCookie;
use Laminas\Session\Container as SessionContainer;

/**
 * Class for storing Collapse and Expand options
 * Provides methods to store and retrieve user settings
 * and to check whether Collapse and Expand is active by user during the session
 * Controlling Result is changed from Result Grouping to Collapse and Expand
 * @author Steven Lolong <steven.lolong@uni-tuebingen.de>
 *
 */
class CollapseExpandGrouping
{
    protected $config;
    /**
     * @var SessionContainer
     */
    protected $container;
    protected $response;
    protected $cookie;

    public function __construct(
        $config,
        SessionContainer $container,
        $response,
        $cookiedata
    ) {
        $this->config = $config;
        $this->container = $container;
        $this->response = $response;
        $this->cookie = $cookiedata;
        $this->restoreFromCookie();
    }

    /**  
     * Restore settings from cookie to session container
     */
    public function restoreFromCookie()
    {
        $collapseConfig = $this->getCollapseConfig();
        $expandConfig = $this->getExpandConfig();
        $configs = $collapseConfig + $expandConfig;
        foreach (array_keys($configs) as $key) {
            if (isset($this->cookie->{$key})) {
                $this->container->offsetSet($key, $this->cookie->{$key});
            }
        }
    }


    /**
     * @param $post
     *
     * @return array
     */
    public function store($post)
    {
        $params = $this->getCurrentSettings();

        foreach(array_keys($post) as $key){
            if(array_key_exists($key, $params)){
                $cookie = new SetCookie(
                    $key,
                    $post[$key],
                    time() + 14 * 24 * 60 * 60,
                    '/'
                );
                $header = $this->response->getHeaders();
                $header->addHeader($cookie);
                $this->container->offsetSet($key, $post[$key]);
                $params[$key] = $post[$key];   
            }
        }
        return $params;
    }

    /** 
     * Get current settings for both collapse and expand
     * @return array
     */
    public function getCurrentSettings(): array
    {
        $params = [];
        $collapseConfig = $this->getCurrentSettingsCollapse();
        $expandConfig = $this->getCurrentSettingsExpand();
        
        if($this->container->offsetExists('collapse.enabled')){
            $params['collapse.enabled'] = $this->container->offsetGet('collapse.enabled');
        } else {
            $params['collapse.enabled'] = $collapseConfig['collapse.field'] !== null ? true : false;
        }

        $params += ($collapseConfig + $expandConfig);
        return $params;
    }

    /** 
     * Get current settings for collapse
     * @return array
     */
    public function getCurrentSettingsCollapse(): array {
        $params = [];
        $collapseConfig = $this->getCollapseConfig();
        foreach( $collapseConfig as $key => $value) {
            if($this->container->offsetExists($key)){
                $params[$key] = $this->container->offsetGet($key);
            } else {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    /**
     * Get current settings for expand
     * @return array
     */
    public function getCurrentSettingsExpand(): array {
        $params = [];
        $expandConfig = $this->getExpandConfig();
        foreach( $expandConfig as $key => $value) {
            if($this->container->offsetExists($key)){
                $params[$key] = $this->container->offsetGet($key);
            } else {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    /** 
     * Check if Collapse and Expand is activated by the user
     * @return bool
     */
    public function isActive(): bool
    {
        $conf = $this->getCurrentSettings();
        return $conf['collapse.enabled'];
    }

    /** 
     * Check if Collapse and Expand is available in the configuration
     * @return bool
     */
    public function isEnabled(): bool{
        return $this->config->get('collapse.field') !== null ? true : false;
    }

    /** 
     * Get Collapse configuration from config file
     * @return array
     */
    public function getCollapseConfig(){
        $collapseConfig = [
            'collapse.field' => $this->config->get('collapse.field') !== null ? $this->config->get('collapse.field') : null,
            'collapse.min' => $this->config->get('collapse.min') !== null ? $this->config->get('collapse.min') : null,
            'collapse.max' => $this->config->get('collapse.max') !== null ? $this->config->get('collapse.max') : null,
            'collapse.sort' => $this->config->get('collapse.sort') !== null ? $this->config->get('collapse.sort') : null,
            'collapse.nullPolicy' => $this->config->get('collapse.nullPolicy') !== null ? $this->config->get('collapse.nullPolicy') : null,
            'collapse.hint' => $this->config->get('collapse.hint') !== null ? $this->config->get('collapse.hint') : null,
            'collapse.size' => $this->config->get('collapse.size') !== null ? $this->config->get('collapse.size') : null,
            'collapse.collectElevatedDocsWhenCollapsing' => $this->config->get('collapse.collectElevatedDocsWhenCollapsing') !== null ? $this->config->get('collapse.collectElevatedDocsWhenCollapsing') : null,
        ];
        return $collapseConfig;
    }

    public function getExpandConfig(){
        $expandConfig = [
            'expand.field' => $this->config->get('expand.field') !== null ? $this->config->get('expand.field') : null,
            'expand.rows' => $this->config->get('expand.rows') !== null ? $this->config->get('expand.rows') : 10, // set default 10 rows
            'expand.q' => $this->config->get('expand.q') !== null ? $this->config->get('expand.q') : null,
            'expand.fq' => $this->config->get('expand.fq') !== null ? $this->config->get('expand.fq') : null,
            'expand.nullGroup' => $this->config->get('expand.nullGroup') !== null ? $this->config->get('expand.nullGroup') : null,
        ];
        return $expandConfig;
    }

}