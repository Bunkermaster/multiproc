<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 01/08/2017
 * Time: 17:22
 */
use \Master\ThreadManager;

require_once "./autoload.php";

header("Content-Type: text/text");
$thread = new ThreadManager(__DIR__."/simple-sleep-thread-test.php", 12, []);
echo "New thread started > process id:".$thread->getProcessId()." > uniqueId:".$thread->getUniqueId().PHP_EOL;
$threadError = new ThreadManager(__DIR__."/simple-sleep-thread-test.php", 1, []);
echo "New thread started > process id:".$threadError->getProcessId()." > uniqueId:".$threadError->getUniqueId().PHP_EOL;
while (is_null($thread->result())) {
    echo "Waiting ".time().PHP_EOL;
    sleep(1);
}
echo PHP_EOL."Results ".$thread->getUniqueId()." :".PHP_EOL.$thread->result();
echo PHP_EOL."Results ".$threadError->getUniqueId()." :".PHP_EOL.$threadError->result();
