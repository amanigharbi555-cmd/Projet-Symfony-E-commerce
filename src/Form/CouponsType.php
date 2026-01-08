<?php

namespace App\Form;

use App\Entity\Coupons;
use App\Entity\CouponsTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CouponsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Code du coupon',
                'attr' => [
                    'placeholder' => 'Ex: PROMO2025',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le code du coupon ne peut pas être vide'
                    ]),
                    new Assert\Length([
                        'max' => 10,
                        'maxMessage' => 'Le code ne peut pas dépasser {{ limit }} caractères'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[A-Z0-9]+$/',
                        'message' => 'Le code doit contenir uniquement des lettres majuscules et des chiffres'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Description du coupon',
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La description ne peut pas être vide'
                    ])
                ]
            ])
            ->add('discount', IntegerType::class, [
                'label' => 'Réduction',
                'attr' => [
                    'placeholder' => 'Montant de la réduction',
                    'class' => 'form-control',
                    'min' => 1
                ],
                'help' => 'Montant en pourcentage ou en TND selon le type',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La réduction ne peut pas être vide'
                    ]),
                    new Assert\Positive([
                        'message' => 'La réduction doit être positive'
                    ])
                ]
            ])
            ->add('max_usage', IntegerType::class, [
                'label' => 'Utilisation maximale',
                'attr' => [
                    'placeholder' => 'Nombre d\'utilisations maximales',
                    'class' => 'form-control',
                    'min' => 1
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'utilisation maximale ne peut pas être vide'
                    ]),
                    new Assert\Positive([
                        'message' => 'L\'utilisation maximale doit être positive'
                    ])
                ]
            ])
            ->add('validity', DateTimeType::class, [
                'label' => 'Date de validité',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de validité ne peut pas être vide'
                    ]),
                    new Assert\GreaterThan([
                        'value' => 'today',
                        'message' => 'La date de validité doit être dans le futur'
                    ])
                ]
            ])
            ->add('is_valid', CheckboxType::class, [
                'label' => 'Coupon actif',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('coupons_types', EntityType::class, [
                'label' => 'Type de coupon',
                'class' => CouponsTypes::class,
                'choice_label' => 'name',
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez sélectionner un type de coupon'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coupons::class,
        ]);
    }
}
