<?php
require_once __DIR__ . '/../config/database.php';

class QuizAttempt {
    private $conn;
    private $table = 'quiz_attempts';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Start new attempt
    public function startAttempt($quiz_id, $student_id) {
        // Check if can attempt
        $sql = "SELECT COUNT(*) as attempts FROM {$this->table} WHERE quiz_id = ? AND student_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $quiz_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // Get quiz max attempts
        $sql = "SELECT max_attempts FROM quizzes WHERE quiz_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $quiz = $result->fetch_assoc();
        
        if ($row['attempts'] >= $quiz['max_attempts']) {
            return false; // Max attempts reached
        }
        
        // Create new attempt
        $sql = "INSERT INTO {$this->table} (quiz_id, student_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $quiz_id, $student_id);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    // Get attempt details
    public function getAttempt($attempt_id) {
        $sql = "SELECT * FROM {$this->table} WHERE attempt_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $attempt_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Save answer (ONE VERSION ONLY)
    public function saveAnswer($attempt_id, $question_id, $selected_option_id, $is_correct) {
        $sql = "INSERT INTO student_answers (attempt_id, question_id, selected_option_id, is_correct) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $attempt_id, $question_id, $selected_option_id, $is_correct);
        return $stmt->execute();
    }
    
    // Complete attempt with score parameters (ONE VERSION ONLY)
    public function completeAttempt($attempt_id, $score, $total_points) {
        $sql = "UPDATE {$this->table} 
                SET score = ?, total_points = ?, completed_at = NOW(), status = 'completed' 
                WHERE attempt_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $score, $total_points, $attempt_id);
        return $stmt->execute();
    }
    
    // Get student's attempts for a quiz
    public function getStudentAttempts($quiz_id, $student_id) {
        $sql = "SELECT * FROM {$this->table} WHERE quiz_id = ? AND student_id = ? ORDER BY started_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $quiz_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>