<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\Util;

// ORM.
use Doctrine\ORM\QueryBuilder;

trait SimpleQueryExpressions
{
    /**
     * @param QueryBuilder $qb
     * @param array $filters
     * @param string $alias
     * @return \Doctrine\ORM\Query\Expr\Base
     */
    private function filtersToExpression(
        QueryBuilder $qb, array $filters, $alias = 'e'
    ) {
        $expressions = [];

        foreach ($filters as $type => $filters) {
            foreach ($filters as $field => $value) {
                $holder = ':' . $field;
                $expr = is_array($value) ? 'in' : 'eq';
                $namespace = $alias . '.' . $field;

                if ('association' == $type) {
                    $qb->join($namespace, $field);
                    $namespace = $field . '.id';
                } elseif ('field' != $type) {
                    throw new \Exception("Tipo de filtro desconocido.");
                }

                $expressions[] = $qb->expr()->$expr($namespace, $holder);
                $qb->setParameter($field, $value);
            }
        }

        return !empty($expressions)
            ? call_user_method_array('andX', $qb->expr(), $expressions)
            : NULL;
    }
}
