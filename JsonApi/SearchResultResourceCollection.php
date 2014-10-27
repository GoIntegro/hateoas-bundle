<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Búsqueda.
use GoIntegro\Bundle\HateoasBundle\Search\FacetedSearchResult as SearchResult;
// Colecciones.
use GoIntegro\Bundle\HateoasBundle\Collections\Paginated;

/**
 * @see http://www.solarium-project.org/documentation/
 */
class SearchResultResourceCollection
    extends ResourceCollection
    implements Paginated
{
    /**
     * @var SearchResult El resultado de búsqueda correspondiente.
     */
    private $searchResult;

    /**
     * @param SearchResult $searchResult
     * @return self
     */
    public function setSearchResult(SearchResult $searchResult)
    {
        $this->searchResult = $searchResult;

        return $this;
    }

    /**
     * @return SearchResult
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }

    /**
     * @see Paginated::total
     */
    public function total()
    {
        return count($this->searchResult);
    }
}
