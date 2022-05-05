<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatHabHabilitacion
 */
class UnivDatHabHabilitacion
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $carreraId;

    /**
     * @var string
     */
    private $gestionAcademicaId;

    /**
     * @var string
     */
    private $gradoAcademicoId;

    /**
     * @var string
     */
    private $tipoFormularioId;

    /**
     * @var string
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $usuarioId;

    /**
     * @var string
     */
    private $citeUniversidad;

    /**
     * @var string
     */
    private $tipoObservacionId;

    /**
     * @var string
     */
    private $tipoEstadoId;

    /**
     * @var string
     */
    private $tipoEstudianteId;

    /**
     * @var string
     */
    private $modalidadGraduacionId;

    /**
     * @var string
     */
    private $formularioObservacion;

    /**
     * @var string
     */
    private $observacionConvalidacion;

    /**
     * @var string
     */
    private $observacionDbachiller;

    /**
     * @var string
     */
    private $observacionReprobado;

    /**
     * @var string
     */
    private $nombreResponsable;

    /**
     * @var string
     */
    private $cargoResponsable;

    /**
     * @var string
     */
    private $nombreLlenado;

    /**
     * @var string
     */
    private $cargoLlenado;

    /**
     * @var string
     */
    private $detalle;

    /**
     * @var string
     */
    private $hojaRuta;

    /**
     * @var string
     */
    private $informe;

    /**
     * @var string
     */
    private $tipo;

    /**
     * @var string
     */
    private $fecha;

    /**
     * @var string
     */
    private $estado;

    /**
     * @var string
     */
    private $modalidadCursada;

    /**
     * @var string
     */
    private $reporte;

    /**
     * @var string
     */
    private $updatedAt;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set carreraId
     *
     * @param string $carreraId
     * @return UnivDatHabHabilitacion
     */
    public function setCarreraId($carreraId)
    {
        $this->carreraId = $carreraId;
    
        return $this;
    }

    /**
     * Get carreraId
     *
     * @return string 
     */
    public function getCarreraId()
    {
        return $this->carreraId;
    }

    /**
     * Set gestionAcademicaId
     *
     * @param string $gestionAcademicaId
     * @return UnivDatHabHabilitacion
     */
    public function setGestionAcademicaId($gestionAcademicaId)
    {
        $this->gestionAcademicaId = $gestionAcademicaId;
    
        return $this;
    }

    /**
     * Get gestionAcademicaId
     *
     * @return string 
     */
    public function getGestionAcademicaId()
    {
        return $this->gestionAcademicaId;
    }

    /**
     * Set gradoAcademicoId
     *
     * @param string $gradoAcademicoId
     * @return UnivDatHabHabilitacion
     */
    public function setGradoAcademicoId($gradoAcademicoId)
    {
        $this->gradoAcademicoId = $gradoAcademicoId;
    
        return $this;
    }

    /**
     * Get gradoAcademicoId
     *
     * @return string 
     */
    public function getGradoAcademicoId()
    {
        return $this->gradoAcademicoId;
    }

    /**
     * Set tipoFormularioId
     *
     * @param string $tipoFormularioId
     * @return UnivDatHabHabilitacion
     */
    public function setTipoFormularioId($tipoFormularioId)
    {
        $this->tipoFormularioId = $tipoFormularioId;
    
        return $this;
    }

    /**
     * Get tipoFormularioId
     *
     * @return string 
     */
    public function getTipoFormularioId()
    {
        return $this->tipoFormularioId;
    }

    /**
     * Set fechaRegistro
     *
     * @param string $fechaRegistro
     * @return UnivDatHabHabilitacion
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return string 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set usuarioId
     *
     * @param string $usuarioId
     * @return UnivDatHabHabilitacion
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return string 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set citeUniversidad
     *
     * @param string $citeUniversidad
     * @return UnivDatHabHabilitacion
     */
    public function setCiteUniversidad($citeUniversidad)
    {
        $this->citeUniversidad = $citeUniversidad;
    
        return $this;
    }

    /**
     * Get citeUniversidad
     *
     * @return string 
     */
    public function getCiteUniversidad()
    {
        return $this->citeUniversidad;
    }

    /**
     * Set tipoObservacionId
     *
     * @param string $tipoObservacionId
     * @return UnivDatHabHabilitacion
     */
    public function setTipoObservacionId($tipoObservacionId)
    {
        $this->tipoObservacionId = $tipoObservacionId;
    
        return $this;
    }

    /**
     * Get tipoObservacionId
     *
     * @return string 
     */
    public function getTipoObservacionId()
    {
        return $this->tipoObservacionId;
    }

    /**
     * Set tipoEstadoId
     *
     * @param string $tipoEstadoId
     * @return UnivDatHabHabilitacion
     */
    public function setTipoEstadoId($tipoEstadoId)
    {
        $this->tipoEstadoId = $tipoEstadoId;
    
        return $this;
    }

    /**
     * Get tipoEstadoId
     *
     * @return string 
     */
    public function getTipoEstadoId()
    {
        return $this->tipoEstadoId;
    }

    /**
     * Set tipoEstudianteId
     *
     * @param string $tipoEstudianteId
     * @return UnivDatHabHabilitacion
     */
    public function setTipoEstudianteId($tipoEstudianteId)
    {
        $this->tipoEstudianteId = $tipoEstudianteId;
    
        return $this;
    }

    /**
     * Get tipoEstudianteId
     *
     * @return string 
     */
    public function getTipoEstudianteId()
    {
        return $this->tipoEstudianteId;
    }

    /**
     * Set modalidadGraduacionId
     *
     * @param string $modalidadGraduacionId
     * @return UnivDatHabHabilitacion
     */
    public function setModalidadGraduacionId($modalidadGraduacionId)
    {
        $this->modalidadGraduacionId = $modalidadGraduacionId;
    
        return $this;
    }

    /**
     * Get modalidadGraduacionId
     *
     * @return string 
     */
    public function getModalidadGraduacionId()
    {
        return $this->modalidadGraduacionId;
    }

    /**
     * Set formularioObservacion
     *
     * @param string $formularioObservacion
     * @return UnivDatHabHabilitacion
     */
    public function setFormularioObservacion($formularioObservacion)
    {
        $this->formularioObservacion = $formularioObservacion;
    
        return $this;
    }

    /**
     * Get formularioObservacion
     *
     * @return string 
     */
    public function getFormularioObservacion()
    {
        return $this->formularioObservacion;
    }

    /**
     * Set observacionConvalidacion
     *
     * @param string $observacionConvalidacion
     * @return UnivDatHabHabilitacion
     */
    public function setObservacionConvalidacion($observacionConvalidacion)
    {
        $this->observacionConvalidacion = $observacionConvalidacion;
    
        return $this;
    }

    /**
     * Get observacionConvalidacion
     *
     * @return string 
     */
    public function getObservacionConvalidacion()
    {
        return $this->observacionConvalidacion;
    }

    /**
     * Set observacionDbachiller
     *
     * @param string $observacionDbachiller
     * @return UnivDatHabHabilitacion
     */
    public function setObservacionDbachiller($observacionDbachiller)
    {
        $this->observacionDbachiller = $observacionDbachiller;
    
        return $this;
    }

    /**
     * Get observacionDbachiller
     *
     * @return string 
     */
    public function getObservacionDbachiller()
    {
        return $this->observacionDbachiller;
    }

    /**
     * Set observacionReprobado
     *
     * @param string $observacionReprobado
     * @return UnivDatHabHabilitacion
     */
    public function setObservacionReprobado($observacionReprobado)
    {
        $this->observacionReprobado = $observacionReprobado;
    
        return $this;
    }

    /**
     * Get observacionReprobado
     *
     * @return string 
     */
    public function getObservacionReprobado()
    {
        return $this->observacionReprobado;
    }

    /**
     * Set nombreResponsable
     *
     * @param string $nombreResponsable
     * @return UnivDatHabHabilitacion
     */
    public function setNombreResponsable($nombreResponsable)
    {
        $this->nombreResponsable = $nombreResponsable;
    
        return $this;
    }

    /**
     * Get nombreResponsable
     *
     * @return string 
     */
    public function getNombreResponsable()
    {
        return $this->nombreResponsable;
    }

    /**
     * Set cargoResponsable
     *
     * @param string $cargoResponsable
     * @return UnivDatHabHabilitacion
     */
    public function setCargoResponsable($cargoResponsable)
    {
        $this->cargoResponsable = $cargoResponsable;
    
        return $this;
    }

    /**
     * Get cargoResponsable
     *
     * @return string 
     */
    public function getCargoResponsable()
    {
        return $this->cargoResponsable;
    }

    /**
     * Set nombreLlenado
     *
     * @param string $nombreLlenado
     * @return UnivDatHabHabilitacion
     */
    public function setNombreLlenado($nombreLlenado)
    {
        $this->nombreLlenado = $nombreLlenado;
    
        return $this;
    }

    /**
     * Get nombreLlenado
     *
     * @return string 
     */
    public function getNombreLlenado()
    {
        return $this->nombreLlenado;
    }

    /**
     * Set cargoLlenado
     *
     * @param string $cargoLlenado
     * @return UnivDatHabHabilitacion
     */
    public function setCargoLlenado($cargoLlenado)
    {
        $this->cargoLlenado = $cargoLlenado;
    
        return $this;
    }

    /**
     * Get cargoLlenado
     *
     * @return string 
     */
    public function getCargoLlenado()
    {
        return $this->cargoLlenado;
    }

    /**
     * Set detalle
     *
     * @param string $detalle
     * @return UnivDatHabHabilitacion
     */
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;
    
        return $this;
    }

    /**
     * Get detalle
     *
     * @return string 
     */
    public function getDetalle()
    {
        return $this->detalle;
    }

    /**
     * Set hojaRuta
     *
     * @param string $hojaRuta
     * @return UnivDatHabHabilitacion
     */
    public function setHojaRuta($hojaRuta)
    {
        $this->hojaRuta = $hojaRuta;
    
        return $this;
    }

    /**
     * Get hojaRuta
     *
     * @return string 
     */
    public function getHojaRuta()
    {
        return $this->hojaRuta;
    }

    /**
     * Set informe
     *
     * @param string $informe
     * @return UnivDatHabHabilitacion
     */
    public function setInforme($informe)
    {
        $this->informe = $informe;
    
        return $this;
    }

    /**
     * Get informe
     *
     * @return string 
     */
    public function getInforme()
    {
        return $this->informe;
    }

    /**
     * Set tipo
     *
     * @param string $tipo
     * @return UnivDatHabHabilitacion
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    
        return $this;
    }

    /**
     * Get tipo
     *
     * @return string 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set fecha
     *
     * @param string $fecha
     * @return UnivDatHabHabilitacion
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return string 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return UnivDatHabHabilitacion
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return string 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set modalidadCursada
     *
     * @param string $modalidadCursada
     * @return UnivDatHabHabilitacion
     */
    public function setModalidadCursada($modalidadCursada)
    {
        $this->modalidadCursada = $modalidadCursada;
    
        return $this;
    }

    /**
     * Get modalidadCursada
     *
     * @return string 
     */
    public function getModalidadCursada()
    {
        return $this->modalidadCursada;
    }

    /**
     * Set reporte
     *
     * @param string $reporte
     * @return UnivDatHabHabilitacion
     */
    public function setReporte($reporte)
    {
        $this->reporte = $reporte;
    
        return $this;
    }

    /**
     * Get reporte
     *
     * @return string 
     */
    public function getReporte()
    {
        return $this->reporte;
    }

    /**
     * Set updatedAt
     *
     * @param string $updatedAt
     * @return UnivDatHabHabilitacion
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return string 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
