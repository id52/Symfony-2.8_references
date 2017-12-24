<?php

namespace KreaLab\CommonBundle\Command;

use KreaLab\CommonBundle\Entity\ServiceLog;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCsvCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:import-csv');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $rootPath = realpath($this->getContainer()->get('kernel')->getRootDir().'/..');
        $file     = $rootPath.'/papers.csv';
        $fileOut  = $rootPath.'/papers_out.csv';

        if (!is_readable($file)) {
            throw new \Exception('File "'.$file.'" could not be found or read.');
        }

        if (!is_writable($rootPath)) {
            throw new \Exception('Dir "'.$rootPath.'" could not be write.');
        }

        if (file_exists($fileOut)) {
            unlink($fileOut);
        }

        touch($fileOut);

        $cnt       = 0;
        $isFirst   = true;
        $errors    = [];
        $handle    = fopen($file, 'r');
        $handleOut = fopen($fileOut, 'w');
        $data      = fgetcsv($handle, 1000, ';');
        while ($data !== false) {
            if ($isFirst) {
                $isFirst = false;
                fputcsv($handleOut, $data, ';');
                continue;
            }

            $comment = '';

            $num = trim($data[0]);

            $numInJournal  = null;
            $sNumInJournal = trim($data[1]);
            if ($sNumInJournal) {
                $numInJournal = $sNumInJournal;
            }

            $dateGiving  = null;
            $sDateGiving = trim($data[2]);
            $date        = \DateTime::createFromFormat('d.m.Y', $sDateGiving);
            if ($date !== false) {
                $dateGiving = $date;
            }

            if (!$dateGiving) {
                fputcsv($handleOut, $data, ';');
                $output->writeln($num.' date_giving '.$sDateGiving);
                continue;
            }

            $lastName   = null;
            $firstName  = null;
            $patronymic = null;
            $sFullName  = trim($data[3]);
            if ($sFullName) {
                $sFullName  = preg_replace('#\s+#', ' ', $sFullName);
                $sFullNameA = explode(' ', $sFullName);
                if (count($sFullNameA) == 3) {
                    $lastName   = trim($sFullNameA[0]);
                    $firstName  = trim($sFullNameA[1]);
                    $patronymic = trim($sFullNameA[2]);
                }
            }

            if (!$lastName || !$firstName || !$patronymic) {
                fputcsv($handleOut, $data, ';');
                continue;
            }

            $service      = null;
            $sServiceCode = trim($data[4]);
            if ($sServiceCode == '95у') {
                continue;
            }

            if ($sServiceCode) {
                $sServiceCode = mb_strtoupper($sServiceCode);
                if ($sServiceCode == 'Ф046') {
                    $sServiceCode = '046';
                }

                if ($sServiceCode == 'ГНОЗ') {
                    $sServiceCode = 'ГНО';
                }

                if ($sServiceCode == 'ГНОЗД') {
                    $sServiceCode = 'ГНО';
                }

                if ($sServiceCode == 'ГНОД') {
                    $sServiceCode = 'ГНО';
                }

                $service = $em->getRepository('CommonBundle:Service')->findOneBy([
                    'code' => $sServiceCode,
                ]);
            }

            if (!$service) {
                fputcsv($handleOut, $data, ';');
                $output->writeln($num.' service '.$sServiceCode);
                if (!isset($errors['services'])) {
                    $errors['services'] = [];
                }

                if (!isset($errors['services'][$sServiceCode])) {
                    $errors['services'][$sServiceCode] = 0;
                }

                $errors['services'][$sServiceCode] ++;
                continue;
            }

            $numBlank  = null;
            $sNumBlank = trim($data[5]);
            if ($sNumBlank) {
                if ($sServiceCode == '046') {
                    if (substr($sNumBlank, 0, 3) == '45№') {
                        $service = $em->getRepository('CommonBundle:Service')->findOneBy([
                            'code' => 'ГНО',
                        ]);
                        if (!$service) {
                            fputcsv($handleOut, $data, ';');
                            $output->writeln($num.' service ГНО');
                            continue;
                        }

                        $numBlank = $sNumBlank;
                    } else {
                        $numBlank = sprintf('%06s', $sNumBlank);
                    }
                } else {
                    $numBlank = $sNumBlank;
                }
            }

            $birthday  = null;
            $sBirthday = trim($data[6]);
            if ($sBirthday) {
                $date = \DateTime::createFromFormat('d.m.Y', $sBirthday);
                if ($date !== false) {
                    $birthday = $date;
                }

                if (!$birthday) {
                    $sBirthday = trim($data[10]);
                    $sBirthday = str_replace(',', '.', $sBirthday);
                    $date      = \DateTime::createFromFormat('d.m.Y', $sBirthday);
                    if ($date !== false) {
                        $birthday = $date;
                    }

                    if (!$birthday) {
                        $date = \DateTime::createFromFormat('m.d.Y', $sBirthday);
                        if ($date !== false) {
                            $birthday = $date;
                        }

                        if (!$birthday) {
                            $birthday = new \DateTime('1900-01-01');
                            $comment .= ' ДР: '.$sBirthday.';';
                        }
                    }
                }
            }

            if (!$birthday) {
                $birthday = new \DateTime('1900-01-01');
            }

            $sCategory = trim($data[7]);
            if ($sCategory) {
                $comment .= ' Категория: '.$sCategory.';';
            }

            $legalEntity  = null;
            $sLegalEntity = trim($data[8]);
            if ($sLegalEntity) {
                $sLegalEntity = str_replace('"', '', $sLegalEntity);
                $legalEntity  = $em->getRepository('CommonBundle:LegalEntity')->findOneBy([
                    'short_name' => $sLegalEntity,
                ]);
            }

            if (!$legalEntity) {
                fputcsv($handleOut, $data, ';');
                $output->writeln($num.' legalEntity '.$sLegalEntity);
                if (!isset($errors['legal_entities'])) {
                    $errors['legal_entities'] = [];
                }

                if (!isset($errors['legal_entities'][$sLegalEntity])) {
                    $errors['legal_entities'][$sLegalEntity] = 0;
                }

                $errors['legal_entities'][$sLegalEntity] ++;
                continue;
            }

            $filial  = null;
            $sFilial = trim($data[9]);
            if ($sFilial) {
                $filial = $em->getRepository('CommonBundle:Filial')->findOneBy([
                    'name' => $sFilial,
                ]);
            }

            if (!$filial) {
                fputcsv($handleOut, $data, ';');
                $output->writeln($num.' filial '.$sFilial);
                if (!isset($errors['filials'])) {
                    $errors['filials'] = [];
                }

                if (!isset($errors['filials'][$sFilial])) {
                    $errors['filials'][$sFilial] = 0;
                }

                $errors['filials'][$sFilial] ++;
                continue;
            }

            $workplace = $em->getRepository('CommonBundle:Workplace')->findOneBy([
                'legalEntity' => $legalEntity,
                'filial'      => $filial,
            ]);
            if (!$workplace) {
                fputcsv($handleOut, $data, ';');
                $sWorkplace = $legalEntity->getShortName().' '.$filial->getName();
                $output->writeln($num.' workplace '.$sWorkplace);
                if (!isset($errors['workplaces'])) {
                    $errors['workplaces'] = [];
                }

                if (!isset($errors['workplaces'][$sWorkplace])) {
                    $errors['workplaces'][$sWorkplace] = 0;
                }

                $errors['workplaces'][$sWorkplace] ++;
                continue;
            }

            $log = new ServiceLog();
            $log->setNum($num);
            $log->setService($service);
            $log->setWorkplace($workplace);
            $log->setDateGiving($dateGiving);
            $params = [
                'last_name'  => $lastName,
                'first_name' => $firstName,
                'patronymic' => $patronymic,
                'birthday'   => $birthday,
                'comment'    => trim($comment),
                'phone'      => '0000000000',
                'passport'   => '-',
                'address'    => '-',
                'sum'        => 0,
            ];
            if ($numInJournal) {
                $params['num_in_journal'] = $numInJournal;
            }

            if ($numBlank) {
                $params['num_blank'] = $numBlank;
            }

            $log->setParams($params);
            $log->setImport(true);
            $em->persist($log);
            $em->flush();
            $em->clear();

            $cnt ++;
        }

        if ($errors) {
            foreach ($errors as $errorKey => $error) {
                echo $errorKey.': '.$error.'\r\n';
            }
        }

        if ($cnt) {
            $output->writeln('Added <info>'.$cnt.'</info> service logs.');
        }
    }
}
