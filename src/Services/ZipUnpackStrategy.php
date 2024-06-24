<?php

namespace App\Services;

use Exception;
use ZipArchive;

class ZipUnpackStrategy implements UnpackStrategy
{
    /**
     * @throws Exception
     */
    public function unpack($source, $destination, $extension = null): void
    {
        $zip = new ZipArchive;
        if ($zip->open($source) === true) {
            $validFiles = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if ($extension === null || pathinfo($filename, PATHINFO_EXTENSION) === $extension) {
                    $validFiles[] = $filename;
                }
            }

            if (empty($validFiles)) {
                throw new Exception('Не найдено файлов в архиве с расширением: ' . $extension);
            }

            $zip->extractTo($destination, $validFiles);
            $zip->close();
        } else {
            throw new Exception('Не могу открыть zip файл: ' . $source);
        }
    }
}

