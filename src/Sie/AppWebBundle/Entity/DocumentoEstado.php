<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentoEstado
 */
class DocumentoEstado
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $documentoEstado;

    /**
     * @var string
     */
    private $obs;


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
     * Set documentoEstado
     *
     * @param string $documentoEstado
     * @return DocumentoEstado
     */
    public function setDocumentoEstado($documentoEstado)
    {
        $this->documentoEstado = $documentoEstado;
    
        return $this;
    }

    /**
     * Get documentoEstado
     *
     * @return string 
     */
    public function getDocumentoEstado()
    {
        return $this->documentoEstado;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return DocumentoEstado
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }
}
