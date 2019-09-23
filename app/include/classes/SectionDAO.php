<?php

class SectionDAO {
    
    // retrieve 1 section based on course code and section
    public function retrieve($course, $section) {
        $sql = 'select course, section, day, start, end, instructor, venue, size from section where course=:course and section=:section';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
             
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);

        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Section($row['course'], $row['section'],$row['day'], $row['start'], $row['end'], $row['instructor'], $row['venue'], $row['size']);
        }

        return null;
    }

    // retrieve a list of sections based on course code
    public function retrieveByCourse($course) {
        $sql = 'select course, section, day, start, end, instructor, venue, size from section where course=:course';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Section($row['course'], $row['section'],$row['day'], $row['start'], $row['end'], $row['instructor'], $row['venue'], $row['size']);
        }

        return $result;
    }

    // retrieve all sections
    public  function retrieveAll() {
        $sql = 'select * from section';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Section($row['userid'], $row['password'],$row['name'], $row['school'], $row['edollar']);
        }

        return $result;
    }    

    public function add($section) {
        $sql = "INSERT IGNORE INTO section (course, section, day, start, end, instructor, venue, size) VALUES (:course, :section, :day, :start, :end, :instructor, :venue, :size)";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':course', $section->course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section->section, PDO::PARAM_STR);
        $stmt->bindParam(':day', $section->day, PDO::PARAM_INT);
        $stmt->bindParam(':start', $section->start, PDO::PARAM_STR);
        $stmt->bindParam(':end', $section->end, PDO::PARAM_STR);
        $stmt->bindParam(':instructor', $section->instructor, PDO::PARAM_STR);
        $stmt->bindParam(':venue', $section->venue, PDO::PARAM_STR);
        $stmt->bindParam(':size', $section->size, PDO::PARAM_INT);

        $isAddOK = $stmt->execute();

        return $isAddOK;
    }
    
    // remove all sections
	public function removeAll() {
        $sql = 'TRUNCATE TABLE section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }    
	
}


