<?php

class BidDAO {
    
    // retrieve a list of bids of the user
    public function retrieveByUser($userid) {
        $sql = 'select userid, amount, code, section from bid where userid=:userid';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        $result = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }

        return $result;
    }

    // retrieve a specific bid of the user based on course code and section
    public function retrieve($userid, $code, $section) {
        $sql = 'select userid, amount, code, section from bid where userid=:userid and code=:code and section=:section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }

        return null;
    }

    public function retrieveAll() {
        $sql = 'select * from bid';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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

        $isAddOK = $stmt->execute();

        return $isAddOK;
    }

    public function remove($bid) {
        $sql = "delete from bid where userid=:userid and code=:code and section=:section";     
        
        $connMgr = new ConnectionManager();           
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        

        $stmt->bindParam(':userid', $bid->userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $bid->code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $bid->section, PDO::PARAM_STR);

        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }
	
	public function removeAll() {
        $sql = 'TRUNCATE TABLE student';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }    
	
}


