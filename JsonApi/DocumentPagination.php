<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\JsonApi;

// Colecciones.
use GoIntegro\Bundle\HateoasBundle\Collections\Paginated;

class DocumentPagination
{
    const COUNT_PAGES_FROM = 1;

    /**
     * @var integer La cantidad de items en total.
     */
    public $total;
    /**
     * @var integer La cantidad de items por página (salvo quizás la última).
     */
    public $size;
    /**
     * @var integer La página acutal.
     */
    public $page;
    /**
     * @var integer El offset del primer item de la página actual.
     */
    public $offset;
    /**
     * @var string
     */
    public $paginationlessUrl;

    /**
     * @param Paginated $collection
     */
    public function fill(Paginated $collection)
    {
        $this->total = $collection->total();
    }
}
