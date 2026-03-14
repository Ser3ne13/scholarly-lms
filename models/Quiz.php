<?php
require_once __DIR__ . '/../config/database.php';

class Quiz {
    private $conn;
    private $table = 'quizzes';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Get all quizzes for a subject
    public function getSubjectQuizzes($subject_id) {
        $sql = "SELECT * FROM {$this->table} WHERE subject_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get single quiz with questions
    public function getQuiz($quiz_id) {
        $sql = "SELECT * FROM {$this->table} WHERE quiz_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Create quiz
    public function createQuiz($data) {
        $sql = "INSERT INTO {$this->table} (subject_id, title, description, time_limit, passing_score, max_attempts) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issiii", 
            $data['subject_id'],
            $data['title'],
            $data['description'],
            $data['time_limit'],
            $data['passing_score'],
            $data['max_attempts']
        );
        
        return $stmt->execute();
    }
    
    // Update quiz
    public function updateQuiz($quiz_id, $data) {
        $sql = "UPDATE {$this->table} SET title=?, description=?, time_limit=?, passing_score=?, max_attempts=? WHERE quiz_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssiiii", 
            $data['title'],
            $data['description'],
            $data['time_limit'],
            $data['passing_score'],
            $data['max_attempts'],
            $quiz_id
        );
        return $stmt->execute();
    }
    
    // Delete quiz
    public function deleteQuiz($quiz_id) {
        $sql = "DELETE FROM {$this->table} WHERE quiz_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        return $stmt->execute();
    }
}
?>