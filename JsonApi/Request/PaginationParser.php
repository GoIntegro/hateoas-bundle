<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// HTTP.
use Symfony\Component\HttpFoundation\Request,
    GoIntegro\Bundle\HateoasBundle\Http\Url;
// Recursos.
use GoIntegro\Bundle\HateoasBundle\JsonApi\DocumentPagination;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\MetadataMinerInterface;

/**
 * @see http://jsonapi.org/format/#fetching
 */
class PaginationParser
{
    const ERROR_UNKNOWN_RESOURCE_TYPE = "El tipo de recurso es desconocido.";

    /**
     * @var MetadataMinerInterface
     */
    private $metadataMiner;
    /**
     * @var string
     */
    private $magicServices;

    /**
     * @param MetadataMinerInterface $metadataMiner
     * @param array $magicServices
     */
    public function __construct(
        MetadataMinerInterface $metadataMiner,
        array $magicServices = []
    )
    {
        $this->metadataMiner = $metadataMiner;
        // @todo Esta verificación debería estar en el DI.
        $this->magicServices = isset($config['magic_services'])
            ? $config['magic_services']
            : [];
    }

    /**
     * @param Request $request
     * @param Params $params
     * @return array
     */
    public function parse(Request $request, Params $params)
    {
        $pagination = new DocumentPagination;

        if (empty($params->primaryClass)) return $pagination;

        $pagination->page = (integer) $request->query->get('page');
        $resourceClassName
            = $this->metadataMiner->getResourceClass($params->primaryClass);
        $pagination->size = (integer) $request->query->get('size')
            ?: $resourceClassName->getProperty('pageSize')->getValue();
        $pagination->offset =
            ($pagination->page - DocumentPagination::COUNT_PAGES_FROM)
            * $pagination->size;
        $pagination->paginationlessUrl
            = $this->parsePaginationlessUrl($request);

        return $pagination;
    }

    /**
     * @param Request $request
     * @return string
     */
    private function parsePaginationlessUrl(Request $request)
    {
        $url = $request->getPathInfo();
        $query = $request->getQueryString();

        if (!empty($query)) {
            $params = explode('&', $query);
            $callback = function($pair) {
                list($key, $value) = explode('=', $pair);

                return !in_array($key, ['page', 'size']);
            };
            $params = array_filter($params, $callback);
            $url .= '?' . implode('&', $params);
        }

        return Url::fromString($url);
    }
}
