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
// ACL.
use Symfony\Component\Security\Acl\Domain\ObjectIdentity,
    Symfony\Component\Security\Acl\Domain\UserSecurityIdentity,
    Symfony\Component\Security\Acl\Permission\MaskBuilder;
// Entidades.
use HateoasInc\Bundle\ExampleBundle\Entity\User;

/**
 * La API privada de usuarios - require autenticación.
 */
class UsersController extends Controller
{
    /**
     * @Route("/users", name="api_create_users", methods="POST")
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
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('HateoasInc\Bundle\ExampleBundle\Entity\User')
            ->findOneBy([]);
        $post = new Post;
        $post->setAuthor($user);
        $post->setContent($data['posts']['content']);
        $errors = $this->get('validator')->validate($post);

        if (0 < count($errors)) {
            throw new BadRequestHttpException($errors);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($post);
        $em->flush();

        // creating the ACL
        $aclProvider = $this->get('security.acl.provider');
        $objectIdentity = ObjectIdentity::fromDomainObject($post);
        $acl = $aclProvider->createAcl($objectIdentity);
        // retrieving the security identity of the currently logged-in user
        $securityContext = $this->get('security.context');
        // $user = $securityContext->getToken()->getUser();
        $securityIdentity = UserSecurityIdentity::fromAccount($user);
        // grant owner access
        $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($acl);

        $resource = $this->get('hateoas.resource_manager')
            ->createResourceFactory()
            ->setEntity($post)
            ->create();
        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setDocumentResources($resource)
            ->create()
            ->serialize();

        return $this->createETagResponse($json, Response::HTTP_CREATED);
    }

    /**
     * @Route("/users/{user}", name="api_update_users", methods="PUT")
     */
    public function updateAction(User $user)
    {
        if (!$this->get('security.context')->isGranted('edit', $post)) {
            throw new AccessDeniedHttpException('Unauthorized access!');
        }

        $rawBody = $this->getRequest()->getContent();

        if (!$this->get('hateoas.json_coder')->matchSchema(
            $rawBody, __DIR__ . self::POSTS_SCHEMA
        )) {
            $message = $this->get('hateoas.json_coder')
                ->getSchemaErrorMessage();
            throw new BadRequestHttpException($message);
        }

        $data = $this->get('hateoas.json_coder')->decode($rawBody);
        $post->setContent($data['posts']['content']);
        $errors = $this->get('validator')->validate($post);

        if (0 < count($errors)) {
            throw new BadRequestHttpException($errors);
        }

        $em = $this->get('doctrine.orm.entity_manager');
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
