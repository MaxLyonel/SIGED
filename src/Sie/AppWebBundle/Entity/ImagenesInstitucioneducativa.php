<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ImagenesInstitucioneducativa
 */
class ImagenesInstitucioneducativa
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombreArchivo;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\ImagenTipo
     */
    private $imagenTipo;


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
     * Set nombreArchivo
     *
     * @param string $nombreArchivo
     * @return ImagenesInstitucioneducativa
     */
    public function setNombreArchivo($nombreArchivo)
    {
        $this->nombreArchivo = $nombreArchivo;
    
        return $this;
    }

    /**
     * Get nombreArchivo
     *
     * @return string 
     */
    public function getNombreArchivo()
    {
        return $this->nombreArchivo;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return ImagenesInstitucioneducativa
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return ImagenesInstitucioneducativa
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set imagenTipo
     *
     * @param \Sie\AppWebBundle\Entity\ImagenTipo $imagenTipo
     * @return ImagenesInstitucioneducativa
     */
    public function setImagenTipo(\Sie\AppWebBundle\Entity\ImagenTipo $imagenTipo = null)
    {
        $this->imagenTipo = $imagenTipo;
    
        return $this;
    }

    /**
     * Get imagenTipo
     *
     * @return \Sie\AppWebBundle\Entity\ImagenTipo 
     */
    public function getImagenTipo()
    {
        return $this->imagenTipo;
    }
}
