<?php
namespace Newscoop\CommentsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmailIsAllowed extends Constraint
{
    public $message = 'The Email address is banned from posting.';
}