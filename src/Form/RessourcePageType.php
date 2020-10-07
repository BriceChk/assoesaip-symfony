<?php

namespace App\Form;

use App\Entity\RessourcePage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RessourcePageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('shortTitle', TextType::class)
            ->add('orderPosition', IntegerType::class)
            ->add('url', TextType::class)
            ->add('description', TextType::class, [
                'required' => false,
                'empty_data' => ''
            ])
            ->add('html', TextType::class, [
                'required' => false,
                'empty_data' => ''
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RessourcePage::class,
            'allow_extra_fields' => true
        ]);
    }
}
