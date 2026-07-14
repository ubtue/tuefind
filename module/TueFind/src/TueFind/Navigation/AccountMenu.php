<?php

namespace TueFind\Navigation;

use function in_array;

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
        if ($user && in_array('admin', $user->getTueFindRights())) {
            return true;
        }
        return false;
    }

    public function checkUserAuthorities(): bool
    {
        $user = $this->getUser();
        if ($this->accountCapabilities->getRequestAuthorityRightsSetting() === 'enabled') {
            return $user && array_intersect(['admin','user_authorities'], $user->getTueFindRights());
        }
        return false;
    }

    public function checkUserPublications(): bool
    {
        $user = $this->getUser();
        if ($this->accountCapabilities->getPublicationSetting() === 'enabled') {
            return $user && array_intersect(['admin','user_authorities'], $user->getTueFindRights());
        }
        return false;
    }

    public function checkPda(): bool
    {
        return $this->accountCapabilities->getPdaSetting() === 'enabled';
    }

    public function checkSubscriptions(): bool
    {
        return $this->accountCapabilities->getSubscriptionSetting() === 'enabled';
    }

    protected function showCMS(): bool
    {
        if ($this->accountCapabilities->getCmsSetting() === 'enabled') {
            $user = $this->getUser();
            if ($user && array_intersect(['admin','cms'], $user->getTueFindRights())) {
                return true;
            }
        }
        return false;
    }

    public function checkCMS(): bool
    {
        return $this->showCMS();
    }

    public function checkAllCMSHistory(): bool
    {
        return $this->showCMS();
    }

    public function checkCMSDocs(): bool
    {
        return $this->showCMS();
    }
}
