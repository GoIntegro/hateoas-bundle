<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Controller;

// Controladores.
use Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
// HTTP.
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException,
    Symfony\Component\HttpFoundation\JsonResponse;
// JSON-API.
use GoIntegro\Hateoas\JsonApi\Merge\UnmergeableResourcesException;

/**
 * Permite obtener muchas URLs de la API de una.
 */
class MultiController extends SymfonyController
{
    use CommonResponseTrait;

    /**
     * @Route("/multi", name="hateoas_multi_get", methods="GET")
     * @todo Integrar las respuestas de verdad, no con "array_merge_recursive".
     */
    public function multiGetAction()
    {
        $blend = [];

        foreach ($this->getRequest()->query->get('url') as $url) {
            $json = $this->get('templating.helper.actions')
                ->render(urldecode($url));
            $json = $this->get('hateoas.json_coder')->decode($json);

            try {
                $blend = $this->get('hateoas.document_blender')
                    ->merge($blend, $json);
            } catch (UnmergeableResourcesException $e) {
                throw new BadRequestHttpException($e->getMessage(), $e);
            }
        }

        return $this->createNoCacheResponse($blend);
    }
}
