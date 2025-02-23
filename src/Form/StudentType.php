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
                'label' => 'registration.form.administrative_info.academic_year',
                'data' => '2024-2025', // Valeur par défaut
                'required' => true,
            ])
            ->add('matricule', TextType::class, [
                'label' => 'registration.form.administrative_info.matricule',
                'required' => false,
            ])
            ->add('fullName', TextType::class, [
                'label' => 'registration.form.personal_info.fullname',
                'required' => true,
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'registration.form.personal_info.birthdate',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('birthPlace', TextType::class, [
                'label' => 'registration.form.personal_info.birthplace',
                'required' => true,
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'registration.form.personal_info.gender',
                'choices' => [
                    'registration.form.personal_info.gender_choices.m' => 'M',
                    'registration.form.personal_info.gender_choices.f' => 'F',
                ],
                'required' => true,
            ])
            ->add('nationality', ChoiceType::class, [
                'label' => 'registration.form.personal_info.nationality',
                'choices' => [
                    'registration.form.personal_info.nationality_choices.ca' => 'Cameroun',
                    'registration.form.personal_info.nationality_choices.co' => 'Congo',
                ],
                'required' => true,
            ])
            ->add('handicap', ChoiceType::class, [
                'label' => 'registration.form.personal_info.handicap',
                'choices' => [
                    'registration.form.personal_info.handicap_choices.yes' => true,
                    'registration.form.personal_info.handicap_choices.no' => false,
                ],
                'required' => true,
            ])
            ->add('baccalaureat', TextType::class, [
                'label' => 'registration.form.academic_info.certificate',
                'required' => true,
            ])
            ->add('filiere', ChoiceType::class, [
                'label' => 'registration.form.academic_info.faculty',
                'choices' => [
                    'registration.form.academic_info.faculty_choices.isn' => 'ISN',
                    'registration.form.academic_info.faculty_choices.ins' => 'INS',
                    'registration.form.academic_info.faculty_choices.cdn' => 'CDN',
                ],
                'required' => true,
            ])
            ->add('language', ChoiceType::class, [
                'label' => 'registration.form.academic_info.language',
                'choices' => [
                    'registration.form.academic_info.language_choices.fr' => 'FRANCAIS',
                    'registration.form.academic_info.language_choices.en' => 'ANGLAIS',
                ],
                'expanded' => true, // Pour afficher comme radio buttons
                'required' => true,
            ])
            ->add('parentName', TextType::class, [
                'label' => 'registration.form.contact_info.parentname',
                'required' => true,
            ])
            ->add('parentPhone', TelType::class, [
                'label' => 'registration.form.contact_info.parentphone',
                'required' => true,
            ])
            ->add('motherName', TextType::class, [
                'label' => 'registration.form.contact_info.mothername',
                'required' => true,
            ])
            ->add('motherPhone', TelType::class, [
                'label' => 'registration.form.contact_info.motherphone',
                'required' => true,
            ])
            ->add('address', TextType::class, [
                'label' => 'registration.form.contact_info.address',
                'required' => true,
            ])
            ->add('phone', TelType::class, [
                'label' => 'registration.form.contact_info.phone',
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'registration.form.contact_info.email',
                'required' => true,
            ])
            ->add('activities', ChoiceType::class, [
                'label' => 'registration.form.hobbies_title.parentname',
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    'registration.form.hobbies_choices.sport' => 'sport',
                    'registration.form.hobbies_choices.music' => 'musique',
                    'registration.form.hobbies_choices.dance' => 'danse',
                    'registration.form.hobbies_choices.comedy' => 'comedie',
                    'registration.form.hobbies_choices.sing' => 'chant',
                    'registration.form.hobbies_choices.other' => 'autre',
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