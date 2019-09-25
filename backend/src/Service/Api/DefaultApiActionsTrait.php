<?php

namespace App\Service\Api;

use App\Service\Api\Problem\ApiProblem;
use App\Service\Api\Problem\ApiProblemException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

trait DefaultApiActionsTrait
{
    use ApiResponseTrait, FormHandlerTrait;

    /**
     * @param Request $request
     * @param mixed   $entity
     * @param string  $formTypeClass
     */
    public function fillEntityFromRequest(Request $request, $entity, string $formTypeClass)
    {
        /** @var FormInterface $form */
        $form = $this->createForm($formTypeClass, $entity);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $errors = $this->getErrorsFromForm($form);
            $this->throwValidationErrorException($errors);
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param mixed                  $entity
     */
    public function saveEntity(EntityManagerInterface $entityManager, $entity)
    {
        try {
            $entityManager->persist($entity);
            $entityManager->flush();
        } catch (ORMException $e) {
            $this->throwDatabaseApiException($e->getMessage());
        }
    }

    /**
     * @param string|null $detail
     */
    public function throwDatabaseApiException(string $detail = null)
    {
        $problem = new ApiProblem(500, ApiProblem::TYPE_SERVER_DATABASE_ERROR);

        if ($detail) {
            $problem->set('detail', $detail);
        }

        throw new ApiProblemException($problem);
    }
}
