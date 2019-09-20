<?php

class CourseDAO {
    
    // retrieve a list of courses under the school
    public function retrieveBySchool($school) {
        $sql = "select course, school, title, description, `exam date`, `exam start`, `exam end` from course where school=:school";
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Course($row['course'], $row['school'], $row['title'], $row['description'], $row['exam date'], $row['exam start'], $row['exam end']);
        }

        return $result;
    }

    // retrieve a course by its code
    public function retrieve($course) {
        $sql = "select course, school, title, description, `exam date`, `exam start`, `exam end` from course where course=:course";
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return new Course($row['course'], $row['school'], $row['title'], $row['description'], $row['exam date'], $row['exam start'], $row['exam end']);
        }

        return null;
    }

    public function retrieveAll() {
        $sql = 'select * from course';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Course($row['course'], $row['school'], $row['title'], $row['description'], $row['exam date'], $row['exam start'], $row['exam end']);
        }

        return $result;
    }

    public function add($course) {
        $sql = "INSERT IGNORE INTO course (course, school, title, description, `exam date`, `exam start`, `exam end`) VALUES (:course, :school, :title, :description, :exam date, :exam start, :exam end)";

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':course', $course->course, PDO::PARAM_STR);
        $stmt->bindParam(':school', $course->school, PDO::PARAM_STR);
        $stmt->bindParam(':title', $course->title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $course->description, PDO::PARAM_STR);
        $stmt->bindParam(':exam date', $course->exam_date, PDO::PARAM_INT);
        $stmt->bindParam(':exam start', $course->exam_start, PDO::PARAM_STR);
        $stmt->bindParam(':exam end', $course->exam_end, PDO::PARAM_STR);

        $isAddOK = $stmt->execute();

        return $isAddOK;
    }

    public function remove($course) {
        $sql = "delete from course where course=:course";     
        
        $connMgr = new ConnectionManager();           
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        

        $stmt->bindParam(':course', $course, PDO::PARAM_STR);

        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }
	
	public function removeAll() {
        $sql = 'TRUNCATE TABLE course';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $isRemoveOk = $stmt->execute();

        return $isRemoveOk;
    }    
	
}

?>
