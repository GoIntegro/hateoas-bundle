<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Interfaces.
use GoIntegro\Interfaces\Factory,
    GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface;
// Colecciones.
use GoIntegro\Bundle\HateoasBundle\Collections\ResourceEntityCollection;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Parser as RequestParser;
// Paginación.
use Doctrine\ORM\Tools\Pagination\Paginator;
// Búsqueda.
use GoIntegro\Bundle\HateoasBundle\Search\FacetedSearchResult as SearchResult;
// Excepciones.
use Exception;

class ResourceCollectionFactory implements Factory
{
    /**
     * @var ResourceManager
     */
    private $resourceManager;
    /**
     * @var MetadataMinerInterface
     */
    private $metadataMiner;
    /**
     * @var RequestParser
     */
    private $requestParser;
    /**
     * @var ArrayCollection
     */
    private $entities;
    /**
     * @var Paginator
     */
    private $paginator;
    /**
     * @var SearchResult
     */
    private $searchResult;

    /**
     * @param ResourceManager $resourceManager
     * @param EntityManagerInterface $metadataMiner
     * @param RequestParser $requestParser
     */
    public function __construct(
        ResourceManager $resourceManager,
        MetadataMinerInterface $metadataMiner,
        RequestParser $requestParser
    )
    {
        $this->resourceManager = $resourceManager;
        $this->metadataMiner = $metadataMiner;
        $this->requestParser = $requestParser;
        $this->entities = new ResourceEntityCollection;
    }

    /**
     * @param ResourceEntityInterface
     * @return self
     */
    public function addEntity(ResourceEntityInterface $entity)
    {
        $this->entities[] = $entity;

        return $this;
    }

    /**
     * @param array $entities
     * @return self
     */
    public function addEntities(array $entities)
    {
        array_walk($entities, function(ResourceEntityInterface $entity) {
            $this->entities[] = $entity;
        });

        return $this;
    }

    /**
     * @param Paginator $paginator
     * @return self
     * @see http://doctrine-orm.readthedocs.org/en/latest/tutorials/pagination.html
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * @param SearchResult $searchResult
     * @return self
     */
    public function setSearchResult(SearchResult $searchResult)
    {
        $this->searchResult = $searchResult;

        return $this;
    }

    /**
     * @return ResourceCollectionInterface
     */
    public function create()
    {
        $collection = NULL;

        if ($this->paginator instanceof Paginator) {
            $collection = $this->createPaginated();
        } elseif ($this->searchResult instanceof SearchResult) {
            $collection = $this->createSearchResult();
        } else {
            $collection = $this->createPlain();
        }

        return $collection;
    }

    /**
     * @return PaginatedResourceCollection
     */
    protected function createPaginated()
    {
        $entities = $this->paginator->getIterator()->getArrayCopy();
        $metadata = $this->mineMetadata($entities);
        $resources = array_map([$this, 'createResource'], $entities);
        $collection = new PaginatedResourceCollection($resources, $metadata);
        $collection->setPaginator($this->paginator);

        return $collection;
    }

    /**
     * @return SearchResultResourceCollection
     */
    protected function createSearchResult()
    {
        $entities = $this->searchResult->getEntities();
        $metadata = $this->mineMetadata($entities);
        $resources = array_map([$this, 'createResource'], $entities);
        $collection
            = new SearchResultResourceCollection($resources, $metadata);
        $collection->setSearchResult($this->searchResult);

        return $collection;
    }

    /**
     * @return ResourceCollection
     */
    protected function createPlain()
    {
        $entities = $this->entities->toArray() ?: [];
        $metadata = $this->mineMetadata($entities);
        // El método map() sólo puede generar colecciones del mismo tipo.
        $resources = array_map([$this, 'createResource'], $entities);

        return new ResourceCollection($resources, $metadata);
    }

    /**
     * @param array $entities
     * @return ResourceMetadata
     */
    private function mineMetadata(array $entities)
    {
        $metadata = NULL;

        if (0 < count($entities)) {
            $entity = reset($entities);
            $metadata = $this->metadataMiner->mine(reset($entities));
        } else {
            $params = $this->requestParser->parse();
            $type = $params->relationshipType ?: $params->primaryType;
            $metadata = $this->metadataMiner->stub($type);
        }

        return $metadata;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @return EntityResource
     */
    private function createResource(ResourceEntityInterface $entity)
    {
        return $this->resourceManager
            ->createResourceFactory()
            ->setEntity($entity)
            ->create();
    }
}
