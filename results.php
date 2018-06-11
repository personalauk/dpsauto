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
//VERSION
//Release1 20170802:    (initial release)
//Release2 20170812:    RESULTSDIR now relative
//                      DATEFORMAT defined
//                      variables initialised
//                      in getUser() apostrophes are converted back from %27
//                      changed browser target window to StayLanddDpsautoStud


//CONSTANTS
//location of captivate results files relative to results.php & student.php location:
define('RESULTSDIR', './CaptivateResults/Staysure/LandD/DPS/');  //ie. http://192.168.17.33/results/CaptivateResults/Staysure/LandD/DPS/
//default format of assessment completion date/time
define('DATEFORMAT', 'd-M-Y H:i:s');  //eg. "02-Dec-2015 10:31:21"


//INITIALISATION
$dir = null;
$results = array();
$entry = $link = $timestamp = '?';


function validFilename($filename) {
//verifies the filename found in the directory is a valid results data file filename
// eg: "Data Protection at Staysure_t.williamson_1438098990742.xml"
    return (
        //check extension
        ('.xml' == strtolower(substr($filename, -4, 4))) &&
        //check beginning of filename
        ( ('Data Protection at Staysure_' == substr($filename, 0, 28)) || ('Data%20Protection%20at%20Staysure_' == substr($filename, 0, 34)) )
    );
}

function getUser($link) {
//extract the username from the full link filename
// eg: "Data Protection at Staysure_t.warner_1449140724838.xml" returns "t.warner"
//WARNING: ***Assumes that $link has already been verified by validFilename()***
//Note: username may have internal underscores, so this is why $link is not tokenised
    $lt = '?';
    $os = 0;
    //truncate string after the username
    $lt = substr($link, 0, strlen($link)-18);
    //calculate offset based on whether $link is rawurlencoded - refer to validFilename (also see warning above)
    ('Data ' == substr($lt, 0, 5))  ?  $os = 28  :  $os = 34;
    //extract username (apostrophes are converted back from %27)
    return rawurldecode(substr($lt, $os));
}

function getTimestamp($link) {
//extract the full timestamp (in milliseconds) from the full link filename
// eg:  "Data Protection at Staysure_t.warner_1449140724838.xml"       returns "1449140724838"
// also "Data%20Protection%20at%20Staysure_t.warner_1449140724838.xml" returns "1449140724838"
    return substr($link, -17, 13);
}

function displayResult($link, $timestamp) {
//display a row in the results list
    $anchor = '?';
    $anchor = '<a target="StayLanddDpsautoStud" href="student.php?link=' . $link . '">';
    //substr gets timestamp in seconds (instead of milliseconds)
    echo '<tr><td>' . $anchor . '<p class="reslist">' . getUser($link) . '</p></a></td><td>' . $anchor . '<p class="reslist">' . date(DATEFORMAT, substr($timestamp, 0, 10)) . '</p></a></td></tr>';
}
?>

<script language="javascript">
function display(id) {
//local javascript function to display 1 of 2 tables depending on the sort order required by clicking the relevant button
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
arsort($results);  //sort by timestamp (reverse date)
foreach ($results as $link => $timestamp) {
    displayResult($link, $timestamp);
}
?>
</table>

<table id="sortuser" style="display: none;">
<?php
ksort($results);  //sort by link (user)
foreach ($results as $link => $timestamp) {
    displayResult($link, $timestamp);
}
?>
</table>

</body>
</html>
