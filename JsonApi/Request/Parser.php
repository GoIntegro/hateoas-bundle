<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request;
// RAML.
use GoIntegro\Bundle\HateoasBundle\Raml\DocFinder;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Document;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface;

/**
 * @see http://jsonapi.org/format/#fetching
 */
class Parser
{
    const HTTP_OPTIONS = 'OPTIONS',
        HTTP_HEAD = 'HEAD',
        HTTP_GET = 'GET',
        HTTP_POST = 'POST',
        HTTP_PUT = 'PUT',
        HTTP_DELETE = 'DELETE',
        HTTP_PATCH = 'PATCH';

    const PRIMARY_RESOURCE_TYPE = 0,
        PRIMARY_RESOURCE_IDS = 1,
        PRIMARY_RESOURCE_FIELD = 2,
        PRIMARY_RESOURCE_RELATIONSHIP = 3,
        RELATIONSHIP_RESOURCE_IDS = 4; // For multiple relationship deletes.

    const ERROR_NO_API_BASE_PATH
            = "The API base path is not configured.",
        ERROR_MULTIPLE_IDS_WITH_RELATIONSHIP = "Multiple Ids are not supported when requesting a resource field or link.",
        ERROR_RESOURCE_NOT_FOUND = "The requested resource was not found.",
        ERROR_ACTION_NOT_ALLOWED = "The attempted action is not allowed on the requested resource. Supported HTTP methods are [%s].",
        ERROR_RELATIONSHIP_UNDEFINED = "The requested relationship is undefined.",
        ERROR_RELATIONSHIP_LINK_ONLY = "The relationship can only be accessed through its own URL, filtering by its relationship with the current resource.";

    /**
     * @var DocFinder
     */
    private $docFinder;
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
     * @var BodyParser
     */
    private $bodyParser;
    /**
     * @var ActionParser
     */
    private $actionParser;
    /**
     * @var ParamEntityFinder
     */
    private $entityFinder;
    /**
     * @var MetadataMinerInterface
     */
    private $mm;

    /**
     * @param DocFinder $docFinder
     * @param FilterParser $filterParser
     * @param PaginationParser $paginationParser
     * @param BodyParser $bodyParser
     * @param ActionParser $actionParser
     * @param ParamEntityFinder $entityFinder
     * @param MetadataMinerInterface $mm
     * @param string $apiUrlPath
     * @param array $config
     */
    public function __construct(
        DocFinder $docFinder,
        FilterParser $filterParser,
        PaginationParser $paginationParser,
        BodyParser $bodyParser,
        ActionParser $actionParser,
        ParamEntityFinder $entityFinder,
        MetadataMinerInterface $mm,
        $apiUrlPath = '',
        array $config = []
    )
    {
        $this->docFinder = $docFinder;
        $this->apiUrlPath = $apiUrlPath;
        // @todo Esta verificación debería estar en el DI.
        $this->magicServices = isset($config['magic_services'])
            ? $config['magic_services']
            : [];
        $this->paginationParser = $paginationParser;
        $this->filterParser = $filterParser;
        $this->bodyParser = $bodyParser;
        $this->actionParser = $actionParser;
        $this->entityFinder = $entityFinder;
        $this->mm = $mm;
    }

