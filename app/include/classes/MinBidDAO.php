<?php 
class MinBidDAO {
    
    // retrieve min bid for course code and section
    public function retrieve($code, $section) {
        $sql = 'SELECT `amount` FROM `minbid` where code=:code and section=:section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['amount'];
        }
        return null;
    }

    public function add($code, $section, $amount) {
        $sql = "INSERT IGNORE INTO `minbid` (code, section, amount) VALUES (:code, :section, :amount)";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);

        $isAddOK = $stmt->execute();

        return $isAddOK;
    }

    public function set($code, $section, $amount) {
        $sql = 'UPDATE `minbid` SET amount=:amount WHERE code=:code and section=:section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);

        $isSetOk = $stmt->execute();

        return $isSetOk;
    }

    // reset the min bid of all sections to a specific amount
    public function resetAll($amount) {
        $sql = 'UPDATE `minbid` SET amount=:amount';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);

        $isResetOk = $stmt->execute();

        return $isResetOk;
    }

    public function removeAll() {
        $sql = 'TRUNCATE TABLE `minbid`';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }    


}

?>