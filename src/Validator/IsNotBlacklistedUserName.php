<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsNotBlacklistedUserName extends Constraint
{
    public $message = 'admin.user.name_in_blacklist';

}