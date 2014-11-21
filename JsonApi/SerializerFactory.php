<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Interfaces.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// HTTP.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Parser as RequestParser;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Params;

class SerializerFactory implements Factory
{
    /**
     * @var ResourceManager
     */
    private $resourceManager;
    /**
     * @var DocumentResource
     */
    private $documentResource;
    /**
     * @var array
     * @see http://jsonapi.org/format/#fetching-includes
     */
    private $include = [];
    /**
     * @var array
     * @see http://jsonapi.org/format/#fetching-sparse-fieldsets
     */
    private $fields = [];
    /**
     * @var array
     * @see http://jsonapi.org/format/#document-structure-top-level
     */
    private $meta = [];
    /**
     * @var array
     */
    private $apiUrlPath;
    /**
     * @var Params
     */
    private $params;

    /**
     * @param ResourceManager $resourceManager
     * @param string $apiUrlPath
     */
    public function __construct(
        ResourceManager $resourceManager,
        $apiUrlPath = ''
    )
    {
        $this->resourceManager = $resourceManager;
        $this->apiUrlPath = $apiUrlPath;
    }

    /**
     * @param DocumentResource $resource
     * @return self
     */
    public function setDocumentResources(DocumentResource $documentResource)
    {
        $this->documentResource = $documentResource;

        return $this;
    }

    /**
     * @param array $include
     * @return self
     */
    public function setInclude(array $include)
    {
        $this->include = $include;

        return $this;
    }

    /**
     * @param array $fields
     * @return self
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param Params $params
     * @return self
     */
    public function setParams(Params $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @param array $meta
     * @return self
     */
    public function addMeta(array $meta)
    {
        $this->meta[] = $meta;

        return $this;
    }

    /**
     * @return ResourceSerializer
     */
    public function create()
    {
        $include = NULL;
        $fields = NULL;

        if (empty($this->fields) || empty($this->include)) {
            if (empty($this->include)) {
                $include = $this->params->include;
            }

            if (empty($this->fields)) {
                $fields = $this->params->sparseFields;
            }
        }

        $document = new Document(
            $this->documentResource,
            $this->resourceManager->resourceCache,
            $include,
            $fields,
            $this->params->pagination
        );

        foreach ($this->meta as $meta) {
            $document->addResourceMeta($this->documentResource, $meta);
        }

        return new DocumentSerializer($document, $this->apiUrlPath);
    }
}
