<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Security.
use Symfony\Component\Security\Core\SecurityContextInterface;

class ParamEntityFinder
{
    const ERROR_ACCESS_DENIED = "Access to the resource was denied.",
        ERROR_RESOURCE_NOT_FOUND = "The resource was not found.";

    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param EntityManagerInterface $em
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        EntityManagerInterface $em,
        SecurityContextInterface $securityContext
    )
    {
        $this->em = $em;
        $this->securityContext = $securityContext;
    }

    /**
     * @param Params $params
     * @return array
     */
    public function find(Params $params)
    {
        if (empty($params->primaryClass)) {
            throw new EntityNotFoundException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $entities = $this->em
            ->getRepository($params->primaryClass)
            ->findById($params->primaryIds);

        foreach ($entities as $entity) {
            if ($this->securityContext->isGranted('view', $entity)) {
                throw new EntityAccessDeniedException(
                    self::ERROR_ACCESS_DENIED
                );
            }
        }

        if (
            empty($entities)
            || count($entities) !== count($params->primaryIds)
        ) {
            throw new EntityNotFoundException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        return $entities;
    }
}
