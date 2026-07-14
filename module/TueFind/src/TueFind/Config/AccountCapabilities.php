<?php

namespace TueFind\Config;

use VuFind\Config\Config;

class AccountCapabilities extends \VuFind\Config\AccountCapabilities
{
    protected $tuefindConfig;

    public function __construct(Config $config, callable $auth, Config $tuefindConfig)
    {
        parent::__construct($config, $auth);
        $this->tuefindConfig = $tuefindConfig;
    }

    /**
     * Are users allowed to use PDA?
     */
    public function getPdaSetting(): string
    {
        return isset($this->tuefindConfig->General->pda)
            && $this->tuefindConfig->General->pda === 'enabled'
            ? 'enabled' : 'disabled';
    }

    /**
     * Are users allowed to use SelfArchiving?
     */
    public function getSelfarchivingSetting(): string
    {
        return isset($this->tuefindConfig->General->SelfArchiving)
            && $this->tuefindConfig->General->SelfArchiving === 'enabled'
            ? 'enabled' : 'disabled';
    }

    /**
     * Are users allowed to upload publications for their registered authorities?
     */
    public function getPublicationSetting(): string
    {
        return isset($this->tuefindConfig->Publication->publications)
            && $this->tuefindConfig->Publication->publications === 'enabled'
            ? 'enabled' : 'disabled';
    }

    /**
     * Are users allowed to request rights on authority datasets?
     */
    public function getRequestAuthorityRightsSetting(): string
    {
        return isset($this->tuefindConfig->General->request_authority_rights)
            && $this->tuefindConfig->General->request_authority_rights === 'enabled'
            ? 'enabled' : 'disabled';
    }

    /**
     * Are users allowed to subscribe rss feeds?
     */
    public function getRssSubscriptionSetting(): string
    {
        return isset($this->tuefindConfig->General->rss_subscriptions)
            && $this->tuefindConfig->General->rss_subscriptions === 'enabled'
            ? 'enabled' : 'disabled';
    }

    /**
     * Are users allowed to subscribe journals?
     */
    public function getSubscriptionSetting(): string
    {
        return isset($this->tuefindConfig->General->subscriptions)
            && $this->tuefindConfig->General->subscriptions === 'enabled'
            ? 'enabled' : 'disabled';
    }

    /**
     * Is CMS enabled?
     */
    public function getCmsSetting(): string
    {
        // Note: While other settings use "enabled" as string, this setting in the config
        //       just uses true/false which is normalized to '1' by the config parser
        //       (maybe change / normalize when time permits)
        return isset($this->tuefindConfig->CMS->enabled)
            && ($this->tuefindConfig->CMS->enabled === 'enabled' || $this->tuefindConfig->CMS->enabled === '1')
            ? 'enabled' : 'disabled';
    }
}
