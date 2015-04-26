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
 * CheckListOption
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class CheckListOption
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
     * @ORM\Column(name="optionText", type="string", length=255)
     */
    private $optionText;

    /**
     * @var boolean
     *
     * @ORM\Column(name="checked", type="boolean")
     */
    private $checked;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer")
     */
    private $position;
    
    /**
     * @ORM\ManyToOne(targetEntity="Pilote\TaskerBundle\Entity\CheckList", inversedBy="checkListOptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $checkList;


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
     * Set optionText
     *
     * @param string $optionText
     * @return CheckListOption
     */
    public function setOptionText($optionText)
    {
        $this->optionText = $optionText;
    
        return $this;
    }

    /**
     * Get optionText
     *
     * @return string 
     */
    public function getOptionText()
    {
        return $this->optionText;
    }

    /**
     * Set checked
     *
     * @param boolean $checked
     * @return CheckListOption
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    
        return $this;
    }

    /**
     * Get checked
     *
     * @return boolean 
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return CheckListOption
     */
    public function setPosition($position)
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

    /**
     * Set CheckList
     *
     * @param \Pilote\CheckListerBundle\Entity\CheckList $checkList
     * @return CheckList
     */
    public function setCheckList(\Pilote\TaskerBundle\Entity\CheckList $checkList)
    {
        $this->checkList = $checkList;

        return $this;
    }

    /**
     * Get checkList
     *
     * @return \Pilote\TaskerBundle\Entity\CheckList 
     */
    public function getCheckList()
    {
        return $this->checkList;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setChecked(false);
        $this->setOptionText("Nouvelle option");
        $this->setPosition(-1);
    }

    public function __toString() {
        return $this->getOptionText();
    }
}
