<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// ORM.
use Doctrine\ORM\QueryBuilder;

class DefaultFilter implements FilterInterface
{
    /**
     * @see FilterInterface::supportsClass
     */
    public function supportsClass($class)
    {
        return is_a($class, 'GoIntegro\\Bundle\\HateoasBundle\\JsonApi\\ResourceEntityInterface', TRUE);
    }

    /**
     * @see FilterInterface::filter
     */
    public function filter(
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
                    continue;
                }

                $expressions[] = $qb->expr()->$expr($namespace, $holder);
                $qb->setParameter($field, $value);
            }
        }

        if (!empty($expressions)) {
            $criteria = call_user_func_array(
                [$qb->expr(), 'andX'], $expressions
            );
            $qb->andWhere($criteria);
        }

        return $qb;
    }
}
