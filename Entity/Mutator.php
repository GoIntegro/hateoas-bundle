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
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Parser,
    GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Validator.
use Symfony\Component\Validator\Validator\ValidatorInterface,
    GoIntegro\Bundle\HateoasBundle\Entity\Validation\ValidationException;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;

class Mutator
{
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
     * @param SecurityContextInterface $securityContext
     * @param ValidatorInterface $validator
     * @param Parser $parser
     */
    public function __construct(
        EntityManagerInterface $em,
        SecurityContextInterface $securityContext,
        ValidatorInterface $validator,
        Parser $parser
    )
    {
        $this->em = $em;
        $this->securityContext = $securityContext;
        $this->validator = $validator;
        $this->parser = $parser;
    }

    /**
     * @param ResourceEntityInterface $entity
     * @param array $data
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface
     * @todo No params, just the parser?
     * @todo Replace the HTTP bad request exception.
     */
    public function update(ResourceEntityInterface $entity, array $data)
    {
        $params = $this->parser->parse();
        $class = new \ReflectionClass($params->primaryClass);

        // @todo Mover al parser.
        foreach ($data[$params->primaryType] as $field => $value) {
            if ('links' == $field) continue;

            $method = 'set' . Inflector::camelize($field);

            if ($class->hasMethod($method)) $entity->$method($value);
        }

        $errors = $this->validator->validate($entity);

        if (0 < count($errors)) {
            throw new ValidationException($errors);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
