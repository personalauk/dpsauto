<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!--
results.php - displays a list of all DPS results XML files 
Alan Lee Staysure 20170802
-->

<link rel="stylesheet" type="text/css" href="dpsauto.css" />

<title>eLearning Results</title>

<?php

//CONSTANTS
//status value when we have a valid set of XML data
//define('DATAVALID', 'completed');
//staysure business rule: pass mark as a percentage (since the total number of questions could vary, but is in the XML data)
//define('PASSPERCENTAGE', 75);

//location of captivate results files:
//define('RESULTSSITE', 'http://192.168.17.33/results/CaptivateResults/Staysure/LandD/DPS/');  //live site
//define('RESULTSSITE', 'http://www.personala.co.uk/dpsauto/CaptivateResults/Staysure/LandD/DPS/');  //test site
define('RESULTSDIR', './CaptivateResults/Staysure/LandD/DPS/');  //test site

/*
//test results file:!!!
$resultsfile = "Data Protection at Staysure_t.warner_1449140724838.xml";



//test processing:!!!
$xml = simplexml_load_file(RESULTSSITE . $resultsfile);


$status     = $xml->xpath('/Course/Result/CoreData/Status/@value')[0];
$attempt    = $xml->xpath('/Course/QuizAttempts/@value')[0];

$username   = $xml->xpath('/Course/Variables/stayUser/@value')[0];
$yourscore  = $xml->xpath('/Course/Result/CoreData/RawScore/@value')[0]; //possibly improve - failsafe from actual attempt-answer output later?
$outof      = $xml->xpath('/Course/TotalQuestions/@value')[0];
$module     = $xml->xpath('/Course/LessonName/@value')[0];
$date       = $xml->xpath('/Course/Variables/stayStarted/@value')[0];

$accuracy   = ($yourscore / $outof) * 100;

$results    = $xml->xpath('/Course/Result/InteractionData/Interactions[Attempt/@value="' . $attempt . '"]');
*/


function validFilename($filename) {
//verifies the filename found in the directory is a valid results data file filename
    return (
        //check extension
        ('.xml' == strtolower(substr($filename, -4, 4))) &&
        //check beginning of filename
        ( ('Data Protection at Staysure_' == substr($filename, 0, 28)) || ('Data%20Protection%20at%20Staysure_' == substr($filename, 0, 34)) )
    );
}

function getUser($link) {
//extract the username from the full link filename
// eg:  "Data Protection at Staysure_t.warner_1449140724838.xml" returns "t.warner"
//WARNING: ***Assumes that $link has already been verified by validFilename***
//Note: username may have internal underscores, so this is why $link is not tokenised
    //truncate string after the username
    $lt = substr($link, 0, strlen($link)-18);
    //calculate offset based on whether $link is rawurlencoded - refer to validFilename (also see warning above)
    ('Data ' == substr($lt, 0, 5)) ? $os = 28 : $os = 34;
    //extract username
    return substr($lt, $os);
}

function getTimestamp($link) {
//extract the full timestamp (in milliseconds) from the full link filename
// eg:  "Data Protection at Staysure_t.warner_1449140724838.xml"       returns "1449140724838"
// also "Data%20Protection%20at%20Staysure_t.warner_1449140724838.xml" returns "1449140724838"
    return substr($link, -17, 13);
}

function displayResult($link, $timestamp) {
//    echo $link . '   ' . getUser($link) . '   ' . date('d-M-Y H:i:s', substr($timestamp, 0, 10)) . PHP_EOL;
//    echo '<tr><td><p class="reslist">' . getUser($link) . '</p></td><td><p class="reslist">' . date('d-M-Y H:i:s', substr($timestamp, 0, 10)) . '</p></td></tr>';
    $anchor = '<a target="staylanddres" href="student.php?link=' . $link . '">';
    echo '<tr><td>' . $anchor . '<p class="reslist">' . getUser($link) . '</p></a></td><td>' . $anchor . '<p class="reslist">' . date('d-M-Y H:i:s', substr($timestamp, 0, 10)) . '</p></a></td></tr>';
}

?>

<script language="javascript">
function display(id) {
    if ('sortdate' == id) {
        //show table sorted by reverse date
        document.getElementById('btnsortdate').style.backgroundColor='yellow';  //button on
        document.getElementById('btnsortuser').style.backgroundColor='silver';  //button off
        document.getElementById('btnsortdate').style.color='red';               //button on
        document.getElementById('btnsortuser').style.color='black';             //button off
        document.getElementById('sortuser').style.display='none';               //table hidden
        document.getElementById('sortdate').style.display='table';              //table visible
    } else {
        //show table sorted by username
        document.getElementById('btnsortuser').style.backgroundColor='yellow';
        document.getElementById('btnsortdate').style.backgroundColor='silver';
        document.getElementById('btnsortuser').style.color='red';
        document.getElementById('btnsortdate').style.color='black';
        document.getElementById('sortdate').style.display='none';
        document.getElementById('sortuser').style.display='table';
    }
}
</script>

</head>



<body>

<p class="atr">Assessment Test Results Data Files</p>

<?php 
$results = array();
$dir = dir(RESULTSDIR);
while ( false !== ($entry = $dir->read()) ) {
    if (validFilename($entry)) {
        $results[rawurlencode($entry)] = getTimestamp($entry);
    }
}
$dir->close();

echo '<p class="data"> ' . count($results) . ' Results</p>'.PHP_EOL;
?>

<form>
<table>
  <tr>
    <td><p class="buttons"><input type="button" id="btnsortuser" onclick="display('sortuser');" value="Sort by Username" style="background-color: silver; color: black;" /></p></td>
    <td><p class="buttons"><input type="button" id="btnsortdate" onclick="display('sortdate');" value="Sort by Date" style="background-color: yellow; color: red;" /></p></td>
  </tr>
</table>
</form>

<table id="sortdate" style="display: table;">
<?php
arsort($results);  //by timestamp (date)
foreach ($results as $link => $timestamp) {
    displayResult($link, $timestamp);
}
?>
</table>

<table id="sortuser" style="display: none;">
<?php
ksort($results);  //by link (user)
foreach ($results as $link => $timestamp) {
    displayResult($link, $timestamp);
}
?>
</table>

</body>
</html>
