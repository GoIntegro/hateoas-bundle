<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;

class ParamEntityFinder
{
    const ACCESS_VIEW = 'view',
        ACCESS_EDIT = 'edit',
        ACCESS_DELETE = 'delete';

    const ERROR_ACCESS_DENIED = "Access to the resource was denied.",
        ERROR_RESOURCE_NOT_FOUND = "The resource was not found.",
        ERROR_CANNOT_CHOOSE_ACCESS = "Cannot choose right access to check";

    /**
     * @var array
     */
    private static $actionToAccess = [
        RequestAction::ACTION_FETCH => self::ACCESS_VIEW,
        RequestAction::ACTION_UPDATE => self::ACCESS_EDIT,
        RequestAction::ACTION_DELETE => self::ACCESS_DELETE
    ];
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param EntityManagerInterface $em
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        EntityManagerInterface $em,
        SecurityContextInterface $securityContext
    )
    {
        $this->em = $em;
        $this->securityContext = $securityContext;
    }

    /**
     * @param Params $params
     * @return array
     */
    public function find(Params $params)
    {
        if (empty($params->primaryClass)) {
            throw new EntityNotFoundException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $entities = $this->em
            ->getRepository($params->primaryClass)
            ->findById($params->primaryIds);

        foreach ($entities as $entity) {
            if (empty(self::$actionToAccess[$params->action->name])) {
                throw new ParseException(self::ERROR_CANNOT_CHOOSE_ACCESS);
            }

            $access = self::$actionToAccess[$params->action->name];

            if (!$this->securityContext->isGranted($access, $entity)) {
                throw new EntityAccessDeniedException(
                    self::ERROR_ACCESS_DENIED
                );
            }
        }

        if (
            empty($entities)
            || count($entities) !== count($params->primaryIds)
        ) {
            throw new EntityNotFoundException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        return $entities;
    }
}
