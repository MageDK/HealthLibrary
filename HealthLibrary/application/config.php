<?php
//*********************************************************
//* APPLICATION CONFIGURATION
//*********************************************************
DEFINE('CFG_SYS_ADMINISTRATOR', '');

//*********************************************************
//* CARRIAGE_RETURN
//*********************************************************
DEFINE('CFG_CR_UNIX', "\n");
DEFINE('CFG_CR_WINDOWS', "\r\n");
DEFINE('CFG_CR', CFG_CR_WINDOWS);

//*********************************************************
//* DATABASE CONNECTION
//*********************************************************
//* Setup the default connection information that can be
//* overriden by the individual applications.
DEFINE('CFG_DB_SERVER', '');
DEFINE('CFG_DB_PORT', '');
DEFINE('CFG_DB_USER', '');
DEFINE('CFG_DB_PASSWORD', '');
DEFINE('CFG_DB_SCHEMA', '');

DEFINE('CFG_DT_SQL_DATE_TIME', 'Y-m-d h:i:s');

//*********************************************************
//* ERROR LOGGING
//*********************************************************
//*
//* CFG_LOG_TO
//* Determines where the log output goes.
//* 1 = Sent to PHP system logger
//* 3 = Output goes to a user specified file.
DEFINE('CFG_LOG_TO', '3');
DEFINE('CFG_LOG_FILE', 'C:\\phplogs\\php-errors.log');
DEFINE('CFG_LOG_SCREEN', true);

//* Define the different levels of logging
DEFINE('CFG_LOG_ALL', 0);
DEFINE('CFG_LOG_TRACE', 1);
DEFINE('CFG_LOG_SQL', 2);
DEFINE('CFG_LOG_DEBUG', 3);
DEFINE('CFG_LOG_ERROR', 4);
DEFINE('CFG_LOG_WARNING', 5);

//* Define the current logging level
DEFINE('CFG_LOG_LEVEL', CFG_LOG_ALL);


//*********************************************************
//* LANGUAGE SETTINGS
//*********************************************************
DEFINE('CFG_LANG_DEFAULT', 'en');
?>
