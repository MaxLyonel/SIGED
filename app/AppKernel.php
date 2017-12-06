<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel {

    public function registerBundles() {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new AppBundle\AppBundle(),
            new Sie\AppWebBundle\SieAppWebBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            //new FOS\UserBundle\FOSUserBundle(),
            //new Gregwar\CaptchaBundle\GregwarCaptchaBundle(),
            new Sie\RegularBundle\SieRegularBundle(),
            new Sie\AlternativaBundle\SieAlternativaBundle(),
            new Sie\EspecialBundle\SieEspecialBundle(),
            new Sie\PermanenteBundle\SiePermanenteBundle(),
            new Sie\DiplomaBundle\SieDiplomaBundle(),
            new Sie\ValidacionBundle\SieValidacionBundle(),
            new Sie\UsuariosBundle\SieUsuariosBundle(),
            new Sie\PnpBundle\SiePnpBundle(),
            new Sie\HerramientaBundle\SieHerramientaBundle(),
            new Sie\RueBundle\SieRueBundle(),
            new Sie\RieBundle\SieRieBundle(),
            new Fresh\DoctrineEnumBundle\FreshDoctrineEnumBundle(),
            new Sie\JuegosBundle\SieJuegosBundle(),
            new Sie\HerramientaAlternativaBundle\SieHerramientaAlternativaBundle(),
            new Sie\DgesttlaBundle\SieDgesttlaBundle(),
            new Sie\EsquemaBundle\SieEsquemaBundle(),
            new Sie\CertificationAltBundle\SieCertificationAltBundle(),
            new Sie\TramitesBundle\SieTramitesBundle(),
            new Sie\InfraestructuraBundle\SieInfraestructuraBundle(),
            new Sie\BjpBundle\SieBjpBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Nelmio\SecurityBundle\NelmioSecurityBundle(),
            new Sie\GisBundle\SieGisBundle(),
            new Sie\InfraBundle\SieInfraBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Acme\DemoBundle\AcmeDemoBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader) {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }

}
