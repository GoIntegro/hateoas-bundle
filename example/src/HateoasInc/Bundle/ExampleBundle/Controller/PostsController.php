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
    const POSTS_SCHEMA = '/../Resources/raml/posts.schema.json';

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

        if (!$this->get('hateoas.json_coder')->matchSchema(
            $rawBody, __DIR__ . self::POSTS_SCHEMA
        )) {
            $message = $this->get('hateoas.json_coder')
                ->getSchemaErrorMessage();
            throw new BadRequestHttpException($message);
        }

        $data = $this->get('hateoas.json_coder')->decode($rawBody);

        $params = $this->get('hateoas.request_parser')->parse();

        $user = new User;
        $user->setEmail('default@gmail.com');
        $user->setPassword('sup3rs3cr3t');
        $post = new Post;
        $post->setAuthor($user);
        $post->setContent($data['posts']['content']);
        $errors = $this->get('validator')->validate($post);

        if (0 < count($errors)) {
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
