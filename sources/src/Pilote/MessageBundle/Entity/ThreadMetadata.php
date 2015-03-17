<?php

namespace Pilote\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pilote\MessageBundle\Entity\Thread;
use Pilote\UserBundle\Entity\User;

/**
 * @ORM\Entity
 */
class ThreadMetadata
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *   targetEntity="Pilote\MessageBundle\Entity\Thread",
     *   inversedBy="metadata"
     * )
     * @var ThreadInterface
     */
    private $thread;

    /**
     * @ORM\ManyToOne(targetEntity="Pilote\UserBundle\Entity\User")
     */
    private $participant;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_read", type="boolean")
     */
    private $read;


    public function __construct($participant = null, $thread = null)
    {
        $this->read = true;
        $this->participant = $participant;
        $this->thread = $thread;
    }


    public function getId()
    {
        return $this->id;
    }

    public function setThread(Thread $thread)
    {
        $this->thread = $thread;

        return $this;
    }

    public function getThread()
    {
        return $this->thread;
    }

    public function setParticipant(User $participant)
    {
        $this->participant = $participant;

        return $this;
    }

    public function getParticipant()
    {
        return $this->participant;
    }

    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    public function isRead()
    {
        return $this->read;
    }

}