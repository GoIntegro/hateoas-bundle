<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\Search;

// Solr.
use Solarium\QueryType\Select\Result\Result;

/**
 * @see https://github.com/nelmio/NelmioSolariumBundle
 * @see www.solarium-project.org/documentation/
 */
class SolrSearchResult implements FacetedSearchResult
{
    /**
     * @var string
     */
    private $query;
    /**
     * @var Result
     */
    private $result;
    /**
     * @var array
     */
    private $facets;
    /**
     * @var array
     */
    private $entities;

    /**
     * @param string $query
     * @param Result $result
     * @param array $facets
     * @param array $entities
     */
    public function __construct(
        $query, Result $result, array $facets, array $entities
    )
    {
        $this->query = $query;
        $this->result = $result;
        $this->facets = $facets;
        $this->entities = $entities;
    }

    /**
     * @see FacetedSearchResult::getQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @see FacetedSearchResult::getQueryTime
     */
    public function getQueryTime()
    {
        return $this->result->getQueryTime();
    }

    /**
     * @see FacetedSearchResult::getFacets
     */
    public function getFacets()
    {
        return $this->facets;
    }

    /**
     * @see FacetedSearchResult::getEntities
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @see Countable::count
     * @todo Esta es la implementación original. Refactor.
     */
    public function count()
    {
        $grouping = $this->result->getGrouping();
        $numFound = $this->result->getNumFound();

        if (empty($numFound) && !empty($grouping)) {
            $groups = $grouping->getGroups();
            $fieldGroup = reset($groups);
            $numFound = $fieldGroup->getNumberOfGroups();
        }

        return $numFound;
    }

    /**
     * @see IteratorAggregate::getIterator
     */
    public function getIterator()
    {
        return $this->result->getIterator();
    }
}
