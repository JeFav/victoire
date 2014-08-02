<?php

namespace Victoire\Bundle\BusinessEntityTemplateBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * BusinessEntityTemplate controller.
 *
 * @Route("/victoire-dcms/business-entity-template/businessentitytemplate")
 */
class BusinessEntityTemplateController extends BaseController
{
    /**
     * Creates a new BusinessEntityTemplate entity.
     *
     * @param Request $request
     * @param integer $id
     *
     * @Route("{id}/create", name="victoire_businessentitytemplate_businessentitytemplate_create")
     * @Method("POST")
     * @Template("VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate:new.html.twig")
     *
     * @return Ambiguous \Victoire\Bundle\BusinessEntityTemplateBundle\Entity\BusinessEntityTemplate NULL
     */
    public function createAction(Request $request, $id)
    {
        //get the business entity
        $businessEntity = $this->getBusinessEntity($id);
        $errorMessage = '';

        $entity = new BusinessEntityTemplate();
        $entity->setBusinessEntity($businessEntity);

        $form = $this->createCreateForm($entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            //get the url of the template
            $templateUrl = $entity->getUrl();

            //the shortcuts service
            $shortcuts = $this->get('av.shortcuts');

            //redirect to the page of the template
            $completeUrl = $shortcuts->generateUrl('victoire_core_page_show', array('url' => $templateUrl));

            $success = true;
        } else {
            //the form error service
            $formErrorService = $this->container->get('av.form_error_service');

            //get the errors as a string
            $errorMessage = $formErrorService->getRecursiveReadableErrors($form);

            $success = false;
            $completeUrl = null;
        }

        return new JsonResponse(array(
            'success' => $success,
            'url'     => $completeUrl,
            'message' => $errorMessage
        ));
    }

    /**
     * Creates a form to create a BusinessEntityTemplate entity.
     *
     * @param BusinessEntityTemplate $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     *
     * @return Form
     */
    private function createCreateForm(BusinessEntityTemplate $entity)
    {
        $businessEntityName = $entity->getBusinessEntityName();
        $businessProperty = $this->getBusinessProperties($entity);

        $form = $this->createForm('victoire_business_entity_template_type', $entity, array(
            'action' => $this->generateUrl('victoire_businessentitytemplate_businessentitytemplate_create', array('id' => $businessEntityName)),
            'method' => 'POST',
            'businessProperty' => $businessProperty
        ));

        return $form;
    }

