<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Mytable
 */
class Mytable
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $mytable;

    /**
     * @var string
     */
    private $a1;


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
     * Set mytable
     *
     * @param integer $mytable
     * @return Mytable
     */
    public function setMytable($mytable)
    {
        $this->mytable = $mytable;

        return $this;
    }

    /**
     * Get mytable
     *
     * @return integer 
     */
    public function getMytable()
    {
        return $this->mytable;
    }

    /**
     * Set a1
     *
     * @param string $a1
     * @return Mytable
     */
    public function setA1($a1)
    {
        $this->a1 = $a1;

        return $this;
    }

    /**
     * Get a1
     *
     * @return string 
     */
    public function getA1()
    {
        return $this->a1;
    }
}
