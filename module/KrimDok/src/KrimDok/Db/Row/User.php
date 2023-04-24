<?php

namespace KrimDok\Db\Row;

class User extends \TueFind\Db\Row\User
{
    public function isSubscribedToNewsletter(): bool {
        return boolval($this->data['krimdok_subscribed_to_newsletter']);
    }

    public function setSubscribedToNewsletter(bool $value) {
        $this->krimdok_subscribed_to_newsletter = intval($value);
        $this->save();
    }
}
