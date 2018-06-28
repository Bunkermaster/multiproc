<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 01/08/2017
 * Time: 17:23
 */
require_once __DIR__.DIRECTORY_SEPARATOR."autoload.php";

Bunkermaster\Multiproc\Servant\Thread::init();
// init tick for timeout check
declare(ticks=1);
register_tick_function('Bunkermaster\Multiproc\Servant\Thread::checkTimeout');
for ($i = 0; $i < 10; $i++) {
    sleep(1);
}
echo "Bingo!";
