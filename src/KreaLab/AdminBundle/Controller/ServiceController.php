<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;
use KreaLab\CommonBundle\Entity\ServiceDiscount;
use Symfony\Component\HttpFoundation\Request;

class ServiceController extends AbstractEntityController
{
    protected $orderBy    = [
        'active'   => 'DESC',
        'position' => 'ASC',
    ];
    protected $tmplItem   = 'AdminBundle:Service:item.html.twig';
    protected $tmplList   = 'AdminBundle:Service:list.html.twig';
    protected $listFields = ['name', ['code', 'min_col'], ['price', 'min_col text-right']];

    /**
     * @inheritdoc
     */
    public function listAction(Request $request)
    {
        return $this->render($this->tmplList, [
            'pagerfanta'  => $this->pagerfanta($this->listQb(), 1000000),
            'list_fields' => $this->listFields,
            'filter_form' => null,
        ]);
    }

    /**
     * @param $entity \KreaLab\CommonBundle\Entity\Service
     * @return \KreaLab\CommonBundle\Entity\Service
     */
    public function prePersist($entity)
    {
        if ($entity->getId()) {
            $this->em->getRepository('CommonBundle:ServiceDiscount')->createQueryBuilder('sd')
                ->delete()
                ->andWhere('sd.service = :service')->setParameter('service', $entity)
                ->getQuery()->execute();
        }

        $request = $this->get('request_stack')->getCurrentRequest();

        $discounts = $this->em->getRepository('CommonBundle:Discount')->findBy([], ['name' => 'ASC']);

        $sDiscounts       = (array)$request->get('s_discounts', []);
        $sDiscountsActive = (array)$request->get('s_discounts_active', []);
        foreach ($discounts as $discount) { /** @var $discount \KreaLab\CommonBundle\Entity\Discount */
            $id        = $discount->getId();
            $sDiscount = new ServiceDiscount();
            $sDiscount->setSum(isset($sDiscounts[$id]) ? max(intval($sDiscounts[$id]), 0) : 0);
            $sDiscount->setActive(isset($sDiscountsActive[$id]));
            $sDiscount->setDiscount($discount);
            $sDiscount->setService($entity);
            $this->em->persist($sDiscount);
        }

        $subjects      = [];
        $sSubjects     = (array)$request->get('subjects', []);
        $sSubjectsBold = (array)$request->get('subjects_bold', []);
        foreach ($sSubjects as $key => $subject) {
            $subject = trim($subject);
            if ($subject) {
                $subjects[] = [
                    $subject,
                    isset($sSubjectsBold[$key]),
                ];
            }
        }

        $entity->setSubjects($subjects);

        $medicalCenterErrors      = [];
        $sMedicalCenterErrors     = (array)$request->get('medical_center_errors', []);
        $sMedicalCenterErrorsBold = (array)$request->get('medical_center_errors_bold', []);
        foreach ($sMedicalCenterErrors as $key => $medicalCenterError) {
            $medicalCenterError = trim($medicalCenterError);
            if ($medicalCenterError) {
                $medicalCenterErrors[] = [
                    $medicalCenterError,
                    isset($sMedicalCenterErrorsBold[$key]),
                ];
            }
        }

        $entity->setMedicalCenterErrors($medicalCenterErrors);

        $duplicates      = [];
        $sDuplicates     = (array)$request->get('duplicates', []);
        $sDuplicatesBold = (array)$request->get('duplicates_bold', []);
        foreach ($sDuplicates as $key => $duplicate) {
            $duplicate = trim($duplicate);
            if ($duplicate) {
                $duplicates[] = [
                    $duplicate,
                    isset($sDuplicatesBold[$key]),
                ];
            }
        }

        $entity->setDuplicates($duplicates);

        if ($entity->getReferenceType()) {
            $reqServcie      = $request->get('service');
            $isEegConclusion = isset($reqServcie['is_eeg_conclusion']) ? true : false;
            $isGnoch         = isset($reqServcie['is_gnoch']) ? true : false;

            $entity->setIsEegConclusion($isEegConclusion);
            $entity->setIsGnoch($isGnoch);
        } else {
            $entity->setIsEegConclusion(false);
            $entity->setIsGnoch(false);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    /**
     * @param $entity \KreaLab\CommonBundle\Entity\Service
     * @return array
     */
    public function getRenderExtraOptions($entity)
    {
        $request           = $this->get('request_stack')->getCurrentRequest();
        $discounts         = $this->em->getRepository('CommonBundle:Discount')->findBy([], ['position' => 'ASC']);
        $sDiscounts        = [];
        $sDiscountsActive  = [];
        $servicesDiscounts = $entity->getServicesDiscounts();
        foreach ($servicesDiscounts as $discount) { /** @var $discount \KreaLab\CommonBundle\Entity\ServiceDiscount */
            $sDiscounts[$discount->getDiscount()->getId()]       = $discount->getSum();
            $sDiscountsActive[$discount->getDiscount()->getId()] = $discount->getActive();
        }

        $sDiscounts       = (array)$request->get('s_discounts', []) + $sDiscounts;
        $sDiscountsActive = (array)$request->get('s_discounts_active', []) + $sDiscountsActive;

        return [
            'discounts'          => $discounts,
            's_discounts'        => $sDiscounts,
            's_discounts_active' => $sDiscountsActive,
        ];
    }

    public function viewAction($id)
    {
        $service = $this->em->find('CommonBundle:Service', $id);
        if (!$service) {
            throw $this->createNotFoundException();
        }

        if ($this->has('profiler')) {
            $this->get('profiler')->disable();
        }

        $workplace = $this->em->getRepository('CommonBundle:Workplace')->createQueryBuilder('w')
            ->andWhere('w.legal_entity IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        $numParts   = [];
        $numParts[] = $workplace->getLegalEntity()->getId();
        $numParts[] = $workplace->getId();
        $numParts[] = $service->getId();
        $numParts[] = time() - strtotime('2016-01-01');

        $params = [
            'num'            => implode('-', $numParts),
            'last_name'      => 'ОченьДлиннаяФамилия',
            'first_name'     => 'ОченьДлинноеИмя',
            'patronymic'     => 'ОченьДлинноеОтчество',
            'birthday'       => new \DateTime('1990-01-01'),
            'd_license_date' => new \DateTime('2000-01-01'),
            'sex'            => 1,
            'phone'          => ' (123) 456-78-90',
            'passport'       => 'Очень длинные и непонятные данные паспорта,'
                .' которые могут быть неверными или не совсем точными',
            'address'        => 'Очень длинный и запутанный адрес неизвестного никому места'
                .' в глубинке нашей необъятной страны',
            'sum'            => '12345',
            'sum_online'     => '12345',
        ];

        return $this->render('AppBundle:Operator:Service/agreement.html.twig', [
            'workplace'             => $workplace,
            'service'               => $service,
            'params'                => $params,
            'admin'                 => true,
            'operatorName'          => 'ОченьДлиннаяФамилияОператора ОченьДлинноеИмяОператора'
                .' ОченьДлинноеОтчествоОператора',
            'operatorPowerAttorney' => 'Оп-1234567890',
        ]);
    }

    public function itemAction(Request $request)
    {
        $id = null;
        $id = $request->get('id');
        if ($id) {
            $entity = $this->repo->find($id);
            if (!$entity) {
                throw $this->createNotFoundException($this->entityName.' for id "'.$id.'" not found.');
            }
        } else {
            $entity = new $this->entityClassName();
        }

        $entity = $this->preForm($entity);

        $form = $this->createForm($this->formClassName, $entity, $this->getFormOptions());
        $form->handleRequest($request);
        if ($request->isMethod('post')) {
            $form = $this->checkData($form);

            if ($form->isValid()) {
                $entity = $this->prePersist($entity);

                $this->em->persist($entity);

                if ($this->withImage) {
                    $image = call_user_func([$entity, 'getImage']);
                    if ($image) {
                        call_user_func([$image, 'set'.$this->entityName], null);
                    }

                    $imageId = intval(call_user_func([$form->get('image_id'), 'getData']));
                    $image   = $this->em->find('CommonBundle:Image', $imageId);
                    if ($image) {
                        call_user_func([$image, 'set'.$this->entityName], $entity);
                    }
                }

                $this->em->flush();

                $this->postFlush($entity);

                if ($id) {
                    $this->addFlash('success', $this->get('translator')->trans('flashes.success_edited'));
                    return $this->redirectToList();
                } else {
                    $this->addFlash('success', $this->get('translator')->trans('flashes.success_added'));
                    return $this->redirectToAdd();
                }
            }
        }

        $options = [
            'form'         => $form->createView(),
            'entity'       => $entity,
            'image_form'   => null,
            'image_filter' => $this->imageFilter,
        ];

        $options = array_merge($options, $this->getRenderExtraOptions($entity));

        return $this->render($this->tmplItem, $options);
    }
}
