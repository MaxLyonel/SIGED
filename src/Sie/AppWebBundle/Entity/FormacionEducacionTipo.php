<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FormacionEducacionTipo
 */
class FormacionEducacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $formacionEducacion;

    /**
     * @var string
     */
    private $activo;

    /**
     * @var string
     */
    private $comentario;


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
     * Set formacionEducacion
     *
     * @param string $formacionEducacion
     * @return FormacionEducacionTipo
     */
    public function setFormacionEducacion($formacionEducacion)
    {
        $this->formacionEducacion = $formacionEducacion;
    
        return $this;
    }

    /**
     * Get formacionEducacion
     *
     * @return string 
     */
    public function getFormacionEducacion()
    {
        return $this->formacionEducacion;
    }

    /**
     * Set activo
     *
     * @param string $activo
     * @return FormacionEducacionTipo
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    
        return $this;
    }

    /**
     * Get activo
     *
     * @return string 
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set comentario
     *
     * @param string $comentario
     * @return FormacionEducacionTipo
     */
    public function setComentario($comentario)
    {
        $this->comentario = $comentario;
    
        return $this;
    }

    /**
     * Get comentario
     *
     * @return string 
     */
    public function getComentario()
    {
        return $this->comentario;
    }
}
