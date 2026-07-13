<?php

namespace KrimDok\Db\Entity;

interface UserEntityInterface extends \TueFind\Db\Entity\UserEntityInterface
{
    public function getSubscribedToNewsletter(): bool;
    public function setSubscribedToNewsletter(bool $subscribed): static;
}
