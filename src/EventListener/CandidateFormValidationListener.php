<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class CandidateFormValidationListener implements EventSubscriberInterface
{
    private $requestStack;
    private $translator;

    public function __construct(RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $errors = [];

        // Validate each step based on the current step
        $currentStep = isset($data['currentStep']) ? (int)$data['currentStep'] : 1;

        switch ($currentStep) {
            case 1:
                $errors = $this->validateStep1($data);
                break;
            case 2:
                $errors = $this->validateStep2($data);
                break;
            case 3:
                $errors = $this->validateStep3($data);
                break;
            case 4:
                $errors = $this->validateStep4($data);
                break;
            case 5:
                $errors = $this->validateStep5($data);
                break;
            case 6:
                $errors = $this->validateStep6($data);
                break;
            // Add more cases for additional steps
        }

        if (!empty($errors)) {
            foreach ($errors as $field => $error) {
                $form->addError(new FormError($error));
            }
        }
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        
        // Perform final validation before form submission
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFormError($form, '', 'form.error.general');
        }
    }

 private function validateStep1(array $data): array
    {
        $errors = [];
        if (empty($data['transactionNumber'])) {
            $errors['transactionNumber'] = $this->translator->trans('form.error.invalid_transaction_number_format');

        } else {
            // Valider le format du numéro de transaction en fonction de l'opérateur
            $isValid = $this->validateTransactionNumber($data['transactionNumber'], $data['paymentOperator']);
            if (!$isValid) {
                $errors['transactionNumber'] = $this->translator->trans('form.error.invalid_transaction_number_format');
            }
        }
        if (empty($data['paymentOperator'])) {
            $errors['paymentOperator'] = $this->translator->trans('form.error.payment_operator_required');
        }
        return $errors;
    }

    private function validateTransactionNumber(string $transactionNumber, string $operator): bool
    {
        switch ($operator) {
            case 'Orange':
                // Format: XX240817.1932.C58339 (où XX peut être MP ou CI)
                return preg_match('/^(MP|CI)\d{6}\.\d{4}\.[A-Z]\d{5}$/', $transactionNumber) === 1;
            case 'MTN':
                // Format: 10 chiffres
                return preg_match('/^\d{10}$/', $transactionNumber) === 1;
            default:
                return false;
        }
    }
    private function validateStep2(array $data): array
    {
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = $this->translator->trans('form.error.name_required');
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = $this->translator->trans('form.error.invalid_email');
        }
        if (empty($data['placeOfBirth'])) {
            $errors['placeOfBirth'] = $this->translator->trans('form.error.placeOfBirth_required');
        }
        if (empty($data['dateOfBirth'])) {
            $errors['dateOfBirth'] = $this->translator->trans('form.error.dateOfBirth_required');
        }
        if (empty($data['birthCertificate'])) {
            $errors['birthCertificate'] = $this->translator->trans('form.error.birthCertificate_required');
        }
        return $errors;
    }

    private function validateStep3(array $data): array
    {
        $errors = [];
        if (empty($data['cni'])) {
            $errors['cni'] = $this->translator->trans('form.error.cni_required');
        }
        if (empty($data['phoneNumber'])) {
            $errors['phoneNumber'] = $this->translator->trans('form.error.phoneNumber_required');
        }
        if (empty($data['cniIssueDate'])) {
            $errors['cniIssueDate'] = $this->translator->trans('form.error.cniIssueDate_required');
        }
        return $errors;
    }

    private function validateStep4(array $data): array
    {
        $errors = [];
        if (empty($data['photo'])) {
            $errors['photo'] = $this->translator->trans('form.error.photo_required');
        }
        return $errors;
    }

    private function validateStep5(array $data): array
    {
        $errors = [];
        if (empty($data['certificate'])) {
            $errors['certificate'] = $this->translator->trans('form.error.certificate_required');
        }
        if (empty($data['certificateYear'])) {
            $errors['certificateYear'] = $this->translator->trans('form.error.certificateYear_required');
        }
        return $errors;
    }

    private function validateStep6(array $data): array
    {
        // Add validations for step 6 if needed
        return [];
    }

    private function addFormError(FormInterface $form, string $field, string $messageKey)
    {
        $errorMessage = $this->translator->trans($messageKey);
        $form->get($field)->addError(new FormError($errorMessage));
    }
}