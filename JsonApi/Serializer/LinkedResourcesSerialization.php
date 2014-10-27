<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Serializer;

// Recursos.
use GoIntegro\Bundle\HateoasBundle\JsonApi\EntityResource;

class LinkedResourcesSerialization
{
    private $linksByType = [];
    private $linkedById = [];

    /**
     * Verifica si hay recursos.
     * @return boolean
     */
    public function hasLinkedResources()
    {
        return !empty($this->linkedById);
    }

    /**
     * Verifica si existe un recurso por su tipo y Id.
     * @param string $type
     * @param integer $id
     * @return boolean
     */
    public function hasLinkedResource($type, $id)
    {
        return isset($this->linkedById[$type][$id]);
    }

    /**
     * @param EntityResource $resource
     * @param array $resourceObject
     * @return self
     */
    public function addLinkedResource(
        EntityResource $resource, array $resourceObject
    )
    {
        $this->linkedById[$resource->getMetadata()->type][$resource->id]
            = [
                'resource' => $resource,
                'serialization' => $resourceObject
            ];
    }

    /**
     * @param string $type
     * @param string $id
     * @return EntityResource
     * @todo Este método tendría que estar en el modelo de JsonApiDocument.
     */
    public function getLinkedResource($type, $id)
    {
        if (
            isset($this->linkedById[$type])
            && isset($this->linkedById[$type][$id])
            && isset($this->linkedById[$type][$id]['resource'])
        ) {
            return $this->linkedById[$type][$id]['resource'];
        }
    }

    /**
     * @param string $type Opcional.
     * @return boolean
     */
    public function hasTopLevelLinks($type = NULL)
    {
        return empty($type)
            ? !empty($this->linksByType)
            : isset($this->linksByType[$type]);
    }

    /**
     * @param string $type
     * @param array $resource
     * @return self
     */
    public function addTopLevelLinks($type, array $links)
    {
        $this->linksByType[$type] = $links;
    }

    /**
     * @return array
     */
    public function getLinkedResources()
    {
        // Allanamos la lista de recursos vinculados de un mismo tipo.
        $serializedResources = [];

        foreach ($this->linkedById as $type => $resources) {
            foreach ($resources as $resourceData) {
                $serializedResources[$type][] = $resourceData['serialization'];
            }
        }

        return $serializedResources;
    }

    /**
     * @return array
     */
    public function getTopLevelLinks()
    {
        // Allanamos la lista de vinculos de alto nivel.
        $topLevelLinks = [];
        $callback = function(array $serializedLinks) use (&$topLevelLinks) {
            $topLevelLinks = array_merge($topLevelLinks, $serializedLinks);
        };
        array_walk($this->linksByType, $callback);

        return $topLevelLinks;
    }
}
