<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 02/08/2017
 * Time: 12:55
 */
namespace Bunkermaster\Multiproc\Config;

use Servant\Thread;

// init constants
require_once __DIR__.DIRECTORY_SEPARATOR."const.php";

/**
 *
 */
function checkTimeout()
{
    Thread::checkTimeout();
}
