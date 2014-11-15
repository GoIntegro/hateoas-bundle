<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// ORM.
use Doctrine\ORM\EntityManagerInterface;

class ParamEntityFinder
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param Params $params
     * @return array
     */
    public function find(Params $params)
    {
        if (empty($params->primaryClass)) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        $entities = $this->em
            ->getRepository($params->primaryClass)
            ->findById($params->primaryIds);

        if (
            empty($entities)
            || count($entities) !== count($params->primaryIds)
        ) {
            throw new NotFoundHttpException(self::ERROR_RESOURCE_NOT_FOUND);
        }

        return $entities;
    }
}
