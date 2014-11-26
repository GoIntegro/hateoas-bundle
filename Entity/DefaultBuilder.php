<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Entity;

// Inflection.
use Doctrine\Common\Util\Inflector;
// ORM.
use Doctrine\ORM\EntityManagerInterface,
    Doctrine\ORM\ORMException;
// Validator.
use Symfony\Component\Validator\Validator\ValidatorInterface,
    Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;

class DefaultBuilder
{
    use Validating;

    const GET = 'get', ADD = 'add', SET = 'set';

    const AUTHOR_IS_OWNER = 'GoIntegro\\Bundle\\HateoasBundle\\Entity\\AuthorIsOwner',
        ERROR_COULD_NOT_CREATE = "Could not create the resource.";

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        SecurityContextInterface $securityContext
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->securityContext = $securityContext;
    }

    /**
     * @param string $class
     * @param array $fields
     * @param array $relationships
     * @return ResourceEntityInterface
     * @throws EntityConflictExceptionInterface
     * @throws ValidationExceptionInterface
     */
    public function create($class, array $fields, array $relationships = [])
    {
        $class = new \ReflectionClass($class);
        $entity = $class->newInstance();

        if ($class->implementsInterface(self::AUTHOR_IS_OWNER)) {
            $entity->setOwner($this->securityContext->getToken()->getUser());
        }

        foreach ($fields as $field => $value) {
            $method = self::SET . Inflector::camelize($field);

            if ($class->hasMethod($method)) $entity->$method($value);
        }

        foreach ($relationships as $relationship => $value) {
            $camelCased = Inflector::camelize($relationship);

            if (is_array($value)) {
                $getter = self::GET . $camelCased;
                $adder = self::ADD . Inflector::singularize($camelCased);

                foreach ($value as $item) $entity->$adder($item);
            } else {
                $method = self::SET . $camelCased;

                if ($class->hasMethod($method)) $entity->$method($value);
            }
        }

        $this->validate($entity);

        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (ORMException $e) {
            throw new PersistenceException(self::ERROR_COULD_NOT_CREATE);
        }

        return $entity;
    }
}