    /**
     * Parsea ciertos parámetros de un pedido de HTTP.
     * @param Request $request
     * @throws ResourceNotFoundException
     * @throws ActionNotAllowedException
     * @throws ParseException
     * @throws EntityAccessDeniedException
     * @throws EntityNotFoundException
     */
    public function parse(Request $request)
    {
        $params = new Params;
        $params->path = $this->parsePath($request);
        $params->primaryType = $this->parsePrimaryType($request);
        $params->primaryClass = $this->getEntityClass($params->primaryType);
        $params->relationship = $this->parseRelationship($request, $params);
        $params->primaryIds
            = $this->parsePrimaryIds($request, $params->relationship);

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
        $content = $request->getContent();

        if (!empty($content)) {
            $params->resources = $this->bodyParser->parse($request, $params);
        }

        // Needs the params from the BodyParser.
        $params->action = $this->actionParser->parse($request, $params);

        if (!empty($params->primaryIds)) {
            // Needs the params from the ActionParser.
            $params->entities = $this->entityFinder->find($params);
        }

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
     * @param string|NULL $relationship
     * @return array
     */
    public function parsePrimaryIds(Request $request, $relationship)
    {
        $ids = $this->parseUrlPart($request, self::PRIMARY_RESOURCE_IDS);
        $ids = !empty($ids) ? explode(',', $ids) : [];

        if (1 < count($ids) && !empty($relationship)) {
            throw new ParseException(
                self::ERROR_MULTIPLE_IDS_WITH_RELATIONSHIP
            );
        }

        if (Document::DEFAULT_RESOURCE_LIMIT < count($ids)) {
            throw new DocumentTooLargeException;
        }

        return $ids;
    }

    /**
     * @param Request $request
     * @param Params $params
     * @return string
     */
    public function parseRelationship(Request $request, Params $params)
    {
        $relationship = $this->parseUrlPart(
            $request, self::PRIMARY_RESOURCE_RELATIONSHIP
        );

        if (!empty($relationship)) {
            $metadata = $this->mm->mine($params->primaryType);

            if (!$metadata->isRelationship($relationship)) {
                throw new ParseException(self::ERROR_RELATIONSHIP_UNDEFINED);
            } elseif ($metadata->isLinkOnlyRelationship($relationship)) {
                throw new ParseException(self::ERROR_RELATIONSHIP_LINK_ONLY);
            }
        }

        return $relationship;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function parseRelationshipIds(Request $request)
    {
        $ids = $this->parseUrlPart($request, self::RELATIONSHIP_RESOURCE_IDS);
        $ids = !empty($ids) ? explode(',', $ids) : [];

        if (Document::DEFAULT_RESOURCE_LIMIT < count($ids)) {
            throw new DocumentTooLargeException;
        }

        return $ids;
    }

    /**
     * @param Request $request
     * @param integer $part
     * @return string
     */
    private function parseUrlPart(
        Request $request, $part = self::PRIMARY_RESOURCE_TYPE
    )
    {
        $path = $this->parsePathParts($request);

        return isset($path[$part]) ? $path[$part] : NULL;
    }

    /**
     * @param Request $request
     * @return array
     */
    private function parsePath(Request $request)
    {
        $parts = $this->parsePathParts($request);
        $path = '/' . implode('/', $parts);
        $ramlDoc = $this->docFinder->find($parts[self::PRIMARY_RESOURCE_TYPE]);
        $method = strtolower($request->getMethod());

        if (!$ramlDoc->isDefined($method, $path)) {
            $allowedMethods = $ramlDoc->getAllowedMethods($path, CASE_UPPER);
            $message = sprintf(
                self::ERROR_ACTION_NOT_ALLOWED, implode(', ', $allowedMethods)
            );
            throw new ActionNotAllowedException($allowedMethods, $message);
        }

        return $path;
    }

    /**
     * The "router" Symfony service cannot be used, regretably.
     * @param Request $request
     * @return array
     */
    private function parsePathParts(Request $request)
    {
        if (empty($this->apiUrlPath)) {
            throw new \Exception(self::ERROR_NO_API_BASE_PATH);
        }

        // Resolving not knowing whether the base contains a domain.
        $base = explode('/', $this->apiUrlPath);
        $path = explode('/', $request->getPathInfo());

        return array_values(array_diff($path, $base));
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
     * @throws ResourceNotFoundException
     */
    private function getEntityClass($type)
    {
        foreach ($this->magicServices as $service) {
            if ($type === $service['resource_type']) {
                return $service['entity_class'];
            }
        }

        throw new ResourceNotFoundException(self::ERROR_RESOURCE_NOT_FOUND);
    }
}
