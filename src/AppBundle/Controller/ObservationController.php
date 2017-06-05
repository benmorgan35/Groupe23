<?php

namespace AppBundle\Controller;

use AppBundle\Form\ObservationEditType;
use AppBundle\Form\ObservationFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use AppBundle\Entity\Observation;
use AppBundle\Form\ObservationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints\Date;


class ObservationController extends BaseController
{


    /**
     * @Route("/addObservation", name="addObservation")
     * @Security("has_role('ROLE_USER')")
     * @Method({"GET", "POST"})
     */
    public function addObservationAction(Request $request)
    {
        // Création de l'entité Observation
        $observation = new Observation();
        $observation->setDate(new \Datetime);


        $observation->setUser($this->get('security.token_storage')->getToken()->getUser());

        $form = $this->createForm(ObservationType::class, $observation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($observation->getImage()){
                $file = $observation->getImage();
                // Générer un nom unique
                $fileName = $this->get('app.image_uploader')->upload($file);
                $observation->setImage($fileName);
            }


            // On récupère l'EntityManager
            $em = $this->getDoctrine()->getManager();
            $em->persist($observation);
            $em->flush();

            //Envoi d'un mail à l'observateur
            $this->get('app.notification')->sendMailPostObservation($observation);

            //Notification d'une nouvelle observation aux admin

            $listAdmins = $em->getRepository('UserBundle:User')->findByRole("ROLE_SUPER_ADMIN");
            foreach ($listAdmins as $user)
            {

                $this->get('app.notification')->sendMailNewObservation($observation, $user);
            }

            return $this->redirect($this->generateUrl('viewObservation', array('id' => $observation->getId())));
        }

        return $this->render('default/addObservation.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/viewObservation/{id}", name="viewObservation")
     * @Method({"GET", "POST"})
     */
    public function viewObservationAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $observation = $em->getRepository('AppBundle:Observation')->find($id);

        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id n'existe pas, d'où ce if :
        if (null === $observation) {
            throw new NotFoundHttpException("L'observation d'id " . $id . " n'existe pas.");
        }


        return $this->render('default/viewObservation.html.twig', array(
            'observation' => $observation,
        ));
    }


    /**
     * @Route("/filter", name="filter")
     * @Method({"GET", "POST"})
     *
     */
    public function filterAction(Request $request)
    {
        $form = $this->createForm(ObservationFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $taxref = $data['taxref'];


            $query = $this->getDoctrine()
                ->getRepository('AppBundle:Observation')
                ->createQueryBuilder('o')
                ->where('o.dateObservation > :debut')
                ->andWhere('o.dateObservation < :fin');

            if ($data['especeFilter']) {
                $query->andWhere('o.taxref IN (:taxref)')
                    ->setParameters(array(
                        'debut' => $data['debut'],
                        'fin' => $data['fin'],
                        'taxref' => $data['taxref']
                    ));
            } else {
                $query->setParameters(array(
                    'debut' => $data['debut'],
                    'fin' => $data['fin'],
                ));
            }


            $observations = $query->getQuery()->getResult();


            return $this->exportObservations($observations);

        };

        return $this->render('default/exportForm.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     *--------------------------------------------------------------------------------------------------------------
     *==============   PARTIE ADMIN   ======================================================================
     * -------------------------------------------------------------------------------------------------------------
     */


    /**
     * Voire toutes les observations
     * @Route("/admin/observations", name="adminObservations")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function viewObservationsAction()
    {
        $listobservations = $this->getDoctrine()->getRepository("AppBundle:Observation")->findAll();
        return $this->render('Admin/observations.html.twig', array('listObservations' => $listobservations,));
    }

    /**
     * Supprime une observation
     * @Route("/admin/{id}/delete", name="deleteObservation")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteObservationAction(Observation $observation, Request $request)
    {
        $referer = $request->headers->get('referer');
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($observation);
        $entityManager->flush();
        $request->getSession()->getFlashbag()->add('success', 'L\'observation a été Supprimée');
        return $this->redirect($referer);
    }


    /**
     * Valide une observation
     * @Method({"GET"})
     * @Route("/admin/{id}/valid", name="validObservation")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function validObservation(Observation $observation, Request $request)
    {
        $referer = $request->headers->get('referer');
        $observation->setValided('1');
        $em = $this->getDoctrine()->getManager();
        $em->persist($observation);
        $em->flush();
        $request->getSession()->getFlashbag()->add('success', 'Le commentaire a été validé');
        return $this->redirect($referer);}

    /**
     * Affiche un formulaire pour modifier une observation
     * @Security("has_role('ROLE_ADMIN')")
     * @Method({"GET", "POST"})
     * @Route("/{id}/edit", requirements={"id": "\d+"}, name="edit")
     */
    public function editAction(Observation $observation, Request $request)
    {
        $referer = $request->headers->get('referer');
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(ObservationEditType::class, $observation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Observation modifiée avec succès');
            return $this->redirect($referer);

        }

        return $this->render(
            'Admin/observationEdit.html.twig',
            [
                'observation' => $observation,
                'form' => $form->createView(),
            ]
        );
    }




    //-------------------------------------------------
    // Autres fonctions
    //-------------------------------------------------
    /**
     * Fonction d'export Excel des observations
     * @param $observations
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportObservations($observations)
    {
        // On appel de service de création de fichier Excel
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        // On définit les propriétés globales du document
        $phpExcelObject->getProperties()->setCreator("Michel Dujardin")
            ->setLastModifiedBy("Giulio De Donato")
            ->setTitle("Liste des observations")
            ->setSubject("Observations d'oiseaux")
            ->setDescription("observations d'oiseau recencées par l'assosiation NAO")
            ->setKeywords("oiseaux nao taxref")
            ->setCategory("data export");

        // On prépare le titre des colonnes
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'id')
            ->setCellValue('B1', 'Date')
            ->setCellValue('C1', 'Auteur')
            ->setCellValue('D1', 'Latitude')
            ->setCellValue('E1', 'Longitude')
            ->setCellValue('F1', 'Commentaire')
            ->setCellValue('G1', 'CDNom')
            ->setCellValue('H1', 'LbNom')
            ->setCellValue('I1', 'Nom Vern');

        //ensuite on boucle pour remplir le tableau excel avec nos observations
        $i = 2;
        foreach ($observations as $observation) {
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $i, $observation->getId())
                ->setCellValue('B' . $i, $observation->getDate())
                ->setCellValue('C' . $i, $observation->getUser()->getUsername())
                ->setCellValue('D' . $i, $observation->getLatitude())
                ->setCellValue('E' . $i, $observation->getLongitude())
                ->setCellValue('F' . $i, $observation->getComment())
                ->setCellValue('G' . $i, $observation->getTaxref()->getCdnom())
                ->setCellValue('H' . $i, $observation->getTaxref()->getLbnom())
                ->setCellValue('I' . $i, $observation->getTaxref()->getNonvern());
            $i = $i + 1;
        }

        // On nomme l'onglet Actif
        $phpExcelObject->getActiveSheet()->setTitle('Export');

        // On précise quel onglet doit être ouvert lors de l'ouverture du fichier
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'liste-observations.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }







}