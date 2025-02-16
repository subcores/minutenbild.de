<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class DefaultController extends AbstractController
{
    private array $extensionToMime = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'tiff' => 'image/tiff',
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Environment $twig,
    ) {
    }

    #[Route('/', name: 'default')]
    public function index(Request $request): Response
    {
        $image = new Image();
        /** @var Form $form */
        $form = $this->createForm(ImageFormType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = new \DateTime();
            $dateFolder = $date->format('Ymd');
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $image->image;
            $image->extension = strtolower($uploadedFile->getClientOriginalExtension());
            $image->hash = $this->getHash($uploadedFile->getClientOriginalName());
            $image->size = (int) round($uploadedFile->getSize() / 1000, 0);
            $image->ip = $request->getClientIp();
            if (null !== $image->password) {
                $image->password = md5($image->password);
            }

            // Move file
            $uploadedFile->move($this->getParameter('storage_path').$dateFolder, $image->hash.'.'.$image->extension);

            $this->entityManager->persist($image);
            $this->entityManager->flush();
            $twigVars = [
                'hash' => $request->getSchemeAndHttpHost().'/'.$image->hash,
            ];

            return $this->render('upload.html.twig', $twigVars);
        }

        $twigVars = [
            'form' => $form->createView(),
            'max_upload_size' => $this->getMaxUploadSize(),
        ];

        return $this->render('upload.html.twig', $twigVars);
    }

    #[Route('/{hash}', name: 'image', requirements: ['hash' => '[a-z0-9]{6}'])]
    public function displayImage(Request $request, string $hash): Response
    {
        /** @var Image $image */
        $image = $this->entityManager->getRepository(Image::class)->findOneBy([
            'isActive' => true,
            'hash' => $hash,
        ]);

        // Is Image?
        if (null === $image) {
            return $this->forward(__CLASS__.'::notAvailable');
        }

        // is Password
        if (null !== $image->password) {
            $form = $this->createFormBuilder([])->add('pass', PasswordType::class, [
                'label' => 'Bitte das Kennwort angeben',
                'help' => '',
                'required' => true,
                'attr' => [
                    'class' => 'text-red',
                ],
                'translation_domain' => false,
            ])->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                if (md5($data['pass']) !== $image->password) {
                    $twigVars = [
                        'error' => 'Passwort ist leider falsch.',
                    ];

                    return $this->render('password.html.twig', $twigVars);
                }
            } else {
                $twigVars = [
                    'form' => $form->createView(),
                ];
                // Show login
                return $this->render('password.html.twig', $twigVars);
            }
        }

        $time = clone $image->createdAt;
        $expireTime = $time->modify('+ '.$image->minutes.' Minutes');
        $timeout = null;
        if ($image->isTimeout) {
            // Set Timeout to 10 seconds.
            $timeout = 10000;
        }
        // Check expired
        if (0 === $image->minutes) {
            // disable at first call
            $image->isActive = false;
            $this->entityManager->flush();
        } elseif ($expireTime <= new \DateTimeImmutable()) {
            // Is expired
            $this->removeImage($image);

            return $this->forward(__CLASS__.'::notAvailable');
        } elseif ($image->isTimeout) {
            // Set expire timeout
            $diff = $expireTime->diff(new \DateTime());
            $timeout = (($diff->i * 60 + $diff->s + 1)) * 1000;
        }

        $twigVars = [
            'image_src' => $this->getImageData($image),
            'message' => $image->message,
            'timeout' => $timeout,
        ];

        return $this->render('view.html.twig', $twigVars);
    }

    #[Route('/expired', name: 'not_available')]
    public function notAvailable(): Response
    {
        return $this->render('not_available.html.twig');
    }

    /**
     * Remove image soft or hard.
     */
    private function removeImage(Image $image): void
    {
        if ($this->getParameter('soft_delete')) {
            // SoftDelete
            $image->isActive = false;
            $this->entityManager->flush();

            return;
        }

        // @todo Delete from filesystem and database

        try {
            $fs = new Filesystem();
            $fs->remove($this->getImagePath($image));
        } catch (IOException $ioe) {
            // do nothing if removal fails
        }
        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }

    /**
     * Gets base64 image-source data string.
     */
    private function getImageData(?Image $image = null): string
    {
        if (null === $image) {
            return $this->getParameter('storage_path');
        }

        $fileContent = base64_encode(file_get_contents($this->getImagePath($image)));

        return 'data:'.$this->extensionToMime[$image->extension].';base64, '.$fileContent;
    }

    /**
     * Get url hash string.
     */
    private function getHash(string $name): string
    {
        return \substr(\md5($name.\time()), 0, 6);
    }

    /**
     * Gets image name with full path.
     */
    private function getImagePath(Image $image): string
    {
        return $this->getParameter('storage_path').
            $image->createdAt->format('Ymd').
            DIRECTORY_SEPARATOR.
            $image->hash.'.'.$image->extension;
    }

    /**
     * Route("/{slug}", name="content", requirements={"slug": "[a-z]+"})
     */
    #[Route('/{slug}', name: 'content', requirements: ['slug' => '[a-z]+'])]
    public function showContent(string $slug): Response
    {
        if ($this->twig->getLoader()->exists('contents/'.$slug.'.html.twig')) {
            return $this->render('contents.html.twig', ['content' => 'contents/'.$slug.'.html.twig']);
        }

        return $this->redirectToRoute('default');
    }

    private function getMaxUploadSize(): string
    {
        $postMaxSize = trim(ini_get('post_max_size'), 'M');
        $uploadMaxFileSize = trim(ini_get('upload_max_filesize'), 'M');

        return ($postMaxSize >= $uploadMaxFileSize ? $uploadMaxFileSize : $postMaxSize).' MB';
    }
}
