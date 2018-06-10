<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!--
student.php - runs a report on a student's DPS results XML file (filename only passed as querystring)
Alan Lee Staysure 20170722
-->

<link rel="stylesheet" type="text/css" href="dpsauto.css" />

<title>Student eLearning Results</title>

<?php
//VERSION
//Release1 20170722:    (initial release)
//Release2 20170812:    RESULTSSITE renamed RESULTSDIR and now relative
//                      DATEFORMAT defined
//                      variables initialised and non-evaluation handling added
//                      added alt string to showResult() (scores) output
//                      display scores in two columns
//                      removed module start screen dummy graphic


//CONSTANTS
//location of captivate results files relative to results.php & student.php location:
define('RESULTSDIR', './CaptivateResults/Staysure/LandD/DPS/');  //ie. http://192.168.17.33/results/CaptivateResults/Staysure/LandD/DPS/
//status value when we have a valid set of XML data
define('DATAVALID', 'completed');
//default format of assessment completion date/time
define('DATEFORMAT', 'd-M-Y H:i:s');  //eg. "02-Dec-2015 10:31:21"
//staysure business rule: pass mark as a percentage (since the total number of questions could vary, but is in the XML data)
define('PASSPERCENTAGE', 75);


//INITIALISATION
$method = $link = '?';
$xml = $results = null;
$status = $module = $date = $username = '?';
$attempt = $yourscore = $outof = $accuracy = 0;

$method = $_SERVER['REQUEST_METHOD'];  //was PHP page access method GET or POST?
if ('GET' == $method) {
    if (!empty($_GET['link'])) {
        $link = $_GET['link'];  //xml filename provided as a querystring to this page ie. student.php?link=<xmlfile>
    }
}

function getTimestamp($link) {
//extract the full timestamp (in milliseconds) from the full link filename
// eg:  "Data Protection at Staysure_t.warner_1449140724838.xml"       returns "1449140724838"
// also "Data%20Protection%20at%20Staysure_t.warner_1449140724838.xml" returns "1449140724838"
    return substr($link, -17, 13);
}

$xml = simplexml_load_file(RESULTSDIR . $link);

if ($xml) {
    $status = $xml->xpath('/Course/Result/CoreData/Status/@value')[0];

    //get basic info for display
    $module     = $xml->xpath('/Course/LessonName/@value')[0];
    $date       = date(DATEFORMAT, substr(getTimestamp($link), 0, 10));  //substr gets timestamp in seconds (instead of milliseconds)
    $username   = $xml->xpath('/Course/Variables/stayUser/@value')[0];

    //only get results if valid
    if (DATAVALID == $status) {
        $attempt    = $xml->xpath('/Course/QuizAttempts/@value')[0];

        $yourscore  = $xml->xpath('/Course/Result/CoreData/RawScore/@value')[0];  //TODO: possibly improve - failsafe from actual attempt-answer output later?
        $outof      = $xml->xpath('/Course/TotalQuestions/@value')[0];
        $accuracy   = ($outof > 0)  ?  ($yourscore / $outof) * 100  :  0;

        //get the right set of results based on the final attempt number
        if ($attempt > 0) {
            $results = $xml->xpath('/Course/Result/InteractionData/Interactions[Attempt/@value="' . $attempt . '"]');
        }
    }
}



function showResult($result) {
//display results for one question
    $q = questionNumber($result->InteractionID['value']);
    if ($q > 0) {
        $verdict = '?';
        
        $verdict = $result->Result['value'];
        if ('C' == $verdict) {
            $image = 'C';
            $alt = 'Correct';
            $answers = '';
        } else {
            $image = 'W';
            $alt = 'Wrong';
            $answers = '(' . $result->StudentResponse['value'] . ')';
        }
        printf('<p class="data">%02d: <img src="%s.png" alt="%s answer" /> %s</p>' . PHP_EOL, 
            $q,
            $image,
            $alt,
            $answers
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
</head>

<body>

<p class="atr">Assessment Test Results</p>
<p class="data"><?php echo "$module   $date" ?></p>

<p class="head">Username: <b><?php echo $username ?></b></p>
<?php 
//only display results if valid
if (DATAVALID == $status) {
    echo "<p class=\"score\">Your Score: $yourscore</p>";
    echo "<p class=\"head\">Out of: <b>$outof</b></p>";
    echo "<p class=\"head\">Accuracy: <b>$accuracy%</b></p>";
    if ($accuracy >= PASSPERCENTAGE) {
        echo '<p class="pass">Congratulations, you passed the assessment test.</p>';
    } else {
        echo '<p class="fail">Sorry, you failed the assessment test.</p>';
    }
} else {
    echo "<p class=\"fail\">Results data is invalid ($status)</p>";
}
?>

<div class="answers">
<table>
<?php 
if (DATAVALID == $status) {
    if ($results) {
        echo '<tr><td>Your Answers</td></tr>' . PHP_EOL;
        echo '<tr>' . PHP_EOL;
        echo '<td width="220" valign="top">' . PHP_EOL;
        //display in 2 columns
        for ($i=0,$j=0; $i<($outof/2); $i++) {  //left column
            showResult($results[$i]);
        }
        echo '</td>' . PHP_EOL;
        echo '<td width="220" valign="top">' . PHP_EOL;
        for ($j=$i; $j<$outof; $j++) {  //right column
            showResult($results[$j]);
        }
        echo '</td>' . PHP_EOL;
    } else {
        echo '<tr><td>(Answers not found)</td></tr>' . PHP_EOL;
    }
}
?>
</table>
</div> 

</body>
</html>
