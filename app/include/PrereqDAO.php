<?php

class PrereqDAO {
    
    // retrieve a list of prerequisite courses for a course
    public function retrieve($course) {
        $sql = 'select course, prerequisite from prerequisite where course=:course';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row['prerequisite'];
        }

        return $result;
    }

    // retrieve a dictionary with course as keys and a list of prerequisite courses as values
    public function retrieveAll() {
        $sql = 'select * from prerequisite';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $course = $row['course'];
            $prerequisite = $row['prerequisite'];
            if (isset($result[$course])) {
                $result[$course][] = $prerequisite;
            } else {
                $result[$course] = array();
            }
        }

        return $result;
    }

    public function add($course, $prerequisite) {
        $sql = "INSERT IGNORE INTO prerequisite (course, prerequisite) VALUES (:course, :prerequisite)";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':prerequisite', $prerequisite, PDO::PARAM_STR);

        $isAddOK = $stmt->execute();

        return $isAddOK;
    }

    public function remove($course, $prerequisite) {
        $sql = "delete from prerequisite where course=:course and prerequisite=:prerequisite";     
        
        $connMgr = new ConnectionManager();           
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':prerequisite', $prerequisite, PDO::PARAM_STR);

        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }
	
	public function removeAll() {
        $sql = 'TRUNCATE TABLE prerequisite';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }    
	
}


