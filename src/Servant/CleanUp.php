<?php

namespace Bunkermaster\Multiproc\Servant;

use Bunkermaster\Multiproc\Helper\TempFilesManager;

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
        // if Thread::output is not null, an error set it before the normal ending of the process
        // in that case, we keep the error message
        if (is_null(Thread::getOutput())) {
            Thread::setOutput(ob_get_clean());
        }
        new TempFilesManager(Thread::getOutputFile(), Thread::getOutput());
    }
}
