<?php

namespace Thurly\Mail;

use Thurly\Main;

abstract class BaseException extends Main\SystemException
{
}

class ReceiverException extends BaseException
{
}
