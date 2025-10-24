<?php

namespace App\Form;

use App\Entity\Empleados;
use App\Entity\Puestos;
use App\Entity\Tiendas;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmpleadoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            ->add('apellido', TextType::class, [
                'label' => 'Apellido',
                'required' => true,
                'attr' => ['maxlength' => 50],
            ])
            // la columna es fecha_nacimiento, la propiedad suele ser fechaNacimiento
            ->add('fecha_nacimiento', DateType::class, [
                'label' => 'Fecha nacimiento',
                'widget' => 'single_text',
                'property_path' => 'fechaNacimiento',
                'required' => true,
            ])
            ->add('salario', NumberType::class, [
                'label' => 'Salario',
                'scale' => 2,
                'html5' => true,
                'required' => true,
            ])
            ->add('puesto', EntityType::class, [
                'class' => Puestos::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Selecciona un puesto',
                'required' => true,
            ])
            ->add('tienda', EntityType::class, [
                'class' => Tiendas::class,
                'choice_label' => 'nombre',
                'placeholder' => 'Selecciona una tienda',
                'required' => true,
            ])
            ->add('fotografia', FileType::class, [
                'label' => 'Fotografía',
                'mapped' => false,       // el archivo no se mapea directo a la entidad
                'required' => false,     // opcional en alta/edición
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
