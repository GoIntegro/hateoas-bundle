<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// Recursos REST.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface,
    GoIntegro\Bundle\HateoasBundle\JsonApi\Document,
    GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource,
    GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceCollectionInterface,
    GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceCollection,
    GoIntegro\Bundle\HateoasBundle\JsonApi\DocumentResource;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceRelationship;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;

class LinkedResourcesSerializer implements SerializerInterface
{
    const RECURSION_DEPTH_LIMIT = 3,
        ERROR_RECURSION_DEPTH = "El nivel de recursión es demasiado profundo",
        ERROR_INHERITANCE_MAPPING = "La herencia del mapeo del ORM para la relación \"%s\" no está siendo bien manejada.",
        ERROR_LINK_ONLY_RELATIONSHIP = "La relación \"%s\" no puede ser incluída, posiblemente por su tamaño. Debe obtener este recurso haciendo un pedido a %s.",
        ERROR_UNKOWN_RELATIONSHIP = "La relación \"%s\" no existe.";

    private $document;
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param Document $document
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        Document $document,
        SecurityContextInterface $securityContext
    )
    {
        $this->document = $document;
        $this->securityContext = $securityContext;
    }

    public function serialize()
    {
        $resourcesSerialization = new LinkedResourcesSerialization;
        $relationOfRelation = [];
        $this->processLinkedResources(
            $this->document->resources,
            $this->document->include,
            $resourcesSerialization
        );

        return $resourcesSerialization->getLinkedResources();
    }

    /**
     * @param ResourceCollectionInterface $resources
     * @param array $include
     * @param LinkedResourcesSerialization $resourcesSerialization
     * @param array &$relationOfRelation
     * @todo Este método merece un refactor.
     */
    private function processLinkedResources(
        ResourceCollectionInterface $resources,
        array $include,
        LinkedResourcesSerialization $resourcesSerialization,
        $depth = 0
    )
    {
        if (self::RECURSION_DEPTH_LIMIT <= $depth) {
            throw new \Exception(self::ERROR_RECURSION_DEPTH);
        }

        foreach ($include as $relationships) {
            $metadata = $resources->getMetadata();
            $relationshipName = $relationships[$depth];
            $linkedResources = [];

            if ($metadata->isToOneRelationship($relationshipName)) {
                $this->processToOneRelationship(
                    $resources,
                    $relationshipName,
                    $linkedResources,
                    $resourcesSerialization
                );
            } elseif ($metadata->isToManyRelationship($relationshipName)) {
                $this->processToManyRelationship(
                    $resources,
                    $relationshipName,
                    $linkedResources,
                    $resourcesSerialization
                );
            } elseif ($metadata->isLinkOnlyRelationship($relationshipName)) {
                $urlTemplate = $resources->getMetadata()
                    ->relationships
                    ->linkOnly[$relationshipName]
                    ->byPrimaryUrl;
                $message = sprintf(
                    self::ERROR_LINK_ONLY_RELATIONSHIP,
                    $relationshipName,
                    $urlTemplate
                );
                throw new \Exception($message);
            } else {
                $message = sprintf(
                    self::ERROR_UNKOWN_RELATIONSHIP, $relationshipName
                );
                throw new \Exception($message);
            }

            if (
                isset($relationships[$depth + 1]) && !empty($linkedResources)
            ) {
                $this->processLinkedResources(
                    ResourceCollection::buildFromArray($linkedResources),
                    [$relationships],
                    $resourcesSerialization,
                    $depth + 1
                );
            }
        }
    }

    /**
     * @param ResourceCollectionInterface $resources
     * @param $relationshipName
     * @param LinkedResourcesSerialization $resourcesSerialization
     * @param array &$linkedResources
     */
    private function processToOneRelationship(
        ResourceCollectionInterface $resources,
        $relationshipName,
        array &$linkedResources,
        LinkedResourcesSerialization $resourcesSerialization
    )
    {
        foreach ($resources as $resource) {
            if ($resource->isRelationshipBlacklisted(
                $relationshipName
            )) {
                $message = sprintf(
                    self::ERROR_UNKOWN_RELATIONSHIP, $relationshipName
                );
                throw new \Exception($message);
            }

            $relationship = $resource->getMetadata()
                ->relationships
                ->toOne[$relationshipName];
            // @todo Refactorizar; la siguiente línea es un hack.
            $entity
                = $resource->callGetter($relationshipName);
            EntityResource::validateToOneRelation(
                $entity, $relationshipName
            );

            if (is_null($entity)) {
                continue;
            }

            $this->addLinkedResource(
                $relationship, $entity, $resourcesSerialization
            );

            $linkedResource
                = $resourcesSerialization->getLinkedResource(
                    $relationship->type,
                    EntityResource::getStringId($entity)
                );

            if (!empty($linkedResource)) {
                $linkedResources[] = $linkedResource;
            }
        }
    }

    /**
     * @param ResourceCollectionInterface $resources
     * @param $relationshipName
     * @param LinkedResourcesSerialization $resourcesSerialization
     * @param array &$linkedResources
     */
    private function processToManyRelationship(
        ResourceCollectionInterface $resources,
        $relationshipName,
        array &$linkedResources,
        LinkedResourcesSerialization $resourcesSerialization
    )
    {
        foreach ($resources as $resource) {
            if ($resource->isRelationshipBlacklisted(
                $relationshipName
            )) {
                $message = sprintf(
                    self::ERROR_UNKOWN_RELATIONSHIP, $relationshipName
                );
                throw new \Exception($message);
            }

            $relationship = $resource->getMetadata()
                ->relationships
                ->toMany[$relationshipName];
            // @todo Refactorizar; la siguiente línea es un hack.
            $collection
                = $resource->callGetter($relationshipName);
            $collection
                = EntityResource::normalizeToManyRelation(
                    $collection, $relationshipName
                );

            // @todo Mover.
            foreach ($collection as $entity) {
                $this->addLinkedResource(
                    $relationship, $entity, $resourcesSerialization
                );

                $linkedResource
                    = $resourcesSerialization->getLinkedResource(
                        $relationship->type,
                        EntityResource::getStringId($entity)
                    );

                if (!empty($linkedResource)) {
                    $linkedResources[] = $linkedResource;
                } else {
                    // @todo Esto no debería pasar. Hay un error vinculado con el tipo de un recurso siendo averiguado erróneamente cuando se trata de un hijo en una herencia de Doctrine, e.g. tipo "applications" para una app ActivityStream.
                    throw new \Exception(sprintf(
                        self::ERROR_INHERITANCE_MAPPING,
                        implode('.', $relationships)
                    ));
                }
            }
        }
    }

    /**
     * @param array $relationship
     * @param ResourceEntityInterface $entity
     * @param LinkedResourcesSerialization $resourcesSerialization
     */
    protected function addLinkedResource(
        ResourceRelationship $relationship,
        ResourceEntityInterface $entity,
        LinkedResourcesSerialization $resourcesSerialization
    )
    {
        // Serialización de recursos embebidos.
        if ($this->document->linkedResources->hasResource(
            $relationship->type, EntityResource::getStringId($entity)
        )) {
            return;
        }

        $resource = $this->document
            ->linkedResources
            ->addResourceForEntity($entity);
        $resourceObject
            = $this->serializeResourceObject($resource);
        $resourcesSerialization->addLinkedResource(
            $resource,
            $resourceObject
        );
    }

    /**
     * @param DocumentResource $resource
     * @return array
     */
    protected function serializeResourceObject(EntityResource $resource)
    {
        $metadata = $resource->getMetadata();
        $fields = isset($this->document->sparseFields[$metadata->type])
            ? $this->document->sparseFields[$metadata->type]
            : [];

        $serializer = new ResourceObjectSerializer(
            $resource, $this->securityContext, $fields
        );

        return $serializer->serialize();
    }
}
