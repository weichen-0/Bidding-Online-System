<?php

class SectionDAO {
    
    // retrieve 1 section based on section number and course code
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
    
    // remove all sections
	public function removeAll() {
        $sql = 'TRUNCATE TABLE section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }    
	
}


