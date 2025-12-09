<?php

namespace TueFind\Db\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tuefind_redirect')]
class Redirect implements RedirectEntityInterface
{

}
