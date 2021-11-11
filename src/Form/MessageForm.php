<?php

namespace Pushword\Conversation\Form;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class MessageForm
{
    use FormTrait;

    protected function getStepOne(): FormBuilderInterface
    {
        /*
        if ($this->getUser()) {
            $this->message->setAuthorEmail($this->getUser()->getEmail());
            $this->message->setAuthorName($this->getUser()->getUsername());
            $user = true;
        }
        /**/

        $formBuilder = $this->initForm();

        if (! $this->getUser()) { // ! isset($user) ||
            $formBuilder->add('authorEmail', EmailType::class, ['constraints' => $this->getAuthorEmailConstraints()]);
            $formBuilder->add('authorName', null, ['constraints' => $this->getAuthorNameConstraints()]);
        }

        $formBuilder->add('content', TextareaType::class);

        return $formBuilder;
    }

    protected function getUser()
    {
        if (null === $token = $this->security->getToken()) {
            return null;
        }

        if (! \is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}
