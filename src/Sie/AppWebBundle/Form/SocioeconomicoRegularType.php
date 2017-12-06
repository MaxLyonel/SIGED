<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SocioeconomicoRegularType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                //->add('discapacidadTipoId')
                //->add('atenmedicaTipoId')
                //->add('certificadoNac')
                //->add('libretaEscolar')
                //->add('libretaFamiliar')
                //->add('libretaMilitar')
                //->add('libretaVacunas')
                //->add('medioComunicacionCelular')
                //->add('medioComunicacionTelefono')
                //->add('medioComunicacionTelevision')
                //->add('medioComunicacionComputadora')
                //->add('medioComunicacionRadio')
                //->add('medioComunicacionInternet')
                //->add('seguroSalud')
                //->add('discapacidadId')
                //->add('servicioAguadomicilio')
                ->add('servicioAguapileta')
                ->add('servicioAgualagolaguna')
                ->add('servicioAguapozo')
                ->add('servicioAguasisterna')
                //->add('servicioAguavertiente')
                //->add('servicioEnergiaredpublica')
                //->add('servicioEnergiageneradorelectr')
                //->add('servicioEnergiapanelsolar')
                //->add('servicioGasdomicilio')
                //->add('servicioGaslicuado')
                //->add('servicioGasnatural')
                ->add('servicioSanitarioalcantarill')
                ->add('servicioSanitariopozoseptico')
                //->add('direccionDepartamentoId')
                ->add('direccionProvinciaId')
                ->add('direccionSeccionId')
                //->add('direccionCantonId')
                //->add('direccionLocalidadId')
                //->add('empleo')
                ->add('direccionDescLocalidad')
                ->add('direccionZona')
                ->add('direccionCalleNro')
                ->add('direccionTelefono')
                ->add('transporteId')
                ->add('transporteTiempo')
                //->add('transporteDistancia')
                //->add('numeroHijo')
                ->add('servicioAguacaneria')
                ->add('servicioAguaotro')
                ->add('servicioSanitariopozo')
                ->add('servicioSanitariocalle')
                ->add('servicioSanitariorio')
                ->add('direccionNroVivienda')
                ->add('centrosalud')
                ->add('frecuenciaSaludId')
                ->add('discapacidadSensorial')
                ->add('discapacidadMotriz')
                ->add('discapacidadMental')
                ->add('origenDiscapacidadId')
                ->add('actividadId')
                ->add('frecuenciaActividadId')
                ->add('actividadPago')
                ->add('internetDomicilio')
                ->add('internetUe')
                ->add('internetPublico')
                ->add('internetNo')
                ->add('frecuenciaInternetId')
                //->add('frecuenciaTransporteId')
                ->add('servicioEnergia')
                ->add('etniaTipo')
                ->add('idiomaTipo')
                ->add('idiomaTipo2')
                ->add('idiomaTipo3')
                ->add('idiomaTipo4')
                ->add('idiomaTipo5')
                ->add('idiomaTipo6')
                ->add('estudianteInscripcion', 'entity', array(
                    'class' => 'Sie\AppWebBundle\Entity\EstudianteInscripcion',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                                ->setMaxResults(1);
                    },
                    'mapped' => false,
                    'property' => 'id',
                        )
                )
                //->add('gestionTipo')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Sie\AppWebBundle\Entity\SocioeconomicoRegular'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sie_appwebbundle_socioeconomicoregular';
    }

}
