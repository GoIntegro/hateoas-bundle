<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
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
class FilterParser
{
    /**
     * @var array
     */
    private static $reserved = ['include', 'fields', 'sort', 'page', 'size'];
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
        $filters = [];

        if (empty($params->primaryClass)) return $filters;

        $metadata = $this->metadataMiner->mine($params->primaryClass);
        $add = function($param, $value, $type) use (&$filters) {
            $property = Inflector::camelize($param);
            if (is_string($value)) $value = explode(',', $value);
            $filters[$type][$property] = $value;
        };

        foreach ($request->query as $param => $value) {
            if ($metadata->isField($param)) {
                $add($param, $value, 'field');
            } elseif ($metadata->isRelationship($param)) {
                $add($param, $value, 'association'); // Doctrine 2 term.
            } elseif (!in_array($param, self::$reserved)) {
                $add($param, $value, 'custom');
            }
        }

        return $filters;
    }
}
