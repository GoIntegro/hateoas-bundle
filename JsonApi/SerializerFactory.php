<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Interfaces.
use GoIntegro\Interfaces\Factory,
    GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// HTTP.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Parser as RequestParser;

class SerializerFactory implements Factory
{
    /**
     * @var ResourceManager
     */
    private $resourceManager;
    /**
     * @var RequestParser
     */
    private $requestParser;
    /**
     * @var ResourceDocument
     */
    private $resourceDocument;
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
     * @param ResourceManager $resourceManager
     * @param RequestParser $requestParser
     * @param string $apiUrlPath
     */
    public function __construct(
        ResourceManager $resourceManager,
        RequestParser $requestParser,
        $apiUrlPath = ''
    )
    {
        $this->resourceManager = $resourceManager;
        $this->requestParser = $requestParser;
        $this->apiUrlPath = $apiUrlPath;
    }

    /**
     * @param ResourceDocument $resource
     * @return self
     */
    public function setResourceDocument(ResourceDocument $resourceDocument)
    {
        $this->resourceDocument = $resourceDocument;

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
            $params = $this->requestParser->parse();

            if (empty($this->include)) {
                $include = $params->include;
            }

            if (empty($this->fields)) {
                $fields = $params->sparseFields;
            }
        }

        $document = new Document(
            $this->resourceDocument,
            $this->resourceManager->resourceCache,
            $include,
            $fields,
            $params->pagination
        );

        foreach ($this->meta as $meta) {
            $document->addResourceMeta($this->resourceDocument, $meta);
        }

        return new DocumentSerializer($document, $this->apiUrlPath);
    }
}
