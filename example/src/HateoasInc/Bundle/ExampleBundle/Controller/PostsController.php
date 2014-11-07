<?php

namespace GoIntegro\Bundle\Api2SampleBundle\Rest2\Controller;

// Controladores.
use GoIntegro\Bundle\HateoasBundle\Controller\Controller,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
// HTTP.
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo La búsqueda devuelve vacío con menos de cuatro caracteres.
 */
class PostsController extends Controller
{
    /**
     * @Route("/posts", name="api2_get_posts", methods="GET")
     */
    public function getAllAction(Request $request)
    {
        $params = $this->get('hateoas.request_parser')->parse();
        $posts = $this->get('hateoas.repo_helper')
            ->findByRequestParams($params);
        $resources = $this->get('hateoas.resource_manager')
            ->createCollectionFactory()
            ->setPaginator($posts->getPaginator())
            ->create();
        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setDocumentResources($resources)
            ->create()
            ->serialize();

        return $this->createETagResponse($json);
    }

    /**
     * @Route("/posts", name="api2_post_posts", methods="POST")
     */
    public function createAction(Request $request)
    {
        $params = $this->get('hateoas.request_parser')->parse();

        /* Variable. */ $le = $params; if (!isset($lb)) $lb = false; $lp = 'file:///tmp/skqr.log'; if (!isset($_ENV[$lp])) $_ENV[$lp] = 0; $le = var_export($le, true); error_log(sprintf("%s/**\n * %s\n * %s\n * %s\n */\n\$params = %s;\n\n", $lb ? '' : str_repeat('=', 14) . ' ' . ++$_ENV[$lp] . gmdate(' r ') . str_repeat('=', 14) . "\n", microtime(true), basename(__FILE__) . ':' . __LINE__, __METHOD__ ? __METHOD__ . '()' : '', $le), 3, $lp); if (!$lb) $lb = true; // Javier Lorenzana <javier.lorenzana@gointegro.com>
        $post = new Post;
        $em = $this->get('doctrine.entity_manager');
        $em->persist($post);
        $em->flush();

        $resource = $this->get('hateoas.resource_manager')
            ->createResourceFactory()
            ->setPaginator($post)
            ->create();
        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setDocumentResources($resource)
            ->create()
            ->serialize();

        return $this->createETagResponse($json);
    }
}
