<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 02/08/2017
 * Time: 17:32
 */
use Bunkermaster\Multiproc\Master\ThreadManager;

session_start();
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

require_once "./autoload.php";
if (!isset($_SESSION['thread'])) {
    die('Please execute test-session-step-1.php first to set the session');
}
$thread = unserialize($_SESSION['thread']);
while (is_null($thread->result())) {
    echo "Waiting ".time().PHP_EOL;
    sleep(1);
}
echo PHP_EOL."Results ".$thread->getUniqueId()." :".PHP_EOL.$thread->result();
