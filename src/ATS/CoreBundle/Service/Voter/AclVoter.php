<?php declare (strict_types = 1);

namespace ATS\CoreBundle\Service\Voter;

use ATS\CoreBundle\Service\AclExpressionLanguage\AclExpressionLanguage;
use ATS\UserBundle\Document\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AclVoter extends Voter
{
    const VIEW = 'view';
    const VIEW_ALL = 'view_all';
    const EDIT = 'edit';
    const CREATE = 'create';
    const DELETE = 'delete';
    const EXPORT = 'export';
    const SEARCH = 'search';

    /**
     * @var array
     */
    private $rules;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * @var AclExpressionLanguage
     */
    private $expression;

    /**
     * @param AccessDecisionManagerInterface $decisionManager
     * @param ContainerInterface $container
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager, ContainerInterface $container)
    {
        $this->decisionManager = $decisionManager;
        $this->rules = $container->getParameter('acl_rules');
        $this->expression = new AclExpressionLanguage();
    }

    /**
     * @param $attribute
     * @param $subject
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, $this->getSupportedAttributes())) {
            return false;
        }

        return true;
    }

    /**
     * @param $attribute
     * @param $subject
     * @param TokenInterface $token
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($this->decisionManager->decide($token, array('ROLE_SUPER_ADMIN'))) {
            return true;
        }

        // @TODO make this MUCH better
        if (is_string($subject)) {
            if (!array_key_exists($subject, $this->rules)) {
                return true;
            }

            if (!array_key_exists($attribute, $this->rules[$subject])) {
                return true;
            }

            $entityRules = $this->rules[$subject][$attribute];

            foreach ($entityRules as $rule) {
                $result = $this->expression->evaluate(
                    $rule,
                    [
                        AclExpressionLanguage::LANG_TOKEN_CURRENT_USER => $user,
                    ]
                );

                if ($result) {
                    return true;
                }
            }
        } else {
            $documentClass = get_class($subject);

            // @TODO make this better
            if (!array_key_exists($documentClass, $this->rules)) {
                return true;
            }

            if (!array_key_exists($attribute, $this->rules[$documentClass])) {
                return true;
            }

            $entityRules = $this->rules[$documentClass][$attribute];

            foreach ($entityRules as $rule) {
                $result = $this->expression->evaluate(
                    $rule,
                    [
                        AclExpressionLanguage::LANG_TOKEN_CURRENT_USER => $user,
                        AclExpressionLanguage::LANG_TOKEN_OBJECT => $subject,
                    ]
                );

                if ($result) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getSupportedAttributes()
    {
        return [
            self::VIEW,
            self::EDIT,
            self::CREATE,
            self::DELETE,
            self::VIEW_ALL,
            self::EXPORT,
            self::SEARCH,
        ];
    }
}
