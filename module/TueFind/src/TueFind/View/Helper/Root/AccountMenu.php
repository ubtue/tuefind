<?php

namespace TueFind\View\Helper\Root;

class AccountMenu extends \VuFind\View\Helper\Root\AccountMenu {
    public function checkSubscriptions(): bool {
        return $this->getView()->plugin('accountCapabilities')()->getSubscriptionSetting() === 'enabled';
    }

    public function checkRssSubscriptions(): bool {
        return $this->getView()->plugin('accountCapabilities')()->getRssSubscriptionSetting() === 'enabled';
    }

    public function checkPda(): bool {
        return $this->getView()->plugin('accountCapabilities')()->getPdaSetting() === 'enabled';
    }

    public function checkPublications(): bool {
        return $this->getView()->plugin('accountCapabilities')()->getPublicationSetting() === 'enabled';
    }

    public function checkAdmins(): bool {
        $user = $this->getAuthHelper()->getUserObject();
        return isset($user->tuefind_rights);
    }

    public function checkUserAuthorities(): bool {
        return $this->checkAdmins() && $this->getView()->plugin('accountCapabilities')()->getRequestAuthorityRightsSetting() === 'enabled';
    }

    public function checkUserPublications(): bool {
        return $this->checkAdmins() && $this->getView()->plugin('accountCapabilities')()->getPublicationSetting() === 'enabled';
    }
}
