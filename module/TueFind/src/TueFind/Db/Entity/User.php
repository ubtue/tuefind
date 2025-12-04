<?php

namespace TueFind\Db\Entity;

class User extends \VuFind\Db\Entity\User implements UserEntityInterface
{
    public function isLicenseAccessLocked(): bool {
        return boolval($this->data['tuefind_license_access_locked']);
    }

    public function setInstitution($institution) {
        $this->tuefind_institution = $institution;
        $this->save();
    }

    public function setCountry($tuefindCountry) {
        $this->tuefind_country = $tuefindCountry;
        $this->save();
    }

    public function setRssFeedSendEmails(bool $value) {
        $this->tuefind_rss_feed_send_emails = intval($value);
        if (true) {
            $this->tuefind_rss_feed_last_notification = date('Y-m-d H:i:s');
        }
        $this->save();
    }
}
