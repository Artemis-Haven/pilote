<!--

Copyright (C) 2015 Rémi Patrizio

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

namespace Pilote\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Pilote\MessageBundle\Entity\Thread;
use Pilote\UserBundle\Entity\User;

/**
 * Représente un message publié au sein d'une conversation.
 * Un message est envoyé par un utilisateur ($sender), dans une conversation ($thread).
 * Il possède un contenu ($body) et une date de création ($createdAt).
 * 
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Pilote\MessageBundle\Entity\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $body;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(
     *   targetEntity="Pilote\MessageBundle\Entity\Thread",
     *   inversedBy="messages"
     * )
     * @var ThreadInterface
     */
    private $thread;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Pilote\UserBundle\Entity\User"
     * )
     * @var User
     */
    private $sender;

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
     * Set body
     *
     * @param string $body
     * @return Message
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Message
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set thread
     *
     * @param \Pilote\MessageBundle\Entity\Thread $thread
     * @return Message
     */
    public function setThread(Thread $thread = null)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Get thread
     *
     * @return \Pilote\MessageBundle\Entity\Thread 
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Set sender
     *
     * @param \Pilote\UserBundle\Entity\User $sender
     * @return Message
     */
    public function setSender(User $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return \Pilote\UserBundle\Entity\User 
     */
    public function getSender()
    {
        return $this->sender;
    }

    public function __toString()
    {
        return $this->body;
    }
}
