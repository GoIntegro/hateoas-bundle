<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Colecciones.
use Doctrine\Common\Collections\ArrayCollection;
// Metadata.
use GoIntegro\Bundle\HateoasBundle\Metadata\Resource\ResourceMetadata;
// Datos.
use Closure;
// Excepciones.
use LogicException;

class ResourceCollection
    extends ArrayCollection
    implements ResourceCollectionInterface
{
    const ERROR_EMPTY_ARRAY = "El array debe contener al menos un recurso de entidad.";

    /**
     * @var ResourceMetadata|NULL
     */
    private $metadata;

    public function __construct(
        array $resources,
        ResourceMetadata $metadata = NULL
    )
    {
        parent::__construct($resources);
        $this->metadata = $metadata;
    }

    /**
     * Factory method para construir a partir de un EntityResource.
     * @param EntityResource $resource
     */
    public static function buildFromResource(EntityResource $resource)
    {
        return new static([$resource], $resource->getMetadata());
    }

    /**
     * Factory method para construir a partir de un array de EntityResource.
     * @param arary $resources
     */
    public static function buildFromArray(array $resources)
    {
        $resource = @$resources[0];

        if (!$resource instanceof EntityResource) {
            throw new LogicException(self::ERROR_EMPTY_ARRAY);
        }

        return new static($resources, $resource->getMetadata());
    }

    /**
     * @see DocumentResource::getMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function map(Closure $func)
    {
        $resources = array_map($func, $this->toArray());

        return new static($resources, $this->getMetadata());
    }
}
