<?php

namespace App\Form;

use App\Entity\Student;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('academicYear', TextType::class, [
                'label' => 'Année académique / Academic Year',
                'data' => '2024-2025', // Valeur par défaut
                'required' => true,
            ])
            ->add('matricule', TextType::class, [
                'label' => 'N° Matricule',
                'required' => false,
            ])
            ->add('fullName', TextType::class, [
                'label' => 'Noms et prénoms de l\'étudiant',
                'required' => true,
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('birthPlace', TextType::class, [
                'label' => 'Lieu de naissance',
                'required' => true,
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => [
                    'Masculin' => 'M',
                    'Féminin' => 'F',
                ],
                'required' => true,
            ])
            ->add('nationality', TextType::class, [
                'label' => 'Nationalité',
                'required' => true,
            ])
            ->add('handicap', ChoiceType::class, [
                'label' => 'Handicap',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'required' => true,
            ])
            ->add('baccalaureat', TextType::class, [
                'label' => 'Baccalauréat (série) / GCE Advanced Level (option)',
                'required' => true,
            ])
            ->add('filiere', TextType::class, [
                'label' => 'Filière',
                'required' => true,
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'Langue officielle',
                'choices' => [
                    'Français' => 'FRANCAIS',
                    'Anglais' => 'ANGLAIS',
                ],
                'expanded' => true, // Pour afficher comme radio buttons
                'required' => true,
            ])
            ->add('parentName', TextType::class, [
                'label' => 'Noms du père ou du tuteur',
                'required' => true,
            ])
            ->add('parentPhone', TelType::class, [
                'label' => 'Téléphone du père/tuteur',
                'required' => true,
            ])
            ->add('motherName', TextType::class, [
                'label' => 'Noms de la mère ou de la tutrice',
                'required' => true,
            ])
            ->add('motherPhone', TelType::class, [
                'label' => 'Téléphone de la mère/tutrice',
                'required' => true,
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse (quartier et cité de résidence)',
                'required' => true,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
            ])
            ->add('activities', ChoiceType::class, [
                'label' => 'Loisirs',
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    'Sport' => 'sport',
                    'Musique' => 'musique',
                    'Danse' => 'danse',
                    'Comédie' => 'comedie',
                    'Chant' => 'chant',
                    'Autre' => 'autre',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
        ]);
    }
}