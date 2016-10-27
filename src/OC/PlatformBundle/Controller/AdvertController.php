<?php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller
{
    /**
     * @param $page
     * @return Response
     */
    public function indexAction($page)
    {
        if($page < 1){
            throw new NotFoundHttpException("Page $page innexistante");
        }

        $repository = $this->getDoctrine()->getManager()->getRepository("OCPlatformBundle:Advert");

        $nbPerPage = 3;

        $listAdverts = $repository->getAdverts($page,$nbPerPage);

        $nbPages = ceil(count($listAdverts)/$nbPerPage);

        if ($page > $nbPages){
            throw $this->createNotFoundException('La page '. $page. ' n\'existe pas.');
        }

        return $this->render('OCPlatformBundle:Advert:index.html.twig', ['listAdverts' => $listAdverts, 'nbPages' => $nbPages, 'page' => $page]);
    }

    /**
     * @param $id
     * @return Response
     */
    public function viewAction($id)
    {

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository("OCPlatformBundle:Advert");

        $advert = $repository->find($id);

        //Liste des candidature
        $listApplications = $em
            ->getRepository('OCPlatformBundle:Application')
            ->findBy(['advert' => $advert]);

        //Liste des compétences
        $listSkills = $em
            ->getRepository('OCPlatformBundle:AdvertSkill')
            ->findBy(['advert' => $advert]);

        if($advert == null){
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        return $this->render('OCPlatformBundle:Advert:view.html.twig', ['advert' => $advert, 'listApplication' => $listApplications, 'listSkills' => $listSkills]);
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addAction(Request $request)
    {

        //Création de l'objet Advert
        $advert = new Advert();
        $advert->setTitle("Recherche Développeur Symfony");
        $advert->setContent("Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…");
        $advert->setAuthor("Lionel");
        $advert->setEmail("lion.mar@me.com");

        //Création de l'entité image
        $image = new Image();
        $image->setUrl("http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg");
        $image->setAlt("Job de rêve");

        //Ajout de l'image à l'advert
        $advert->setImage($image);

        //Ajout des candidature
        $candidature1 = new Application();
        $candidature1->setAuthor("Lionel");
        $candidature1->setContent("Première candidature");
        $candidature1->setAdvert($advert);

        $candidature2 = new Application();
        $candidature2->setAuthor("Justin");
        $candidature2->setContent("Deuxième candidature");
        $candidature2->setAdvert($advert);

        //Récupération de l'entity manager
        $em = $this->getDoctrine()->getManager();

        //Ajout des compétences
        $listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();

        foreach ($listSkills as $skill)
        {
            $advertSkill = new AdvertSkill();

            $advertSkill->setAdvert($advert);

            $advertSkill->setSkill($skill);

            $advertSkill->setLevel('Expert');

            $em->persist($advertSkill);
        }

        $listCategores = $em->getRepository('OCPlatformBundle:Category')->findAll();


            //Persistance de l'objet
        $em->persist($advert);
        $em->persist($candidature1);
        $em->persist($candidature2);

        //Flush tous ce qui à été persisté avant
        $em->flush();

        if($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            return $this->redirectToRoute('oc_plateform_view', ['id' => $advert->getId()]);
        }

        return $this->render('OCPlatformBundle:Advert:add.html.twig');

    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction($id, Request $request)
    {

        if($request->isMethod('POST')){
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_plateform_view', ['id' => $id]);
        }

        $em = $this->getDoctrine()->getManager();

        //On récupère l'annocne
        $advert = $em->getRepository("OCPlatformBundle:Advert")->find($id);

        if ($advert === null) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        //On récupère les catégorie
        $listCategories = $em->getRepository("OCPlatformBundle:Category")->findAll();

        //On ajoute toutes les catéories à l'annonce
        foreach ($listCategories as $category){
            $advert->addCategory($category);
        }

        $em->flush();

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', ['advert' => $advert]);
    }

    /**
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        //On récupère l'annocne
        $advert = $em->getRepository("OCPlatformBundle:Advert")->find($id);

        if ($advert === null) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        //Suppression de toutes les catégories
        foreach ($advert->getCategories() as $category) {
            $advert->removeCategory($category);
        }

        //Suppression des compétences
        $skills = $em->getRepository("OCPlatformBundle:AdvertSkill")->findBy(['advert' => $advert]);
        foreach ($skills as $s){
            $em->remove($s);
        }

        //Suppression des candidatures
        $application = $em->getRepository("OCPlatformBundle:Application")->findBy(['advert' => $advert]);
        foreach ($application as $a){
            $em->remove($a);
        }

        $em->remove($advert);

        //On enregistre en base de données
        $em->flush();

        return $this->render('OCPlatformBundle:Advert:delete.html.twig');
    }

    public function menuAction($limit)
    {
        $listAdverts = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository("OCPlatformBundle:Advert")
            ->findBy(
                [],
                ['date' => 'desc'],
                $limit,
                0
            );

        return $this->render('OCPlatformBundle:Advert:_menu.html.twig', ['listAdverts' => $listAdverts]);
    }


}
