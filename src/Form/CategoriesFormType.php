<?php

namespace App\Form;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoriesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la catégorie',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom de la catégorie'
                ]
            ])
            ->add('parent', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
                'label' => 'Catégorie parente (optionnel)',
                'required' => false,
                'placeholder' => 'Aucune (catégorie principale)',
                'query_builder' => function(CategoriesRepository $cr) {
                    return $cr->createQueryBuilder('c')
                        ->where('c.parent IS NULL')
                        ->orderBy('c.name', 'ASC');
                },
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('categoryOrder', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'value' => 0
                ],
                'data' => 0
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}

