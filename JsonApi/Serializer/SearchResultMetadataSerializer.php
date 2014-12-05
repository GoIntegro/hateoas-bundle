<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
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
class SearchResultMetadataSerializer implements DocumentSerializerInterface
{
    public function serialize(Document $document)
    {
        $json = [];

        if (
            $document->resources
                instanceof SearchResultResourceCollection
        ) {
            $searchResult = $document->resources->getSearchResult();

            foreach (['query', 'query-time', 'facets'] as $property) {
                $method = 'get' . Inflector::camelize($property, TRUE);
                $json[$property] = $searchResult->$method();
            }
        }

        return $json;
    }
}
