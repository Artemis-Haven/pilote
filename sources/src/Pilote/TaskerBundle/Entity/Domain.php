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
 * Un Domaine est la principale composante d'un Board et est découpé
 * en plusieurs Étapes (Step).
 * Il est représenté dans la vue du Board par un "accordéon".
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
class Domain
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
     * @ORM\Column(name="description", type="text")
     */
    private $description;
    
    /**
     * @ORM\ManyToOne(targetEntity="Pilote\TaskerBundle\Entity\Board", inversedBy="domains", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $board;

    /**
     * @ORM\OneToMany(targetEntity="Pilote\TaskerBundle\Entity\Step", mappedBy="domain", cascade={"persist", "remove"})
     */
    private $steps; 


    public function addStep(\Pilote\TaskerBundle\Entity\Step $step)
    {
        $this->steps[] = $step;
        $step->setDomain($this); 
        return $this;
    }

    public function removeStep(\Pilote\TaskerBundle\Entity\Step $step)
    {
        $this->steps->removeElement($step);
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
     * @return Domain
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
     * @return Domain
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
     * Set board
     *
     * @param \Pilote\TaskerBundle\Entity\Board $board
     * @return Domain
     */
    public function setBoard(\Pilote\TaskerBundle\Entity\Board $board)
    {
        $this->board = $board;

        return $this;
    }

    /**
     * Get board
     *
     * @return \Pilote\TaskerBundle\Entity\Board 
     */
    public function getBoard()
    {
        return $this->board;
    }

    public function __toString() {
        return $this->getName();
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->steps = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName("Nouveau corps de métier");
        $this->setDescription("");
    }

    /**
     * Get steps
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSteps()
    {
        return $this->steps;
    }
}
