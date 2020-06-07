<?php

namespace App\Controller;

use App\Entity\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Form\Type\TemplateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Require ROLE_ADMIN for *every* controller method in this class.
 *
 * @IsGranted("ROLE_ADMIN")
 */
class TemplateController extends AbstractController
{
    /**
     * @Route("/template/delete/{template}", name="template_delete")
     */
    public function templateDelete(
        Request $request,
        EntityManagerInterface $em,
        Template $template
    ) {
        if (isset($template) && $request->isXmlHttpRequest()) {
            $em->remove($template);
            $em->flush();
            return new JsonResponse(
                array(
                    'status' => 'OK'
                ),
                200
            );
        }
        return new JsonResponse(
            array(
                'status' => 'Error',
                'message' => 'Error'
            ),
            400
        );
    }

    /**
     * @Route("/template/edit/{template}", name="template_edit")
     */
    public function templateEdit(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Request $request,
        Template $template
    ) {
        $templateForm = $this->createForm(TemplateType::class, $template);
        $templateForm->handleRequest($request);
        if ($templateForm->isSubmitted() && $templateForm->isValid()) {
            $entityManager->flush();
        } else {
            $errors = $validator->validate($templateForm);
        }
        return $this->render('admin/edit_template.html.twig', [
            'templateForm' => $templateForm->createView(),
            'template' => $template,
            'errors' => $errors ?? null
        ]);
    }
}
