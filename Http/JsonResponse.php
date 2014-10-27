<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace GoIntegro\Bundle\HateoasBundle\Http;

// HTTP.
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

class JsonResponse extends SymfonyJsonResponse
{
    /**
     * For the time being, it's the only supported type.
     */
    const HATEOAS_CONTENT_TYPE = 'application/vnd.api+json';

    /**
     * @see SymfonyHttpResponse::__construct
     */
    public function __construct($data = NULL, $status = 200, $headers = [])
    {
        parent::__construct($data, $status, $headers);
        $this->headers->set('Content-Type', static::HATEOAS_CONTENT_TYPE);

        // Keeps the data NULL if NULL it is.
        $this->setData($data);
    }
}
