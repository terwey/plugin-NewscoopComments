<?php
namespace Newscoop\CommentsBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IPIsAllowedValidator extends ConstraintValidator
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        $request = \Zend_Registry::get('container')->getService('request');
        $publicationId = $request->get('_newscoop_publication_metadata')['alias']['publication_id'];
        $publication = $this->em->getRepository('Newscoop\Entity\Publication')->findOneBy(array('id'=>$publicationId));

        $acceptanceRepository = $this->em->getRepository('Newscoop\Entity\Comment\Acceptance');

        if ($acceptanceRepository->checkBanned(array('ip'=>$value), $publication)) {
            $this->context->addViolation($constraint->message, array('%string%' => $value));
        }
    }
}