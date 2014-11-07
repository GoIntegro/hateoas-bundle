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

/**
 * @ORM\Entity
 * @ORM\Table
 */
class User implements ResourceEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=TRUE)
     */
    protected $surname;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(
     *   targetEntity="HateoasInc\Bundle\ExampleBundle\Entity\User"
     * )
     */
    protected $followers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->followers = new ArrayCollection();
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
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set surname
     *
     * @param string $surname
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Add followers
     *
     * @param \HateoasInc\Bundle\ExampleBundle\Entity\User $followers
     * @return User
     */
    public function addFollower(\HateoasInc\Bundle\ExampleBundle\Entity\User $followers)
    {
        $this->followers[] = $followers;

        return $this;
    }

    /**
     * Remove followers
     *
     * @param \HateoasInc\Bundle\ExampleBundle\Entity\User $followers
     */
    public function removeFollower(\HateoasInc\Bundle\ExampleBundle\Entity\User $followers)
    {
        $this->followers->removeElement($followers);
    }

    /**
     * Get followers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFollowers()
    {
        return $this->followers;
    }
}
