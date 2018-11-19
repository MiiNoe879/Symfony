<?php

if (CModule::IncludeModule('mail') && IsModuleInstalled("thurlyos"))
{
    $installFile = $_SERVER['DOCUMENT_ROOT'] . '/thurly/modules/mail/install/index.php';
    if (!is_file($installFile))
        return false;

    include_once($installFile);

    $moduleIdTmp = 'mail';
    if (!class_exists($moduleIdTmp))
        return false;

    $module = new $moduleIdTmp;

    $module->installThurlyOSMailService();
}
