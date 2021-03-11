<?php

if (class_exists('RD_mail', false)) {
    return;
}

$classMap = [
    'rd\\mail\\mail' => 'RD_Mail',
    'rd\\mail\\mail_addon' => 'RD_Mail_Addon',
];

foreach ($classMap as $class => $alias) {
    class_alias($class, $alias);
}

//class RD_Mail extends \rd\mail\mail {}
//class RD_Mail_Addon extends \rd\mail\mail_addon {}
