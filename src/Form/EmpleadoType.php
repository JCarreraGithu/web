<?php

namespace App\Form;

use App\Entity\Empleados;
use App\Entity\Puestos;
use App\Entity\Tiendas;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmpleadoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre')
            ->add('apellido')
            ->add('fecha_nacimiento', DateType::class, [
                'widget' => 'single_text',
            ])
           ->add('fotografia', FileType::class, [
    'mapped' => false,
    'required' => false,
])
            ->add('salario')
            ->add('puesto', EntityType::class, [
                'class' => Puestos::class,
                'choice_label' => 'nombre',
            ])
            ->add('tienda', EntityType::class, [
                'class' => Tiendas::class,
                'choice_label' => 'nombre',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Empleados::class,
        ]);
    }
}
