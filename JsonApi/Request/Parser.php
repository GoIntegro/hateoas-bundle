<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface;
// Inflector.
use GoIntegro\Bundle\HateoasBundle\Util\Inflector;

/**
 * @see http://jsonapi.org/format/#fetching
 */
class Parser
{
    const PRIMARY_RESOURCE_TYPE = 0,
        PRIMARY_RESOURCE_IDS = 1,
        PRIMARY_RESOURCE_FIELD = 2,
        RELATIONSHIP_RESOURCE_TYPE = 3,
        ERROR_NO_API_BASE_PATH
            = "Se necesita definir el path base de la API",
        ERROR_MULTIPLE_IDS_WITH_RELATIONSHIP = "No pueden pedirse múltiples Ids al pedir un recurso relacionado.";

    /**
     * @var MetadataMinerInterface
     */
    private $metadataMiner;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var string
     */
    private $apiUrlPath;
    /**
     * @var array
     */
    private $magicServices;
    /**
     * @var PaginationParser
     */
    private $paginationParser;
    /**
     * @var FilterParser
     */
    private $filterParser;

    /**
     * @param MetadataMinerInterface $metadataMiner
     * @param Request $request
     * @param string $apiUrlPath
     * @param array $config
     */
    public function __construct(
        MetadataMinerInterface $metadataMiner,
        Request $request,
        $apiUrlPath = '',
        array $config = []
    )
    {
        $this->request = $request;
        $this->metadataMiner = $metadataMiner;
        $this->apiUrlPath = $apiUrlPath;
        // @todo Esta verificación debería estar en el DI.
        $this->magicServices = isset($config['magic_services'])
            ? $config['magic_services']
            : [];
        $this->paginationParser
            = new PaginationParser($metadataMiner, $this->magicServices);
        $this->filterParser
            = new FilterParser($metadataMiner, $this->magicServices);
    }

    /**
     * Parsea ciertos parámetros de un pedido de HTTP.
     * @param Request $request
     */
    public function parse(Request $request = NULL)
    {
        $request = $request ?: $this->request;
        $params = new Params;
        $params->primaryType = $this->parsePrimaryType($request);
        $params->primaryClass = $this->getEntityClass($params->primaryType);
        $params->relationshipType = $this->parseRelationshipType($request);
        $params->primaryIds
            = $this->parsePrimaryIds($request, $params->relationshipType);

        if ($request->query->has('include')) {
            $params->include = $this->parseInclude($request);
        }

        if ($request->query->has('fields')) {
            $params->sparseFields
                = $this->parseSparseFields($request, $params->primaryType);
        }

        if ($request->query->has('sort')) {
            $params->sorting
                = $this->parseSorting($request, $params->primaryType);
        }

        if ($request->query->has('page')) {
            $params->pagination
                = $this->paginationParser->parse($request, $params);
        }

        $params->filters = $this->filterParser->parse($request, $params);

        return $params;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function parsePrimaryType(Request $request)
    {
        return $this->parseUrlPart($request, self::PRIMARY_RESOURCE_TYPE);
    }

    /**
     * @param Request $request
     * @param string|NULL $relationshipType
     * @return array
     */
    public function parsePrimaryIds(Request $request, $relationshipType)
    {
        $ids = $this->parseUrlPart($request, self::PRIMARY_RESOURCE_IDS);
        $ids = !empty($ids) ? explode(',', $ids) : [];

        if (1 < count($ids) && !empty($relationshipType)) {
            throw new \Exception(self::ERROR_MULTIPLE_IDS_WITH_RELATIONSHIP);
        }

        return $ids;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function parseRelationshipType(Request $request)
    {
        return $this->parseUrlPart($request, self::RELATIONSHIP_RESOURCE_TYPE);
    }

    /**
     * No se puede usar el servicio "router" de Symfony, lamentablemente.
     * @param Request $request
     * @param integer $part
     * @return string
     */
    private function parseUrlPart(
        Request $request, $part = self::PRIMARY_RESOURCE_TYPE
    )
    {
        if (empty($this->apiUrlPath)) {
            throw new \Exception(self::ERROR_NO_API_BASE_PATH);
        }

        $base = explode('/', $this->apiUrlPath);
        $path = explode('/', $request->getPathInfo());
        $path = array_values(array_diff($path, $base));

        return isset($path[$part]) ? $path[$part] : NULL;
    }

    /**
     * @param Request $request
     * @return array
     */
    private function parseInclude(Request $request)
    {
        $include = explode(',', $request->query->get('include'));
        array_walk($include, function(&$relationship) {
            $relationship = explode('.', $relationship);
        });

        return $include;
    }

    /**
     * @param Request $request
     * @param string $primaryType
     * @return array
     */
    private function parseSparseFields(Request $request, $primaryType)
    {
        $fields = $request->query->get('fields');
        $callback = function($fields) {
            return explode(',', $fields);
        };

        if (is_array($fields)) {
            $fields = array_map($callback, $fields);
        } else {
            $fields = [$primaryType => $callback($fields)];
        }

        return $fields;
    }

    /**
     * @param Request $request
     * @param string $primaryType
     * @return array
     */
    private function parseSorting(Request $request, $primaryType)
    {
        $sort = $request->query->get('sort');
        $sorting = [];
        $callback = function($sort, $type) use (&$sorting) {
            foreach (explode(',', $sort) as $field) {
                if ('-' != substr($field, 0, 1)) {
                    $order = Params::ASCENDING_ORDER;
                } else {
                    $order = Params::DESCENDING_ORDER;
                    $field = substr($field, 1);
                }

                $sorting[$type][$field] = $order;
            }
        };

        if (!is_array($sort)) {
            $sort = [$primaryType => $sort];
        }

        array_walk($sort, $callback);

        return $sorting;
    }

    /**
     * @param string $type
     * @return string
     */
    private function getEntityClass($type)
    {
        foreach ($this->magicServices as $service) {
            if ($type === $service['resource_type']) {
                return $service['entity_class'];
            }
        }
    }
}
