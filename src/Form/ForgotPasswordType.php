<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ForgotPasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array<string> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'auth.forgetpassword.email',
                'attr' => [
                    "class" => "form-control"
                ],
                'constraints' => [
                    new NotNull([
                        'message' => 'Veuiller insérer une adresse valide'
                    ])
                ]
            ])
            ->add('resetPassword', SubmitType::class, [
                'label' => 'auth.forgetpassword.submit',
                'attr' => [
                    'class' => 'btn btn-success'
                ] 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
