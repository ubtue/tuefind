<?php

namespace KrimDok\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class User extends \TueFind\Db\Entity\User implements UserEntityInterface
{
    #[ORM\Column(name: 'krimdok_subscribed_to_newsletter', type: 'boolean', nullable: false, options: ['default' => false])]
    protected $krimdokSubscribedToNewsletter = false;

    public function getSubscribedToNewsletter(): bool
    {
        return $this->krimdokSubscribedToNewsletter;
    }

    public function setSubscribedToNewsletter(bool $subscribed): static
    {
        $this->krimdokSubscribedToNewsletter = $subscribed;
        return $this;
    }
}
