<?php

namespace Servant;

use Helper\TempFilesManager;

/**
 * Class CleanUp
 * this class is only used to generate the output file at the end of the sub
 * process execution.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Servant
 */
class CleanUp
{
    public function __destruct()
    {
        Thread::setOutput(ob_get_clean());
        new TempFilesManager(Thread::getOutputFile(), Thread::getOutput());
    }
}
