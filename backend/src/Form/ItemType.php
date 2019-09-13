<?php

namespace App\Form;

use App\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('priceType', TextType::class, ['name' => 'price_type'])
            ->add('priceMin', TextType::class, ['name' => 'price_min'])
            ->add('priceMax', TextType::class, ['name' => 'price_max'])
            ->add('isBargainPossible', TextType::class, ['name' => 'is_bargain_possible'])
            ->add('isExchangePossible', TextType::class, ['name' => 'is_exchange_possible']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'      => Item::class,
            'is_edit'         => false,
            'csrf_protection' => false,
        ]);
    }
}
