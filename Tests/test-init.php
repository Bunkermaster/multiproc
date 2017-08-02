<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 01/08/2017
 * Time: 17:22
 */
use Bunkermaster\Multiproc\Master\ThreadManager;

require_once "./autoload.php";

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

$thread = new ThreadManager(__DIR__.DIRECTORY_SEPARATOR."simple-sleep-thread-test.php", 12, []);
echo "New thread started > process id:".$thread->getProcessId().
    " > uniqueId:".$thread->getUniqueId().
    " will return 'Bingo!'".
    PHP_EOL;
$threadError = new ThreadManager(__DIR__.DIRECTORY_SEPARATOR."simple-sleep-thread-test.php", 1, []);
echo "New thread started > process id:".$threadError->getProcessId().
    " > uniqueId:".$threadError->getUniqueId().
    " will return a timeout error".
    PHP_EOL;
while (is_null($thread->result())) {
    echo "Waiting ".time().PHP_EOL;
    sleep(1);
}
echo PHP_EOL."Results ".$thread->getUniqueId()." :".PHP_EOL.$thread->result();
echo PHP_EOL."Results ".$threadError->getUniqueId()." :".PHP_EOL.$threadError->result();
