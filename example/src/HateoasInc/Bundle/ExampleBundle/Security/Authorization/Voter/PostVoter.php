<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace HateoasInc\Bundle\ExampleBundle\Security\Authorization\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PostVoter implements VoterInterface
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const SUPPORTED_CLASS = 'HateoasInc\\Bundle\\ExampleBundle\\Entity\\Post';

    public function supportsAttribute($attribute)
    {
        return in_array($attribute, [self::VIEW, self::EDIT]);
    }

    public function supportsClass($class)
    {
        return self::SUPPORTED_CLASS === $class
            || is_subclass_of($class, self::SUPPORTED_CLASS);
    }

    /**
     * @param TokenInterface $post
     * @param mixed $post
     * @param array $attributes
     * @return integer
     */
    public function vote(TokenInterface $token, $post, array $attributes)
    {
        if (!$this->supportsClass(get_class($post))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (1 !== count($attributes)) {
            throw new \InvalidArgumentException(
                'Only one attribute is allowed for VIEW or EDIT'
            );
        }

        $attribute = $attributes[0];

        if (!$this->supportsAttribute($attribute)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return VoterInterface::ACCESS_DENIED;
        }

        switch($attribute) {
            case self::VIEW:
                return VoterInterface::ACCESS_GRANTED;
                break;

            case self::EDIT:
                if ($user->getId() === $post->getOwner()->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
