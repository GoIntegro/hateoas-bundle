<?php

namespace GoIntegro\Bundle\Api2SampleBundle\Rest2\Controller;

// Controladores.
use GoIntegro\Bundle\HateoasBundle\Controller\Controller,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
// Entidades.
use GoIntegro\Entity\User;

/**
 * La API privada de usuarios - require autenticación.
 */
class UsersController extends Controller
{
    /**
     * @Route("/users/{user}", name="api2_get_user", methods="GET")
     */
    public function getUserAction(User $user)
    {
        $resource = $this->get('hateoas.resource_manager')
            ->createResourceFactory()
            ->setEntity($user)
            ->create();
        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setDocumentResources($resource)
            ->addMeta(["hum"])
            ->create()
            ->serialize();

        // Get the document as an array and output it as JSON
        return $this->createETagResponse($json);
    }

    /**
     * @Route("/users", name="api2_get_users", methods="GET")
     */
    public function getUsersAction()
    {
        $users = $this->getDoctrine()
            ->getManager()
            ->getRepository('GoIntegro\Entity\User')
            ->findByPlatform($this->getUser()->getPlatform());
        $resources = $this->get('hateoas.resource_manager')
            ->createCollectionFactory()
            ->addEntities($users)
            ->create();
        $json = $this->get('hateoas.resource_manager')
            ->createSerializerFactory()
            ->setDocumentResources($resources)
            ->create()
            ->serialize();

        // Get the document as an array and output it as JSON
        return $this->createETagResponse($json);
    }
}
