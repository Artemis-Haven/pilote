<?php

namespace PIL\TaskerBundle\Entity;
use PIL\UserBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * HasCommented
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PIL\TaskerBundle\Entity\HasCommentedRepository")
 */
class HasCommented
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
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;
    
    /**
     * @ORM\ManyToOne(targetEntity="PIL\UserBundle\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="PIL\TaskerBundle\Entity\Task", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $task;


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
     * Set comment
     *
     * @param string $comment
     * @return HasCommented
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return HasCommented
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set user
     *
     * @param \PIL\UserBundle\Entity\User $user
     * @return HasCommented
     */
    public function setUser(\PIL\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \PIL\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set Task
     *
     * @param \PIL\TaskerBundle\Entity\Task $task
     * @return HasCommented
     */
    public function setTask(\PIL\TaskerBundle\Entity\Task $task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return \PIL\UserBundle\Entity\Task 
     */
    public function getTask()
    {
        return $this->task;
    }
    
    /**
     * Constructor
     */
    public function __construct(\PIL\UserBundle\Entity\User $user, \PIL\TaskerBundle\Entity\Task $task)
    {
        $this->task = $task;
        $this->user = $user;
        $this->date = new \DateTime("now");
    }
    
}
