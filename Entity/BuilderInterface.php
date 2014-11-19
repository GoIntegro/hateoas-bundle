<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

interface BuilderInterface
{
    /**
     * @param array $fields
     * @param array $relationships
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface
     * @throws \GoIntegro\Bundle\HateoasBundle\Entity\Validation\EntityConflictExceptionInterface
     * @throws \GoIntegro\Bundle\HateoasBundle\Entity\Validation\ValidationExceptionInterface
     */
    public function create(array $fields, array $relationships = []);
}
