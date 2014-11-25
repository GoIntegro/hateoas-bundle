<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Security;

// Security.
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
// Queries.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\FilterInterface;

/**
 * Provides the same access control on paginated queries and "isGranted" calls.
 * @see http://symfony.com/doc/current/cookbook/security/voters_data_permission.html
 */
interface VoterFilterInterface extends VoterInterface, FilterInterface
{
}
