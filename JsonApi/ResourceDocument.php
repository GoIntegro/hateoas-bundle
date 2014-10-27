<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

interface ResourceDocument
{
    /**
     * @return \GoIntegro\Bundle\HateoasBundle\Metadata\ResourceMetadata
     */
    public function getMetadata();
}
