<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => 'Votre note',
                'choices' => ['1 étoile' => 1, '2 étoiles' => 2, '3 étoiles' => 3, '4 étoiles' => 4, '5 étoiles' => 5],
                'expanded' => true,
                'placeholder' => false,
                'constraints' => [new Assert\NotBlank(message: 'Choisissez une note.')],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Votre avis (optionnel)',
                'required' => false,
                'attr' => ['rows' => 4, 'maxlength' => 1000, 'placeholder' => 'Qualité du panier, accueil, quantité…'],
                'constraints' => [new Assert\Length(max: 1000)],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Review::class]);
    }
}
