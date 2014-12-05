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
 * @todo Move a un sub-namespace "JsonApi\Extension".
 */
class PaginationMetadataSerializer implements DocumentSerializerInterface
{
    public function serialize(Document $document)
    {
        $json = [];

        if (!empty($document->pagination)) {
            foreach (['page', 'size', 'total'] as $key) {
                if (is_null($document->pagination->$key)) continue;
                $json[$key] = $document->pagination->$key;
            }
        }

        return $json;
    }
}
