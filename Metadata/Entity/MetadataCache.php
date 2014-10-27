<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\Metadata\Entity;

interface MetadataCache
{
    /**
     * @param string|ResourceEntityInterface
     * @return \ReflectionClass
     */
    public function getReflection($classNameOrInstance);

    /**
     * @param string|ResourceEntityInterface
     * @return \Doctrine\ORM\Mapping\ClassMetadataInfo
     */
    public function getMapping($classNameOrInstance);
}
