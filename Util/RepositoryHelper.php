<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Util;

// ORM.
use Doctrine\ORM\EntityManagerInterface;
// Paginadores
use Doctrine\ORM\Tools\Pagination\Paginator;
// Colecciones
use GoIntegro\Bundle\HateoasBundle\Collections\PaginatedCollection;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

class RepositoryHelper
{
    const RESOURCE_ENTITY_INTERFACE = 'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\ResourceEntityInterface';

    const ERROR_DUPLICATED_FILTER = "A filter called \"%s\" is already registered for the resource type \"%s\".";

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var array
     */
    private $filters = [];

    /**
     * @param EntityManagerInterface
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Helper method to paginate a query using the HATEOAS request parameters.
     * @param Request\Params $request
     * @return PaginatedCollection
     */
    public function findByRequestParams(Request\Params $params)
    {
        return $this->findPaginated(
            $params->primaryClass,
            $params->filters,
            $params->getPageOffset(),
            $params->getPageSize()
        );
    }

    /**
     * Helper method to paginate "find by" queries.
     * @param string $entityClass
     * @param array $filters
     * @param integer $offset
     * @param integer $limit
     * @return PaginatedCollection
     */
    public function findPaginated(
        $entityClass,
        array $filters,
        $offset = Request\Params::DEFAULT_PAGE_OFFSET,
        $limit = Request\Params::DEFAULT_PAGE_SIZE
    )
    {
        $qb = $this->entityManager
            ->getRepository($entityClass)
            ->createQueryBuilder('e')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        // if ($expr = $this->filtersToExpression($qb, $filters, 'e')) {
        //     $qb->where($expr);
        // }

        foreach ($this->filters as $filter) {
            $class = $filter->getClass();

            if (
                $entityClass === $class
                || is_subclass_of($entityClass, $class)
            ) {
                $qb = $filter->filter($qb);
            }
        }

        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        $collection = new PaginatedCollection($paginator);

        return $collection;
    }

    /**
     * @param Request\FilterInterface $filter
     * @param string $class
     */
    public function addFilter(
        Request\FilterInterface $filter,
        $class = self::RESOURCE_ENTITY_INTERFACE
    )
    {
        if (isset($this->filters[$class])) {
            $message = sprintf(self::ERROR_DUPLICATED_FILTER, $class);
            throw new \ErrorException($message);
        }

        $this->filters[$class] = $filter;
    }
}
