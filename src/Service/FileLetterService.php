<?php

namespace App\Service;

use App\Entity\Letter;

use App\Repository\LetterRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileLetterService
{
    private $uploadPernDirectory;
    private $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function __construct(ManagerRegistry $doctrine, ParameterBagInterface $parameterBag, LetterRepository $letterRepository)
    {
        $this->uploadPernDirectory = $parameterBag->get('upload_directory');
    }



    /**
     * Path to stored file (on server)
     * @param Letter $letter
     * @return string
     */
    public function getFilePath(Letter $letter): string
    {

        $filePath = $this->uploadPernDirectory .DIRECTORY_SEPARATOR. $letter->getFileName();

        return $filePath;
    }


    public function deleteFile(Letter $letter): bool
    {
        $filePath = $this->getFilePath($letter);
        if (file_exists($filePath)) {
            $unlinkResult = unlink($filePath);
            if ($this->output) $this->output->writeln("<value>" . $filePath . "</value>\t file deleted");
            $letter->setFileName(null);
            return $unlinkResult;
        }

        return false;
    }


}