<?php

namespace Helper;

use const Config\MIN_TEMP_DIR_FREE_SPACE;

/**
 * Class TemporaryFilesManager
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Helper
 */
class TemporaryFilesManager
{
    private $fileName = null;

    /**
     * TemporaryFilesManager constructor.
     * @param null $fileName
     */
    public function __construct($fileName)
    {
        $this->fileName = $fileName;
        // check for output directory accessibility
        $tempDir = sys_get_temp_dir();
        if (!is_writable($tempDir)) {
            // @todo Exception if temp dir is not writable
        }
        if (disk_free_space($tempDir) < MIN_TEMP_DIR_FREE_SPACE) {
            // @todo Exception if temp dir does not have 1MB free space
        }
    }


}