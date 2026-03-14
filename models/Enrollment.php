<?php
require_once __DIR__ . '/../config/database.php';

class Enrollment {
    private $conn;
    private $table = 'enrollments';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Enroll student in subject
    public function enroll($student_id, $subject_id) {
        // Check if already enrolled
        if ($this->isEnrolled($student_id, $subject_id)) {
            return false;
        }
        
        $sql = "INSERT INTO {$this->table} (student_id, subject_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $subject_id);
        return $stmt->execute();
    }
    
    // Check if student is enrolled
    public function isEnrolled($student_id, $subject_id) {
        $sql = "SELECT * FROM {$this->table} WHERE student_id = ? AND subject_id = ? AND status = 'enrolled'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    // Drop subject
    public function dropSubject($student_id, $subject_id) {
        $sql = "UPDATE {$this->table} SET status = 'dropped' WHERE student_id = ? AND subject_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $subject_id);
        return $stmt->execute();
    }
    
    // Get enrolled students for a subject
    public function getEnrolledStudents($subject_id) {
        $sql = "SELECT u.* FROM users u 
                INNER JOIN {$this->table} e ON u.user_id = e.student_id 
                WHERE e.subject_id = ? AND e.status = 'enrolled'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get enrolled subjects for a student
    public function getEnrolledSubjects($student_id) {
        $sql = "SELECT s.* FROM subjects s 
                INNER JOIN {$this->table} e ON s.subject_id = e.subject_id 
                WHERE e.student_id = ? AND e.status = 'enrolled'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // ADD THIS NEW METHOD
    public function getEnrolledStudentsCount($subject_id) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE subject_id = ? AND status = 'enrolled'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}
?>