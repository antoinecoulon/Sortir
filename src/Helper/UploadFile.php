<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UploadFile
{
    public function __construct(private readonly SluggerInterface $slugger) { }

    public function upload(UploadedFile $file, String $fileName, String $destination): String
    {
        $name = $this->slugger->slug($fileName) . '-' . uniqid('', false) . '.' . $file->guessExtension();
        $file->move($destination, $name);
        return $name;
    }
}