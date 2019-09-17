<?php

class BidDAO {
    
    // retrieve a list of bids based on user ID
    public function retrieve($userid) {
        $sql = 'select userid, amount, code, section from bid where userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }

        return $result;
    }

    public function retrieveAll() {
        $sql = 'select * from bid';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }

        return $result;
    }

    public function add($bid) {
        $sql = "INSERT IGNORE INTO bid (userid, amount, code, section) VALUES (:userid, :amount, :code, :section)";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':userid', $bid->userid, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $bid->amount, PDO::PARAM_INT);
        $stmt->bindParam(':code', $bid->code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $bid->section, PDO::PARAM_STR);

        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }

    public function remove($userid, $course, $section) {
        $sql = "delete from bid where userid=:userid and course=:course and section=:section";     
        
        $connMgr = new ConnectionManager();           
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);

        $isRemoveOk = False;
        if ($stmt->execute()) {
            $isRemoveOk = True;
        }

        return $isRemoveOk;
    }
	
	public function removeAll() {
        $sql = 'TRUNCATE TABLE student';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }    
	
}


