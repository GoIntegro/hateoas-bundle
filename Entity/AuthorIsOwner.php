<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

// Security.
use Symfony\Component\Security\Core\User\UserInterface;

interface AuthorIsOwner
{
    /**
     * @param User
     * @return self
     */
    public function setOwner(UserInterface $owner);

    /**
     * @return User
     */
    public function getOwner();
}
