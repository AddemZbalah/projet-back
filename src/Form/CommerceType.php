<?php

namespace App\Form;

use App\Entity\Commerce;
use App\Service\ImageUploader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommerceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $remaining = max(0, ImageUploader::MAX_IMAGES - $options['existing_images']);

        $builder
            ->add('name', null, [
                'label' => 'Nom du commerce',
                'attr' => ['placeholder' => 'Ex. Boulangerie du Marché'],
            ])
            ->add('description', null, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['placeholder' => 'Présentez votre commerce en quelques mots', 'rows' => 3],
            ])
            ->add('address', null, [
                'label' => 'Adresse',
                'attr' => ['placeholder' => '12 rue de la République, 75011 Paris', 'autocomplete' => 'street-address'],
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
                'attr' => ['placeholder' => 'Boulangerie, Restaurant, Primeur…'],
            ])
            ->add('phone', null, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => '01 23 45 67 89', 'autocomplete' => 'tel'],
            ])
            // Non mappé : les fichiers sont traités par ImageUploader dans le contrôleur.
            ->add('photos', FileType::class, [
                'label' => 'Photos',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => ['accept' => 'image/jpeg,image/png,image/webp', 'data-max' => $remaining],
                'constraints' => [
                    new Assert\Count(max: $remaining, maxMessage: 'Vous pouvez ajouter {{ limit }} photo(s) au maximum.'),
                    new Assert\All([
                        new Assert\Image(
                            maxSize: '8M',
                            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                            mimeTypesMessage: 'Formats acceptés : JPG, PNG ou WebP.',
                            maxSizeMessage: 'Chaque photo doit faire moins de {{ limit }} {{ suffix }}.',
                        ),
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commerce::class,
            'existing_images' => 0,
        ]);
        $resolver->setAllowedTypes('existing_images', 'int');
    }
}
