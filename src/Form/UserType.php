<?php

namespace App\Form;


use App\Entity\Site;
use App\Entity\User;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ]);
            $passwordConstraints = [];
            if (!$isEdit) {
                $passwordConstraints[] = new NotBlank([
                    'message' => 'Le mot de passe ne peut pas être vide',
                ]);
            }

            $passwordConstraints[] = new PasswordStrength([
                'message' => 'Entrez un mot de passe plus sécurisé',
                'minScore' => PasswordStrength::STRENGTH_WEAK
            ]);

            $builder->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => !$isEdit,
                'options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'first_options' => [
                    'label' => $isEdit ? 'Nouveau mot de passe (laisser vide pour conserver l\'actuel)' : 'Mot de Passe',
                    'constraints' => $passwordConstraints,
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
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'name',
            ])
            ->add('profilePicture', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'maxSizeMessage' => 'Image too large',
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image file (.jpeg, .jpg, .png)',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}
