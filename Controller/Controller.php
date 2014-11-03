<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Controller;

// Controladores.
use Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController;
// Request.
use GoIntegro\Bundle\HateoasBundle\JsonApi\Request\Params;

/**
 * An abstract controller that custom JSON-API controllers can extend.
 */
abstract class Controller extends SymfonyController
{
    const DEFAULT_RESOURCE_LIMIT = 50;

    use CommonResponseTrait;

    /**
     * @param Params $params
     * @return \GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceDocument
     */
    public function getResourcesFromRepo(Params $params)
    {
        $entities = $this->get('hateoas.repo_helper')
            ->findByRequestParams($params);

        if (self::DEFAULT_RESOURCE_LIMIT < count($entities)) {
            throw new DocumentTooLargeHttpException;
        }

        $resources = $this->get('hateoas.resource_manager')
            ->createCollectionFactory()
            ->setPaginator($entities->getPaginator())
            ->create();

        return $resources;
    }
}
