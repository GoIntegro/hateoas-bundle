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
        ERROR_RELATIONSHIP_NOT_FOUND = "The relationship was not found.",
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
        $entities = (object) [
            'primary' => $this->findPrimaryEntities($params),
            'translations' => []
        ];

        if ($params->translations) {
            $repository = $this->em->getRepository(
                'Gedmo\\Translatable\\Entity\\Translation'
            );

            if (!empty($repository)) { // Do we have Gedmo?
                foreach ($entities->primary as $entity) {
                    $translations = $repository->findTranslations($entity);

                    if (!empty($translations)) {
                        $entity->translations
                            = static::rearrangeTranslations($translations);
                    }
                }
            }
        }

        if (!empty($params->relationship)) {
            $entity = reset($entities->primary);
            $entities->relationship
                = $this->findRelationshipEntities($params, $entity);
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
        if (empty($params->primaryClass)) {
            throw new EntityNotFoundException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $entities = $this->em
            ->getRepository($params->primaryClass)
            ->findById($params->primaryIds);

        if (!$this->canAccessEntities($params, $entities)) {
            throw new EntityAccessDeniedException(self::ERROR_ACCESS_DENIED);
        }

        if (
            empty($entities)
            || count($entities) !== count($params->primaryIds)
        ) {
            throw new EntityNotFoundException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        return $entities;
    }

    /**
     * @param Params $params
     * @param ResourceEntityInterface $entity
     * @return array
     * @throws EntityAccessDeniedException
     * @todo Find by relationship Ids when deleting.
     * @todo Refactor.
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

        return empty($params->relationshipIds)
            ? $this->filterRelationshipEntities($params, $entities)
            : $this->selectRelationshipEntities($params, $entities);
    }

    /**
     * @param Params $params
     * @param array $entities
     * @return array
     */
    private function filterRelationshipEntities(
        Params $params, array $entities
    )
    {
        $visible = [];

        foreach ($entities as $entity) {
            if ($this->securityContext->isGranted(
                self::ACCESS_VIEW, $entity
            )) {
                $visible[] = $entity;
            }
        }

        return $visible;
    }

    /**
     * @param Params $params
     * @param array $entities
     * @return array
     * @throws EntityAccessDeniedException
     */
    private function selectRelationshipEntities(
        Params $params, array $entities
    )
    {
        $selected = [];

        foreach ($entities as $entity) {
            if (in_array(
                (string) $entity->getId(), $params->relationshipIds
            )) {
                if (!$this->securityContext->isGranted(
                    self::ACCESS_VIEW, $entity
                )) {
                    throw new EntityAccessDeniedException(self::ERROR_ACCESS_DENIED);
                }

                $selected[] = $entity;
            }
        }

        if (
            empty($selected)
            || count($selected) !== count($params->primaryIds)
        ) {
            throw new EntityNotFoundException(
                self::ERROR_RELATIONSHIP_NOT_FOUND
            );
        }

        return $selected;
    }

    /**
     * @param Params $params
     * @param array $entities
     * @throws ParseException
     */
    private function canAccessEntities(Params $params, array $entities)
    {
        $access = NULL;

        if (RequestAction::TARGET_RELATIONSHIP === $params->action->target) {
            $access = self::ACCESS_EDIT;
        } elseif (!empty(self::$actionToAccess[$params->action->name])) {
            $access = self::$actionToAccess[$params->action->name];
        } else {
            throw new ParseException(self::ERROR_CANNOT_CHOOSE_ACCESS);
        }

        foreach ($entities as $entity) {
            if (!$this->securityContext->isGranted($access, $entity)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * @param array $translations
     * @return array
     */
    private static function rearrangeTranslations(array $byLocale)
    {
        $byField = [];

        foreach ($byLocale as $locale => $fields) {
            foreach ($fields as $field => $value) {
                $byField[$field][] = compact(['locale', 'value']);
            }
        }

        return $byField;
    }
}
