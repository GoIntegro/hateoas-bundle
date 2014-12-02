<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

// Inflection.
use Doctrine\Common\Util\Inflector;
// JSON-API.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// ORM.
use Doctrine\ORM\EntityManagerInterface,
    Doctrine\ORM\ORMException;
// Validator.
use Symfony\Component\Validator\ValidatorInterface,
    GoIntegro\Bundle\HateoasBundle\Entity\Validation\ValidationException;

class DefaultMutator implements MutatorInterface
{
    use Validating;

    const GET = 'get', REMOVE = 'remove', ADD = 'add', SET = 'set';

    const TRANSLATION_ENTITY = 'Gedmo\\Translatable\\Entity\\Translation';

    const ERROR_COULD_NOT_UPDATE = "Could not update the resource.";

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator
    )
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @param array $fields
     * @param array $relationships
     * @param array $metadata
     * @return ResourceEntityInterface
     * @throws EntityConflictExceptionInterface
     * @throws ValidationExceptionInterface
     */
    public function update(
        ResourceEntityInterface $entity,
        array $fields,
        array $relationships = [],
        array $metadata = []
    )
    {
        $class = new \ReflectionClass($entity);

        foreach ($fields as $field => $value) {
            $method = self::SET . Inflector::camelize($field);

            if ($class->hasMethod($method)) $entity->$method($value);
        }

        foreach ($relationships as $relationship => $value) {
            $camelCased = Inflector::camelize($relationship);

            if (is_array($value)) {
                $getter = self::GET . $camelCased;
                $singular = Inflector::singularize($camelCased);
                $remover = self::REMOVE . $singular;
                $adder = self::ADD . $singular;

                // @todo Improve algorithm.
                foreach ($entity->$getter() as $item) $entity->$remover($item);

                foreach ($value as $item) $entity->$adder($item);
            } else {
                $method = self::SET . $camelCased;

                if ($class->hasMethod($method)) $entity->$method($value);
            }
        }

        $entity = $this->updateTranslations($entity, $metadata);

        $errors = $this->validate($entity);

        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (ORMException $e) {
            throw new PersistenceException(self::ERROR_COULD_NOT_UPDATE);
        }

        return $entity;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @param array $translations
     * @return ResourceEntityInterface
     */
    private function updateTranslations(
        ResourceEntityInterface $entity, array $translations
    )
    {
        $repository = $manager->getRepository(self::TRANSLATION_ENTITY);

        foreach ($translations as $locale => $fields) {
            foreach ($fields as $field => $value) {
                $repository->translate($entity, $field, $locale, $value);
            }
        }

        return $entity;
    }
}
