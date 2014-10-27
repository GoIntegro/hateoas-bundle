<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Paginación.
use Doctrine\ORM\Tools\Pagination\Paginator;
// Colecciones.
use GoIntegro\Bundle\HateoasBundle\Collections\Paginated;

/**
 * @see http://doctrine-orm.readthedocs.org/en/latest/tutorials/pagination.html
 */
class PaginatedResourceCollection
    extends ResourceCollection
    implements Paginated
{
    /**
     * @var Paginator El paginador correspondiente.
     */
    private $paginator;

    /**
     * Define el paginador.
     * @param Paginator $paginator
     * @return self
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Obtiene el paginador.
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * @see Paginated::total
     */
    public function total()
    {
        return count($this->getPaginator());
    }
}
