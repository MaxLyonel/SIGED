<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sie\DiplomaBundle\Extension;

use Symfony\Bundle\TwigBundle\TokenParser\RenderTokenParser;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\ActionsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Twig extension for Symfony actions helper.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @deprecated Deprecated in 2.2, to be removed in 3.0.
 */
class AppExtension extends \Twig_Extension
{
    private $container;

    public function getFilters()
    {
        return array('base64_encode' => new \Twig_Filter_Method($this, 'base64Encode')); 
    }   

    public function getName() 
    {
        return 'app extension';
    }
     
    public function base64Encode($string)
    {
        return base64_encode($string);  
    }   

}
