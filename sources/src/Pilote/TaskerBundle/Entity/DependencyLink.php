<?php

namespace Pilote\TaskerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DependencyLink
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
