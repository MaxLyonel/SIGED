<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FormacionTipo
 */
class FormacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $formacion;
    
    public function __toString() {
        return $this->formacion;
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
     * Set formacion
     *
     * @param string $formacion
     * @return FormacionTipo
     */
    public function setFormacion($formacion)
    {
        $this->formacion = $formacion;

        return $this;
    }

    /**
     * Get formacion
     *
     * @return string 
     */
    public function getFormacion()
    {
        return $this->formacion;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaTipo
     */
    private $institucioneducativaTipo;


    /**
     * Set institucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo
     * @return FormacionTipo
     */
    public function setInstitucioneducativaTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo = null)
    {
        $this->institucioneducativaTipo = $institucioneducativaTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaTipo 
     */
    public function getInstitucioneducativaTipo()
    {
        return $this->institucioneducativaTipo;
    }
}
