<!--

Copyright (C) 2015 Hamza Ayoub, Valentin Chareyre, Sofian Hamou-Mamar, 
Alain Krok, Wenlong Li, Rémi Patrizio, Yamine Zaidou

________________________________

This file is part of Pilote.

    Pilote is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Pilote is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Pilote.  If not, see <http://www.gnu.org/licenses/>.

-->

<?php

namespace Pilote\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use FOS\TaskerBundle\Entity\HasCommented;
use Pilote\MessageBundle\Entity\ThreadMetadata as Metadata;
use Pilote\MessageBundle\Entity\Thread;

/**
 * Un User représente un utilisateur de notre site web.
 * Il hérite de la classe User du FOSUserBundle.
 * 
 * @ORM\Entity
 * @ORM\Table(name="pilote_user")
 * @ORM\Entity(repositoryClass="Pilote\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * L'UUID est un identifiant unique généré automatiquement à la création de l'utilisateur.
     * Cela permet d'éviter que les identifiants utilisés se suivent. En effet, les $id
     * des utilisateurs sont : 01, 02, 03, etc.
     * Alors qu'un UUID ressemble plutôt à "552bab3da65e6".
     * 
     * @ORM\Column(type="string", length=255)
     */
    protected $uuid;
    

    /**
     * Commentaires postés par l'utilisateur
     * 
     * @ORM\OneToMany(targetEntity="Pilote\TaskerBundle\Entity\HasCommented", mappedBy="user")
     */
    private $comments; 
    

    /**
     * Tâches assignées à l'utilisateur
     * 
     * @ORM\OneToMany(targetEntity="Pilote\TaskerBundle\Entity\Task", mappedBy="creator")
     */
    private $tasks; 


    /**
     * Boards (=projets) rattaché à l'utilisateur
     * 
     * @ORM\ManyToMany(targetEntity="Pilote\TaskerBundle\Entity\Board", inversedBy="users")
     * @ORM\JoinTable(name="boards_users")
     */
    private $boards;

    /**
     * Image de profil de l'utilisateur
     * 
     * @ORM\OneToOne(targetEntity="Pilote\UserBundle\Entity\Picture")
     * @ORM\JoinColumn(name="picture_id", referencedColumnName="id", nullable=true)
     */
    private $picture;

    /**
     * Notifications reçues par l'utilisateur
     * 
     * @ORM\OneToMany(targetEntity="Pilote\UserBundle\Entity\Notification", mappedBy="receiver", cascade={"remove", "persist"})
     */
    private $notifications;

    /**
     * Liens avec les conversations associées à l'utilisateur.
     * 
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
        return $this->username;
    }
}
