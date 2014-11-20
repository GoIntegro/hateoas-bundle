<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Util;

// ORM.
use Doctrine\ORM\QueryBuilder;

interface FilterExpression
{
    /**
     * @param QueryBuilder $qb
     * @param array $filters
     * @param string $alias
     * @return QueryBuilder
     */
    public function filter(QueryBuilder $qb, array $filters, $alias = 'e');
}
