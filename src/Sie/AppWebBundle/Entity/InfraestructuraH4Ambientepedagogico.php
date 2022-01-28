<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4Ambientepedagogico
 */
class InfraestructuraH4Ambientepedagogico
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n12AmbienteAnchoMts;

    /**
     * @var integer
     */
    private $n12AmbienteLargoMts;

    /**
     * @var integer
     */
    private $n12AmbienteAltoMts;

    /**
     * @var integer
     */
    private $n14CapacidadAmbiente;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4OrgcurricularTipo
     */
    private $n15UsoOrgcurricularTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13CielorasoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13PinturaEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13PuertasEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13VentanasEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13TechoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13ParedEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13PisoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13SeguridadEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13IluminacionelectricaEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13IluminacionnaturalEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13AmbienteEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4AmbienteTipo
     */
    private $n11AmbienteTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica
     */
    private $infraestructuraJuridiccionGeografica;


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
     * Set n12AmbienteAnchoMts
     *
     * @param integer $n12AmbienteAnchoMts
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN12AmbienteAnchoMts($n12AmbienteAnchoMts)
    {
        $this->n12AmbienteAnchoMts = $n12AmbienteAnchoMts;
    
        return $this;
    }

    /**
     * Get n12AmbienteAnchoMts
     *
     * @return integer 
     */
    public function getN12AmbienteAnchoMts()
    {
        return $this->n12AmbienteAnchoMts;
    }

    /**
     * Set n12AmbienteLargoMts
     *
     * @param integer $n12AmbienteLargoMts
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN12AmbienteLargoMts($n12AmbienteLargoMts)
    {
        $this->n12AmbienteLargoMts = $n12AmbienteLargoMts;
    
        return $this;
    }

    /**
     * Get n12AmbienteLargoMts
     *
     * @return integer 
     */
    public function getN12AmbienteLargoMts()
    {
        return $this->n12AmbienteLargoMts;
    }

    /**
     * Set n12AmbienteAltoMts
     *
     * @param integer $n12AmbienteAltoMts
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN12AmbienteAltoMts($n12AmbienteAltoMts)
    {
        $this->n12AmbienteAltoMts = $n12AmbienteAltoMts;
    
        return $this;
    }

    /**
     * Get n12AmbienteAltoMts
     *
     * @return integer 
     */
    public function getN12AmbienteAltoMts()
    {
        return $this->n12AmbienteAltoMts;
    }

    /**
     * Set n14CapacidadAmbiente
     *
     * @param integer $n14CapacidadAmbiente
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN14CapacidadAmbiente($n14CapacidadAmbiente)
    {
        $this->n14CapacidadAmbiente = $n14CapacidadAmbiente;
    
        return $this;
    }

    /**
     * Get n14CapacidadAmbiente
     *
     * @return integer 
     */
    public function getN14CapacidadAmbiente()
    {
        return $this->n14CapacidadAmbiente;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setFecharegistro($fecharegistro)
    {
        $this->fecharegistro = $fecharegistro;
    
        return $this;
    }

    /**
     * Get fecharegistro
     *
     * @return \DateTime 
     */
    public function getFecharegistro()
    {
        return $this->fecharegistro;
    }

    /**
     * Set n15UsoOrgcurricularTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4OrgcurricularTipo $n15UsoOrgcurricularTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN15UsoOrgcurricularTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4OrgcurricularTipo $n15UsoOrgcurricularTipo = null)
    {
        $this->n15UsoOrgcurricularTipo = $n15UsoOrgcurricularTipo;
    
        return $this;
    }

    /**
     * Get n15UsoOrgcurricularTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4OrgcurricularTipo 
     */
    public function getN15UsoOrgcurricularTipo()
    {
        return $this->n15UsoOrgcurricularTipo;
    }

    /**
     * Set n13CielorasoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13CielorasoEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13CielorasoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13CielorasoEstadogeneralTipo = null)
    {
        $this->n13CielorasoEstadogeneralTipo = $n13CielorasoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13CielorasoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13CielorasoEstadogeneralTipo()
    {
        return $this->n13CielorasoEstadogeneralTipo;
    }

    /**
     * Set n13PinturaEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PinturaEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13PinturaEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PinturaEstadogeneralTipo = null)
    {
        $this->n13PinturaEstadogeneralTipo = $n13PinturaEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13PinturaEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13PinturaEstadogeneralTipo()
    {
        return $this->n13PinturaEstadogeneralTipo;
    }

    /**
     * Set n13PuertasEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PuertasEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13PuertasEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PuertasEstadogeneralTipo = null)
    {
        $this->n13PuertasEstadogeneralTipo = $n13PuertasEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13PuertasEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13PuertasEstadogeneralTipo()
    {
        return $this->n13PuertasEstadogeneralTipo;
    }

    /**
     * Set n13VentanasEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13VentanasEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13VentanasEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13VentanasEstadogeneralTipo = null)
    {
        $this->n13VentanasEstadogeneralTipo = $n13VentanasEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13VentanasEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13VentanasEstadogeneralTipo()
    {
        return $this->n13VentanasEstadogeneralTipo;
    }

    /**
     * Set n13TechoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13TechoEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13TechoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13TechoEstadogeneralTipo = null)
    {
        $this->n13TechoEstadogeneralTipo = $n13TechoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13TechoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13TechoEstadogeneralTipo()
    {
        return $this->n13TechoEstadogeneralTipo;
    }

    /**
     * Set n13ParedEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13ParedEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13ParedEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13ParedEstadogeneralTipo = null)
    {
        $this->n13ParedEstadogeneralTipo = $n13ParedEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13ParedEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13ParedEstadogeneralTipo()
    {
        return $this->n13ParedEstadogeneralTipo;
    }

    /**
     * Set n13PisoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PisoEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13PisoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PisoEstadogeneralTipo = null)
    {
        $this->n13PisoEstadogeneralTipo = $n13PisoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13PisoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13PisoEstadogeneralTipo()
    {
        return $this->n13PisoEstadogeneralTipo;
    }

    /**
     * Set n13SeguridadEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13SeguridadEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13SeguridadEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13SeguridadEstadogeneralTipo = null)
    {
        $this->n13SeguridadEstadogeneralTipo = $n13SeguridadEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13SeguridadEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13SeguridadEstadogeneralTipo()
    {
        return $this->n13SeguridadEstadogeneralTipo;
    }

    /**
     * Set n13IluminacionelectricaEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13IluminacionelectricaEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13IluminacionelectricaEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13IluminacionelectricaEstadogeneralTipo = null)
    {
        $this->n13IluminacionelectricaEstadogeneralTipo = $n13IluminacionelectricaEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13IluminacionelectricaEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13IluminacionelectricaEstadogeneralTipo()
    {
        return $this->n13IluminacionelectricaEstadogeneralTipo;
    }

    /**
     * Set n13IluminacionnaturalEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13IluminacionnaturalEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13IluminacionnaturalEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13IluminacionnaturalEstadogeneralTipo = null)
    {
        $this->n13IluminacionnaturalEstadogeneralTipo = $n13IluminacionnaturalEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13IluminacionnaturalEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13IluminacionnaturalEstadogeneralTipo()
    {
        return $this->n13IluminacionnaturalEstadogeneralTipo;
    }

    /**
     * Set n13AmbienteEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13AmbienteEstadogeneralTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN13AmbienteEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13AmbienteEstadogeneralTipo = null)
    {
        $this->n13AmbienteEstadogeneralTipo = $n13AmbienteEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13AmbienteEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13AmbienteEstadogeneralTipo()
    {
        return $this->n13AmbienteEstadogeneralTipo;
    }

    /**
     * Set n11AmbienteTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4AmbienteTipo $n11AmbienteTipo
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setN11AmbienteTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4AmbienteTipo $n11AmbienteTipo = null)
    {
        $this->n11AmbienteTipo = $n11AmbienteTipo;
    
        return $this;
    }

    /**
     * Get n11AmbienteTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4AmbienteTipo 
     */
    public function getN11AmbienteTipo()
    {
        return $this->n11AmbienteTipo;
    }

    /**
     * Set infraestructuraJuridiccionGeografica
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica $infraestructuraJuridiccionGeografica
     * @return InfraestructuraH4Ambientepedagogico
     */
    public function setInfraestructuraJuridiccionGeografica(\Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica $infraestructuraJuridiccionGeografica = null)
    {
        $this->infraestructuraJuridiccionGeografica = $infraestructuraJuridiccionGeografica;
    
        return $this;
    }

    /**
     * Get infraestructuraJuridiccionGeografica
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica 
     */
    public function getInfraestructuraJuridiccionGeografica()
    {
        return $this->infraestructuraJuridiccionGeografica;
    }
}
