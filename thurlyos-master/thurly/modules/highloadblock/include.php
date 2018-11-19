<?php

CModule::AddAutoloadClasses('highloadblock', array(
	'Thurly\Highloadblock\HighloadBlockTable' => 'lib/highloadblock.php',
	'\Thurly\Highloadblock\HighloadBlockTable' => 'lib/highloadblock.php',
	'CIBlockPropertyDirectory' => 'classes/general/prop_directory.php',
	'CUserTypeHlblock' => 'classes/general/cusertypehlblock.php'
));