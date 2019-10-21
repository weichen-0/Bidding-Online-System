<?php

class Section {
    // property declaration
    public $course;
    public $section;
    public $day;    
    public $start;
    public $end;
    public $instructor;
    public $venue;
    public $size;
    
    public function __construct($course, $section, $day, $start, $end, $instructor, $venue, $size) {
        $this->course = $course;
        $this->section = $section;
        $this->day = $day;
        $this->start = $start;
        $this->end = $end;
        $this->instructor = $instructor;
        $this->venue = $venue;
        $this->size = $size;
    }

    // converts date and time to iso_datetime for ease of comparison
    function convertDateTime($date, $time) {
        $datetime = $date . ' ' . $time;
        $dateObj = DateTime::createFromFormat("Ymd H:i", $datetime);
        return $dateObj->format(Datetime::ATOM);
    }

    // checks if the class timetable for this section clashes with that of another section
    public function classClashWith($section) {
        
        // return false immediately if on different days
        if ($this->day != $section->day) {
            return false;
        }

        $dateToday = date('Ymd');

        // Since we only want to compare the time, it doesn't matter as long as we use same date throughout for consistent datetime
        $current_start = $this->convertDateTime($dateToday, $this->start);
        $current_end = $this->convertDateTime($dateToday, $this->end);

        $other_start = $this->convertDateTime($dateToday, $section->start);
        $other_end = $this->convertDateTime($dateToday, $section->end);
        
        // if the class for this section starts or ends during that of another section
        if (($current_start >= $other_start && $current_start < $other_end) || ($current_end > $other_start && $current_end <= $other_end)) {
            return true;
        }

        return false;
    }
}

?>