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
use GoIntegro\Bundle\HateoasBundle\JsonApi\ResourceEntityInterface;
// Validation.
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class Comment implements ResourceEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @var ArrayCollection
     * @ORM\ManyToOne(
     *   targetEntity="HateoasInc\Bundle\ExampleBundle\Entity\User"
     * )
     * @Assert\NotBlank()
     */
    protected $author;

    /**
     * @var ArrayCollection
     * @ORM\ManyToOne(
     *   targetEntity="HateoasInc\Bundle\ExampleBundle\Entity\Post"
     * )
     */
    protected $subject;

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
     * @return Comment
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
     * Set author
     *
     * @param \HateoasInc\Bundle\ExampleBundle\Entity\User $author
     * @return Comment
     */
    public function setAuthor(\HateoasInc\Bundle\ExampleBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \HateoasInc\Bundle\ExampleBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set subject
     *
     * @param \HateoasInc\Bundle\ExampleBundle\Entity\Post $subject
     * @return Comment
     */
    public function setSubject(\HateoasInc\Bundle\ExampleBundle\Entity\Post $subject = null)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return \HateoasInc\Bundle\ExampleBundle\Entity\Post
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