    /**
     * Displays a form to create a new BusinessEntityTemplate entity.
     * @param string $id The id of the businessEntity
     *
     * @Route("/{id}/new", name="victoire_businessentitytemplate_businessentitytemplate_new")
     * @Method("GET")
     * @Template()
     *
     * @return array The entity and the form
     */
    public function newAction($id)
    {
        //get the business entity
        $businessEntity = $this->getBusinessEntity($id);

        $entity = new BusinessEntityTemplate();
        $entity->setBusinessEntity($businessEntity);

        $form = $this->createCreateForm($entity);

        $businessEntityHelper = $this->get('victoire_business_entity_template.business_entity_template_helper');
        $businessProperties = $businessEntityHelper->getBusinessProperties($businessEntity);

        $parameters = array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'businessProperties' => $businessProperties
        );

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate:new.html.twig',
                $parameters
            ),
            'success' => true
        ));
    }

    /**
     * Displays a form to edit an existing BusinessEntityTemplate entity.
     * @param string $id The id of the businessEntity
     *
     * @Route("/{id}/edit", name="victoire_businessentitytemplate_businessentitytemplate_edit")
     * @Method("GET")
     * @Template()
     *
     * @return array The entity and the form
     *
     * @throws \Exception
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $businessEntityTemplateHelper = $this->get('victoire_business_entity_template.business_entity_template_helper');
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');

        $entity = $em->getRepository('VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BusinessEntityTemplate entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        //the business property link to the page
        $businessEntityId = $entity->getBusinessEntityName();
        $businessEntity = $businessEntityHelper->findById($businessEntityId);

        $businessEntityTemplateHelper = $this->get('victoire_business_entity_template.business_entity_template_helper');

        $businessProperties = $businessEntityTemplateHelper->getBusinessProperties($businessEntity);

        $parameters = array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'businessProperties' => $businessProperties
        );

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate:edit.html.twig',
                $parameters
            ),
            'success' => true
        ));
    }

    /**
    * Creates a form to edit a BusinessEntityTemplate entity.
    *
    * @param BusinessEntityTemplate $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(BusinessEntityTemplate $entity)
    {
        $businessProperty = $this->getBusinessProperties($entity);

        $form = $this->createForm('victoire_business_entity_template_type', $entity, array(
            'action' => $this->generateUrl('victoire_businessentitytemplate_businessentitytemplate_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'businessProperty' => $businessProperty
        ));

        return $form;
    }
    /**
     * Edits an existing BusinessEntityTemplate entity.
     * @param Request $request
     * @param string  $id
     *
     * @Route("/{id}", name="victoire_businessentitytemplate_businessentitytemplate_update")
     * @Method("PUT")
     * @Template("VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate:edit.html.twig")
     *
     * @return array The parameter for the response
     *
     * @throws \Exception
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $template = $em->getRepository('VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate')->find($id);

        if (!$template) {
            throw $this->createNotFoundException('Unable to find BusinessEntityTemplate entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($template);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            //get the url of the template
            $templateUrl = $template->getUrl();

            //the shortcuts service
            $shortcuts = $this->get('av.shortcuts');

            //redirect to the page of the template
            $completeUrl = $shortcuts->generateUrl('victoire_core_page_show', array('url' => $templateUrl));

            $success = true;
        } else {
            $success = false;
            $completeUrl = null;
        }

        return new JsonResponse(array(
            'success' => $success,
            'url' => $completeUrl
        ));
    }

    /**
     * Deletes a BusinessEntityTemplate entity.
     * @param Request $request
     * @param string  $id
     *
     * @Route("/{id}", name="victoire_businessentitytemplate_businessentitytemplate_delete")
     * @Method("DELETE")
     *
     * @throws \Exception
     *
     * @return redirect
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find BusinessEntityTemplate entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('businessentitytemplate'));
    }

    /**
     * Creates a form to delete a BusinessEntityTemplate entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('victoire_businessentitytemplate_businessentitytemplate_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }

    /**
     * List the entities that matches the query of the businessEntityTemplate
     * @param BusinessEntityTemplate $entity
     *
     * @Route("listEntities/{id}", name="victoire_businessentitytemplate_businessentitytemplate_listentities")
     * @ParamConverter("id", class="VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate")
     * @return array The list of items for this template
     *
     * @throws Exception
     */
    public function listEntitiesAction(BusinessEntityTemplate $entity)
    {
        //services
        $businessEntityTemplateHelper = $this->get('victoire_business_entity_template.business_entity_template_helper');

        $entities = $businessEntityTemplateHelper->getEntitiesAllowed($entity);

        //parameters for the view
        $parameters = array(
            'businessEntityTemplate' => $entity,
            'items' => $entities);

        return new JsonResponse(array(
            'html' => $this->container->get('victoire_templating')->render(
                'VictoireBusinessEntityTemplateBundle:BusinessEntityTemplate:listEntities.html.twig',
                $parameters
            ),
            'success' => true
        ));
    }

    /**
     * Get an array of business properties by the business entity template
     *
     * @param BusinessEntityTemplate $entity
     *
     * @return array of business properties
     */
    public function getBusinessProperties(BusinessEntityTemplate $entity)
    {
        $businessEntityHelper = $this->get('victoire_core.helper.business_entity_helper');
        //the name of the business entity link to the business entity template
        $businessEntityName = $entity->getBusinessEntityName();

        $businessEntity = $businessEntityHelper->findById($businessEntityName);
        $businessProperties = $businessEntity->getBusinessPropertiesByType('businessIdentifier');

        $businessProperty = array();

        foreach ($businessProperties as $bp) {
            $entityProperty = $bp->getEntityProperty();
            $businessProperty[$entityProperty] = $entityProperty;
        }

        return $businessProperty;
    }
}