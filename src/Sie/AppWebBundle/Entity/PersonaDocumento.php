<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PersonaDocumento
 */
class PersonaDocumento
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $personaId;

    /**
     * @var string
     */
    private $ruta;

    /**
     * @var \Sie\AppWebBundle\Entity\DocumentoTipo
     */
    private $documento;


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
     * Set personaId
     *
     * @param integer $personaId
     * @return PersonaDocumento
     */
    public function setPersonaId($personaId)
    {
        $this->personaId = $personaId;
    
        return $this;
    }

    /**
     * Get personaId
     *
     * @return integer 
     */
    public function getPersonaId()
    {
        return $this->personaId;
    }

    /**
     * Set ruta
     *
     * @param string $ruta
     * @return PersonaDocumento
     */
    public function setRuta($ruta)
    {
        $this->ruta = $ruta;
    
        return $this;
    }

    /**
     * Get ruta
     *
     * @return string 
     */
    public function getRuta()
    {
        return $this->ruta;
    }

    /**
     * Set documento
     *
     * @param \Sie\AppWebBundle\Entity\DocumentoTipo $documento
     * @return PersonaDocumento
     */
    public function setDocumento(\Sie\AppWebBundle\Entity\DocumentoTipo $documento = null)
    {
        $this->documento = $documento;
    
        return $this;
    }

    /**
     * Get documento
     *
     * @return \Sie\AppWebBundle\Entity\DocumentoTipo 
     */
    public function getDocumento()
    {
        return $this->documento;
    }
}
