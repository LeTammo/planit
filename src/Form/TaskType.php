<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'placeholder' => 'Task title'
                ],
                'label_attr' => ['class' => 'hidden'],
            ])
            ->add('description', null, [
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'rows' => 3,
                    'placeholder' => 'Task description'
                ],
                'label_attr' => ['class' => 'hidden'],
                'required' => false,
            ])
            ->add('startDate', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
            ])
            ->add('endDate', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
            ])
            ->add('hasDate', CheckboxType::class, [
                'required' => false,
                'data' => true,
            ])
            ->add('hasRange', CheckboxType::class, [
                'required' => false,
                'data' => false,
            ])
            ->add('hasTime', CheckboxType::class, [
                'required' => false,
                'data' => false,
            ])
            ->add('tags', null, [
                'attr' => [
                    'class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500',
                    'placeholder' => 'Tags (comma separated)'
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}