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

    public function set($code, $section, $amount) {
        $sql = 'UPDATE `minbid` SET code=:code and section=:section and amount=:amount';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);

        $isSetOk = $stmt->execute();

        return $isSetOk;
    }
}

?>