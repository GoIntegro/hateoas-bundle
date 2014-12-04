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
     * @param TranslationsMetadataSerializer $translationsSerializer
     */
    public function __construct(
        TranslationsMetadataSerializer $translationsSerializer
    )
    {
        $this->translationsSerializer = $translationsSerializer;
    }

    /**
     * @param Document $document
     * @return array
     */
    public function serialize(Document $document)
    {
        // @todo Make services out of these guys.
        $this->document = $document;
        $this->paginationSerializer
            = new PaginationMetadataSerializer($this->document);
        $this->searchResultSerializer
            = new SearchResultMetadataSerializer($this->document);

        $json = [];

        $this->addMetadata($json)
            ->addPagination($json)
            ->addSearchResult($json)
            ->addTranslations($document, $json);

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
                = $this->document->resources->getMetadata()->type;
            $json[$primaryType]['translations'] = $translations;
        }

        return $this;
    }
}
