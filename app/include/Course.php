<?php

class Course {
    // property declaration
    public $course;
    public $school;
    public $title;    
    public $description;
    public $exam_date;
    public $exam_start;
    public $exam_end;
    

    public function __construct($course, $school, $title, $description, $exam_date, $exam_start, $exam_end) {
        $this->course = $course;
        $this->school = $school;
        $this->title = $title;
        $this->description = $description;
        $this->exam_date = $exam_date;
        $this->exam_start = $exam_start;
        $this->exam_end = $exam_end;
    }

    // checks if the exam timetable for this course clashes with that of another course
    public function examClashWith($course) {
        $current_start = convertDateTime($this->exam_date, $this->exam_start);
        $current_end = convertDateTime($this->exam_date, $this->exam_end);

        $other_start = convertDateTime($course->exam_date, $course->exam_start);
        $other_end = convertDateTime($course->exam_date, $course->exam_end);
        
        // if the exam for this course starts or ends during that of another course
        if (($current_start >= $other_start && $current_start <= $other_end) || ($current_end >= $other_start && $current_end <= $other_end)) {
            return true;
        }

        return false;
    }

    // converts date and time to iso_datetime for ease of comparison
    private function convertDateTime($date, $time) {
        $datetime = $date . ' ' . $time;
        $dateObj = DateTime::createFromFormat("Ymd H:i", $datetime);
        return $dateObj->format(Datetime::ATOM);
    }

}

?>