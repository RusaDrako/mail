<?php

if (class_exists('RD_Mail', false)) {
	return;
}

$classMap = [
	'RusaDrako\\mail\\mail'         => 'RD_Mail',
	'RusaDrako\\mail\\mail_addon'   => 'RD_Mail_Addon',
];

foreach ($classMap as $class => $alias) {
	class_alias($class, $alias);
}
