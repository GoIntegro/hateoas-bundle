<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

class Builder
{
    const DUPLICATED_BUILDER = "A builder for the resource type \"%s\" is already registered.";

    /**
     * @var array
     */
    private $builders = [];

    /**
     * @param string $resourceType
     * @param array $fields
     * @param array $relationships
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface
     */
    public function create(
        $resourceType,
        array $fields,
        array $relationships = []
    )
    {
        return isset($builders[$resourceType])
            ? $this->builders[$resourceType]->create($fields, $relationships)
            : $this->builders['default']->create($fields, $relationships);
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
