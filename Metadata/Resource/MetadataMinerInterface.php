<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Resource;

interface MetadataMinerInterface
{
    /**
     * @param string|ResourceEntityInterface $entityClassName
     * @param ResourceMetadata
     */
    public function mine($entityClassName);

    /**
     * @param string|ResourceEntityInterface $entityClassName
     * @return ReflectionClass
     */
    public function getResourceClass($entityClassName);
}
