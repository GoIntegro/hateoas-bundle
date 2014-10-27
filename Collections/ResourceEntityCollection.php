<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\Collections;

// Colecciones.
use Doctrine\Common\Collections\ArrayCollection;
// Recursos REST.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// Excepciones.
use Exception;

class ResourceEntityCollection extends ArrayCollection
{
    const ERROR_MIXED_TYPES = "Las entidades de la colección deben ser de un mismo tipo.";

    /**
     * @var string
     */
    private $className;

    /**
     * Initializes a new ArrayCollection.
     * @param array $entities
     */
    public function __construct(array $entities = [])
    {
        array_walk($entities, function(ResourceEntityInterface $entity) {
            $this->assertCanAdd($entity);
            $this->add($entity);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $entity)
    {
        $this->assertCanAdd($entity);
        return parent::set($key, $entity);
    }

    /**
     * {@inheritDoc}
     */
    public function add($entity)
    {
        $this->assertCanAdd($entity);
        return parent::add($entity);
    }

    /**
     * Verifica que la entidad sea del tipo correcto.
     * @param ResourceEntityInterface $entity
     * @return boolean
     */
    public function canAdd(ResourceEntityInterface $entity)
    {
        return empty($this->className)
            || get_class($entity) == $this->className;
    }

    /**
     * Verifica que la entidad sea del tipo correcto.
     * @param ResourceEntityInterface $entity
     * @throws Exception
     */
    private function assertCanAdd(ResourceEntityInterface $entity)
    {
        if (!$this->canAdd($entity)) {
            throw new Exception(self::ERROR_MIXED_TYPES);
        }
    }
}
