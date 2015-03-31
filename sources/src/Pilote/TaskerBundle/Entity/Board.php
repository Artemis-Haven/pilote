<?php

namespace Pilote\TaskerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pilote\MessageBundle\Entity\Thread;

/**
 * Board
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Pilote\TaskerBundle\Entity\BoardRepository")
 */
class Board
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="creationDate", type="date")
     */
    private $creationDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;

    /**
     * @var string
     *
     * @ORM\Column(name="theme", type="string", length=255, nullable=true)
     */
    private $theme;

    /**
     * @ORM\OneToMany(targetEntity="Pilote\TaskerBundle\Entity\Domain", mappedBy="board", cascade={"persist", "remove"})
     */
    private $domains; 

    /**
     * @ORM\ManyToMany(targetEntity="Pilote\UserBundle\Entity\User", mappedBy="boards")
     */
    private $users;

    /**
     * @ORM\OneToOne(targetEntity="Pilote\MessageBundle\Entity\Thread", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="thread_id", referencedColumnName="id", nullable=false)
     */
    private $thread;


    public function addDomain(\Pilote\TaskerBundle\Entity\Domain $domain)
    {
        $this->domains[] = $domain;
        $domain->setBoard($this); 
        return $this;
    }

    public function removeDomain(\Pilote\TaskerBundle\Entity\Domain $domain)
    {
        $this->domains->removeElement($domain);
    }



    public function addUser(\Pilote\UserBundle\Entity\User $user)
    {
        $this->users[] = $user;
        return $this;
    }

    public function removeUser(\Pilote\UserBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
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
     * Set name
     *
     * @param string $name
     * @return Board
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
     * Set description
     *
     * @param string $description
     * @return Board
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Board
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime 
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Board
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set theme
     *
     * @param string $theme
     * @return Board
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return string 
     */
    public function getTheme()
    {
        return $this->theme;
    }

    public function __toString() {
        return $this->getName();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->domains = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->creationDate = new \DateTime('now');
        $this->enabled = true;
        $l1 = new TList();
        $l1->setName("A faire");
        $l2 = new TList();
        $l2->setName("En cours");
        $l3 = new TList();
        $l3->setName("Fait");

        $s = new Step();
        $s->addTList($l1, 0);
        $s->addTList($l2, 1);
        $s->addTList($l3, 2);
        $d = new Domain();
        $d->addStep($s);
        $this->addDomain($d);
    }

    /**
     * Get domains
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set thread
     *
     * @param string $thread
     * @return Board
     */
    public function setThread($thread)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Get thread
     *
     * @return Thread 
     */
    public function getThread()
    {
        return $this->thread;
    }
}
