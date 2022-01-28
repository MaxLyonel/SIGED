<?php

namespace Sie\AppWebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormViewInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sie\AppWebBundle\Form\DataTransformer\EntityToIdTransformer;

class AutocompleteEntityType extends AbstractType {

    private $om;

    public function __construct(ObjectManager $om) {
        $this->om = $om;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $class = $options['class'];

        $transformer = new EntityToIdTransformer($this->om, $class);
        $builder->addViewTransformer($transformer);
    }

    public function buildView(FormViewInterface $view, FormInterface $form, array $options) {
        $view->vars['update_route'] = $options['update_route'];
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setRequired(array('class', 'update_route'));
    }

    public function getParent() {
        return 'hidden';
    }

    public function getName() {
        return 'autocomplete_entity';
    }

}
