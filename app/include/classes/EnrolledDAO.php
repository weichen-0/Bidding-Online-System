<?php

class EnrolledDAO {
    
    // checks if record exists in EnrolledDAO
    public function contains($userid, $course, $section) {
        $sql = 'select * from enrolled where userid=:userid and course=:course and section=:section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        
        $stmt->execute();

        return count($stmt->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }

    // retrieves a dictionary with enrolled course as keys and enrolled section as values for the given user 
    public function retrieveByUser($userid) {
        $sql = 'select course, section from enrolled where userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);

        $stmt->execute();

        $result = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['course']] = $row['section'];
        }

        return $result;
    }

    public function add($userid, $course, $section) {
        $sql = "INSERT IGNORE INTO student (userid, course, section) VALUES (:userid, :course, :section)";
  

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);

        $isAddOK = $stmt->execute();

        return $isAddOK;
    }

    public function remove($userid, $course, $section) { 
        $sql = "delete from enrolled where userid=:userid and course=:course and section=:section";   
        
        $connMgr = new ConnectionManager();           
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);

        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }
	
	public function removeAll() {
        $sql = 'TRUNCATE TABLE enrolled';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }    
	
}


