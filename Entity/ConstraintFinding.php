<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// Validator.
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

trait ContraintFinding
{
    /**
     * @param ResourceEntityInterface $entity
     * @return array
     */
    protected function getUniqueContraints(ResourceEntityInterface $entity)
    {
        $metadata = $this->validator->getMetadataFor($entity);
        $constraints = [];

        foreach ($metadata->getConstraints() as $constraint) {
            if (
                $constraint instanceof UniqueEntity
                || $constraint
                    instanceof Validation\ConflictConstraintInterface
            ) {
                $constraints[] = $constraint;
            }
        }

        return $constraints;
    }
}
