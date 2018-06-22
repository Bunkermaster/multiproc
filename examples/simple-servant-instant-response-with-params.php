<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 01/08/2017
 * Time: 17:23
 */

use Bunkermaster\Multiproc\Servant\Thread;

require_once __DIR__.DIRECTORY_SEPARATOR."autoload.php";

Thread::init($argv);
// init tick for timeout check
declare(ticks=1);
register_tick_function('Bunkermaster\Multiproc\Servant\Thread::checkTimeout');
sleep(1);

echo "Bingo!".PHP_EOL;
echo var_export(Thread::getArguments());
