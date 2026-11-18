<?php

namespace App\Form;

use App\Entity\Commerce;
use App\Entity\Offre;
use App\Entity\Preference;
use App\Service\ImageUploader;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Commerce[] $commerces */
        $commerces = $options['commerces'];
        $remaining = max(0, ImageUploader::MAX_IMAGES - $options['existing_images']);

        if (count($commerces) > 1) {
            $builder->add('commerce', EntityType::class, [
                'class' => Commerce::class,
                'choices' => $commerces,
                'choice_label' => 'name',
                'label' => 'Commerce',
            ]);
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l’offre',
                'attr' => ['placeholder' => 'Ex. Panier surprise viennoiseries', 'maxlength' => 255],
                'constraints' => [new Assert\NotBlank(message: 'Donnez un titre à votre offre.')],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['placeholder' => 'Que peut contenir le panier ? Allergènes, conseils…', 'rows' => 3],
            ])
            ->add('originalPrice', NumberType::class, [
                'label' => 'Valeur réelle (€)',
                'input' => 'string',
                'scale' => 2,
                'html5' => true,
                'attr' => ['min' => 0.01, 'step' => '0.01', 'placeholder' => '15.00'],
                'constraints' => [new Assert\NotBlank(), new Assert\Positive(message: 'Le prix doit être positif.')],
            ])
            ->add('discountedPrice', NumberType::class, [
                'label' => 'Prix de vente (€)',
                'input' => 'string',
                'scale' => 2,
                'html5' => true,
                'attr' => ['min' => 0.01, 'step' => '0.01', 'placeholder' => '5.00'],
                'constraints' => [new Assert\NotBlank(), new Assert\Positive(message: 'Le prix doit être positif.')],
            ])
            ->add('quantityTotal', IntegerType::class, [
                'label' => 'Nombre de paniers',
                'attr' => ['min' => 1, 'placeholder' => '5'],
                'constraints' => [new Assert\NotBlank(), new Assert\Range(min: 1, max: 999, notInRangeMessage: 'Entre {{ min }} et {{ max }} paniers.')],
            ])
            ->add('preferences', EntityType::class, [
                'class' => Preference::class,
                'choice_label' => static fn (Preference $p) => ucfirst($p->getName()),
                'query_builder' => static fn ($repo) => $repo->createQueryBuilder('p')->orderBy('p.name', 'ASC'),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Régimes compatibles',
                'help' => 'Cochez uniquement ce qui est garanti pour tout le panier.',
            ])
            // Non mappé : les fichiers sont traités par ImageUploader dans le contrôleur.
            ->add('photos', FileType::class, [
                'label' => 'Photos des produits',
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
            ->add('pickupStart', DateTimeType::class, [
                'label' => 'Début du retrait',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('pickupEnd', DateTimeType::class, [
                'label' => 'Fin du retrait',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new Assert\NotBlank()],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
            'commerces' => [],
            'existing_images' => 0,
            'constraints' => [new Assert\Callback($this->validateOffre(...))],
        ]);
        $resolver->setAllowedTypes('commerces', 'array');
        $resolver->setAllowedTypes('existing_images', 'int');
    }

    public function validateOffre(Offre $offre, ExecutionContextInterface $context): void
    {
        if ($offre->getOriginalPrice() !== null && $offre->getDiscountedPrice() !== null
            && (float) $offre->getDiscountedPrice() >= (float) $offre->getOriginalPrice()) {
            $context->buildViolation('Le prix de vente doit être inférieur à la valeur réelle.')
                ->atPath('discountedPrice')->addViolation();
        }

        if ($offre->getPickupStart() && $offre->getPickupEnd() && $offre->getPickupEnd() <= $offre->getPickupStart()) {
            $context->buildViolation('La fin du retrait doit être après le début.')
                ->atPath('pickupEnd')->addViolation();
        }

        if ($offre->getId() === null && $offre->getPickupEnd() && $offre->getPickupEnd() <= new \DateTimeImmutable()) {
            $context->buildViolation('Le créneau de retrait est déjà passé.')
                ->atPath('pickupEnd')->addViolation();
        }
    }
}
