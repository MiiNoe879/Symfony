<?php

\Thurly\Main\Loader::registerAutoLoadClasses("scale", array(
	"Thurly\\Scale\\Logger" => "lib/logger.php",
	"Thurly\\Scale\\ServerBxInfoException" => "lib/exceptions.php",
	"Thurly\\Scale\\NeedMoreUserInfoException" => "lib/exceptions.php"
));
