<?php

class CourseCompletedDAO {
    
    // retrieve a list of course completed by the user
    public function retrieve($userid) {
        $sql = 'select userid, code from course_completed where userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row['code'];
        }

        return $result;
    }

    // retrieves a dictionary with userid as keys and a list of completed courses by user as values
    public function retrieveAll() {
        $sql = 'select * from course_completed';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $userid = $row['userid'];
            $code = $row['code'];
            if (isset($result[$userid])) {
                $result[$userid][] = $code;
            } else {
                $result[$userid] = array();
            }
        }

        return $result;
    }

    // adds a course completed by the user
    public function add($userid, $code) {
        $sql = "INSERT IGNORE INTO course_completed (userid, code) VALUES (:userid, :code)";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);

        $isAddOK = $stmt->execute();

        return $isAddOK;
    }

    // removes a course completed by the user
    public function remove($userid, $code) {
        $sql = "delete from course_completed where userid=:userid and code=:code";     
        
        $connMgr = new ConnectionManager();           
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);

        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }
	
	public function removeAll() {
        $sql = 'TRUNCATE TABLE course_completed';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        
        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }    
	
}


