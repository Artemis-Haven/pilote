<?php

namespace Pilote\TaskerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BoardType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'Titre du projet', 
                'attr' => array(
                    'class' => 'form-control' 
                    )))
            ->add('description', 'textarea', array(
                'label' => 'Description', 
                'required' => false,
                'attr' => array(
                    'class' => 'form-control' 
                    )))
            ->add('color', 'hidden', array(
                'label' => 'Couleur de fond', 
                'attr' => array(
                    'class' => 'form-control' 
                    )));
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pilote\TaskerBundle\Entity\Board'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pilote_taskerbundle_board';
    }
}
