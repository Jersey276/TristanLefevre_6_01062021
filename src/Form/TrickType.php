<?php

namespace App\Form;

use App\Entity\Trick;
use App\Entity\TrickGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType as TypeTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class TrickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('title', TypeTextType::class, [
                'label' => 'Nom de la figure'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('trickGroup', EntityType::class, [
                'label' => 'Catégorie',
                'class' => TrickGroup::class,
                'choice_label' => 'nameGroup',
                'placeholder' => 'Veuiller choisir une catégorie',
                'constraints' => [
                    new NotNull([
                        'message' => 'Veuiller choisir une catégorie',
                    ])
                ]
            ])
            ->add('frontPath', HiddenType::class)
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
            'data_class' => Trick::class,
        ]);
    }
}
