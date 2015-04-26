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
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE") 
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