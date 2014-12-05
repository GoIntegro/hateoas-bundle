<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// Recursos REST.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document;

class MetadataSerializer implements DocumentSerializerInterface
{
    private $document;
    private $translationsSerializer;
    private $paginationSerializer;
    private $searchResultSerializer;

    /**
     * @param PaginationMetadataSerializer $paginationSerializer
     * @param SearchResultMetadataSerializer $searchResultSerializer
     * @param TranslationsMetadataSerializer $translationsSerializer
     */
    public function __construct(
        PaginationMetadataSerializer $paginationSerializer,
        SearchResultMetadataSerializer $searchResultSerializer,
        TranslationsMetadataSerializer $translationsSerializer
    )
    {
        $this->paginationSerializer = $paginationSerializer;
        $this->searchResultSerializer = $searchResultSerializer;
        $this->translationsSerializer = $translationsSerializer;
    }

    /**
     * @param Document $document
     * @return array
     */
    public function serialize(Document $document)
    {
        $json = [];

        $this->addMetadata($document, $json)
            ->addPagination($document, $json)
            ->addSearchResult($document, $json)
            ->addTranslations($document, $json);

        return $json;
    }

    /**
     * @param Document $document
     * @param array &$json
     * @return self
     */
    protected function addMetadata(Document $document, array &$json)
    {
        $meta = $document->getResourceMeta();
        $json = array_merge($json, $meta);

        return $this;
    }

    /**
     * @param Document $document
     * @param array &$json
     * @return self
     */
    protected function addPagination(Document $document, array &$json)
    {
        $pagination = $this->paginationSerializer->serialize($document);

        if ($pagination) {
            $primaryType
                = $document->resources->getMetadata()->type;
            $json[$primaryType]['pagination'] = $pagination;
        }

        return $this;
    }

    /**
     * @param Document $document
     * @param array &$json
     * @return self
     */
    protected function addSearchResult(Document $document, array &$json)
    {
        $searchResult = $this->searchResultSerializer->serialize($document);

        if ($searchResult) {
            $primaryType
                = $document->resources->getMetadata()->type;
            $json[$primaryType]['searchResult'] = $searchResult;
        }

        return $this;
    }

    /**
     * @param Document $document
     * @param array &$json
     * @return self
     */
    protected function addTranslations(Document $document, array &$json)
    {
        $translations = $this->translationsSerializer->serialize($document);

        if ($translations) {
            $primaryType
                = $document->resources->getMetadata()->type;
            $json[$primaryType]['translations'] = $translations;
        }

        return $this;
    }
}
