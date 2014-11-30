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
class TranslationsMetadataSerializer implements SerializerInterface
{
    public $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function serialize()
    {
        $json = [];

        if (!empty($this->document->pagination)) {
            foreach (['page', 'size', 'total'] as $key) {
                if (is_null($this->document->pagination->$key)) continue;
                $json[$key] = $this->document->pagination->$key;
            }
        }

        return $json;
    }
}
