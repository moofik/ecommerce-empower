<?php

namespace App\Service\Api;

use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Require Symfony Form and Symfony Http Foundation
 * Trait FormHandlerTrait.
 */
trait FormHandlerTrait
{
    /**
     * @param Request       $request
     * @param FormInterface $form
     */
    private function processForm(Request $request, FormInterface $form)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            $apiProblem = new ApiProblem(400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);

            throw new ApiProblemException($apiProblem);
        }

        $clearMissing = $request->getMethod() != 'PATCH';

        try {
            $form->submit($data, $clearMissing);
        } catch (\Exception $exception) {
            $apiProblem = new ApiProblem(400, ApiProblem::TYPE_VALIDATION_ERROR);
            $apiProblem->set('detail', $exception->getMessage());

            throw new ApiProblemException($apiProblem);
        }
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}
