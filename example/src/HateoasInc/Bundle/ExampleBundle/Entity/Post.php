<?php
/**
 * @copyright 2014 Integ S.A.
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @author Javier Lorenzana <javier.lorenzana@gointegro.com>
 */

namespace HateoasInc\Bundle\ExampleBundle\Entity;

// ORM.
use Doctrine\ORM\Mapping as ORM,
    Doctrine\Common\Collections\ArrayCollection;
// HATEOAS.
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface,
    GoIntegro\Bundle\HateoasBundle\Entity\AuthorIsOwner;
// Validation.
use Symfony\Component\Validator\Constraints as Assert;
// Security.
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class Post implements ResourceEntityInterface, AuthorIsOwner
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @var ArrayCollection
     * @ORM\JoinColumn(nullable=FALSE)
     * @ORM\ManyToOne(
     *   targetEntity="HateoasInc\Bundle\ExampleBundle\Entity\User"
     * )
     * @Assert\NotBlank()
     */
    protected $owner;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(
     *   targetEntity="HateoasInc\Bundle\ExampleBundle\Entity\Comment",
     *   mappedBy="subject"
     * )
     */
    protected $comments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set owner
     *
     * @param \HateoasInc\Bundle\ExampleBundle\Entity\User $owner
     * @return self
     */
    public function setOwner(UserInterface $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \HateoasInc\Bundle\ExampleBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Add comments
     *
     * @param \HateoasInc\Bundle\ExampleBundle\Entity\Comment $comments
     * @return self
     */
    public function addComment(\HateoasInc\Bundle\ExampleBundle\Entity\Comment $comments)
    {
        $this->comments[] = $comments;

        return $this;
    }

    /**
     * Remove comments
     *
     * @param \HateoasInc\Bundle\ExampleBundle\Entity\Comment $comments
     */
    public function removeComment(\HateoasInc\Bundle\ExampleBundle\Entity\Comment $comments)
    {
        $this->comments->removeElement($comments);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }
}
