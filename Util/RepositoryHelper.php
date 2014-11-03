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
use GoIntegro\Bundle\HateoasBundle\Collections\Paginated
    as PaginatedCollection;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Params;

/**
 * An abstract controller that custom JSON-API controllers can extend.
 * @todo Make it a trait?
 */
class RepositoryHelper
{
    use SimpleQueryExpressions;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Helper method to paginate a query using the HATEOAS request parameters.
     * @param Params $request
     * @return PaginatedCollection
     */
    public function findByRequestParams(Params $params)
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
        $offset = Params::DEFAULT_PAGE_OFFSET,
        $limit = Params::DEFAULT_PAGE_SIZE
    )
    {
        $qb = $this->entityManager
            ->getRepository($entityClass)
            ->createQueryBuilder('e')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($expr = $this->filtersToExpression($qb, $filters, 'e')) {
            $qb->where($expr);
        }

        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        $collection = new PaginatedCollection($paginator);

        return $collection;
    }
}
