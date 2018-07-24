<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Service\AclExpressionLanguage;

use ATS\UserBundle\Document\User;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class AclExpressionLanguage extends ExpressionLanguage
{

    const LANG_TOKEN_OBJECT = 'object';
    const LANG_TOKEN_CURRENT_USER = 'currentUser';
    const LANG_FUNC_HAS_ROLE = 'hasRole';

    public function __construct($cache = null, array $providers = array())
    {
        parent::__construct($cache, $providers);

        $this->register(
            self::LANG_FUNC_HAS_ROLE,
            function (User $user, $role) {
                return sprintf('(%1$s.%2$s(%3$s)', $user, self::LANG_FUNC_HAS_ROLE, $role);
            },
            function (User $user, $role) {
                if (in_array($role, $user->getRoles())) {
                    return true;
                }

                return false;
            }
        );
    }
}
