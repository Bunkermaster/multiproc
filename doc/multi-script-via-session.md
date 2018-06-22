# Using multiproc with sessions

This library can also be used to start processes in a script and hook back up on the process on another script via the session.

The ThreadManager can be pushed in session and it will push the ThreadLog to session to keep all running threads in memory. 

***This process will be optimized at a later date to make use of the ThreadLog storage of all ThreadManager instances.***

[See example](/examples/test-session-step-1.php)
