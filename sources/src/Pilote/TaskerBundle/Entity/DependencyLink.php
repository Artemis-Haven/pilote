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

namespace Pilote\TaskerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Un DependencyLink est un lien de dépendance entre deux tâches.
 * Ces liens de dépendances ne sont affichés que sur le Diagramme de Gantt.
 * 
 * Le type ($type) de ces liens est un paramètre de la librairie DHTMLXGantt.
 * Il peut prendre 4 valeurs : 
 * - "finish_to_start"  : "0"
 * - "start_to_start"   : "1"
 * - "finish_to_finish" : "2"
 * - "start_to_finish"  : "3"
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class DependencyLink
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
     * @ORM\ManyToOne(targetEntity="Pilote\TaskerBundle\Entity\Task", inversedBy="sourceLinks")
     */
    private $source;

    /**
     * @ORM\ManyToOne(targetEntity="Pilote\TaskerBundle\Entity\Task", inversedBy="targetLinks")
     */
    private $target;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="text")
     */
    private $type;


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
     * Set source
     *
     * @param \Pilote\TaskerBundle\Entity\Task $source
     * @return DependencyLink
     */
    public function setSource(\Pilote\TaskerBundle\Entity\Task $source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return \Pilote\TaskerBundle\Entity\Task 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set target
     *
     * @param \Pilote\TaskerBundle\Entity\Task $target
     * @return DependencyLink
     */
    public function setTarget(\Pilote\TaskerBundle\Entity\Task $target = null)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return \Pilote\TaskerBundle\Entity\Task 
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return DependencyLink
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }
}
