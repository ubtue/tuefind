<?php

namespace TueFind\Role\Assertion;

use Lmc\Rbac\Assertion\AssertionInterface;
use Lmc\Rbac\Identity\IdentityInterface;
use TueFind\Db\Entity\UserEntityInterface;

use function in_array;

class HasUserAuthoritiesRightsAssertion implements AssertionInterface
{
    public function assert(
        string $permission,
        ?IdentityInterface $identity = null,
        mixed $context = null
    ): bool {
        if ($identity instanceof UserEntityInterface) {
            return in_array('user_authorities', $identity->getTueFindRights());
        }
        return false;
    }
}
