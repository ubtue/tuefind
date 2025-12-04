<?php

namespace KrimDok\Db\Entity;

class User extends \TueFind\Db\Entity\User implements UserEntityInterface
{
    public function isSubscribedToNewsletter(): bool {
        return boolval($this->data['krimdok_subscribed_to_newsletter']);
    }

    public function setSubscribedToNewsletter(bool $value) {
        $this->krimdok_subscribed_to_newsletter = intval($value);
        $this->save();
    }
}
