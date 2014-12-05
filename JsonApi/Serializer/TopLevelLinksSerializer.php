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
    private $linkedResourcesSerializer;
    private $paginationLinksSerializer;

    /**
     * @param TopLevelLinkedLinksSerializer $linkedResourcesSerializer
     * @param TopLevelPaginationLinksSerializer $paginationLinksSerializer
     */
    public function __construct(
        TopLevelLinkedLinksSerializer $linkedResourcesSerializer,
        TopLevelPaginationLinksSerializer $paginationLinksSerializer
    )
    {
        $this->linkedResourcesSerializer = $linkedResourcesSerializer;
        $this->paginationLinksSerializer = $paginationLinksSerializer;
    }

    /**
     * @see DocumentSerializerInterface::serialize
     */
    public function serialize(Document $document)
    {
        $json = [];

        $this->addLinkedResources($document, $json)
            ->addPaginationLinks($document, $json);

        return $json;
    }

    /**
     * @param Document $document
     * @param array &$json
     * @return self
     */
    protected function addLinkedResources(Document $document, array &$json)
    {
        $linkedResources
            = $this->linkedResourcesSerializer->serialize($document);

        if ($linkedResources) $json = array_merge($json, $linkedResources);

        return $this;
    }

    /**
     * @param Document $document
     * @param array &$json
     * @return self
     */
    protected function addPaginationLinks(Document $document, array &$json)
    {
        $paginationLinks
            = $this->paginationLinksSerializer->serialize($document);

        if ($paginationLinks) $json = array_merge($json, $paginationLinks);

        return $this;
    }
}
