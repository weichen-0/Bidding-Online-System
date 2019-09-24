<?php
require_once '../include/bootstrap.php';
require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
// can assume that bootstrap-file is present/can be unzipped
$errors = [ isMissingOrEmpty ('token') ];
$errors = array_filter($errors);

if (!isEmpty($errors)) {
    $result = [
        "status" => "error",
        "messages" => array_values($errors)
        ];

} else {

    $bid_dao = new BidDAO();
    $course_dao = new CourseDAO();
    $enrolment_dao = new EnrolmentDAO();
    $student_dao = new StudentDAO();
    $section_dao = new SectionDAO();
    $prereq_dao = new PrereqDAO();
    $course_completed_dao = new CourseCompletedDAO();
    $sort_class = new Sort();

    $course_result = array();
    $courses = $course_dao->retrieveAll();
    foreach ($courses as $course) {
        $course_result[] = ["course" => $course->course, 
                            "school" => $course->school, 
                            "title" => $course->title,
                            "description" => $course->description, 
                            "exam date" => $course->exam_date, 
                            "exam start" => $course->exam_start, 
                            "exam end" => $course->exam_end];
    }
    $course_result = $sort_class->sort_it($course_result, "course");

    $section_result = array();
    $sections = $section_dao->retrieveAll();
    foreach ($sections as $section) {
        $section_result[] = ["course" => $section->course, 
                             "section" => $section->section, 
                             "day" => $section->day, 
                             "start" => $section->start, 
                             "end" => $section->end, 
                             "instructor" => $section->instructor, 
                             "venue" => $section->venue, 
                             "size" => $section->size];
    }
    $section_result = $sort_class->sort_it($section_result, "section");

    $student_result = array();
    $students = $student_dao->retrieveAll();
    foreach ($students as $student) {
        $student_result[] = ["userid" => $student->userid,
                             "password" => $student->password, 
                             "name" => $student->name, 
                             "school" => $student->school, 
                             "edollar" => $student->edollar];
    }
    $student_result = $sort_class->sort_it($student_result, "student");

    $prereq_result = array();
    $prereqs = $prereq_dao->retrieveAll();
    foreach ($prereqs as $course => $prereq_list) {
        foreach ($prereq_list as $ele) {
            $prereq_result[] = ["course" => $course, 
                                "prerequisite" => $ele];
        }
    }
    $prereq_result = $sort_class->sort_it($prereq_result, "prereq");

    $bid_result = array();
    $bids = $bid_dao->retrieveAll();
    foreach ($bids as $bid) {
        $bid_result[] = ["userid" => $bid->userid,
                         "amount" => $bid->amount, 
                         "course" => $bid->code, 
                         "section" => $bid->section];
    }
    $bid_result = $sort_class->sort_it($bid_result, "bid");

    $course_completed_result = array();
    $courses_completed = $course_completed_dao->retrieveAll();
    foreach ($courses_completed as $userid => $course_list) {
        foreach ($course_list as $ele) {
            $course_completed_result[] = ["userid" => $userid, 
                                          "course" => $ele];
        }
    }
    $course_completed_result = $sort_class->sort_it($course_completed_result, "course_completed");

    $enrolment_result = array();
    $enrolments = $enrolment_dao->retrieveAll();
    foreach ($enrolments as $enrolment) {
        $enrolment_result[] = ["userid" => $enrolment->userid, 
                               "course" => $enrolment->code, 
                               "section" => $enrolment->section, 
                               "amount" => $enrolment->amount];
    }
    $enrolment_result = $sort_class->sort_it($enrolment_result, "enrolment");

    $result = ["status" => "success", 
               "course" => $course_result,
               "section" => $section_result,
               "student" => $student_result,
               "prerequisite" => $prereq_result,
               "bid" => $bid_result,
               "completed-course" => $course_completed_result,
               "section-student" => $enrolment_result];
}


header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
exit;

?>