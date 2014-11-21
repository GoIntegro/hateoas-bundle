<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// Utils.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;
// Collections.
use Doctrine\Common\Collections\Collection;

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
     * @throws ParseException
     * @throws EntityNotFoundException
     * @throws EntityAccessDeniedException
     */
    public function find(Params $params)
    {
        if (empty($params->primaryClass)) {
            throw new EntityNotFoundException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $entities = $this->findPrimaryEntities($params);

        if (
            empty($entities)
            || count($entities) !== count($params->primaryIds)
        ) {
            throw new EntityNotFoundException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        if (!empty($params->relationship)) {
            $entity = reset($entities);
            $entities = $this->findRelationshipEntities($params, $entity);
        }

        return $entities;
    }

    /**
     * @param Params $params
     * @return array
     * @throws EntityAccessDeniedException
     */
    protected function findPrimaryEntities(Params $params)
    {
        $entities = $this->em
            ->getRepository($params->primaryClass)
            ->findById($params->primaryIds);

        if (!$this->canAccessEntities($params, $entities)) {
            throw new EntityAccessDeniedException(self::ERROR_ACCESS_DENIED);
        }

        return $entities;
    }

    /**
     * @param Params $params
     * @param ResourceEntityInterface $entity
     * @return array
     * @throws EntityAccessDeniedException
     */
    protected function findRelationshipEntities(
        Params $params,
        ResourceEntityInterface $entity
    )
    {
        $method = 'get' . Inflector::camelize($params->relationship);
        $entities = $entity->$method();

        if ($entities instanceof Collection) {
            $entities = $entities->toArray();
        } elseif (!is_array($entities)) {
            $entities = [$entities];
        }

        if (!$this->canAccessEntities($params, $entities)) {
            throw new EntityAccessDeniedException(self::ERROR_ACCESS_DENIED);
        }

        return $entities;
    }

    /**
     * @param Params $params
     * @param array $entities
     * @throws ParseException
     */
    private function canAccessEntities(Params $params, array $entities)
    {
        if (empty(self::$actionToAccess[$params->action->name])) {
            throw new ParseException(self::ERROR_CANNOT_CHOOSE_ACCESS);
        }

        $access = self::$actionToAccess[$params->action->name];

        foreach ($entities as $entity) {
            if (!$this->securityContext->isGranted($access, $entity)) {
                return FALSE;
            }
        }

        return TRUE;
    }
}
