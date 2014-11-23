<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;

class Deleter
{
    const DUPLICATED_DELETER = "A deleter for the resource type \"%s\" is already registered.";

    /**
     * @var array
     */
    private $deleters = [];

    /**
     * @param string $resourceType
     * @param ResourceEntityInterface $entity
     */
    public function delete($resourceType, ResourceEntityInterface $entity)
    {
        return isset($this->deleters[$resourceType])
            ? $this->deleters[$resourceType]->delete($entity)
            : $this->deleters['default']->delete($entity);
    }

    /**
     * @param DeleterInterface
     */
    public function addDeleter(DeleterInterface $deleter, $resourceType)
    {
        if (isset($this->deleters[$resourceType])) {
            $message = sprintf(self::DUPLICATED_DELETER, $resourceType);
            throw new \ErrorException($message);
        }

        $this->deleters[$resourceType] = $deleter;
    }
}
