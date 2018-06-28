<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 01/08/2017
 * Time: 17:22
 */
use Bunkermaster\Multiproc\Master\ThreadManager;

require_once __DIR__.DIRECTORY_SEPARATOR."autoload.php";

// change content type if not CLI
if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
}

// turn logging on
ThreadManager::toggleThreadLog(true);

$thread = new ThreadManager(__DIR__.DIRECTORY_SEPARATOR."simple-servant-instant-response.php", 5, []);
echo "New thread started > process id:".$thread->getProcessId().
    " > uniqueId:".$thread->getUniqueId().
    " will return 'Bingo!'".
    PHP_EOL;
while (!$thread->result()) {
    echo "Waiting ".time().PHP_EOL;
    usleep(500000);
}
echo PHP_EOL."Results ".$thread->getUniqueId()." :".PHP_EOL.$thread->result();

ThreadManager::showAllLogs();
ThreadManager::showAllLogsChrono();
