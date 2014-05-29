<?php

namespace PIL\TaskerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Task
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PIL\TaskerBundle\Entity\TaskRepository")
 */
class Task
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
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="StartDate", type="date")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="EndDate", type="date")
     */
    private $endDate;
    
    /**
     * @ORM\ManyToOne(targetEntity="PIL\TaskerBundle\Entity\TList", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tList;
    
    /**
     * @ORM\ManyToOne(targetEntity="PIL\USerBundle\Entity\User", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * @ORM\OneToMany(targetEntity="PIL\TaskerBundle\Entity\HasCommented", mappedBy="task")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="PIL\TaskerBundle\Entity\CheckList", mappedBy="task")
     */
    private $checkLists;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;
    

    /**
     * Constructeur de la classe Task.
     */
    public function __construct()
    {
        $this->name = "Nouvelle tâche";
        $this->content = "";
        $this->startDate = new \DateTime("now");
        $this->endDate = new \DateTime("now");
        $this->position = 0;
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
     * @return Task
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
     * Set content
     *
     * @param string $content
     * @return Task
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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Task
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Task
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }


    /**
     * Set tList
     *
     * @param \PIL\TaskerBundle\Entity\TList $tList
     * @return Task
     */
    public function setTList(\PIL\TaskerBundle\Entity\TList $tList)
    {
        $this->tList = $tList;

        return $this;
    }

    /**
     * Get tList
     *
     * @return \PIL\TaskerBundle\Entity\TList 
     */
    public function getTList()
    {
        return $this->tList;
    }


    /**
     * Set tList
     *
     * @param \PIL\UserBundle\Entity\User $tList
     * @return Task
     */
    public function setCreator(\PIL\UserBundle\Entity\User $user)
    {
        $this->creator = $user;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \PIL\UserBundle\Entity\User 
     */
    public function getCreator()
    {
        return $this->creator;
    }


    public function addComment(\PIL\TaskerBundle\Entity\HasCommented $comment)
    {
        $this->comments[] = $comment;
        $comment->setTask($this); 
        return $this;
    }

    public function removeComment(\PIL\TaskerBundle\Entity\HasCommented $comment)
    {
        $this->comments->removeElement($comment);
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


    public function addCheckList(\PIL\TaskerBundle\Entity\CheckList $checkList)
    {
        $this->checkLists[] = $checkList;
        $checkList->setTask($this); 
        return $this;
    }

    public function removeCheckList(\PIL\TaskerBundle\Entity\CheckList $checkList)
    {
        $this->checkLists->removeElement($checkList);
    }

    /**
     * Get checkLists
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCheckLists()
    {
        return $this->checkLists;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Task
     */
    public function setPosition ($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function __toString() {
        return $this->getName();
    }
}
