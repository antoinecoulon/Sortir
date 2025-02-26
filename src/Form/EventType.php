<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Site;
use App\Entity\User;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('description', TextAreaType::class, [
                'label' => 'Description',
                'required' => false
            ])
            ->add('maxParticipant', IntegerType::class, [
                'label' => 'Maximum de participants',
                'attr' => [
                    'min' => 1,
                    'max' => 100
                ]
            ])
            // ->add('photo')
            ->add('startAt', DateTimeType::class, [
                'label' => "Début de l'évènement",
                'data' => (new \DateTimeImmutable())->modify('+2 day'),
                'widget' => 'single_text',
            ])
            ->add('endAt', DateTimeType::class, [
                'label' => "Fin de l'évènement",
                'data' => (new \DateTime())->modify('+2 day')->modify('+3 hours'),
                'widget' => 'single_text'
            ])
            ->add('inscriptionLimitAt', DateTimeType::class, [
                'label' => "Date limite d'inscription",
                'data' => (new \DateTime())->modify('+1 day'),
                'widget' => 'single_text'
            ])
            ->add('site', EntityType::class, [
                'label' => 'Site organisateur',
                'class' => Site::class,
                'choice_label' => 'name',
            ])
            ->add('location', EntityType::class, [
                'label' => "Lieu de l'évènement",
                'class' => Location::class,
                'choice_label' => 'name',
            ]);

        if($options['display_isPublish']) {
            $builder->add('isPublished', CheckboxType::class, [
                'label' => 'Publié ?',
                'required' => false
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'display_isPublish' => true
        ]);
    }
}
