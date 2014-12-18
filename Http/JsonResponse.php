<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 * @author Menzo Wijmenga <menzow@GitHub>
 */

namespace GoIntegro\Hateoas\Http;

// HTTP.
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;
// JSON-API.
use GoIntegro\Hateoas\JsonApi\JsonApiSpec;

class JsonResponse extends SymfonyJsonResponse
{
    /**
     * @todo Remove when Symfony 2.7 LTS is released.
     */
    const HTTP_OK = 200,
        HTTP_CREATED = 201,
        HTTP_NO_CONTENT = 204;

    /**
     * @see SymfonyHttpResponse::__construct
     */
    public function __construct($data = NULL, $status = 200, $headers = [])
    {
        parent::__construct($data, $status, $headers);
        $this->headers->set('Content-Type', JsonApiSpec::HATEOAS_CONTENT_TYPE);

        // Keeps the data NULL if NULL it is.
        $this->setData($data);
    }
}
