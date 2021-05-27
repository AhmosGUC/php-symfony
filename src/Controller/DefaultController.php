<?php
// src/AppBundle/Controller/DefaultController.php
namespace App\Controller;

use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FileType;
/**
 * Class DefaultController
 *
 * @package App\Controller
 */

class DefaultController extends AbstractController
{
    // takes the name of the day and date of the day
    // check if it is a weekend and calculate new deadline
    // check if it is a holiday and calcluate new deadline
    public function avoidDayOff($deadline): string{
        $year = date('Y', strtotime($deadline));
        // second friday in december
        $secDecFri = date('Y-m-d',strtotime('second fri of December'. $year ));
        $holiday=[$year .'-'.'12-31',$year .'-'.'06-02',$year .'-'.'11-24',$secDecFri];
        $num = count($holiday);
        for ($c=0; $c < $num; $c++) { 
            if($holiday[$c] == $deadline){
                $deadline = date('Y-m-d', strtotime($deadline. '- 1 days'));
            }
        } 
        $deadDay = date('D', strtotime($deadline));
        if($deadDay == "Sat"){
            $deadline = date('Y-m-d', strtotime($deadline. '- 1 days'));
        }else{
            if($deadDay == "Sun"){
                $deadline = date('Y-m-d', strtotime($deadline.'- 2 days'));
            }
        }
        return $deadline;
    }
    // takes the name of the state , project start and commencement dates,
    // order type , and accorddingly calculates the deadline
    public function calcDeadline($state,$startDate,$commencementDate,$ordType): string{
        $noticeTX = $state == 'TX' && $ordType == 'Notice';
        if($noticeTX){
            $commencementDate = strtotime($commencementDate);
            $first_date = strtotime('first day of next month', $commencementDate);
            $fdonm = date('Y-m-d',$first_date);
            $deadline = date('Y-m-d',strtotime($fdonm .'+ 14 days'));
            $deadline = $this->avoidDayOff($deadline);
            return $deadline;
        }else{
            $duration = $ordType == 'Notice' ? 60 : 90;
            $baseDate = $ordType == 'Notice' ? $commencementDate : $startDate;        
            $deadline = date('Y-m-d', strtotime($baseDate. '+'.$duration.' days'));
            $deadline = $this->avoidDayOff($deadline);
            return $deadline;
        }
    }
    // takes the csv line content as data and row number
    // checks for correctness (number of project required attributes)
    // number of feilds in line
    // then echo the report for the project
    public function parseLine($data,$row): void
    {
        $row = $row -1;
        $num = count($data);
        $flag = TRUE;
        if($num == 9){
            for ($c=0; $c < 7; $c++) {
                if($data[$c] == ''){
                    $flag = FALSE;
                    break;
                }
            }
        if($flag){
            $custName = $data[0];
            $projName = $data[1];
            $state = $data[4];
            $startDate = $data[6];
            $comDate = $data[8];
                if($comDate == ''){
                    $comDate = date("Y-m-d");
                }
                $d1 = $this->calcDeadline($state,$startDate,$comDate,'Notice');
                $d2 = $this->calcDeadline($state,$startDate,$comDate,'Lien');
                echo $row.') '.$custName.' - '.$projName.' - Notice Deadline ['.$d1.'] - Lien Deadline ['.$d2.']';
            }else{
                echo $row.') ERROR : Missing Attribute';
            }
        }else{
            echo $row.') ERROR : Corrupted number of fileds less than excpected (9)';
        }
    }
    public function newAction(Request $request)
    {
        // just setup a fresh $task object (remove the dummy data)
        $task = new Task();
    
        $form = $this->createFormBuilder($task)
            ->add('project',FileType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Task'])
            ->getForm();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();
            $file = $task->getProject();
            $row = 1;
            if (($handle = fopen($file->getPathname(), "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if($row>1){
                        $this->parseLine($data,$row);
                    }
                    $row++;
                    echo '<br>';
                }
                echo '<br>';
                fclose($handle);
            }
        }
    
        return $this->render('new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
