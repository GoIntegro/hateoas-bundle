<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Params;

class Builder
{
    const DUPLICATED_BUILDER = "A builder for the resource type \"%s\" is already registered.";

    /**
     * @var array
     */
    private $builders = [];

    /**
     * @param Params $params
     * @param array $fields
     * @param array $relationships
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface
     */
    public function create(
        Params $params,
        array $fields,
        array $relationships = []
    )
    {
        return isset($this->builders[$params->primaryType])
            ? $this->builders[$params->primaryType]
                ->create($fields, $relationships)
            : $this->builders['default']
                ->create($params->primaryClass, $fields, $relationships);
    }

    /**
     * @param BuilderInterface
     */
    public function addBuilder(BuilderInterface $builder, $resourceType)
    {
        if (isset($this->builders[$resourceType])) {
            $message = sprintf(self::DUPLICATED_BUILDER, $resourceType);
            throw new \ErrorException($message);
        }

        $this->builders[$resourceType] = $builder;
    }
}
