<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Cities;
use App\Entity\Files;
use App\Entity\Passwords;
use App\Entity\Users;
use App\Service\UsersService;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
// cut out //

class UserPreviewType extends AbstractType
{

    public function buildForm(
      FormBuilderInterface $builder,
      array $options
    ): void {
        $user = $options['data'];
        /** @var UsersService $usersService */
        $usersService = $options['users_service'];
        $route        = $options['route'];

        $path = fn(int $pageSize, int $pageIndex) => ($options['path'](
          $route,
          ['pageSize' => $pageSize, 'pageIndex' => $pageIndex]
        ));

        $builder
          ->add('name')
          ->add('first_name')
          ->add('last_name')
          ->add('birthday', null, [
            'widget' => 'single_text',
            'html5'  => true,
          ])
          ->add('last_visit', null, [
            'widget' => 'single_text',
            'html5'  => true,
          ])
          ->add('active')
          ->add('email', EmailType::class)
          ->add('banned')
          //   ->add('avatar)
          ->add('category', EntityType::class, [
            'class'        => Categories::class,
            'choice_label' => 'caption',
          ])
          ->add('city', EntityType::class, [
            'class'        => Cities::class,
            'choice_label' => 'name',
          ])
          ->add('files', ChoiceType::class, [
            'mapped'       => false,
            'required'     => false,
            'multiple'     => true,
            'choices'      => $user->getFiles(),
            'choice_label' => (fn($f) => ($f->getId(
              ) . ': ' . ($f->getOriginalName() ?? $f->getName()))),
            'choice_value' => (fn($f) => $f->getId()),
          ])
          ->add('newFiles', FileType::class, [
            'mapped'      => false,
            'required'    => false,
            'multiple'    => true,
            'constraints' => [
              new File([
                'maxSize' => '4096k',
              ]),
            ],
          ])
          ->add('avatar', FileType::class, [
            'mapped'      => false,
            'required'    => false,
            'constraints' => [
              new File([
                'maxSize'          => '4096k',
                'mimeTypes'        => [
                  'image/jpeg',
                ],
                'mimeTypesMessage' => 'Please upload a valid image',
              ]),
            ],
          ])
          ->add(
            'authPass',
            RepeatedType::class,
            [
              'type'            => PasswordType::class,
              'required'        => false,
              'invalid_message' => 'Passwords must match',
              'first_options'   => ['label' => ' '],
              'second_options'  => ['label' => ' '],
              'options'         => [
                'attr' => [
                  'pattern' => '.{8,}',
                  'title'   => 'Minimum 8 characters',
                ],
              ],
            ],
          )
          // cut out //
          ->add('submit', SubmitType::class, [
            'attr'  => ['class' => 'btn btn-primary'],
            'label' => 'Update user',
          ])
          ->add('back', SubmitType::class, [
            'attr'  => [
              'class'      => 'btn btn-secondary',
              'formAction' => $path(0, 0), // 0,0 -> from session
            ],
            'label' => 'PREV',
          ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
          'data_class'    => Users::class,
          'users_service' => UsersService::class,
          'path'          => '',
          'route'         => '',
        ]);
    }

    // cut out //
}