<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document,
    GoIntegro\Bundle\HateoasBundle\JsonApi\SearchResultResourceCollection;
// Utils.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;

/**
 * @todo Move a un sub-namespace "JsonApi\Extension".
 */
class SearchResultMetadataSerializer implements SerializerInterface
{
    public $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function serialize()
    {
        $json = [];

        if (
            $this->document->resources
                instanceof SearchResultResourceCollection
        ) {
            $searchResult = $this->document->resources->getSearchResult();

            foreach (['query', 'query-time', 'facets'] as $property) {
                $method = 'get' . Inflector::camelize($property, TRUE);
                $json[$property] = $searchResult->$method();
            }
        }

        return $json;
    }
}
