<?php

namespace PIL\TaskerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Step
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PIL\TaskerBundle\Entity\StepRepository")
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
     * @ORM\ManyToOne(targetEntity="PIL\TaskerBundle\Entity\Domain", inversedBy="steps")
     * @ORM\JoinColumn(nullable=false)
     */
    private $domain;

    /**
     * @ORM\OneToMany(targetEntity="PIL\TaskerBundle\Entity\TList", mappedBy="step")
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
     * @param \PIL\TaskerBundle\Entity\Domain $domain
     * @return Step
     */
    public function setDomain(\PIL\TaskerBundle\Entity\Domain $domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return \PIL\TaskerBundle\Entity\Domain 
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
     * @param \PIL\TaskerBundle\Entity\TList $tList
     * @return Step
     */
    public function addTList(\PIL\TaskerBundle\Entity\TList $tList)
    {
        $this->tLists[] = $tList;
        $tList->setStep($this); 
        return $this;
    }

    /**
     * Remove tLists
     *
     * @param \PIL\TaskerBundle\Entity\TList $tList
     */
    public function removeTList(\PIL\TaskerBundle\Entity\TList $tList)
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

    public function __toString() {
        return $this->getName();
    }
}
