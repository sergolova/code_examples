<?php

namespace App\Service;

use App\Entity\Files;
use App\Entity\Users;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UsersService implements UsersServiceInterface
{

    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function findUploadedFileByHash(UploadedFile $uploadFile): ?Files
    {
        $pathName = $uploadFile->getPathName();
        return $this->findFileByHash($pathName);
    }

    public function findFileByHash(string $fileName, bool $actual=true): ?Files
    {
        if (file_exists($fileName)) {
            $hash = md5_file($fileName);
            $filesRep = $this->doctrine->getManager()->getRepository(Files::class);

            return $filesRep->findOneBy(['hash' => $hash]);
        }
        return null;
    }

    public function filterUserFiles(
      Users $user,
      array $filteredFiles,
      string $filesDir,
    ): void {
        $em       = $this->doctrine->getManager();
        $oldFiles = $user->getFiles();
        $needFlush = false;

        foreach ($oldFiles as $of) {
            if (!in_array($of, $filteredFiles)) {
                $needFlush = true;
                $of->removeUser($user);
                $restUsers = $of->getUsers();
                if (!$restUsers->count()) { // remove poor lonely file
                    $of->unlink($filesDir);
                    $em->remove($of);
                }
            }
        }

        if ($needFlush) {
            $em->flush();
        }
    }

    public function addUploadedUserFiles(
      Users $user,
      array $uploadFiles,
      string $filesDir,
    ): void {
        $em       = $this->doctrine->getManager();
        $needFlush = false;

        foreach ($uploadFiles as $uf) {
            $f = $this->findUploadedFileByHash($uf);
            if (!$f) {
                // add new file to disk and DB
                $f = Files::fromUpload($uf, $filesDir);
            }
            if ($f) {
                // add link to users_files table
                $f->addUser($user);
                $em->persist($f);
                $needFlush = true;
            }
        }//foreach

        if ($needFlush) {
            $em->flush();
        }
    }

}