<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// Recursos REST.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface,
    GoIntegro\Bundle\HateoasBundle\JsonApi\DocumentResource;
// Colecciones.
use Doctrine\Common\Collections\Collection as CollectionInterface;
// Utils.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;
// JSON-API
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document,
    GoIntegro\Bundle\HateoasBundle\JsonApi\DocumentPagination;

/**
 * @see https://github.com/json-api/json-api/issues/236#issuecomment-45655029
 * @todo Para poder mezclar mejor distintos documentos de JSON-API con
 * paginación, los links de top-level que registra un documento paginado
 * deberían ser plantillas genuinas, sin parámetros "hard-codeados".
 * Así notifications:next pasaría de ser /notifications?page=2&size=2
 * a ser /notifications?page={notifications.pagination.next}&size={notifications.pagination.size}
 */
class TopLevelPaginationLinksSerializer implements DocumentSerializerInterface
{
    /**
     * @var array
     */
    private static $relationships = ['first', 'prev', 'next', 'last'];

    public function serialize(Document $document)
    {
        $json = [];
        $pagination = $document->pagination;

        if (!empty($pagination)) {
            foreach (self::$relationships as $relationship) {
                $method = 'get' . Inflector::camelize($relationship);
                $page = $this->$method();

                if (is_null($page)) continue;

                $resource = $document->resources;
                $relationKey
                    = $this->buildRelationKey($resource, $relationship);
                $query = $pagination->paginationlessUrl->getQuery();
                $url = $pagination->paginationlessUrl->getOriginal();
                $url .= empty($query) ? '?' : '&';
                $url .= 'page=' . $page . '&size=' . $pagination->size;
                $json[$relationKey] = [
                    'href' => $url,
                    'type' => $resource->getMetadata()->type
                ];
            }
        }

        return $json;
    }

    /**
     * @param DocumentResource $resource
     * @param string $relationship
     * @return string
     */
    public static function buildRelationKey(
        DocumentResource $resource, $relationship
    )
    {
        return $resource->getMetadata()->type . ':' . $relationship;
    }

    /**
     * @return integer
     */
    protected function getFirst()
    {
        return DocumentPagination::COUNT_PAGES_FROM;
    }

    /**
     * @return integer
     */
    protected function getPrev()
    {
        $page = $document->pagination->page - 1;

        return 0 < $page ? $page : NULL;
    }

    /**
     * @return integer
     */
    protected function getNext()
    {
        $page = $document->pagination->page + 1;

        return $page <= $this->getLast() ? $page : NULL;
    }

    /**
     * @return integer
     */
    protected function getLast()
    {
        return floor(
            $document->pagination->total
            / $document->pagination->size
        ) + 1;
    }
}
