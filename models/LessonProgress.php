<?php
require_once __DIR__ . '/../config/database.php';

class LessonProgress {
    private $conn;
    private $table = 'lesson_progress';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function markCompleted($student_id, $lesson_id) {
        $sql = "SELECT * FROM {$this->table} WHERE student_id = ? AND lesson_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $lesson_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $sql = "UPDATE {$this->table} SET completed = TRUE, completed_at = NOW() 
                    WHERE student_id = ? AND lesson_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $student_id, $lesson_id);
        } else {
            $sql = "INSERT INTO {$this->table} (student_id, lesson_id, completed, completed_at) 
                    VALUES (?, ?, TRUE, NOW())";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $student_id, $lesson_id);
        }
        
        return $stmt->execute();
    }
    
    public function getStudentProgress($student_id, $subject_id) {
        $sql = "SELECT l.*, 
                       CASE WHEN lp.completed THEN TRUE ELSE FALSE END as completed
                FROM lessons l
                LEFT JOIN {$this->table} lp ON l.lesson_id = lp.lesson_id AND lp.student_id = ?
                WHERE l.subject_id = ?
                ORDER BY l.order_number ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getCompletedCount($student_id, $subject_id) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} lp
                INNER JOIN lessons l ON lp.lesson_id = l.lesson_id
                WHERE lp.student_id = ? AND l.subject_id = ? AND lp.completed = TRUE";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function isCompleted($student_id, $lesson_id) {
    $sql = "SELECT * FROM {$this->table} WHERE student_id = ? AND lesson_id = ? AND completed = TRUE";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $student_id, $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

}
?>