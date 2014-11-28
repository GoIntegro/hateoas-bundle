<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;

interface MutatorInterface
{
    /**
     * @param ResourceEntityInterface $entity
     * @param array $fields
     * @param array $relationships
     * @param array $metadata
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface
     * @throws \GoIntegro\Bundle\HateoasBundle\Entity\Validation\EntityConflictExceptionInterface
     * @throws \GoIntegro\Bundle\HateoasBundle\Entity\Validation\ValidationExceptionInterface
     */
    public function update(
        ResourceEntityInterface $entity,
        array $data,
        array $relationships = [],
        array $metadata = []
    );
}
