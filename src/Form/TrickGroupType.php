<?php

namespace App\Form;

use App\Entity\TrickGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class TrickGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('nameGroup', TextType::class,
            [
                'label' => 'Nom du groupe',
                'row_attr' => [
                    'class' => 'form-group'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => new NotNull([
                    'message' => 'Veuiller nommer la nouvelle catégorie'
                ]),
            ])
            ->add('Envoyer', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data_class' => TrickGroup::class,
        ]);
    }
}
