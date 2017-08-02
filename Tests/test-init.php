<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 01/08/2017
 * Time: 17:22
 */
use \Master\ThreadManager;

require_once "./autoload.php";

$thread = new ThreadManager(__DIR__."/simple-sleep-thread-test.php", 12, []);
while (is_null($thread->result())) {
    usleep(500);
}
