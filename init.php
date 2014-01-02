<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Custom exception handler.
 */
set_exception_handler(array('Alt_Exception', 'handler'));
