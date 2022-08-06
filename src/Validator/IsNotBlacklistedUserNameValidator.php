<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsNotBlacklistedUserNameValidator extends ConstraintValidator
{
    
    private $blacklistedUserNames;

    public function __construct(array $blacklistedUserNames) 
    {
        $this->blacklistedUserNames = $blacklistedUserNames;
    }    
        
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsNotBlacklistedUserName) {
            throw new UnexpectedTypeException($constraint, IsNotBlacklistedUserName::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }
        
        $str_val = mb_strtolower($value);
        
        $found = false;
        foreach ($this->blacklistedUserNames as $blacklistedUserName) {
            if (strrpos($str_val, $blacklistedUserName) !== false) {
                $found = true;
                break;
            }            
        }
        
        if ($found) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();            
        }
    }
}