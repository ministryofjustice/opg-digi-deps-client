<?php
namespace AppBundle\Validator\Constraints;

use AppBundle\Form\Traits\HasTranslatorTrait;
use AppBundle\Form\Traits\TokenStorageTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailSameDomainValidator extends ConstraintValidator
{
    use TokenStorageTrait;
    use HasTranslatorTrait;

    /**
     * Validates a given email address matches the same domain as the logged in user
     *
     * @param mixed      $email
     * @param Constraint $constraint
     */
    public function validate($email, Constraint $constraint)
    {
        $creatorEmail = $this->getLoggedUserEmail();

        $creatorDomain = $this->getDomain($creatorEmail);
        $targetDomain = $this->getDomain($email);

        if (!empty($targetDomain) && $targetDomain !== $creatorDomain) {
            $this->context->addViolationAt('email', $constraint->message, ['creatorDomain' => $creatorDomain]);
        }
    }

    /**
     * Return domain portion of email address
     *
     * @param $email string
     *
     * @return string
     */
    private function getDomain($email)
    {
        return substr(strrchr($email, '@'), 1);
    }
}
