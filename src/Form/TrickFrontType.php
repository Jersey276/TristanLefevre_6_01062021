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
        $id = $options['id'];
        $builder
            ->add('front', EntityType::class, [
                'label' => 'trick.front.front',

                'class' => Media::class,
                'query_builder' => function(EntityRepository $er) use ($id) {
                    return $er->createQueryBuilder('media')
                        ->where("media.type = 1")
                        ->where("media.trick = :trick")
                        ->setParameter(":trick", $id)
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
            'data-class' => Trick::class,
            'id' => null
        ]);
    }
}
