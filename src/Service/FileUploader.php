<?php


namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $em;

    private $slugger;

    private $avatarsDirectory;
    private $quizesDirectory;

    public function __construct(EntityManagerInterface $em, SluggerInterface $slugger, $avatarsDirectory, $quizesDirectory)
    {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->avatarsDirectory = $avatarsDirectory;
        $this->quizesDirectory = $quizesDirectory;
    }

    public function getAvatarsDirectory()
    {
        return $this->avatarsDirectory;
    }
    public function getQuizesDirectory()
    {
        return $this->quizesDirectory;
    }

    public function uploadAvatar(UploadedFile $file, User $user)
    {
        $fileName = $this->generateFilename($file);

        if ($user->getImage()) {
            $this->removeImage($this->getAvatarsDirectory(), $user->getImage());
        }

        try {
            $file->move($this->getAvatarsDirectory(), $fileName);
        } catch (FileException $e) {
            throw new FileException('Avatar upload fail ' . $e->getMessage());
        }

        $user->setImage($fileName);
        $this->em->flush();
    }

    private function generateFilename(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = md5($safeFilename.'-'.uniqid()).'.'.$file->guessExtension();

        return $fileName;

    }

    private function removeImage($directory, $fileName)
    {
        unlink($directory . $fileName);
    }
}