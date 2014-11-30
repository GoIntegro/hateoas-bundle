<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

interface AbstractBuilderInterface extends GenericBuilderInterface
{
    /**
     * @param string $class
     * @param array $fields
     * @param array $relationships
     * @param array $metadata
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface
     * @throws \GoIntegro\Bundle\HateoasBundle\Entity\Validation\EntityConflictExceptionInterface
     * @throws \GoIntegro\Bundle\HateoasBundle\Entity\Validation\ValidationExceptionInterface
     */
    public function create(
        $class,
        array $fields,
        array $relationships = [],
        array $metadata = []
    );
}
