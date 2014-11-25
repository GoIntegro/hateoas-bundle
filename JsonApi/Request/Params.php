<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi\Request;

// Resources.
use GoIntegro\Bundle\HateoasBundle\JsonApi\DocumentResource;
// ORM.
use Doctrine\Common\Collections\Criteria;

/**
 * @see http://jsonapi.org/format/#fetching
 */
class Params
{
    const ASCENDING_ORDER = Criteria::ASC,
        DESCENDING_ORDER = Criteria::DESC,
        DEFAULT_PAGE_OFFSET = 0,
        DEFAULT_PAGE_SIZE = 10;

    /**
     * @var string The clean JSON-API request path.
     */
    public $path;
    /**
     * @var string
     * @see http://jsonapi.org/format/#urls-reference-document
     */
    public $primaryType;
    /**
     * @var string
     */
    public $primaryClass;
    /**
     * @var array
     * @see http://jsonapi.org/format/#urls-individual-resources
     */
    public $primaryIds = [];
    /**
     * @var string
     * @see http://jsonapi.org/format/#urls-relationships
     */
    public $relationship;
    /**
     * @var array
     * @see http://jsonapi.org/format/#crud-updating-to-many-relationships
     */
    public $relationshipIds = [];
    /**
     * @var array
     * @see http://jsonapi.org/format/#fetching-includes
     */
    public $include = [];
    /**
     * @var array
     * @see http://jsonapi.org/format/#fetching-sparse-fieldsets
     */
    public $sparseFields = [];
    /**
     * @var array
     * @see http://jsonapi.org/format/#fetching-sorting
     */
    public $sorting = [];
    /**
     * @var DocumentPagination Yes, it's "pagination", not "paging".
     */
    public $pagination;
    /**
     * @var array
     * @see http://jsonapi.org/format/#fetching-filtering
     */
    public $filters = [];
    /**
     * @var array
     */
    public $resources = [];
    /**
     * @var array
     */
    public $entities = [];
    /**
     * @var RequestAction
     */
    public $action;

    /**
     * @param string $field
     * @param string $relationship
     * @return string
     */
    public function getSortOrder($field, $relationship = NULL)
    {
        $sortedByRelationship = NULL;

        if (empty($relationship)) {
            $relationship = $this->primaryType;
        }

        if (isset($this->sorting[$relationship])) {
            foreach ($this->sorting[$relationship] as $sorting => $order) {
                if ($field == $sorting) {
                    $sortedByRelationship = $order;
                    break;
                }
            }
        }

        return $sortedByRelationship;
    }

    /**
     * @param integer $default
     * @return integer
     */
    public function getPageOffset($default = self::DEFAULT_PAGE_OFFSET)
    {
        return !empty($this->pagination)
            ? $this->pagination->offset
            : $default;
    }

    /**
     * @param integer $default
     * @return integer
     */
    public function getPageSize($default = self::DEFAULT_PAGE_SIZE)
    {
        return !empty($this->pagination)
            ? $this->pagination->size
            : $default;
    }
}
