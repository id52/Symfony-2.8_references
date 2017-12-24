<?php

namespace KreaLab\AdminBundle\Controller;

use KreaLab\CommonBundle\Entity\Schedule;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use KreaLab\AdminSkeletonBundle\Controller\AbstractEntityController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class FilialController extends AbstractEntityController
{
    protected $listFields = [['id', 'min_col'], ['name_short', 'min_col'], 'name'];
    protected $orderBy    = ['active' => 'DESC', 'name' => 'ASC'];
    protected $perms      = ['ROLE_MANAGE_FILIALS'];
    protected $tmplItem   = 'AdminBundle:Filial:item.html.twig';
    protected $tmplList   = 'AdminBundle:Filial:list.html.twig';

    protected function getRenderExtraOptions($entity)
    {
        /** @var $entity \KreaLab\CommonBundle\Entity\Filial */
        $ban = null;
        if ($entity->getId() && !$entity->getActive()) {
            $ban = $this->em->getRepository('CommonBundle:FilialBanLog')->createQueryBuilder('fbl')
                ->andWhere('fbl.filial = :filial')->setParameter('filial', $entity)
                ->addOrderBy('fbl.created_at', 'DESC')
                ->setMaxResults(1)
                ->getQuery()->getOneOrNullResult();
        }

        return ['ban' => $ban];
    }

    public function scheduleAction($id)
    {
        $filial = $this->em->getRepository('CommonBundle:Filial')->find($id);

        if (!$filial) {
            throw $this->createNotFoundException();
        }

        $startDate = new \DateTime('yesterday');

        $schedules = $this->em->getRepository('CommonBundle:Schedule')->createQueryBuilder('s')
            ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
            ->andWhere('s.date >= :date')->setParameter('date', $startDate)
            ->getQuery()->getResult();

        $dates = [];
        foreach ($schedules as $schedule) { /** @var $schedule \KreaLab\CommonBundle\Entity\Schedule */
            $dates[$schedule->getDate()->format('Y-m-d')] = $schedule->getDate()->format('Y-m-d');
        }

        $startDate->add(new \DateInterval('P1D'));

        return $this->render('AdminBundle:Filial:schedule.html.twig', [
            'filial'    => $filial,
            'startDate' => $startDate->format('d.m.Y'),
            'dates'     => $dates,
        ]);
    }

    public function scheduleEditDayAction(Request $request, $id)
    {
        $filial = $this->em->getRepository('CommonBundle:Filial')->find($id);

        if (!$filial) {
            throw $this->createNotFoundException('no filial');
        }

        $date = $request->get('date');

        if (!$date) {
            throw $this->createNotFoundException('no date');
        }

        $date = new \DateTime($date);

        if ($date < new \DateTime('yesterday')) {
            throw $this->createNotFoundException('date < now');
        }

        $schedule = $this->em->getRepository('CommonBundle:Schedule')->createQueryBuilder('s')
            ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
            ->andWhere('s.date = :date')->setParameter('date', $date)
            ->getQuery()->getOneOrNullResult(); /** @var $schedule \KreaLab\CommonBundle\Entity\Schedule */

        if (!$schedule) {
            $schedule = new Schedule();
            $schedule->setDate($date);
            $schedule->setFilial($filial);
        }

        $data = [];
        if ($schedule->getStartTime()) {
            $data['day_from'] = $schedule->getStartTime();
        }

        if ($schedule->getEndTime()) {
            $data['day_to'] = $schedule->getEndTime();
        }

        $fb = $this->createFormBuilder($data, [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);

        $fb->add('day_from', TimeType::class, [
            'label'    => twig_localized_date_filter(new \Twig_Environment(), $date, 'full', 'none'),
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
            'disabled' => ($schedule->getStartTime() and $schedule->getStartTime() < new \DateTime()),
        ]);

        $fb->add('day_to', TimeType::class, [
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('post')) {
            $start = null;
            $end   = null;

            if ($form->get('day_from')->getData() and $form->get('day_to')->getData()) {
                $start = new \DateTime();
                $end   = new \DateTime();

                $hFrom = $form->get('day_from')->getData()->format('H');
                $mFrom = $form->get('day_from')->getData()->format('i');

                $hTo = $form->get('day_to')->getData()->format('H');
                $mTo = $form->get('day_to')->getData()->format('i');

                $start->setTime($hFrom, $mFrom);
                $end->setTime($hTo, $mTo);

                $start->setDate($date->format('Y'), $date->format('m'), $date->format('d'));
                $end->setDate($date->format('Y'), $date->format('m'), $date->format('d'));

                if ($start < new \DateTime() and $schedule->getStartTime() != $start) {
                    $form->get('day_from')->addError(new FormError('Время начала смены меньше текущего времени'));
                }

                if ($start > $end) {
                    $form->get('day_from')->addError(new FormError('Время окончания меньше времени начала'));
                }

                if ($form->get('day_from')->getData() xor $form->get('day_to')->getData()) {
                    $form->get('day_from')->addError(new FormError('Заполните все поля'));
                }
            }

            if ($form->isValid()) {
                if ($form->get('day_from')->getData() and $form->get('day_to')->getData()) {
                    $schedule->setStartTime($start);
                    $schedule->setEndTime($end);
                    $this->em->persist($schedule);
                    $this->em->flush();
                    $this->addFlash('success', 'Заполнили');

                    return $this->redirectToRoute('admin_filial_schedule', ['id' => $filial->getId()]);
                } else {
                    $this->em->remove($schedule);
                    $this->em->flush();

                    $this->addFlash('success', 'Очистили');
                    return $this->redirectToRoute('admin_filial_schedule', ['id' => $filial->getId()]);
                }
            }
        }

        return $this->render('AdminBundle:Filial:schedule_edit_day.html.twig', [
            'filial' => $filial,
            'form'   => $form->createView(),
            'date'   => $date,
        ]);
    }

    public function scheduleFillWeekAction(Request $request, $id)
    {
        $filial = $this->em->getRepository('CommonBundle:Filial')->find($id);

        if (!$filial) {
            throw $this->createNotFoundException('no filial');
        }

        $fb = $this->createFormBuilder([], [
            'translation_domain' => false,
            'attr'               => ['class' => 'form-group-lg'],
        ]);
        $fb->add('monday_from', TimeType::class, [
            'label'    => 'Понедельник',
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('monday_to', TimeType::class, [
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('tuesday_from', TimeType::class, [
            'label'    => 'Вторник',
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('tuesday_to', TimeType::class, [
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('wednesday_from', TimeType::class, [
            'label'    => 'Среда',
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('wednesday_to', TimeType::class, [
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('thursday_from', TimeType::class, [
            'label'    => 'Четверг',
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('thursday_to', TimeType::class, [
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('friday_from', TimeType::class, [
            'label'    => 'Пятница',
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('friday_to', TimeType::class, [
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('saturday_from', TimeType::class, [
            'label'    => 'Суббота',
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('saturday_to', TimeType::class, [
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('sunday_from', TimeType::class, [
            'label'    => 'Воскресенье',
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('sunday_to', TimeType::class, [
            'input'    => 'datetime',
            'widget'   => 'choice',
            'required' => false,
        ]);
        $fb->add('fill_from', DateType::class, [
            'label'       => 'Заполнить с',
            'input'       => 'datetime',
            'widget'      => 'choice',
            'data'        => new \DateTime('today + 1 days'),
            'constraints' => new Assert\GreaterThanOrEqual(new \DateTime('today + 1 days')),
        ]);
        $fb->add('fill_to', DateType::class, [
            'label'       => 'Заполнить по',
            'input'       => 'datetime',
            'widget'      => 'choice',
            'data'        => new \DateTime('today + 1 days'),
            'constraints' => new Assert\GreaterThanOrEqual(new \DateTime('today + 1 days')),
        ]);

        $form = $fb->getForm();
        $form->handleRequest($request);

        if ($request->isMethod('post')) {
            if ($form->isValid()) {
                $from = $form->get('fill_from')->getData(); /** var $from \DateTime */
                $to   = $form->get('fill_to')->getData(); /** var $to \DateTime */

                $filled = false;
                for (; $from <= $to; $from->add(new \DateInterval('P1D'))) {
                    $start = 0;
                    $end   = 0;
                    switch ($from->format('l')) {
                        case 'Monday':
                            if ($form->get('monday_from')->getData() and $form->get('monday_to')->getData()) {
                                $start = $form->get('monday_from')->getData();
                                $end   = $form->get('monday_to')->getData();
                            }
                            break;
                        case 'Tuesday':
                            if ($form->get('tuesday_from')->getData() and $form->get('tuesday_to')->getData()) {
                                $start = $form->get('tuesday_from')->getData();
                                $end   = $form->get('tuesday_to')->getData();
                            }
                            break;
                        case 'Wednesday':
                            if ($form->get('wednesday_from')->getData() and $form->get('wednesday_to')->getData()) {
                                $start = $form->get('wednesday_from')->getData();
                                $end   = $form->get('wednesday_to')->getData();
                            }
                            break;
                        case 'Thursday':
                            if ($form->get('thursday_from')->getData() and $form->get('thursday_to')->getData()) {
                                $start = $form->get('thursday_from')->getData();
                                $end   = $form->get('thursday_to')->getData();
                            }
                            break;
                        case 'Friday':
                            if ($form->get('friday_from')->getData() and $form->get('friday_to')->getData()) {
                                $start = $form->get('friday_from')->getData();
                                $end   = $form->get('friday_to')->getData();
                            }
                            break;
                        case 'Saturday':
                            if ($form->get('saturday_from')->getData() and $form->get('saturday_to')->getData()) {
                                $start = $form->get('saturday_from')->getData();
                                $end   = $form->get('saturday_to')->getData();
                            }
                            break;
                        case 'Sunday':
                            if ($form->get('sunday_from')->getData() and $form->get('sunday_to')->getData()) {
                                $start = $form->get('sunday_from')->getData();
                                $end   = $form->get('sunday_to')->getData();
                            }
                            break;
                    };

                    if ($start < $end) { /** @var $schedule \KreaLab\CommonBundle\Entity\Schedule */
                        $schedule = $this->em->getRepository('CommonBundle:Schedule')->createQueryBuilder('s')
                            ->andWhere('s.filial = :filial')->setParameter('filial', $filial)
                            ->andWhere('s.date = :date')->setParameter('date', $from)
                            ->getQuery()->getOneOrNullResult();

                        if (!$schedule) {
                            $schedule = new Schedule();
                            $schedule->setDate($from);
                            $schedule->setFilial($filial);
                        }

                        $hFrom = $start->format('H'); /** @var $start \DateTime */
                        $mFrom = $start->format('i');

                        $hTo = $end->format('H'); /** @var $end \DateTime */
                        $mTo = $end->format('i');

                        $start->setTime($hFrom, $mFrom);
                        $end->setTime($hTo, $mTo);

                        $start->setDate($from->format('Y'), $from->format('m'), $from->format('d'));
                        $end->setDate($from->format('Y'), $from->format('m'), $from->format('d'));

                        $schedule->setStartTime($start);
                        $schedule->setEndTime($end);

                        $this->em->persist($schedule);
                        $this->em->flush();

                        $filled = true;
                    }
                }

                if ($filled) {
                    $this->addFlash('success', 'Заполнили');
                    return $this->redirectToRoute('admin_filial_schedule', ['id' => $filial->getId()]);
                } else {
                    $this->addFlash('danger', 'Время окончания меньше времени начала');
                }
            } else {
                $this->addFlash('danger', 'Заполните поля');
            }
        }

        return $this->render('AdminBundle:Filial:schedule_fill_week.html.twig', [
            'form'   => $form->createView(),
            'filial' => $filial,
        ]);
    }
}
