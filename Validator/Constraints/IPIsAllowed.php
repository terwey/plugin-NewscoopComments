<?php
namespace Newscoop\CommentsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IPIsAllowed extends Constraint
{
    public $message = 'The IP is banned from posting.';
}