<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace HateoasInc\Bundle\ExampleBundle\DataFixtures\ORM;

// ORM.
use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\Persistence\ObjectManager,
    Doctrine\Common\Collections\ArrayCollection;
// Entities.
use HateoasInc\Bundle\ExampleBundle\Entity\User,
    HateoasInc\Bundle\ExampleBundle\Entity\Post,
    HateoasInc\Bundle\ExampleBundle\Entity\Comment;
// DI.
use Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

class SocialDataFixture
    extends AbstractFixture
    implements ContainerAwareInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = NULL)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $user = new User;
        $user->setEmail("this.guy@gmail.com");
        $user->setPassword("cl34rt3xt1sn0tf0rp4ssw0rds");

        $post = new Post;
        $post->setContent("Check this bundle out. #RockedMyWorld");
        $post->setAuthor($user);

        $comment = new Comment;
        $comment->setContent("Mine too. #RockedMyWorld");
        $comment->setAuthor($user);
        $post->addComment($comment);

        $this->manager->persist($user);
        $this->manager->persist($post);
        $this->manager->persist($comment);

        $manager->flush();
    }
}
