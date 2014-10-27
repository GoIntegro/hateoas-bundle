<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

interface ResourceCache
{
    /**
     * @param EntityResource $resource
     * @return self
     */
    public function addResource(EntityResource $resource);

    /**
     * @param ResourceEntityInterface $entity
     * @return EntityResource
     */
    public function getResourceForEntity(ResourceEntityInterface $entity);
}
