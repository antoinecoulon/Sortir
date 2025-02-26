<?php

namespace App\Form;


use App\Entity\Site;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class,[
                'label' => 'Pseudo',
            ])
            ->add('email',)
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'first_options' => [
                    'label' => 'Mot de Passe',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Le mot de passe ne peut pas être vide',
                        ]),
                        new PasswordStrength([
                            'message' => 'Entrez un mot de passe plus sécurisé',
                            'minScore' => PasswordStrength::STRENGTH_WEAK
                        ])
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le Mot de Passe',
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
            ])
            ->add('name', TextType::class,[
                'label' => 'Nom',
            ])
            ->add('firstname', TextType::class,[
                'label' => 'Prénom',
            ])
            ->add('phone', TextType::class,[
                'label' => 'Téléphone',
            ])
            //->add('photo')

            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
