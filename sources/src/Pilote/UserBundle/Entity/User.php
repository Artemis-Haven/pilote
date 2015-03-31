<?php

namespace Pilote\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use FOS\TaskerBundle\Entity\HasCommented;
use Pilote\MessageBundle\Entity\ThreadMetadata as Metadata;
use Pilote\MessageBundle\Entity\Thread;
use FR3D\LdapBundle\Model\LdapUserInterface as LdapUserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="pilote_user")
 * @ORM\Entity(repositoryClass="Pilote\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser implements LdapUserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $uuid;
    

    /**
     * @ORM\OneToMany(targetEntity="Pilote\TaskerBundle\Entity\HasCommented", mappedBy="user")
     */
    private $comments; 
    

    /**
     * @ORM\OneToMany(targetEntity="Pilote\TaskerBundle\Entity\Task", mappedBy="creator")
     */
    private $tasks; 


    /**
    * @ORM\ManyToMany(targetEntity="Pilote\TaskerBundle\Entity\Board", inversedBy="users")
    * @ORM\JoinTable(name="boards_users")
    */
    private $boards;

    /**
     * @ORM\OneToOne(targetEntity="Pilote\UserBundle\Entity\Picture")
     * @ORM\JoinColumn(name="picture_id", referencedColumnName="id", nullable=true)
     */
    private $picture;

    /**
     * @ORM\OneToMany(targetEntity="Pilote\UserBundle\Entity\Notification", mappedBy="receiver", cascade={"remove", "persist"})
     */
    private $notifications;

    /**
     * @ORM\Column(type="string", nullable=true)    
     */
    protected $dn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $displayName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Pilote\MessageBundle\Entity\ThreadMetadata", 
     *     mappedBy="participant", 
     *     cascade={"all"}
     * )
     */
    private $threadMetadata;


    public function __construct()
    {
        parent::__construct();
        $this->setUuid(uniqid());
        if (empty($this->roles)) {
            $this->roles[] = 'ROLE_USER';
        }
	$this->email = "";
    }

    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get uuid
     *
     * @return string 
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set uuid
     *
     * @param integer $uuid
     * @return User
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }


    public function addComment(\Pilote\TaskerBundle\Entity\HasCommented $comment)
    {
        $this->comments[] = $comment;
        $comment->setUser($this); 
        return $this;
    }

    public function removeComment(\Pilote\TaskerBundle\Entity\HasCommented $comment)
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


    public function addTask(\Pilote\TaskerBundle\Entity\Task $task)
    {
        if ($this->tasks == null)
            $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tasks[] = $task;
        $task->setCreator($this);
        return $this;
    }

    public function removeTask(\Pilote\TaskerBundle\Entity\Task $task)
    {
        $this->tasks->removeElement($task);
        $task->setCreator(null);
    }

    /**
     * Get boards
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBoards()
    {
        return $this->boards;
    }


    public function addBoard(\Pilote\TaskerBundle\Entity\Board $board)
    {
        if ($this->boards == null)
            $this->boards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->boards[] = $board;
        return $this;
    }

    public function removeBoard(\Pilote\TaskerBundle\Entity\Board $board)
    {
        $this->boards->removeElement($board);
    }

    /**
     * Set picture
     *
     * @param boolean $picture
     * @return User
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return Picture 
     */
    public function getPicture()
    {
        return $this->picture;
    }


    public function addNotification(\Pilote\UserBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;
        $notification->setUser($this); 
        return $this;
    }

    public function removeNotification(\Pilote\UserBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection  
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    public function addMetadata(Metadata $metadata)
    {
        $this->threadMetadata[] = $metadata;
        $metadata->setParticipant($this);
        return $this;
    }

    public function removeMetadata(Metadata $metadata)
    {
        $this->threadMetadata->removeElement($metadata);
    }

    public function getMetadata()
    {
        return $this->threadMetadata;
    }

    public function getThreads()
    {
        $result = array();
        foreach ($this->threadMetadata as $metadata) {
            $result[] = $metadata->getThread();
        }
        return $result;
    }

    public function setDn($dn) {
        $this->dn = $dn;
    }

    public function getDn() {
        return $this->dn;
    }

    public function setDisplayName($displayName) {
        $this->displayName = $displayName;
    }

    public function getDisplayName() {
        return $this->displayName;
    }

    public function setUsername($username) {
        $this->username = $username;
        if ($this->displayName == NULL)
	    $this->displayName = $username;
    }
    
    public function isGranted($role)
    {
        return in_array($role, $this->getRoles());
    }
    
    public function getUnreadThreadsNbr()
    {
        $result = 0;
        foreach ($this->threadMetadata as $metadata) {
            if (! $metadata->isRead()) {
                $result++;
            }
        }
        return $result;
    }
    
    public function getUnreadNotifsNbr()
    {
        $result = 0;
        foreach ($this->notifications as $notif) {
            if (! $notif->isRead()) {
                $result++;
            }
        }
        return $result;
    }

    public function __toString()
    {
        if ($this->displayName != null)
            return $this->displayName;
        else
            return $this->username;
    }
}
