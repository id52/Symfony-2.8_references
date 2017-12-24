<?php

namespace KreaLab\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertServiceLogParamsToFieldsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:convert-service-log-params-to-fields');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $itemsPerPage = 20;

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $count = $em->getRepository('CommonBundle:ServiceLog')->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()->getSingleScalarResult();
        $pages = ceil($count / $itemsPerPage);

        $defaultBday = new \DateTime('1900-01-01 00:00:00');

        for ($page = 0; $page < $pages; $page ++) {
            $serviceLogs = $em->getRepository('CommonBundle:ServiceLog')
                ->findBy([], [], $itemsPerPage, $page * $itemsPerPage);
            foreach ($serviceLogs as &$serviceLog) { /** @var $serviceLog \KreaLab\CommonBundle\Entity\ServiceLog */
                $params = $serviceLog->getParams();
                $serviceLog->setLastName(isset($params['last_name']) ? $params['last_name'] : '');
                $serviceLog->setFirstName(isset($params['first_name']) ? $params['first_name'] : '');
                $serviceLog->setPatronymic(isset($params['patronymic']) ? $params['patronymic'] : '');
                $serviceLog->setBirthday(isset($params['birthday']) ? $params['birthday'] : $defaultBday);
                $serviceLog->setNumBlank(isset($params['num_blank']) ? $params['num_blank'] : '');
                $em->persist($serviceLog);
            }

            $em->flush();
            $em->clear();

            $output->writeln(sprintf('%0.2f%%', 100 * ($page + 1) / $pages));
        }

        $output->writeln('Done');
    }
}
