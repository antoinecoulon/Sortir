<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadFile
{
    public function __construct(private readonly SluggerInterface $slugger) { }

    /**
     * @param UploadedFile $file file uploaded
     * @param String $fileName name of the file with slug
     * @param String $destination path of destination
     * @return String name of the file to save
     */
    public function upload(UploadedFile $file, String $fileName, String $destination): String
    {
        $name = $this->slugger->slug($fileName) . '-' . uniqid('', false) . '.' . $file->guessExtension();
        $file->move($destination, $name);
        return $name;
    }
}