<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Iteración.
use IteratorAggregate, AppendIterator, Countable;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Params as RequestParams;
// Colecciones.
use GoIntegro\Bundle\HateoasBundle\Collections\Paginated;

class Document implements IteratorAggregate, Countable
{
    const DEFAULT_RESOURCE_LIMIT = 50;

    /**
     * @var ResourceCollectionInterface
     * @todo La propiedad $primaryResources es muy similar a esta.
     */
    public $resources;
    /**
     * @var TypedResourceCollection
     * @todo La propiedad $resources es muy similar a esta.
     */
    public $primaryResources;
    /**
     * @var TypedResourceCollection
     */
    public $linkedResources;
    /**
     * @var array
     */
    public $include = [];
    /**
     * @var array
     */
    public $sparseFields = [];
    /**
     * @var array
     * @see http://jsonapi.org/format/#document-structure-top-level
     */
    public $meta = [];
    /**
     * @var boolean Se trata del tipo de representación.
     * @see http://jsonapi.org/format/#document-structure-resource-representations
     */
    public $wasCollection = FALSE;
    /**
     * @var DocumentPagination
     * @todo ¿Mover a un subtipo?
     */
    public $pagination;

    /**
     * @param ResourceCache $resourceCache
     * @todo Organizar.
     */
    public function __construct(
        DocumentResource $documentResource,
        ResourceCache $resourceCache,
        array $include = [],
        array $sparseFields = [],
        DocumentPagination $pagination = NULL
    )
    {
        $this->primaryResources = new TypedResourceCollection($resourceCache);
        $this->linkedResources = new TypedResourceCollection($resourceCache);
        $this->include = $include;
        $this->sparseFields = $sparseFields;

        if ($documentResource instanceof ResourceCollectionInterface) {
            $this->wasCollection = TRUE;

            if (!empty($pagination)) {
                $this->pagination = $pagination;

                if ($documentResource instanceof Paginated) {
                    $this->pagination->fill($documentResource);
                }
            }
        } else {
            $documentResource
                = ResourceCollection::buildFromResource($documentResource);
        }

        foreach ($documentResource as $resource) {
            $this->primaryResources->addResource($resource);
        }

        $this->resources = $documentResource;
    }

    /**
     * @param DocumentResource $resource
     * @param array $meta
     * @return self
     */
    public function getResourceMeta(
        DocumentResource $resource = NULL, $key = NULL
    )
    {
        $meta = NULL;

        if (is_null($resource)) {
            $meta = $this->meta;
        } elseif (isset($this->meta[$resource->getMetadata()->type])) {
            $meta = isset($this->meta[$resource->getMetadata()->type][$key])
                ? $this->meta[$resource->getMetadata()->type][$key]
                : $this->meta[$resource->getMetadata()->type];
        }

        return $meta;
    }

    /**
     * @param DocumentResource $resource
     * @param array $meta
     * @return self
     */
    public function addResourceMeta(DocumentResource $resource, array $meta)
    {
        if (!isset($this->meta[$resource->getMetadata()->type])) {
            $this->meta[$resource->getMetadata()->type] = [];
        }

        $this->meta[$resource->getMetadata()->type]
            = array_merge($this->meta[$resource->getMetadata()->type], $meta);

        return $this;
    }

    /**
     * @see IteratorAggregate::getIterator
     */
    public function getIterator()
    {
        $iterator = new AppendIterator;
        $iterator->append($this->primaryResources->getIterator());
        $iterator->append($this->linkedResources->getIterator());

        return $iterator;
    }

    /**
     * @see Countable::count
     */
    public function count()
    {
        $amount = 0;

        foreach ($this->getIterator()->getArrayIterator() as $iterator) {
            $amount += count($iterator);
        }

        return $amount;
    }
}
