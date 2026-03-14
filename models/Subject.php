<?php
require_once __DIR__ . '/../config/database.php';

class Subject {
    private $conn;
    private $table = 'subjects';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Get all subjects for a teacher
    public function getTeacherSubjects($teacher_id) {
        $sql = "SELECT * FROM {$this->table} WHERE teacher_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get all subjects for a student (enrolled)
    public function getStudentSubjects($student_id) {
        $sql = "SELECT s.* FROM {$this->table} s 
                INNER JOIN enrollments e ON s.subject_id = e.subject_id 
                WHERE e.student_id = ? AND e.status = 'enrolled'
                ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get single subject
    public function getSubject($subject_id) {
        $sql = "SELECT * FROM {$this->table} WHERE subject_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Create new subject
    public function createSubject($data) {
        $sql = "INSERT INTO {$this->table} 
                (subject_code, subject_name, description, teacher_id, schedule, room, academic_year, semester) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssissss", 
            $data['subject_code'],
            $data['subject_name'],
            $data['description'],
            $data['teacher_id'],
            $data['schedule'],
            $data['room'],
            $data['academic_year'],
            $data['semester']
        );
        
        return $stmt->execute();
    }
    
    // Update subject
    public function updateSubject($subject_id, $data) {
        $sql = "UPDATE {$this->table} 
                SET subject_code=?, subject_name=?, description=?, schedule=?, room=?, academic_year=?, semester=?, status=?, teacher_id=?
                WHERE subject_id=?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssi", 
            $data['subject_code'],
            $data['subject_name'],
            $data['description'],
            $data['schedule'],
            $data['room'],
            $data['academic_year'],
            $data['semester'],
            $data['status'],
            $data['teacher_id'],
            $subject_id
        );
        
        return $stmt->execute();
    }

        public function deleteSubject($subject_id) {
            $sql = "DELETE FROM {$this->table} WHERE subject_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $subject_id);
            
            if ($stmt->execute()) {
                return true;
            } else {
                error_log("Delete failed: " . $this->conn->error);
                return false;
            }
        }
    
    // Get enrolled students count
    public function getEnrolledStudentsCount($subject_id) {
        $sql = "SELECT COUNT(*) as count FROM enrollments WHERE subject_id = ? AND status = 'enrolled'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    // Get all subjects (for admin)
public function getAllSubjects() {
    $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
    $result = $this->conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get subjects by teacher
public function getSubjectsByTeacher($teacher_id) {
    $sql = "SELECT * FROM {$this->table} WHERE teacher_id = ? ORDER BY created_at DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Update subject status
public function updateSubjectStatus($subject_id, $status) {
    $sql = "UPDATE {$this->table} SET status = ? WHERE subject_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $status, $subject_id);
    return $stmt->execute();
}

// Get enrolled students count (already have this)
// public function getEnrolledStudentsCount($subject_id) { ... }

}
?>