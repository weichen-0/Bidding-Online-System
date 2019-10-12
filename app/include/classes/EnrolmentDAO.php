<?php

class EnrolmentDAO {
    
    // retrieve a specific enrolment of the user based on course code and section
    public function retrieve($userid, $code, $section) {
        $sql = 'select * from enrolment where userid=:userid and code=:code and section=:section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Enrolment($row['userid'], $row['amount'],$row['code'], $row['section']);
        }

        return null;
    }

    // retrieve a list of enrolment for a specific section
    public function retrieveBySection($code, $section) {
        $sql = 'select * from enrolment where code=:code and section=:section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);

        $stmt->execute();

        $result = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Enrolment($row['userid'], $row['amount'],$row['code'], $row['section']);
        }

        return $result;
    }

    // retrieves a list of enrolment under the user
    public function retrieveByUser($userid) {
        $sql = 'select * from enrolment where userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);

        $stmt->execute();

        $result = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Enrolment($row['userid'], $row['amount'],$row['code'], $row['section']);
        }

        return $result;
    }

    public function retrieveAll() {
        $sql = 'select * from enrolment';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Enrolment($row['userid'], $row['amount'],$row['code'], $row['section']);
        }

        return $result;
    }

    public function add($enrolment) {
        $sql = "INSERT IGNORE INTO student (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)";
  

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $enrolment->userid, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $enrolment->amount, PDO::PARAM_STR);
        $stmt->bindParam(':code', $enrolment->code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $enrolment->section, PDO::PARAM_STR);

        $isAddOK = $stmt->execute();

        return $isAddOK;
    }

    public function remove($enrolment) { 
        $sql = "delete from enrolment where userid=:userid and code=:code and section=:section";   
        
        $connMgr = new ConnectionManager();           
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $enrolment->userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $enrolment->code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $enrolment->section, PDO::PARAM_STR);

        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }
	
	public function removeAll() {
        $sql = 'TRUNCATE TABLE enrolment';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }    
	
}


