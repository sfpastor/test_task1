<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsNotBlacklistedUserEmailDomainValidator extends ConstraintValidator
{
    
    private $blacklistedEmailDomains;

    public function __construct(array $blacklistedEmailDomains) 
    {
        $this->blacklistedEmailDomains = $blacklistedEmailDomains;
    }    
        
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsNotBlacklistedUserEmailDomain) {
            throw new UnexpectedTypeException($constraint, IsNotBlacklistedUserEmailDomain::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }
        
        $val = mb_strtolower(substr($value, strpos($value, '@') + 1));
        
        if (in_array($val, $this->blacklistedEmailDomains)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();            
        }
    }
}