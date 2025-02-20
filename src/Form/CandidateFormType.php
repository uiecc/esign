<?php

namespace App\Form;

use App\Entity\Candidate;
use App\EventListener\CandidateFormValidationListener;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;


class CandidateFormType extends AbstractType
{
    private $translator;
    private $validationListener;

    public function __construct(TranslatorInterface $translator, CandidateFormValidationListener $validationListener)
    {
        $this->translator = $translator;
        $this->validationListener = $validationListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentStep', HiddenType::class, [
                'mapped' => false, // This ensures currentStep is not mapped to the Candidate entity
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'oninput' => "this.value = this.value.toUpperCase();"
                ],
            ])
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('candidate_form.sexe.male') => $this->translator->trans('candidate_form.sexe.male'),
                    $this->translator->trans('candidate_form.sexe.female') => $this->translator->trans('candidate_form.sexe.female'),
                ],
            ])
            ->add('region', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    $this->translator->trans('candidate_form.nationality.cameroon') => [
                        $this->translator->trans('candidate_form.region.adamaoua') => $this->translator->trans('candidate_form.region.adamaoua'),
                        $this->translator->trans('candidate_form.region.centre') => $this->translator->trans('candidate_form.region.centre'),
                        $this->translator->trans('candidate_form.region.est') => $this->translator->trans('candidate_form.region.est'),
                        $this->translator->trans('candidate_form.region.extreme_nord') => $this->translator->trans('candidate_form.region.extreme_nord'),
                        $this->translator->trans('candidate_form.region.littoral') => $this->translator->trans('candidate_form.region.littoral'),
                        $this->translator->trans('candidate_form.region.nord') => $this->translator->trans('candidate_form.region.nord'),
                        $this->translator->trans('candidate_form.region.nord_ouest') => $this->translator->trans('candidate_form.region.nord_ouest'),
                        $this->translator->trans('candidate_form.region.ouest') => $this->translator->trans('candidate_form.region.ouest'),
                        $this->translator->trans('candidate_form.region.sud') => $this->translator->trans('candidate_form.region.sud'),
                        $this->translator->trans('candidate_form.region.sud_ouest') => $this->translator->trans('candidate_form.region.sud_ouest'),
                    ],
                    $this->translator->trans('candidate_form.nationality.congo') => [
                        $this->translator->trans('candidate_form.region.bouenza') => $this->translator->trans('candidate_form.region.bouenza'),
                        $this->translator->trans('candidate_form.region.cuvette') => $this->translator->trans('candidate_form.region.cuvette'),
                        $this->translator->trans('candidate_form.region.cuvette_ouest') => $this->translator->trans('candidate_form.region.cuvette_ouest'),
                        $this->translator->trans('candidate_form.region.likouala') => $this->translator->trans('candidate_form.region.likouala'),
                        $this->translator->trans('candidate_form.region.lékoumou') => $this->translator->trans('candidate_form.region.lékoumou'),
                        $this->translator->trans('candidate_form.region.niari') => $this->translator->trans('candidate_form.region.niari'),
                        $this->translator->trans('candidate_form.region.pointe_noire') => $this->translator->trans('candidate_form.region.pointe_noire'),
                        $this->translator->trans('candidate_form.region.pool') => $this->translator->trans('candidate_form.region.pool'),
                        $this->translator->trans('candidate_form.region.sangha') => $this->translator->trans('candidate_form.region.sangha'),
                        $this->translator->trans('candidate_form.region.brazzaville') => $this->translator->trans('candidate_form.region.brazzaville'),
                        $this->translator->trans('candidate_form.region.kouilou') => $this->translator->trans('candidate_form.region.kouilou'),
                        $this->translator->trans('candidate_form.region.plateaux') => $this->translator->trans('candidate_form.region.plateaux'),
                    ],
                ],
                'attr' => ['class' => 'region-select'],
            ])
            
            ->add('depertement', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    $this->translator->trans('candidate_form.nationality.congo') => [
                        $this->translator->trans('candidate_form.region.bouenza') => [
                            'Boko-Songho' => 'Boko-Songho',
                            'Loudima' => 'Loudima',
                            'Madingo-Kayes' => 'Madingo-Kayes',
                            'Mouyondzi' => 'Mouyondzi',
                            'Nkayi' => 'Nkayi',
                            'Yamba' => 'Yamba',
                            'Kingoué' => 'Kingoué',
                        ],
                        $this->translator->trans('candidate_form.region.cuvette') => [
                            'Boundji' => 'Boundji',
                            'Lékana' => 'Lékana',
                            'Makoua' => 'Makoua',
                            'Mossaka' => 'Mossaka',
                            'Ngoko' => 'Ngoko',
                            'Owando' => 'Owando',
                            'Oyo' => 'Oyo',
                            'Tchikapika' => 'Tchikapika',
                        ],
                        $this->translator->trans('candidate_form.region.cuvette_ouest') => [
                            'Ewo' => 'Ewo',
                            'Etoumbi' => 'Etoumbi',
                            'Kelle' => 'Kelle',
                            'Mbama' => 'Mbama',
                            'Okoyo' => 'Okoyo',
                        ],
                        $this->translator->trans('candidate_form.region.likouala') => [
                            'Bétou' => 'Bétou',
                            'Dongou' => 'Dongou',
                            'Epéna' => 'Epéna',
                            'Impfondo' => 'Impfondo',
                            'Liranga' => 'Liranga',
                            'Loukolela' => 'Loukolela',
                        ],
                        $this->translator->trans('candidate_form.region.lékoumou') => [
                            'Bambama' => 'Bambama',
                            'Komono' => 'Komono',
                            'Sibiti' => 'Sibiti',
                            'Mayéyé' => 'Mayéyé',
                            'Zanaga' => 'Zanaga',
                        ],
                        $this->translator->trans('candidate_form.region.niari') => [
                            'Divenié' => 'Divenié',
                            'Kimongo' => 'Kimongo',
                            'Loutété' => 'Loutété',
                            'Louzala' => 'Louzala',
                            'Mossendjo' => 'Mossendjo',
                            'Moutamba' => 'Moutamba',
                        ],
                        $this->translator->trans('candidate_form.region.pointe_noire') => [
                            'Arrondissement 1 Lumumba' => 'Arrondissement 1 Lumumba',
                            'Arrondissement 2 Mvoumvou' => 'Arrondissement 2 Mvoumvou',
                            'Arrondissement 3 Tié-Tié' => 'Arrondissement 3 Tié-Tié',
                            'Arrondissement 4 Loandjili' => 'Arrondissement 4 Loandjili',
                            'Arrondissement 5 Mongo Mpoukou' => 'Arrondissement 5 Mongo Mpoukou',
                        ],
                        $this->translator->trans('candidate_form.region.pool') => [
                            'Kinkala' => 'Kinkala',
                            'Mindouli' => 'Mindouli',
                            'Ngabé' => 'Ngabé',
                            'Loukanga' => 'Loukanga',
                            'Mayama' => 'Mayama',
                            'Vindza' => 'Vindza',
                            'Kindamba' => 'Kindamba',
                        ],
                        $this->translator->trans('candidate_form.region.sangha') => [
                            'Ouesso' => 'Ouesso',
                            'Sembé' => 'Sembé',
                            'Souanké' => 'Souanké',
                            'Pikounda' => 'Pikounda',
                            'Kabo' => 'Kabo',
                        ],
                        $this->translator->trans('candidate_form.region.brazzaville') => [
                            'Arrondissement 1 Makélékélé' => 'Arrondissement 1 Makélékélé',
                            'Arrondissement 2 Bacongo' => 'Arrondissement 2 Bacongo',
                            'Arrondissement 3 Poto-Poto' => 'Arrondissement 3 Poto-Poto',
                            'Arrondissement 4 Moungali' => 'Arrondissement 4 Moungali',
                            'Arrondissement 5 Ouenzé' => 'Arrondissement 5 Ouenzé',
                            'Arrondissement 6 Talangaï' => 'Arrondissement 6 Talangaï',
                            'Arrondissement 7 Mfilou' => 'Arrondissement 7 Mfilou',
                            'Arrondissement 8 Madibou' => 'Arrondissement 8 Madibou',
                            'Arrondissement 9 Djiri' => 'Arrondissement 9 Djiri',
                        ],
                        $this->translator->trans('candidate_form.region.kouilou') => [
                            'Hinda' => 'Hinda',
                            'Madingo-Kayes' => 'Madingo-Kayes',
                            'Loango' => 'Loango',
                            'Nzassi' => 'Nzassi',
                        ], 
                        $this->translator->trans('candidate_form.region.plateaux') => [
                            'Abala' => 'Abala',
                            'Djambala' => 'Djambala',
                            'Gamboma' => 'Gamboma',
                            'Lekana' => 'Lekana',
                            'Makotimpoko' => 'Makotimpoko',
                            'Mbon' => 'Mbon',
                            'Mpouya' => 'Mpouya',
                            'Ngo' => 'Ngo',
                            'Ollombo' => 'Ollombo',
                        ],                          
                    ],
                    $this->translator->trans('candidate_form.nationality.cameroon') => [

                            $this->translator->trans('candidate_form.region.adamaoua') => [
                                'Djérem' => 'Djérem',
                                'Faro-et-Déo' => 'Faro-et-Déo',
                                'Mbéré' => 'Mbéré',
                                'Mayo-Banyo' => 'Mayo-Banyo',
                                'Vina' => 'Vina',
                            ],
                            $this->translator->trans('candidate_form.region.centre') => [
                                'Haute-Sanaga' => 'Haute-Sanaga',
                                'Lekié' => 'Lekié',
                                'Mbam-et-Inoubou' => 'Mbam-et-Inoubou',
                                'Mbam-et-Kim' => 'Mbam-et-Kim',
                                'Méfou-et-Afamba' => 'Méfou-et-Afamba',
                                'Méfou-et-Akono' => 'Méfou-et-Akono',
                                'Mfoundi' => 'Mfoundi',
                                'Nyong-et-Kéllé' => 'Nyong-et-Kéllé',
                                'Nyong-et-Mfoumou' => 'Nyong-et-Mfoumou',
                                'Nyong-et-So\'o' => 'Nyong-et-So\'o',
                            ],
                            $this->translator->trans('candidate_form.region.est') => [
                                'Boumba-et-Ngoko' => 'Boumba-et-Ngoko',
                                'Haut-Nyong' => 'Haut-Nyong',
                                'Lom-et-Djerem' => 'Lom-et-Djerem',
                                'Kadey' => 'Kadey',
                            ],
                            $this->translator->trans('candidate_form.region.extreme_nord') => [
                                'Diamaré' => 'Diamaré',
                                'Mayo-Danay' => 'Mayo-Danay',
                                'Mayo-Kani' => 'Mayo-Kani',
                                'Mayo-Sava' => 'Mayo-Sava',
                                'Logone-et-Chari' => 'Logone-et-Chari',
                                'Mayo-Tsanaga' => 'Mayo-Tsanaga',
                            ],
                            $this->translator->trans('candidate_form.region.littoral') => [
                                'Wouri' => 'Wouri',
                                'Sanaga-Maritime' => 'Sanaga-Maritime',
                                'Nkam' => 'Nkam',
                                'Moungo' => 'Moungo',
                            ],
                            $this->translator->trans('candidate_form.region.nord') => [
                                'Bénoué' => 'Bénoué',
                                'Faro' => 'Faro',
                                'Mayo-Louti' => 'Mayo-Louti',
                                'Mayo-Rey' => 'Mayo-Rey',
                            ],
                            $this->translator->trans('candidate_form.region.nord_ouest')  => [
                                'Boyo' => 'Boyo',
                                'Donga-Mantung' => 'Donga-Mantung',
                                'Menchum' => 'Menchum',
                                'Momo' => 'Momo',
                                'Ngo-ketunjia' => 'Ngo-ketunjia',
                                'Bui' => 'Bui',
                                'Mezam' => 'Mezam',
                            ],
                            $this->translator->trans('candidate_form.region.sud') => [
                                'Dja-et-Lobo' => 'Dja-et-Lobo',
                                'Océan' => 'Océan',
                                'Mvila' => 'Mvila',
                                'Vallée du Ntem' => 'Vallée du Ntem'
                            ],
                            $this->translator->trans('candidate_form.region.sud_ouest') => [
                                'Fako' => 'Fako',
                                'Kupe-Muanenguba' => 'Kupe-Muanenguba',
                                'Lebialem' => 'Lebialem',
                                'Manyu' => 'Manyu',
                                'Ndian' => 'Ndian',
                                'Meme' => 'Meme',
                            ],
                            $this->translator->trans('candidate_form.region.ouest') => [
                                'Bamboutos' => 'Bamboutos',
                                'Haut-Nkam' => 'Haut-Nkam',
                                'Menoua' => 'Menoua',
                                'Mifi' => 'Mifi',
                                'Ndé' => 'Ndé',
                                'Noun' => 'Noun',
                                'Koung-Khi' => 'Koung-Khi',
                                'Hauts-Plateaux' => 'Hauts-Plateaux',
                            ],
                    ],
                    
                 
                    // Add other departments for each region
                ],
                'attr' => ['class' => 'department-select'],
            ])
            ->add('cni', TextType::class)
            ->add('field', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('candidate_form.field.digital_creation') => 'Création et Design Numériques',
                    $this->translator->trans('candidate_form.field.digital_systems') => 'Ingénierie des Systèmes Numériques',
                    $this->translator->trans('candidate_form.field.sociotechnical') => 'Ingénierie Numérique Sociotechnique',
                ],
                'attr' => ['class' => 'field-select'],
            ])
            ->add('educationSubsystem', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    'FRANCOPHONE' => 'FRANCOPHONE',
                    'ANGLOPHONE' => 'ANGLOPHONE',

                ],
                'attr' => ['class' => 'educationSubSystem-select'],
            ])
            

            ->add('educationalSystemCheck', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    $this->translator->trans('candidate_form.step2_title.yes') => 'Oui',
                    $this->translator->trans('candidate_form.step2_title.no') => 'Non',

                ],
                'attr' => ['class' => 'educationalSystemCheck-select'],
            ])

            ->add('bacSubject', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    'MATHS' => 'MATHS',
                    'INFORMATIQUE' => 'INFORMATIQUE',
                ],
                'attr' => ['class' => 'bacSubject-select'],
            ])

            ->add('bacSubjectMark', TextType::class, [
                'attr' => [
                    'class' => 'bacSubjectMark-input',
                ],
            ])

            ->add('probatoireSubject', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    'MATHS' => 'MATHS',
                    'INFORMATIQUE' => 'INFORMATIQUE',

                ],
                'attr' => ['class' => 'probatoireSubject-select'],
            ])

            ->add('probatoireSubjectMark', TextType::class, [
                'attr' => [
                    'class' => 'probatoireSubjectMark-input',
                ],
            ])

            ->add('gceAlevelSubject', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    'MATHS' => 'MATHS',
                    'COMPUTER SCIENCE' => 'COMPUTER SCIENCE',
                    'ICT' => 'ICT',

                ],
                'attr' => ['class' => 'gceAlevelSubject-select'],
            ])

            ->add('gceAlevelSubjectGrade', TextType::class)

            ->add('gceOlevelSubject', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    'MATHS' => 'MATHS',
                    'COMPUTER SCIENCE' => 'COMPUTER SCIENCE',
                    'ICT' => 'ICT',

                ],
                'attr' => ['class' => 'gceOlevelSubject-select'],
            ])

            ->add('gceOlevelSubjectGrade', TextType::class)


            ->add('admissionType', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    $this->translator->trans('candidate_form.admissionType1.onTitle') => 'Sur Titre',
                    $this->translator->trans('candidate_form.admissionType1.byExam')  => 'Par Epreuve',

                ],
                'attr' => ['class' => 'admissionType-select'],
            ])

            ->add('examinationCenter', ChoiceType::class, [
                'choices' => [
                    'Sangmelima' => 'SANGMÉLIMA',
                    'Bafoussam' => 'BAFOUSSAM',
                    'Yaoundé' => 'YAOUNDÉ.',
                    'Douala' => 'DOUALA',
                    'Garoua' => 'GAROUA',
                    'Brazzaville' => 'BRAZZAVILLE',
                    'Pointe-Noire' => 'POINTE-NOIRE',
                    'Owando' => 'OWANDO',
                    'Ouesso' => 'OUESSO'
                    // Add other examination centers if necessary
                ],
            ])
            ->add('certificate', FileType::class, [
                'label' => 'Certificate (Image file)',
                'mapped' => false,
                'data_class' => null,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/heic',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image document',
                    ])
                ],
            ])

            // In your CandidateFormType class
            ->add('medicalCertificate', FileType::class, [
                'label' => 'Medical Certificate (PDF or Image file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/heic',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF or image document',
                    ])
                ],
            ])
            ->add('criminalRecordExtract', FileType::class, [
                'label' => 'Criminal Record Extract (PDF or Image file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/heic',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF or image document',
                    ])
                ],
            ])
            ->add('transcript', FileType::class, [
                'label' => 'Transcript (PDF or Image file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/heic',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF or image document',
                    ])
                ],
            ])

            ->add('dateOfBirth', DateType::class, [
                'widget' => 'single_text',
                'html5' => false, // Disable HTML5 native date picker to ensure flatpickr is used
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'placeholder' => 'dd/mm/yyyy',
                ],
               'constraints' => [
    new GreaterThanOrEqual([
        'value' => new \DateTime('2002-10-26'), // Set to 26 October 2002
        'message' => 'La date de naissance doit être le 26 octobre 2002 ou après.',
    ]),
],

            ])
            
                
            ->add('placeOfBirth', TextType::class)
            ->add('certificateYearBAC', ChoiceType::class, [
                'choices' => array_merge(
                    ['-' => '-'], // Add "-" option
                    array_combine(range(2001, 2024), range(2001, 2024)) // Map years to values
                ),
                'choice_label' => function ($choice, $key, $value) {
                    return $choice; // Use the same value for the label
                },
                'attr' => ['class' => 'form-select'],
                'required' => true,
            ])
            ->add('certificateYearGCE', ChoiceType::class, [
                'choices' => array_merge(
                    ['-' => '-'], // Add "-" option
                    array_combine(range(2001, 2024), range(2001, 2024)) // Map years to values
                ),
                'choice_label' => function ($choice, $key, $value) {
                    return $choice; // Use the same value for the label
                },
                'attr' => ['class' => 'form-select'],
                'required' => true,
            ])
            ->add('language', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('candidate_form.language.french') => 'Français',
                    $this->translator->trans('candidate_form.language.english') => 'Anglais',
                ],
            ])
            ->add('transactionNumber', TextType::class)
            ->add('phoneNumber', TextType::class)

            ->add('nationality', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('candidate_form.nationality.africa') => [
                        $this->translator->trans('candidate_form.nationality.cameroon') => $this->translator->trans('candidate_form.nationality.cameroon'),
                        $this->translator->trans('candidate_form.nationality.congo') => 'Congo',
                        $this->translator->trans('candidate_form.nationality.gabon') => 'Gabon',
                        $this->translator->trans('candidate_form.nationality.ghana') => 'Ghana',
                        $this->translator->trans('candidate_form.nationality.mali') => 'Mali',
                        $this->translator->trans('candidate_form.nationality.mauritania') => 'Mauritanie',
                        $this->translator->trans('candidate_form.nationality.niger') => 'Niger',
                        $this->translator->trans('candidate_form.nationality.nigeria') => 'Nigeria',
                        $this->translator->trans('candidate_form.nationality.senegal') => 'Senegal',
                        $this->translator->trans('candidate_form.nationality.algeria') => 'Algerie',
                        $this->translator->trans('candidate_form.nationality.togo') => 'Togo',
                        $this->translator->trans('candidate_form.nationality.tunisia') => 'Tunisia',
                        $this->translator->trans('candidate_form.nationality.egypt') => 'Egypt',
                        $this->translator->trans('candidate_form.nationality.morocco') => 'Maroc',
                        $this->translator->trans('candidate_form.nationality.sudan') => 'South Sudan',
                        $this->translator->trans('candidate_form.nationality.south_africa') => 'South Africa',
                        $this->translator->trans('candidate_form.nationality.angola') => 'Angola',
                        $this->translator->trans('candidate_form.nationality.burkina_faso') => 'Burkina Faso',
                        $this->translator->trans('candidate_form.nationality.burundi') => 'Burundi',
                        $this->translator->trans('candidate_form.nationality.central_african_republic') => 'Central African Republic',
                        $this->translator->trans('candidate_form.nationality.chad') => 'Chad',
                        $this->translator->trans('candidate_form.nationality.comoros') => 'Comoros',
                        $this->translator->trans('candidate_form.nationality.dr_congo') => 'Democratic Republic of Congo',
                        $this->translator->trans('candidate_form.nationality.djibouti') => 'Djibouti',
                        $this->translator->trans('candidate_form.nationality.eritrea') => 'Eritrea',
                        $this->translator->trans('candidate_form.nationality.ethiopia') => 'Ethiopia',
                        $this->translator->trans('candidate_form.nationality.gambia') => 'Gambia',
                        $this->translator->trans('candidate_form.nationality.guinea') => 'Guinea',
                        $this->translator->trans('candidate_form.nationality.guinea_bissau') => 'Guinea Bissau',
                        $this->translator->trans('candidate_form.nationality.kenya') => 'Kenya',
                        $this->translator->trans('candidate_form.nationality.lesotho') => 'Lesotho',
                        $this->translator->trans('candidate_form.nationality.liberia') => 'Liberia',
                        $this->translator->trans('candidate_form.nationality.libya') => 'Libya',
                        $this->translator->trans('candidate_form.nationality.madagascar') => 'Madagascar',
                        $this->translator->trans('candidate_form.nationality.malawi') => 'Malawi',
                        $this->translator->trans('candidate_form.nationality.mauritius') => 'Mauritius',
                        $this->translator->trans('candidate_form.nationality.mozambique') => 'Mozambique',
                        $this->translator->trans('candidate_form.nationality.myanmar') => 'Myanmar',
                        $this->translator->trans('candidate_form.nationality.rwanda') => 'Rwanda',
                        $this->translator->trans('candidate_form.nationality.sao_tome_and_principe') => 'Sao Tome and Principe',
                        $this->translator->trans('candidate_form.nationality.seychelles') => 'Sychelles',
                        $this->translator->trans('candidate_form.nationality.somalia') => 'Somalia',
                        $this->translator->trans('candidate_form.nationality.sudan') => 'Sudan',
                        $this->translator->trans('candidate_form.nationality.benin') => 'Benin',
                        $this->translator->trans('candidate_form.nationality.botswana') => 'Botswana',
                        $this->translator->trans('candidate_form.nationality.cabo_verde') => 'Cabo Verde',
                        $this->translator->trans('candidate_form.nationality.eq_guinea') => 'Equatorial Guinea',
                        $this->translator->trans('candidate_form.nationality.eswatini') => 'Eswatini',
                        $this->translator->trans('candidate_form.nationality.ivory_coast') => 'Ivory Coast',
                        $this->translator->trans('candidate_form.nationality.namibia') => 'Namibia',
                        $this->translator->trans('candidate_form.nationality.sierra_leone') => 'Sierra Leone',
                        $this->translator->trans('candidate_form.nationality.tanzania') => 'Tanzania',
                        $this->translator->trans('candidate_form.nationality.uganda') => 'Uganda',
                        $this->translator->trans('candidate_form.nationality.zambia') => 'Zambia',
                        $this->translator->trans('candidate_form.nationality.zimbabwe') => 'Zimbabwe',

                    ],
                    $this->translator->trans('candidate_form.nationality.other_countries') => [
                        '-',
                        $this->translator->trans('candidate_form.nationality.canada') => 'Canada',
                        $this->translator->trans('candidate_form.nationality.us') => 'United States of America',
                        $this->translator->trans('candidate_form.nationality.uk') => 'United Kingdom',
                        $this->translator->trans('candidate_form.nationality.france') => 'France',
                        $this->translator->trans('candidate_form.nationality.india') => 'India',
                        $this->translator->trans('candidate_form.nationality.germany') => 'Germany',
                        $this->translator->trans('candidate_form.nationality.china') => 'China',
                        $this->translator->trans('candidate_form.nationality.russia') => 'Russia',

                    ],

                ],
                'attr' => ['class' => 'nationality-select'],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo (Image file)',
                'mapped' => false,
                'data_class' => null,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'application/pdf',

                        ],
                        'mimeTypesMessage' => 'Please upload a valid image document',
                    ])
                ],
            ])
            ->add('birthCertificate', FileType::class, [
                'label' => 'Photo (Image file)',
                'mapped' => false,
                'data_class' => null,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10240k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image document',
                    ])
                ],
            ])

            ->add('cniIssueDate', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy', // Specify the expected format
                'attr' => [
                    'class' => 'js-datepicker',
                    'placeholder' => 'dd/mm/yyyy',
                ],
                // Add any necessary constraints here
            ])
            ->add('secondaryEducationStartYear', ChoiceType::class, [
                'choices' => array_merge(
                    ['-' => '-'], // Add "-" option
                    array_combine(range(2001, 2024), range(2001, 2024)) // Map years to values
                ),
                'choice_label' => function ($choice, $key, $value) {
                    return $choice; // Use the same value for the label
                },
                'attr' => ['class' => 'form-select'],
            ])
            ->add('secondaryEducationEndYear', ChoiceType::class, [
                'choices' => array_merge(
                    ['-' => '-'], // Add "-" option
                    array_combine(range(2001, 2024), range(2001, 2024)) // Map years to values
                ),
                'choice_label' => function ($choice, $key, $value) {
                    return $choice; // Use the same value for the label
                },
                'attr' => ['class' => 'form-select'],
            ])

            ->add('secondCycleStudyType', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('candidate_form.secondCycleStudyType.Littéraire') => $this->translator->trans('candidate_form.secondCycleStudyType.Littéraire'),
                    $this->translator->trans('candidate_form.secondCycleStudyType.Scientifique') => $this->translator->trans('candidate_form.secondCycleStudyType.Scientifique'),
                    $this->translator->trans('candidate_form.secondCycleStudyType.Technique_industriel') => $this->translator->trans('candidate_form.secondCycleStudyType.Technique_industriel'),
                    $this->translator->trans('candidate_form.secondCycleStudyType.Technique_tertiaire') => $this->translator->trans('candidate_form.secondCycleStudyType.Technique_tertiaire')
                ],
            ])
            ->add('baccalaureateCountry', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    $this->translator->trans('candidate_form.nationality.africa') => [
                        $this->translator->trans('candidate_form.nationality.cameroon') => $this->translator->trans('candidate_form.nationality.cameroon'),
                        $this->translator->trans('candidate_form.nationality.congo') => 'Congo',
                        $this->translator->trans('candidate_form.nationality.gabon') => 'Gabon',
                        $this->translator->trans('candidate_form.nationality.mali') => 'Mali',
                        $this->translator->trans('candidate_form.nationality.mauritania') => 'Mauritanie',
                        $this->translator->trans('candidate_form.nationality.niger') => 'Niger',
                        $this->translator->trans('candidate_form.nationality.senegal') => 'Senegal',
                        $this->translator->trans('candidate_form.nationality.algeria') => 'Algerie',
                        $this->translator->trans('candidate_form.nationality.togo') => 'Togo',
                        $this->translator->trans('candidate_form.nationality.tunisia') => 'Tunisia',
                        $this->translator->trans('candidate_form.nationality.egypt') => 'Egypt',
                        $this->translator->trans('candidate_form.nationality.morocco') => 'Maroc',
                        $this->translator->trans('candidate_form.nationality.sudan') => 'South Sudan',
                        $this->translator->trans('candidate_form.nationality.burkina_faso') => 'Burkina Faso',
                        $this->translator->trans('candidate_form.nationality.burundi') => 'Burundi',
                        $this->translator->trans('candidate_form.nationality.central_african_republic') => 'Central African Republic',
                        $this->translator->trans('candidate_form.nationality.chad') => 'Chad',
                        $this->translator->trans('candidate_form.nationality.dr_congo') => 'Democratic Republic of Congo',
                        $this->translator->trans('candidate_form.nationality.djibouti') => 'Djibouti',
                        $this->translator->trans('candidate_form.nationality.eritrea') => 'Eritrea',
                        $this->translator->trans('candidate_form.nationality.ethiopia') => 'Ethiopia',
                        $this->translator->trans('candidate_form.nationality.gambia') => 'Gambia',
                        $this->translator->trans('candidate_form.nationality.guinea') => 'Guinea',
                        $this->translator->trans('candidate_form.nationality.guinea_bissau') => 'Guinea Bissau',
                        $this->translator->trans('candidate_form.nationality.kenya') => 'Kenya',
                        $this->translator->trans('candidate_form.nationality.lesotho') => 'Lesotho',
                        $this->translator->trans('candidate_form.nationality.liberia') => 'Liberia',
                        $this->translator->trans('candidate_form.nationality.libya') => 'Libya',
                        $this->translator->trans('candidate_form.nationality.madagascar') => 'Madagascar',
                        $this->translator->trans('candidate_form.nationality.malawi') => 'Malawi',
                        $this->translator->trans('candidate_form.nationality.mauritius') => 'Mauritius',
                        $this->translator->trans('candidate_form.nationality.mozambique') => 'Mozambique',
                        $this->translator->trans('candidate_form.nationality.cabo_verde') => 'Cabo Verde',
                        $this->translator->trans('candidate_form.nationality.eq_guinea') => 'Equatorial Guinea',
                        $this->translator->trans('candidate_form.nationality.angola') => 'Angola',
                        $this->translator->trans('candidate_form.nationality.benin') => 'Benin',
                        $this->translator->trans('candidate_form.nationality.botswana') => 'Botswana',
                        $this->translator->trans('candidate_form.nationality.comoros') => 'Comoros',
                        $this->translator->trans('candidate_form.nationality.ivory_coast') => 'Ivory Coast',
                        $this->translator->trans('candidate_form.nationality.eswatini') => 'Eswatini',
                        $this->translator->trans('candidate_form.nationality.ghana') => 'Ghana',
                        $this->translator->trans('candidate_form.nationality.zambia') => 'Zambia',
                        $this->translator->trans('candidate_form.nationality.zimbabwe') => 'Zimbabwe',
                        $this->translator->trans('candidate_form.nationality.mauritius') => 'Mauritius',
                        $this->translator->trans('candidate_form.nationality.morocco') => 'Morocco',
                        $this->translator->trans('candidate_form.nationality.namibia') => 'Namibia',
                        $this->translator->trans('candidate_form.nationality.nigeria') => 'Nigeria',
                        $this->translator->trans('candidate_form.nationality.sao_tome_and_principe') => 'Sao Tome and Principe',
                        $this->translator->trans('candidate_form.nationality.seychelles') => 'Seychelles',
                        $this->translator->trans('candidate_form.nationality.sierra_leone') => 'Sierra Leone',
                        $this->translator->trans('candidate_form.nationality.somalia') => 'Somalia',
                        $this->translator->trans('candidate_form.nationality.tanzania') => 'Tanzania',
                        $this->translator->trans('candidate_form.nationality.uganda') => 'Uganda',
                        $this->translator->trans('candidate_form.nationality.south_africa') => 'South Africa',



                    ],
                    $this->translator->trans('candidate_form.nationality.other_countries') => [
                        $this->translator->trans('candidate_form.nationality.canada') => 'Canada',
                        $this->translator->trans('candidate_form.nationality.france') => 'France',

                    ],

                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('baccalaureateSeries', TextType::class)
            ->add('baccalaureateMention', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    'Très bien' => 'Tres Bien',
                    'Bien' => 'Bien',
                    'Assez bien' => 'Assez Bien',
                    'Passable' => 'Passable',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('gceALevelCountry', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    $this->translator->trans('candidate_form.nationality.africa') => [
                        $this->translator->trans('candidate_form.nationality.cameroon') => $this->translator->trans('candidate_form.nationality.cameroon'),
                        $this->translator->trans('candidate_form.nationality.congo') => 'Congo',
                        $this->translator->trans('candidate_form.nationality.gabon') => 'Gabon',
                        $this->translator->trans('candidate_form.nationality.ghana') => 'Ghana',
                        $this->translator->trans('candidate_form.nationality.mali') => 'Mali',
                        $this->translator->trans('candidate_form.nationality.mauritania') => 'Mauritanie',
                        $this->translator->trans('candidate_form.nationality.niger') => 'Niger',
                        $this->translator->trans('candidate_form.nationality.nigeria') => 'Nigeria',
                        $this->translator->trans('candidate_form.nationality.senegal') => 'Senegal',
                        $this->translator->trans('candidate_form.nationality.algeria') => 'Algerie',
                        $this->translator->trans('candidate_form.nationality.togo') => 'Togo',
                        $this->translator->trans('candidate_form.nationality.tunisia') => 'Tunisia',
                        $this->translator->trans('candidate_form.nationality.egypt') => 'Egypt',
                        $this->translator->trans('candidate_form.nationality.morocco') => 'Maroc',
                        $this->translator->trans('candidate_form.nationality.sudan') => 'South Sudan',
                        $this->translator->trans('candidate_form.nationality.south_africa') => 'South Africa',
                        $this->translator->trans('candidate_form.nationality.angola') => 'Angola',
                        $this->translator->trans('candidate_form.nationality.burkina_faso') => 'Burkina Faso',
                        $this->translator->trans('candidate_form.nationality.burundi') => 'Burundi',
                        $this->translator->trans('candidate_form.nationality.central_african_republic') => 'Central African Republic',
                        $this->translator->trans('candidate_form.nationality.chad') => 'Chad',
                        $this->translator->trans('candidate_form.nationality.comoros') => 'Comoros',
                        $this->translator->trans('candidate_form.nationality.dr_congo') => 'Democratic Republic of Congo',
                        $this->translator->trans('candidate_form.nationality.djibouti') => 'Djibouti',
                        $this->translator->trans('candidate_form.nationality.eritrea') => 'Eritrea',
                        $this->translator->trans('candidate_form.nationality.ethiopia') => 'Ethiopia',
                        $this->translator->trans('candidate_form.nationality.gambia') => 'Gambia',
                        $this->translator->trans('candidate_form.nationality.guinea') => 'Guinea',
                        $this->translator->trans('candidate_form.nationality.guinea_bissau') => 'Guinea Bissau',
                        $this->translator->trans('candidate_form.nationality.kenya') => 'Kenya',
                        $this->translator->trans('candidate_form.nationality.lesotho') => 'Lesotho',
                        $this->translator->trans('candidate_form.nationality.liberia') => 'Liberia',
                        $this->translator->trans('candidate_form.nationality.libya') => 'Libya',
                        $this->translator->trans('candidate_form.nationality.madagascar') => 'Madagascar',
                        $this->translator->trans('candidate_form.nationality.malawi') => 'Malawi',
                        $this->translator->trans('candidate_form.nationality.mauritius') => 'Mauritius',
                        $this->translator->trans('candidate_form.nationality.mozambique') => 'Mozambique',
                        $this->translator->trans('candidate_form.nationality.rwanda') => 'Rwanda',
                        $this->translator->trans('candidate_form.nationality.sao_tome_and_principe') => 'Sao Tome and Principe',
                        $this->translator->trans('candidate_form.nationality.seychelles') => 'Sychelles',
                        $this->translator->trans('candidate_form.nationality.somalia') => 'Somalia',
                        $this->translator->trans('candidate_form.nationality.sudan') => 'Sudan',
                        $this->translator->trans('candidate_form.nationality.benin') => 'Benin',
                        $this->translator->trans('candidate_form.nationality.botswana') => 'Botswana',
                        $this->translator->trans('candidate_form.nationality.cabo_verde') => 'Cabo Verde',
                        $this->translator->trans('candidate_form.nationality.eq_guinea') => 'Equatorial Guinea',
                        $this->translator->trans('candidate_form.nationality.eswatini') => 'Eswatini',
                        $this->translator->trans('candidate_form.nationality.ivory_coast') => 'Ivory Coast',
                        $this->translator->trans('candidate_form.nationality.namibia') => 'Namibia',
                        $this->translator->trans('candidate_form.nationality.sierra_leone') => 'Sierra Leone',
                        $this->translator->trans('candidate_form.nationality.tanzania') => 'Tanzania',
                        $this->translator->trans('candidate_form.nationality.uganda') => 'Uganda',
                        $this->translator->trans('candidate_form.nationality.zambia') => 'Zambia',
                        $this->translator->trans('candidate_form.nationality.zimbabwe') => 'Zimbabwe',



                    ],
                    $this->translator->trans('candidate_form.nationality.other_countries') => [
                        $this->translator->trans('candidate_form.nationality.canada') => 'Canada',
                        $this->translator->trans('candidate_form.nationality.us') => 'United States of America',
                        $this->translator->trans('candidate_form.nationality.uk') => 'United Kingdom',

                    ],

                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('gceALevelPapersCount', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('gceALevelGradeACount', ChoiceType::class, [
                'choices' => [
                    '-' => '-',
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4,
                    '5' => 5,
                ],
                'attr' => ['class' => 'form-select'],
            ])

            ->add('gceOLevelYear', ChoiceType::class, [
                'choices' => array_merge(
                    ['-' => '-'], // Add "-" option
                    array_combine(range(2001, 2024), range(2001, 2024)) // Map years to values
                ),
                'choice_label' => function ($choice, $key, $value) {
                    return $choice; // Use the same value for the label
                },
                'attr' => ['class' => 'form-select'],
            ])

            ->add('gceOLevelPapersCount', ChoiceType::class, [
                'choices' => array_merge(
                    ['-' => '-'], // Add "-" option
                    array_combine(range(1, 11), range(1, 11)) // Map numbers 1 to 11 to themselves
                ),
                'choice_label' => function ($choice, $key, $value) {
                    return $choice; // Use the same value for the label
                },
                'attr' => ['class' => 'form-select'],
            ])

            ->add('paymentOperator', ChoiceType::class, [
                'choices' => [
                    'EXPRESS UNION' => 'EXPRESS UNION',
                    'Direction Générale Des Affaires Sociales et des Œuvres Universitaires' => 'DGASOU'
                    
                ],
                'attr' => ['class' => 'nationality-select'],
            ])
            ->add('probatoireYear', ChoiceType::class, [
                'choices' => array_merge(
                    ['-' => '-'], // Add "-" option
                    array_combine(range(2001, 2024), range(2001, 2024)) // Map years to values
                ),
                'choice_label' => function ($choice, $key, $value) {
                    return $choice; // Use the same value for the label
                },
                'attr' => ['class' => 'form-select'],
            ])

        ;
        $builder->addEventSubscriber($this->validationListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidate::class,
            'allow_extra_fields' => true, // This allows currentStep to be submitted with the form
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'csrf_token_id', // Make sure this matches what you are using in your form handling logic
        ]);
    }
}
