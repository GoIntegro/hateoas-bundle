<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Controller;

// Controladores.
use GoIntegro\Bundle\HateoasBundle\Http\JsonResponse;

/**
 * An abstract controller that custom JSON-API controllers can extend.
 */
trait CommonResponseTrait
{
    /**
     * @param mixed $content
     * @param integer $status
     * @param array $headers
     * @return JsonResponse
     * @see http://jsonapi.org/format/#http-caching
     */
    protected function createETagResponse(
        $content, $status = JsonResponse::HTTP_OK, array $headers = []
    )
    {
        $response = new JsonResponse($content, $status, $headers);
        $response->setETag(md5($response->getContent()));
        $response->setPublic();
        $response->isNotModified($this->getRequest());

        return $response;
    }

    /**
     * @param mixed $content
     * @param integer $status
     * @param array $headers
     * @return JsonResponse
     */
    protected function createNoCacheResponse(
        $content, $status = JsonResponse::HTTP_OK, array $headers = []
    )
    {
        $response = new JsonResponse($content, $status, $headers);
        $response->headers->set(
            'Cache-Control',
            'no-cache, no-store, must-revalidate'
        );
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
