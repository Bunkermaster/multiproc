<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 02/08/2017
 * Time: 17:31
 */
use \Master\ThreadManager;

session_start();

require_once "./autoload.php";

$thread = new ThreadManager(__DIR__."/simple-sleep-thread-test.php", 12, []);
$_SESSION['thread'] = serialize($thread);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <p>New thread started >
        process id:<?=$thread->getProcessId()?> >
        uniqueId:<?=$thread->getUniqueId()?>
        will return 'Bingo!' in <a href="test-session-step-2.php">next step</a>
    </p>
</body>
</html>
