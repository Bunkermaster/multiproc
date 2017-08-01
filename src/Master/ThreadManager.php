<?php

namespace Master;

use Exception\ScriptNotFoundException;

/**
 * Class ThreadManager
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Master
 */
class ThreadManager
{
    private $startTime = null; // set on construct
    private $endTime = null; // calculated from $startTime + $timeout
    private $script = null; // set on construct
    private $timeout = null; // int set on construct
    private $args = null; // array set on construct
    private $commFile = null; // set on construct
    private $pid = null; // int process id set on construct

    /**
     * Thread constructor.
     * @param string $script script to execute in new CLI thread
     * @param int $timeout in seconds
     * @param array $args script arguments
     * @throws ScriptNotFoundException script not found
     */
    public function __construct(string $script, int $timeout = 1, array $args = [])
    {
        $this->startTime = microtime(true);
        $this->script = $script;
        if(!file_exists($script)){
            throw new ScriptNotFoundException('Script requested "'.$script.'"was not found on filesystem');
        }
        $this->timeout = $timeout;
        $this->args = $args;
    }

    /**
     *  retourne null si script pas fini, une chaine avec le résultat du traitement dans tout autre cas
     * @return null|string
     */
    public function result(): ?string
    {
        return null;
    }

    /**
     * tue le PID de la thread et nettoie le $commFile
     * @return bool
     */
    public function terminate(): bool
    {
        return false;
    }

}