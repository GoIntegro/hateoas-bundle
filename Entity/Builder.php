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
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Parser;
// ORM.
use Doctrine\ORM\EntityManagerInterface,
    Doctrine\ORM\ORMException;
// Validator.
use Symfony\Component\Validator\Validator\ValidatorInterface,
    GoIntegro\Bundle\HateoasBundle\Entity\Validation\ValidationException;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;

class Builder
{
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
     * @var Parser
     */
    private $parser;

    /**
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param SecurityContextInterface $securityContext
     * @param Parser $parser
     */
    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        SecurityContextInterface $securityContext,
        Parser $parser
    )
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->securityContext = $securityContext;
        $this->parser = $parser;
    }

    /**
     * @param array $data
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface
     * @todo No params, just the parser?
     * @todo Replace the HTTP bad request exception.
     */
    public function create(array $data)
    {
        $params = $this->parser->parse();
        $class = new \ReflectionClass($params->primaryClass);
        $entity = $class->newInstance();

        if ($class->implementsInterface(self::AUTHOR_IS_OWNER)) {
            $entity->setOwner($this->securityContext->getToken()->getUser());
        }

        // @todo Mover al parser.
        foreach ($data as $field => $value) {
            if ('links' == $field) continue;

            $method = 'set' . Inflector::camelize($field);

            if ($class->hasMethod($method)) $entity->$method($value);
        }

        $errors = $this->validator->validate($entity);

        if (0 < count($errors)) {
            throw new ValidationException($errors);
        }

        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (ORMException $e) {
            throw new PersistenceException(self::ERROR_COULD_NOT_CREATE);
        }

        return $entity;
    }
}
