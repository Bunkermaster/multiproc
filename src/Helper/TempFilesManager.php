<?php

namespace Bunkermaster\Multiproc\Helper;

use const Bunkermaster\Multiproc\Config\MIN_TEMP_DIR_FREE_SPACE;

/**
 * Class TemporaryFilesManager
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Helper
 */
class TempFilesManager
{
    private $fileName = null;

    /**
     * TemporaryFilesManager constructor.
     * @param string $fileName target file name
     * @param string $content target file content
     * @throws \Exception
     */
    public function __construct(string $fileName, string $content = null)
    {
        // get target directory
        $tempDir = sys_get_temp_dir();
        // check for output directory accessibility
        if (!is_writable($tempDir)) {
            // @todo Exception if temp dir is not writable
            throw new \Exception('Exception if temp dir is not writable');
        }
        if (disk_free_space($tempDir) < MIN_TEMP_DIR_FREE_SPACE) {
            // @todo Exception if temp dir does not have 1MB free space
            throw new \Exception('Exception if temp dir does not have 1MB free space');
        }
        $this->fileName = $tempDir."/".$fileName;
        if (!is_null($content) && file_exists($this->fileName)) {
            // @todo Exception if temp temp file failed to create
            throw new \Exception('Exception if temp temp file failed to create ('.$this->fileName.')');
        }
        if (!is_null($content)) {
            $this->output($content);
        }
    }

    /**
     * @param string $content
     * @throws \Exception
     */
    public function output(string $content) : void
    {
        if (!file_put_contents($this->fileName, $content)) {
            // @todo Exception if temp file failed to write
            throw new \Exception('Exception if temp file failed to write ('.$this->fileName.')');
        }
    }

    /**
     * @return null|string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}
