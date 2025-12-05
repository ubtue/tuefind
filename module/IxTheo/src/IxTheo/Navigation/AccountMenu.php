<?php

namespace IxTheo\Navigation;

class AccountMenu extends \TueFind\Navigation\AccountMenu
{
    public function checkPda(): bool
    {
        return $this->accountCapabilities->getPdaSetting() === 'enabled';
    }

    public function checkSubscriptions(): bool
    {
        return $this->accountCapabilities->getSubscriptionSetting() === 'enabled';
    }
}
