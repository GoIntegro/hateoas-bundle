<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace HateoasInc\Bundle\ExampleBundle\Controller;

// Controladores.
use GoIntegro\Bundle\HateoasBundle\Controller\Controller,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
// HTTP.
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
// Entidades.
use HateoasInc\Bundle\ExampleBundle\Entity\User,
    HateoasInc\Bundle\ExampleBundle\Entity\Post;

/**
 * @todo La búsqueda devuelve vacío con menos de cuatro caracteres.
 */
class PostsController extends Controller
{
    /**
     * @Route("/posts", name="api_get_posts", methods="GET")
     */
    public function getAllAction()
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
     * @Route("/posts", name="api_create_posts", methods="POST")
     */
    public function createAction()
    {
        // @todo Access control here.

        $rawBody = $this->getRequest()->getContent();

        if ($this->get('json')->matchSchema(
            $rawBody, __DIR__ . self::POST_SCHEMA
        )) {
            throw new BadRequestHttpException("This is a test.");
        }

        $data = $this->get('json')->decode($rawBody);

        $params = $this->get('hateoas.request_parser')->parse();

        /* Variable. */ $le = $params; if (!isset($lb)) $lb = false; $lp = 'file:///tmp/skqr.log'; if (!isset($_ENV[$lp])) $_ENV[$lp] = 0; $le = var_export($le, true); error_log(sprintf("%s/**\n * %s\n * %s\n * %s\n */\n\$params = %s;\n\n", $lb ? '' : str_repeat('=', 14) . ' ' . ++$_ENV[$lp] . gmdate(' r ') . str_repeat('=', 14) . "\n", microtime(true), basename(__FILE__) . ':' . __LINE__, __METHOD__ ? __METHOD__ . '()' : '', $le), 3, $lp); if (!$lb) $lb = true; // Javier Lorenzana <javier.lorenzana@gointegro.com>
        $user = new User;
        // $user->setEmail('default@gmail.com');
        // $user->setPassword('sup3rs3cr3t');
        $post = new Post;
        // $post->setAuthor($user);
        // $post->setContent("");

        if ($errors = $this->get('validator')->validate($post)) {
            throw new BadRequestHttpException($errors);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($user);
        $em->persist($post);
        $em->flush();

        $resource = $this->get('hateoas.resource_manager')
            ->createResourceFactory()
            ->setEntity($post)
            ->create();
        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setDocumentResources($resource)
            ->create()
            ->serialize();

        return $this->createETagResponse($json);
    }
}
