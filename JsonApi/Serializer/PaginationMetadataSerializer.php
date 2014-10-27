<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// JSON-API
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document;

/**
 * @todo Move a un sub-namespace "JsonApi\Extension".
 * @todo Para poder mezclar mejor distintos documentos de JSON-API con
 * paginación, los links de top-level que registra un documento paginado
 * deberían ser plantillas genuinas, sin parámetros "hard-codeados".
 * Así notifications:next pasaría de ser /notifications?page=2&size=2
 * a ser /notifications?page={notifications.pagination.next}&size={notifications.pagination.size}
 */
class PaginationMetadataSerializer implements SerializerInterface
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
