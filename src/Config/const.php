<?php
/**
 * Created by PhpStorm.
 * @author Yann Le Scouarnec <bunkermaster@gmail.com>
 * Date: 02/08/2017
 * Time: 12:07
 */
namespace Bunkermaster\Multiproc\Config;

const MIN_TEMP_DIR_FREE_SPACE = 1048576;
const TEMP_FILE_PREFIX = "multiproc";
const PROCESS_ID_FILE_EXTENSION = ".pid";
const OUTPUT_FILE_EXTENSION = ".out";
const OPTION_FLAG_TIMEOUT = "T"; // CLI option
const OPTION_FLAG_UID = "u"; // CLI option
const DEFAULT_TIME_OUT = 1; // expressed in seconds
const PID_FILE_CREATION_TIME_OUT = 1; // expressed in seconds
