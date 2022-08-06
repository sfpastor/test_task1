<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsNotBlacklistedUserEmailDomain extends Constraint
{
    public $message = 'admin.user.email_in_blacklist';

}