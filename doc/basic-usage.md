# MultiProc basic usage
MultiProc synchronizes scripts with files created in the system temporary directory. Each thread is identified by a uniqueid() so you can refer to it later on.

## Master
MultiProc works in a rather simple fashion. 
Require the Composer autoload.
Instanciate ```\Master\ThreadManager```. When you need the results, just test for availability of the results and wait.
```
\Master\ThreadManager::__construct(string $script, int $timeout = DEFAULT_TIME_OUT, array $args = []);
```

***Note : $args is not yet implemented***
 
```php
<?php
use Bunkermaster\Multiproc\Master\ThreadManager;

require_once "./autoload.php";

$thread = new ThreadManager(__DIR__.DIRECTORY_SEPARATOR."yourscript.php", 12, []);
// loop master while the servant thread does its thing
while (is_null($thread->result())) {
    echo "Waiting ".time().PHP_EOL;
    sleep(1);
}
// display results
echo PHP_EOL."Results ".$thread->getUniqueId()." :".PHP_EOL.$thread->result();
```

## Servant
The servant requires a bit more work. 
* Require the Composer autoload.
* Call ```\Servant\Thread::init()``` to initiate the synchronization files.
* Declare the tick, as is. 
```php
<?php
declare(ticks=1);
register_tick_function('Config\checkTimeout');
```
* The cleanup process is handled by a simple ```\Servant\CleanUp::__destruct()``` little hack.
```php
<?php
require_once "vendor/autoload.php";

Bunkermaster\Multiproc\Servant\Thread::init();
// init tick for timeout check
declare(ticks=1);
register_tick_function('Config\checkTimeout');
// do your thing
echo "Results";
```
