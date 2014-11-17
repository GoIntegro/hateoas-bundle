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
     * @param array $data
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface
     * @todo No params, just the parser?
     * @todo Replace the HTTP bad request exception.
     * @todo Entity conflict?
     */
    public function update(ResourceEntityInterface $entity, array $data);
}
