<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('promo', ChoiceType::class, [
                'choices' => [
                    'ING1' => 'ING1', 'ING2' => 'ING2',
                    'IR3' => 'IR3', 'IR4' => 'IR4', 'IR5' => 'IR5',
                    'SEP3' => 'SEP3', 'SEP4' => 'SEP4', 'SEP5' => 'SEP5',
                    'IRA3' => 'IRA3', 'IRA4' => 'IRA4', 'IRA5' => 'IRA5',
                    'SEPA3' => 'SEPA3', 'SEPA4' => 'SEPA4', 'SEPA5' => 'SEPA5',
                    'BACH1' => 'BACH1', 'BACH2' => 'BACH2', 'BACH3' => 'BACH3',
                    'CPI' => 'CPI', 'Personnel esaip' => 'Personnel esaip'
                ]
            ])
            ->add('campus', ChoiceType::class, [
                'choices' => ['Angers' => 'Angers', 'Aix' => 'Aix']
            ])
            ->add('avatarFile', FileType::class, [
                'required' => false,
                'label' => 'Photo de profil',
                'help' => 'Au format .jpg ou .png. La photo de profil sera affichée sur les articles et les pages de vos projets.'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
