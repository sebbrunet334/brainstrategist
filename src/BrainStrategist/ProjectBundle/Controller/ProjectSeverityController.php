<?php

namespace BrainStrategist\ProjectBundle\Controller;

use BrainStrategist\ProjectBundle\Entity\Severity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

use BrainStrategist\KernelBundle\Entity\Organization;
use BrainStrategist\ProjectBundle\Entity\Project;
use BrainStrategist\ProjectBundle\Form\SeverityForm;
use BrainStrategist\KernelBundle\Entity\User;

use BrainStrategist\ProjectBundle\Form\TicketForm;
use Symfony\Component\HttpFoundation\File\File;
use BrainStrategist\KernelBundle\Entity;

class ProjectSeverityController extends Controller
{

    private $currentUser;

    /**
     *
     * Pre dispatcher event to check the security access of the current user
     *
     */
    public function preExecute(){

        if($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY') ) {
            $this->currentUser = $this->get('security.token_storage')->getToken()->getUser();
        }else{
            throw new HttpException(400, "You are not allowed to access Project. Please register or login first");
        }
    }


    /**
     * @Route("/{_locale}/project/{slug}/severity/create",name="severity_create")
     * @Route("/{_locale}/project/{slug}/severity/edit/{id}",name="severity_edit")
     */
    public function manageAction(Request $request,$id=null,$slug=null){

        $breadcrumbs = $this->get("white_october_breadcrumbs");
        $breadcrumbs->addItem( $this->get('translator')->trans("Home"), $this->get("router")->generate("kernel"));
        $breadcrumbs->addItem( $this->get('translator')->trans("Organizations"), $this->get("router")->generate("kernel"));
        $breadcrumbs->addItem( $this->get('translator')->trans("Projects"), $this->get("router")->generate("organize_access",array("slug"=>$slug)));

        $params=array();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $em = $this->getDoctrine()->getEntityManager();
        $organizationEntity= $em->getRepository("BrainStrategistKernelBundle:Organization");
        $projectEntity= $em->getRepository("BrainStrategistProjectBundle:Project");
        $severityEntity= $em->getRepository("BrainStrategistProjectBundle:Severity");

        if(isset($slug)){
            $project = $projectEntity->findBySlug($slug);
            $severity = new Severity();
        }else{
            return $this->redirectToRoute("default");
        }
        if(isset($id)){
            $breadcrumbs->addItem( $this->get('translator')->trans("Edit"));

            if($projectEntity->isMyProject($project[0]->getId(),$this->currentUser->getId()) && $project[0]->getCreator()->getId()==$this->currentUser->getId()){
                $form = $this->createForm(SeverityForm::class,$severityEntity->find($id));
            }else{
                return $this->redirectToRoute("default");
            }

        }else{
            $form = $this->createForm(SeverityForm::class,$severity);
        }

        if ('POST' == $request->getMethod()) {

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {

                $severity->setProject($project[0]);
                $response = $form->getData();
                if(is_null($id)) {
                    $em->persist($severity);
                }
                $em->persist($response);
                $em->flush();

                return $this->redirectToRoute("project_access", array('slug'=>$slug));
            }
        }
        $params = array_merge($params,
            array(
                "form" => $form->createView(),
            ));
        return $this->render(
            'BrainStrategistProjectBundle:Severity:manage.html.twig',
            $params
        );


    }

}