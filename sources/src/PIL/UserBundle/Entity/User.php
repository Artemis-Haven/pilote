<?php

namespace PIL\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\MessageBundle\Model\ParticipantInterface;
use FOS\UserBundle\Entity\User as BaseUser;
use FOS\TaskerBundle\Entity\HasCommented;

/**
 * @ORM\Entity
 * @ORM\Table(name="pilote_user")
 */
class User extends BaseUser implements ParticipantInterface
{
    /**
16   * @ORM\Id
17   * @ORM\Column(type="integer")
18   * @ORM\GeneratedValue(strategy="AUTO")
19   */
    protected $id;
    

    /**
     * @ORM\OneToMany(targetEntity="PIL\TaskerBundle\Entity\HasCommented", mappedBy="user")
     */
    private $comments; 
    

    /**
     * @ORM\OneToMany(targetEntity="PIL\TaskerBundle\Entity\Task", mappedBy="creator")
     */
    private $tasks; 


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }


    public function addComment(\PIL\TaskerBundle\Entity\HasCommented $comment)
    {
        $this->comments[] = $comment;
        $comment->setUser($this); 
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

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTasks()
    {
        return $this->tasks;
    }


    public function addTask(\PIL\TaskerBundle\Entity\Task $task)
    {
      	if ($this->tasks == null)
          	$this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tasks[] = $task;
        $task->setCreator($this);
        return $this;
    }

    public function removeTask(\PIL\TaskerBundle\Entity\Task $task)
    {
        $this->tasks->removeElement($task);
    }
}
