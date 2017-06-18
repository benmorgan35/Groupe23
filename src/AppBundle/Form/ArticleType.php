<?php

namespace AppBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use AppBundle\Form\Type\TinyMCEType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class ArticleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('date', DatePickerType::class)
            ->add('title')
            ->add('image',FileType::class, array(
                'required' => false))
            ->add('content', TinyMCEType::class)
        ->add('enableComments', CheckboxType::class, array(
            'label'    => 'Autoriser les commentaire ?',
            'required' => false,));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'AppBundle\Entity\Article',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_article';
    }


}
