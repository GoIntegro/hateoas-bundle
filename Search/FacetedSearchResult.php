<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Search;

// Array.
use Countable, IteratorAggregate;

/**
 * @see https://en.wikipedia.org/wiki/Faceted_search
 * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-facets.html
 */
interface FacetedSearchResult extends Countable, IteratorAggregate
{
    /**
     * @return string The original query.
     */
    public function getQuery();

    /**
     * @return string The time it took to complete the query.
     */
    public function getQueryTime();

    /**
     * @return array A JSON-serializable list of matching facets.
     */
    public function getFacets();

    /**
     * @return array A list of entities found by the matching indexes.
     */
    public function getEntities();
}
