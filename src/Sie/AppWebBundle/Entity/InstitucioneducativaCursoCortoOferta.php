<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaCursoCortoOferta
 */
class InstitucioneducativaCursoCortoOferta
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $horas;

    /**
     * @var string
     */
    private $modulo;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\MaestroInscripcion
     */
    private $maestroInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto
     */
    private $institucioneducativaCursoCorto;


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
     * Set horas
     *
     * @param integer $horas
     * @return InstitucioneducativaCursoCortoOferta
     */
    public function setHoras($horas)
    {
        $this->horas = $horas;
    
        return $this;
    }

    /**
     * Get horas
     *
     * @return integer 
     */
    public function getHoras()
    {
        return $this->horas;
    }

    /**
     * Set modulo
     *
     * @param string $modulo
     * @return InstitucioneducativaCursoCortoOferta
     */
    public function setModulo($modulo)
    {
        $this->modulo = $modulo;
    
        return $this;
    }

    /**
     * Get modulo
     *
     * @return string 
     */
    public function getModulo()
    {
        return $this->modulo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaCursoCortoOferta
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set maestroInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcion
     * @return InstitucioneducativaCursoCortoOferta
     */
    public function setMaestroInscripcion(\Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcion = null)
    {
        $this->maestroInscripcion = $maestroInscripcion;
    
        return $this;
    }

    /**
     * Get maestroInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\MaestroInscripcion 
     */
    public function getMaestroInscripcion()
    {
        return $this->maestroInscripcion;
    }

    /**
     * Set institucioneducativaCursoCorto
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto $institucioneducativaCursoCorto
     * @return InstitucioneducativaCursoCortoOferta
     */
    public function setInstitucioneducativaCursoCorto(\Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto $institucioneducativaCursoCorto = null)
    {
        $this->institucioneducativaCursoCorto = $institucioneducativaCursoCorto;
    
        return $this;
    }

    /**
     * Get institucioneducativaCursoCorto
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCursoCorto 
     */
    public function getInstitucioneducativaCursoCorto()
    {
        return $this->institucioneducativaCursoCorto;
    }
}
