<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// JSON-API
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document;

/**
 * @see http://jsonapi.org/format/#document-structure-top-level
 */
class TopLevelLinksSerializer implements DocumentSerializerInterface
{
    private $apiUrlPath;
    private $linkedResourcesSerializer;
    private $paginationLinksSerializer;

    /**
     * @param string $apiUrlPath
     */
    public function __construct($apiUrlPath = '')
    {
        $this->apiUrlPath = $apiUrlPath;
    }

    /**
     * @see DocumentSerializerInterface::serialize
     */
    public function serialize(Document $document)
    {
        $this->linkedResourcesSerializer
            = new TopLevelLinkedLinksSerializer($document, $this->apiUrlPath);
        $this->paginationLinksSerializer
            = new TopLevelPaginationLinksSerializer($document);

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
