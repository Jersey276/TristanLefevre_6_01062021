<?php

namespace App\Form;

use App\Entity\Media;
use App\Entity\MediaType;
use App\Entity\Trick;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickFrontType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('front', EntityType::class, [
                'label' => 'trick.front.front',
                'class' => Media::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('media')
                        ->where("media.type = 1")
                        ->orderBy('media.id', 'ASC');
                },
                'choice_label' => 'path',
                'placeholder' => 'trick.front.placeholder'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'trick.front.submit'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            'data-class' => Trick::class
        ]);
    }
}
