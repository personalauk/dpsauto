<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!--
student.php - runs a report on a student's DPS results XML file (filename only passed as querystring (eventually))
Alan Lee Staysure 20170722 - derived from rucc main.php
-->

<link rel="stylesheet" type="text/css" href="dpsauto.css" />

<title>Student eLearning Results</title>

<?php

//CONSTANTS
//status value when we have a valid set of XML data
define('DATAVALID', 'completed');
//staysure business rule: pass mark as a percentage (since the total number of questions could vary, but is in the XML data)
define('PASSPERCENTAGE', 75);

//location of captivate results files:
//define('RESULTSSITE', 'http://192.168.17.33/results/CaptivateResults/Staysure/LandD/DPS/');  //live site
define('RESULTSSITE', 'http://www.personala.co.uk/dpsauto/CaptivateResults/Staysure/LandD/DPS/');  //test site

//test results file:!!!
//$resultsfile = "Data Protection at Staysure_t.warner_1449140724838.xml";

$method = $_SERVER["REQUEST_METHOD"]; //was PHP page access method GET or POST?
if ($method == "GET") {
   if (!empty($_GET["link"])) {
      $link = $_GET["link"];
   }
}

//test processing:!!!
//$xml = simplexml_load_file(RESULTSSITE . $resultsfile);



function getTimestamp($link) {
//extract the full timestamp (in milliseconds) from the full link filename
// eg:  "Data Protection at Staysure_t.warner_1449140724838.xml"       returns "1449140724838"
// also "Data%20Protection%20at%20Staysure_t.warner_1449140724838.xml" returns "1449140724838"
    return substr($link, -17, 13);
}



$xml = simplexml_load_file(RESULTSSITE . $link);


$status     = $xml->xpath('/Course/Result/CoreData/Status/@value')[0];
$attempt    = $xml->xpath('/Course/QuizAttempts/@value')[0];

$username   = $xml->xpath('/Course/Variables/stayUser/@value')[0];
$yourscore  = $xml->xpath('/Course/Result/CoreData/RawScore/@value')[0]; //possibly improve - failsafe from actual attempt-answer output later?
$outof      = $xml->xpath('/Course/TotalQuestions/@value')[0];
$module     = $xml->xpath('/Course/LessonName/@value')[0];

$date       = date('d-M-Y H:i:s', substr($timestamp, 0, 10));

$accuracy   = ($yourscore / $outof) * 100;

$results    = $xml->xpath('/Course/Result/InteractionData/Interactions[Attempt/@value="' . $attempt . '"]');

function showResult($result) {
//display results for one question
    $q = questionNumber($result->InteractionID['value']);
    if ($q > 0) {
        printf('<p class="data">%02d: <img src="%s.png" /> %s</p>' . PHP_EOL, 
            $q,
            $result->Result['value'],
            ('W' == $result->Result['value']) ? '(' . $result->StudentResponse['value'] . ')' : ''
        ); 
    }
}

function questionId($question) {
//convert question number to the interaction id
    $p = 'staydpsstaydps';
    switch ($question) :
        case  1: return $p . '23242';
        case  2: return $p . '23103';
        case  3: return $p . '22964';
        case  4: return $p . '23381';
        case  5: return $p . '23520';
        case  6: return $p . '23937';
        case  7: return $p . '23798';
        case  8: return $p . '23659';
        case  9: return $p . '24076';
        case 10: return $p . '24215';
        case 11: return $p . '28302';
        case 12: return $p . '28502';
        case 13: return $p . '28702';
        case 14: return $p . '28902';
        case 15: return $p . '29102';
        case 16: return $p . '29302';
        case 17: return $p . '40148';
        case 18: return $p . '40443';
        case 19: return $p . '40683';
        case 20: return $p . '40923';
        default: return 0;
    endswitch;
}

function questionNumber($interaction) {
//convert interaction id to the question number
    switch (substr($interaction, 14, 5)) :  //remove 'staydpsstaydps' from id
        case '23242': return  1;
        case '23103': return  2;
        case '22964': return  3;
        case '23381': return  4;
        case '23520': return  5;
        case '23937': return  6;
        case '23798': return  7;
        case '23659': return  8;
        case '24076': return  9;
        case '24215': return 10;
        case '28302': return 11;
        case '28502': return 12;
        case '28702': return 13;
        case '28902': return 14;
        case '29102': return 15;
        case '29302': return 16;
        case '40148': return 17;
        case '40443': return 18;
        case '40683': return 19;
        case '40923': return 20;
        default: return 0;
    endswitch;
}
?>

<script language="javascript">
//empty
</script>

</head>

<body>

<p class="atr">Assessment Test Results</p>
<p class="data"><?php echo $module . '   ' . date('d-M-Y H:i:s', substr(getTimestamp($link), 0, 10)) ?></p>

<p class="head">Username: <b><?php echo $username ?></b></p>
<p class="score">Your Score: <?php echo $yourscore ?></p>
<p class="head">Out of: <b><?php echo $outof ?></b></p>
<p class="head">Accuracy: <b><?php echo $accuracy ?>%</b></p>
<?php 
    if ($accuracy >= PASSPERCENTAGE) {
        echo '<p class="pass">Congratulations, you passed the assessment test.</p>';
    } else {
        echo '<p class="fail">Sorry, you failed the assessment test.</p>';
    }
?>

<div class="answers">
<table><tr>
<td width="180" valign="top">
Your Answers

<?php 
    for ($i=0; $i<20; $i++) {
        showResult($results[$i]);
    }
?>
</td>
<td valign="top">
<img id="qimage" width="600" src="dpsstart.png" alt="Module start screen" title="Data Protection at Staysure" />
</td>
</tr></table>
</div> 

</body>
</html>
