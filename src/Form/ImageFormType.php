<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('image', FileType::class, [
                'label' => false,
                'help' => 'Klicke hier um dein Bild auszusuchen oder ziehe die Datei einfach hier rein. Maximal: '.$this->getMaxUploadSize(),
                'required' => true,
                'attr' => [
                        'class' => 'text-red',
                ],
                'translation_domain' => false,
        ])->add('minutes', ChoiceType::class, [
            'label' => 'Bild löschen nach',
            'help' => 'Bitte gebe die Minuten an, die dein Bild gespeichert wird.',
            'choices' => [
                'dem ersten Aufruf' => '0',
                '1 Minute' => '1',
                '2 Minuten' => '2',
                '5 Minuten' => '5',
                '10 Minuten' => '10',
                '30 Minuten' => '30',
                '59 Minuten' => '59',
            ],
            'translation_domain' => false,
            'attr' => [
                'class' => 'form-control',
            ],
        ])->add('password', PasswordType::class, [
            'label' => 'Paranoid Modus',
            'required' => false,
            'help' => 'Das Bild zusätzlich per Kennwort schützen [optional]',
            'translation_domain' => false,
            'attr' => [
                'class' => 'form-control',
            ],
        ])->add('message', TextType::class, [
                'label' => 'Bild Untertitel',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Kurze Nachricht',
                ],
                'help' => 'Kurze Nachricht unter dem Bild anzeigen [optional]',
                'translation_domain' => false,
        ])->add('isTimeout', CheckboxType::class, [
                'label' => 'Nach Ablauf der Minuten, die Seite neu laden um das Bild zu löschen.',
                'required' => false,
                'label_attr' => [
                        'class' => 'switch-custom',
                ],
                'help' => 'Bei Löschung nach dem ersten Aufruf 10 Sekunden.',
                'translation_domain' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function getBlockPrefix(): string
    {
        return 'imageupload';
    }

    private function getMaxUploadSize(): string
    {
        $postMaxSize = trim(ini_get('post_max_size'), 'M');
        $uploadMaxFileSize = trim(ini_get('upload_max_filesize'), 'M');

        return ($postMaxSize >= $uploadMaxFileSize ? $uploadMaxFileSize : $postMaxSize).' MB';
    }
}
