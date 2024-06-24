<?php

namespace App\Services;

use Exception;

class UnpackService
{
    private UnpackStrategy $strategy;

    public function setStrategy(UnpackStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @throws Exception
     */
    public function unpackFile($source, $destination, $extension): void
    {
        $archiveExtension = pathinfo($source, PATHINFO_EXTENSION);

        switch ($archiveExtension) {
            case 'zip':
                $this->setStrategy(new ZipUnpackStrategy());
                break;
            // В случае необходимости можно добавить другие стратегии
            default:
                throw new Exception('Unsupported archive type: ' . $archiveExtension);
        }

        $this->strategy->unpack($source, $destination, $extension);
    }
}

