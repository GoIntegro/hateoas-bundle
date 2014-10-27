<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// JSON-API
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document;

/**
 * @see http://jsonapi.org/format/#document-structure-top-level
 */
class TopLevelLinksSerializer implements SerializerInterface
{
    private $document;
    private $linkedResourcesSerializer;
    private $paginationLinksSerializer;

    public function __construct(Document $document, $apiUrlPath = '')
    {
        $this->document = $document;
        $this->linkedResourcesSerializer
            = new TopLevelLinkedLinksSerializer($this->document, $apiUrlPath);
        $this->paginationLinksSerializer
            = new TopLevelPaginationLinksSerializer($this->document);
    }

    /**
     * @see SerializerInterface::serialize
     */
    public function serialize()
    {
        $json = [];

        $this->addLinkedResources($json)
            ->addPaginationLinks($json);

        return $json;
    }

    /**
     * @param array &$json
     * @return self
     */
    protected function addLinkedResources(array &$json)
    {
        $linkedResources = $this->linkedResourcesSerializer->serialize();

        if ($linkedResources) $json = array_merge($json, $linkedResources);

        return $this;
    }

    /**
     * @param array &$json
     * @return self
     */
    protected function addPaginationLinks(array &$json)
    {
        $paginationLinks = $this->paginationLinksSerializer->serialize();

        if ($paginationLinks) $json = array_merge($json, $paginationLinks);

        return $this;
    }
}
