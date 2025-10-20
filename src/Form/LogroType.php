<?php

namespace App\Form;

use App\Entity\Logros;
use App\Entity\Empleados;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LogroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('empleado', EntityType::class, [
                'class' => Empleados::class,
                'choice_label' => function (Empleados $empleado) {
                    return $empleado->getNombre() . ' ' . $empleado->getApellido();
                },
                'label' => 'Empleado',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
            ])
            ->add('tipo', ChoiceType::class, [
                'choices' => [
                    'Positivo' => 'positivo',
                    'Negativo' => 'negativo',
                ],
                'label' => 'Tipo de logro',
            ])
            ->add('fecha_ocurrencia', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha de ocurrencia',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Logros::class,
        ]);
    }
}
