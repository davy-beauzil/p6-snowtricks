<?php

declare(strict_types=1);

namespace App\Form\CreateTrick;

use App\Entity\Group;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class CreateTrickForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la figure',
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
            ])
            ->add('trickGroup', EntityType::class, [
                'class' => Group::class,
                'label' => 'Groupe',
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'mapped' => true,
            ])
            ->add('mainImage', FileType::class, [
                'label' => 'Image mise en avant',
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => ['image/*'],
                        'mimeTypesMessage' => 'Vous devez importer une image valide',
                    ]),
                ],
                'help' => 'Ce sera l’image affichée par défaut',
            ])
            ->add('images', FileType::class, [
                'label' => 'Images secondaires',
                'required' => false,
                'mapped' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '2048k',
                            'mimeTypes' => ['image/*'],
                            'mimeTypesMessage' => 'Vous devez importer des images valides',
                        ]),
                    ]),
                    //                    new File([
                    //                        'maxSize' => '2048k',
                    //                        'mimeTypes' => ['image/*'],
                    //                        'mimeTypesMessage' => 'Vous devez importer des images valides',
                    //                    ]),
                ],
                'help' => 'Ces images seront visibles depuis la page de la figure',
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => UrlType::class,
                'label' => 'Vidéos',
                'entry_options' => [
                    'label' => false,
                    'required' => false,
                ],
                'mapped' => false,
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'help' => 'Vidéos provenant de Youtube ou Daylimotion',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
