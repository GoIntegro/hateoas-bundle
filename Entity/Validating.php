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

trait Validating
{
    /**
     * @param ResourceEntityInterface $entity
     * @throws Validation\EntityConflictException
     * @throws Validation\ValidationException
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function validate(ResourceEntityInterface $entity)
    {
        $constraints = $this->getUniqueContraints($entity);
        $errors = $this->validator->validateValue($entity, $constraints);

        if (0 < count($errors)) {
            throw new Validation\EntityConflictException($errors);
        } else {
            $errors = $this->validator->validate($entity);

            if (0 < count($errors)) {
                throw new Validation\ValidationException($errors);
            }
        }

        return $errors;
    }

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
