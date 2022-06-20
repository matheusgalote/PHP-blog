<?php

spl_autoload_register(function($class) {
    require_once($dot.'myadmin/src/'.$class.'.inc.php');
});
