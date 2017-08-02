<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 02/08/2017
 * Time: 12:55
 */
namespace Config;

use Servant\Thread;

// init constants
require_once __DIR__.DIRECTORY_SEPARATOR."const.php";

// init tick for timeout check
declare(ticks=1);
register_tick_function('Config\checkTimeout', true);

/**
 *
 */
function checkTimeout()
{
    if (!is_null(Thread::getTimeout())) {

    }
}
