<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// Recursos REST.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document;

class MetadataSerializer implements SerializerInterface
{
    private $document;
    private $paginationSerializer;

    public function __construct(Document $document)
    {
        $this->document = $document;
        $this->paginationSerializer
            = new PaginationMetadataSerializer($this->document);
        $this->searchResultSerializer
            = new SearchResultMetadataSerializer($this->document);
    }

    /**
     * @see SerializerInterface::serialize
     */
    public function serialize()
    {
        $json = [];

        $this->addMetadata($json)
            ->addPagination($json)
            ->addSearchResult($json);

        return $json;
    }

    /**
     * @param array &$json
     * @return self
     */
    protected function addMetadata(array &$json)
    {
        $meta = $this->document->getResourceMeta();
        $json = array_merge($json, $meta);

        return $this;
    }

    /**
     * @param array &$json
     * @return self
     */
    protected function addPagination(array &$json)
    {
        $pagination = $this->paginationSerializer->serialize();

        if ($pagination) {
            $primaryType
                = $this->document->resources->getMetadata()->type;
            $json[$primaryType]['pagination'] = $pagination;
        }

        return $this;
    }

    /**
     * @param array &$json
     * @return self
     */
    protected function addSearchResult(array &$json)
    {
        $searchResult = $this->searchResultSerializer->serialize();

        if ($searchResult) {
            $primaryType
                = $this->document->resources->getMetadata()->type;
            $json[$primaryType]['searchResult'] = $searchResult;
        }

        return $this;
    }
}
