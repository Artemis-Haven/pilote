<?php

namespace Pilote\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Pilote\MessageBundle\Entity\Message;
use Pilote\MessageBundle\Entity\ThreadMetadata as Metadata;

/**
 * @ORM\Entity
 */
class Thread
{

    const SIMPLE_THREAD = 0;
    const BOARD_THREAD = 1;
    const ADMIN_THREAD = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Pilote\MessageBundle\Entity\Message",
     *   mappedBy="thread",
     *   cascade={"all"}
     * )
     * @var Message[]|\Doctrine\Common\Collections\Collection
     */
    private $messages;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Pilote\MessageBundle\Entity\ThreadMetadata",
     *   mappedBy="thread",
     *   cascade={"all"}
     * )
     * @var ThreadMetadata[]|\Doctrine\Common\Collections\Collection
     */
    private $metadata;

    /**
     * @ORM\Column(name="last_message_date", type="datetime", nullable=true)
     * 
     * @var datetime
     */
    private $lastMessageDate;

    /**
     * @ORM\OneToOne(targetEntity="Pilote\UserBundle\Entity\User")
     */
    private $creator;


    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;


    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type = self::SIMPLE_THREAD;


    /**
     * @ORM\OneToOne(targetEntity="Pilote\TaskerBundle\Entity\Board", mappedBy="thread")
     */
    private $board;


    public function __construct($title = null, $creator = null)
    {
        $this->messages = new ArrayCollection();
        $this->metadata = new ArrayCollection();
        $this->lastMessageDate = null;
        $this->board = null;
        $this->title = $title;
        $this->creator = $creator;
    }


    public function getId()
    {
        return $this->id;
    }

    public function addMessage(Message $message)
    {
        $this->messages[] = $message;
        $message->setThread($this);
        return $this;
    }

    public function removeMessage(Message $message)
    {
        $this->messages->removeElement($message);
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function addMetadata(Metadata $metadata)
    {
        $this->metadata[] = $metadata;
        $metadata->setThread($this);
        return $this;
    }

    public function removeMetadata(Metadata $metadata)
    {
        $this->metadata->removeElement($metadata);
    }

    public function getMetadata()
    {
        return $this->metadata;
    }
    
    public function setLastMessageDate($date)
    {
        $this->lastMessageDate = $date;
        return $this;
    }

    public function getLastMessageDate()
    {
        return $this->lastMessageDate;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }
    
    public function setCreator($creator)
    {
        $this->creator = $creator;
        return $this;
    }

    public function getCreator()
    {
        return $this->creator;
    }
    
    public function setBoard($board)
    {
        $this->board = $board;
        return $this;
    }

    public function getBoard()
    {
        return $this->board;
    }

    /**
     * Set type
     *
     * @param int $type
     * @return Thread
     */
    public function setType($type)
    {
        if (in_array($type, array(
            self::SIMPLE_THREAD, 
            self::BOARD_THREAD, 
            self::ADMIN_THREAD))) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * Get type
     *
     * @return int 
     */
    public function getType()
    {
        return $this->type;
    }

    public function getParticipants()
    {
        $result = array();
        foreach ($this->metadata as $md) {
            $result[] = $md->getParticipant();
        }
        return $result;
    }
}