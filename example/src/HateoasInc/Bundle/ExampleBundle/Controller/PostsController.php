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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException,
    Symfony\Component\HttpKernel\Exception\BadRequestHttpException,
    Symfony\Component\HttpFoundation\Response;
// Entidades.
use HateoasInc\Bundle\ExampleBundle\Entity\User,
    HateoasInc\Bundle\ExampleBundle\Entity\Post;
// ACL.
use Symfony\Component\Security\Acl\Domain\ObjectIdentity,
    Symfony\Component\Security\Acl\Domain\UserSecurityIdentity,
    Symfony\Component\Security\Acl\Permission\MaskBuilder;

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

        foreach ($posts as $post) {
            if (!$this->get('security.context')->isGranted('view', $post)) {
                throw new AccessDeniedHttpException('Unauthorized access!');
            }
        }

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
     * @Route("/posts/{post}", name="api_get_post", methods="GET")
     */
    public function getOneAction(Post $post)
    {
        if (!$this->get('security.context')->isGranted('view', $post)) {
            throw new AccessDeniedHttpException('Unauthorized access!');
        }

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
}
