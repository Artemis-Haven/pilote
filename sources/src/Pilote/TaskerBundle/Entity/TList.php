<?php

namespace Pilote\TaskerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TList
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Pilote\TaskerBundle\Entity\TListRepository")
 */
class TList
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
     * @ORM\ManyToOne(targetEntity="Pilote\TaskerBundle\Entity\Step", inversedBy="tLists", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $step;
    

    /**
     * @ORM\OneToMany(targetEntity="Pilote\TaskerBundle\Entity\Task", mappedBy="tList", cascade={"remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $tasks; 

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;


    public function addTask(\Pilote\TaskerBundle\Entity\Task $task, $position = -1)
    {
        $this->tasks[] = $task;
        $task->setTList($this); 
      	if ($position != -1)
        {
            $task->setPosition($position);
        }
        return $this;
    }

    public function removeTask(\Pilote\TaskerBundle\Entity\Task $task)
    {
        $this->tasks->removeElement($task);
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
     * @return TList
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
     * @return TList
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
     * Set step
     *
     * @param \Pilote\TaskerBundle\Entity\Step $step
     * @return TList
     */
    public function setStep(\Pilote\TaskerBundle\Entity\Step $step = null)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return \Pilote\TaskerBundle\Entity\Step 
     */
    public function getStep()
    {
        return $this->step;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->name = "Nouvelle Liste";
        $this->description = "";
        $this->position = 0;
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
  
  	public function getMaxTaskPosition()
    {
        $max = 0;
        foreach ($this->tasks as $t)
        {
          $max = ($t->getPosition() > $max) ? $t->getPosition() : $max; 
        }
      	return $max;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return TList
     */
    public function setPosition ($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function __toString() {
        return $this->getName();
    }
}