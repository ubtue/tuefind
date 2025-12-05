<?php

namespace TueFind\Navigation;

class AccountMenu extends \VuFind\Navigation\AccountMenu
{
    public function checkRssSubscriptions(): bool
    {
        return $this->accountCapabilities->getRssSubscriptionSetting() === 'enabled';
    }

    public function checkSelfarchiving(): bool
    {
        return $this->accountCapabilities->getSelfarchivingSetting() === 'enabled';
    }

    public function checkPublications(): bool
    {
        return $this->accountCapabilities->getPublicationSetting() === 'enabled';
    }

    public function checkAdmins(): bool
    {
        $user = $this->getUser();
        if ($user && in_array('admin', $user->getRights())) {
            return true;
        }
        return false;
    }

    public function checkUserAuthorities(): bool
    {
        return $this->checkAdmins() && $this->accountCapabilities->getRequestAuthorityRightsSetting() === 'enabled';
    }

    public function checkUserPublications(): bool
    {
        return $this->checkAdmins() && $this->accountCapabilities->getPublicationSetting() === 'enabled';
    }
}
