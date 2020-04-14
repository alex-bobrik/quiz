<?php


namespace App\Service;


use App\Entity\Quiz;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $em;

    private $slugger;

    private $imageResizer;

    private $avatarsDirectory;

    private $quizesDirectory;

    public function __construct
    (
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        $avatarsDirectory,
        $quizesDirectory,
        ImageResizer $imageResizer
    )
    {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->avatarsDirectory = $avatarsDirectory;
        $this->quizesDirectory = $quizesDirectory;
        $this->imageResizer = $imageResizer;
    }

    public function getAvatarsDirectory()
    {
        return $this->avatarsDirectory;
    }

    public function getQuizesDirectory()
    {
        return $this->quizesDirectory;
    }

    // Resize and upload image in selected folder w/ selected fileName
    private function upload
    (
        UploadedFile $file,
        string $fileName,
        $directory,
        int $width = 200,
        int $height = 200
    )
    {
        $this->imageResizer->load($file->getRealPath());
        $this->imageResizer->resize($width, $height);
        $this->imageResizer->save($directory . $fileName);
    }


    public function uploadQuizImage(UploadedFile $file, Quiz $quiz): string
    {
        $fileName = $this->generateFilename($file);

        $this->upload($file, $fileName, $this->getQuizesDirectory(), 300, 300);

        return $fileName;
    }

    public function uploadAvatar(UploadedFile $file, User $user)
    {
        if ($user->getImage()) {
            $this->removeImage($this->getAvatarsDirectory(), $user->getImage());
        }

        $fileName = $this->generateFilename($file);

        $this->upload($file, $fileName, $this->getAvatarsDirectory());

        $user->setImage($fileName);
        $this->em->flush();
    }

    private function generateFilename(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        return md5($safeFilename . '-' . uniqid()) . '.' . $file->guessExtension();
    }

    // Removing image from selected directory
    public function removeImage($directory, $fileName)
    {
        try {
            unlink($directory . $fileName);
        } catch (\Exception $e) {
            return;
        }
    }
}