<?php
namespace Newscoop\CommentsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IPIsAllowedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        // $repositoryAcceptance = \Zend_Registry::get('container')->getHelper('entity')->getRepository('Newscoop\Entity\Comment\Acceptance');
        // if ($repositoryAcceptance->checkBanned(array('ip'=>'192.168.2.1'), '1')) {
        // if (!preg_match('/^[a-zA-Za0-9]+$/', $value, $matches)) {
        if (true) {
            $this->context->addViolation($constraint->message, array('%string%' => $value));
        }
    }
}