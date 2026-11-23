<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Model\CategoryData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['maxlength' => 120, 'placeholder' => 'Nombre de la categoría'],
            ])
            ->add('save', SubmitType::class, ['label' => $options['submit_label']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CategoryData::class, 'submit_label' => 'Guardar categoría']);
        $resolver->setAllowedTypes('submit_label', 'string');
    }
}
