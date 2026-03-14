<?php
require_once __DIR__ . '/../config/database.php';

class Lesson {
    private $conn;
    private $table = 'lessons';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function getSubjectLessons($subject_id) {
        $sql = "SELECT * FROM {$this->table} WHERE subject_id = ? ORDER BY order_number ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getLesson($lesson_id) {
        $sql = "SELECT * FROM {$this->table} WHERE lesson_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $lesson_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    public function createLesson($data) {
        $sql = "SELECT MAX(order_number) as max_order FROM {$this->table} WHERE subject_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $data['subject_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $order_number = ($row['max_order'] ?? 0) + 1;
        
        $sql = "INSERT INTO {$this->table} (subject_id, title, content, file_path, video_url, order_number) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssi", 
            $data['subject_id'],
            $data['title'],
            $data['content'],
            $data['file_path'],
            $data['video_url'],
            $order_number
        );
    }

        public function deleteLesson($lesson_id) {
    $sql = "DELETE FROM {$this->table} WHERE lesson_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $lesson_id);
    return $stmt->execute();
    }

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
}
?>