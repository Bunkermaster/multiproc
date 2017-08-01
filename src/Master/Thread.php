<?php

namespace Master;

/**
 * Class Thread
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * @package Master
 */
class Thread
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
     * @param string $script
     * @param int $timeout
     * @param array $args
     */
    public function __construct(string $script, int $timeout = 1, array $args = [])
    {
        $this->script = $script;
        if(!file_exists($script)){
            throw new
        }
        $this->timeout = $timeout;
        $this->args = $args;
    }

    /*
     *  retourne null si script pas fini, une chaine avec le résultat du traitement dans tout autre cas
     */
    public function result(): ?string
    {

    }

    public function terminate(): bool; // tue le PID de la thread et nettoie le $commFile

}