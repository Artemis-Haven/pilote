<?php

namespace Pilote\TaskerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CheckList
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Pilote\TaskerBundle\Entity\CheckListRepository")
 */
class CheckList
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
     * @ORM\ManyToOne(targetEntity="Pilote\TaskerBundle\Entity\Task", inversedBy="checkLists")
     * @ORM\JoinColumn(nullable=false)
     */
    private $task;

    /**
     * @ORM\OneToMany(targetEntity="Pilote\TaskerBundle\Entity\CheckListOption", mappedBy="checkList", cascade="remove")
     */
    private $checkListOptions;


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
     * @return CheckList
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
     * Set Task
     *
     * @param \Pilote\TaskerBundle\Entity\Task $task
     * @return CheckList
     */
    public function setTask(\Pilote\TaskerBundle\Entity\Task $task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return \Pilote\TaskerBundle\Entity\Task 
     */
    public function getTask()
    {
        return $this->task;
    }


    public function addCheckListOption(\Pilote\TaskerBundle\Entity\CheckListOption $checkListOption)
    {
        $this->checkListOptions[] = $checkListOption;
        $checkListOption->setCheckList($this); 
        return $this;
    }

    public function removeComment(\Pilote\TaskerBundle\Entity\CheckListOption $checkListOption)
    {
        $this->checkListOptions->removeElement($checkListOption);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCheckListOptions()
    {
        return $this->checkListOptions;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->checkListOptions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName("Nouvelle liste");
    }

    public function __toString() {
        return $this->getName();
    }
}
