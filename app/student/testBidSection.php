<?php
require_once '../include/common.php';

$bid_dao = new BidDAO();
$round_dao = new RoundDAO();


// Test Case 1: Inactive Bidding Round
echo"<h2>Test Case 1: Inactive Bidding Round</h2>";
$inactiveRound = $round_dao->retrieveStatus();

echo"<h3>Expected: FAILED</h3>";
if($inactiveRound == 'INACTIVE'){
    echo"<h3>Actual: FAILED</h3>";
}else{
    echo"<h3>Actual: PASSED</h3>";
}

//Test Case 2: Check course and/or section exists 
echo"<h2>Test Case 2: Check course and/or section exists</h2>";
$section_dao = new SectionDAO();

$course = 'IS900';
$section = 'S1';
//$amt = '11';

$sectionObj = $section_dao->retrieve($course, $section);

echo"<h3>Expected: FAILED</h3>";
if ($sectionObj == null) {
    echo"<h3>Actual: FAILED</h3>";
}else{
    echo"<h3>Actual: PASSED</h3>";
}


//Test Case 3: checks if round 1, if yes then students only allowed to bid for courses under their own school
echo"<h2>Test Case 3: checks if round 1, if yes then students only allowed to bid for courses under their own school</h2>";
$course_dao = new CourseDAO();
$student_dao = new StudentDAO();

$round_dao->retrieveRound() == 1;

$course = 'MGMT001';
$courseObj = $course_dao->retrieve($course);

$userid = 'ben.ng.2009';
$studentObj = $student_dao->retrieve($userid);

echo"<h3>Expected: FAILED</h3>";
if ($round_dao->retrieveRound() == 1 && $studentObj->school != $courseObj->school) {
    echo"<h3>Actual: FAILED</h3>";
}else{
    echo"<h3>Actual: PASSED</h3>";
}


//Test Case 4: checks if student has enough e$
echo"<h2>Test Case 4: checks if student has enough e$</h2>";






?>;