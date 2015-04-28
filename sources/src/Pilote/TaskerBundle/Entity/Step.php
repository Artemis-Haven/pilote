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

namespace Pilote\TaskerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Une Étape (Step) est une subdivision d'un Domain, lui-même
 * subdivision d'un Board. Elle contient un ensemble de Listes
 * de Tâches (TList).
 *
 * Board
 * - Domain1
 *   - Step1
 *     - TList1
 *       - Task1
 *       - Task2
 *     - TList2
 *   - Step2
 *     - TList3
 * - Domain2
 *   - Step3
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class Step
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
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="date")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="date")
     */
    private $endDate;
    
    /**
     * @ORM\ManyToOne(targetEntity="Pilote\TaskerBundle\Entity\Domain", inversedBy="steps", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $domain;

    /**
     * @ORM\OneToMany(targetEntity="Pilote\TaskerBundle\Entity\TList", mappedBy="step", cascade={"persist", "remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $tLists; 

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
     * @return Step
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
     * @return Step
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
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Step
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
     * @return Step
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
     * Set domain
     *
     * @param \Pilote\TaskerBundle\Entity\Domain $domain
     * @return Step
     */
    public function setDomain(\Pilote\TaskerBundle\Entity\Domain $domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return \Pilote\TaskerBundle\Entity\Domain 
     */
    public function getDomain()
    {
        return $this->domain;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tLists = new \Doctrine\Common\Collections\ArrayCollection();
        $this->name = "Nouvelle étape";
        $this->description = "";
        $this->startDate = new \DateTime("now");
        $this->endDate = new \DateTime("now");
    }

    /**
     * Add tList
     *
     * @param \Pilote\TaskerBundle\Entity\TList $tList
     * @return Step
     */
    public function addTList(\Pilote\TaskerBundle\Entity\TList $tList, $position = -1)
    {
        $this->tLists[] = $tList;
        $tList->setStep($this); 
      	if ($position != -1)
        {
            $tList->setPosition($position);
        }
        return $this;
    }

    /**
     * Remove tLists
     *
     * @param \Pilote\TaskerBundle\Entity\TList $tList
     */
    public function removeTList(\Pilote\TaskerBundle\Entity\TList $tList)
    {
        $this->tLists->removeElement($tList);
    }

    /**
     * Get tLists
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTLists()
    {
        return $this->tLists;
    }
  
  	public function getMaxTListPosition()
    {
        $max = 0;
        foreach ($this->tLists as $l)
        {
          $max = ($l->getPosition() > $max) ? $l->getPosition() : $max; 
        }
      	return $max;
    }

    public function __toString() {
        return $this->getName();
    }
}
