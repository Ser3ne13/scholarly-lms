<?php
require_once __DIR__ . '/../config/database.php';

class Question {
    private $conn;
    private $table = 'questions';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Get questions for a quiz with their options
    public function getQuizQuestions($quiz_id) {
        $sql = "SELECT * FROM {$this->table} WHERE quiz_id = ? ORDER BY order_number ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = $result->fetch_all(MYSQLI_ASSOC);
        
        // Get options for each question
        foreach ($questions as &$question) {
            $sql = "SELECT * FROM question_options WHERE question_id = ? ORDER BY option_id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $question['question_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $question['options'] = $result->fetch_all(MYSQLI_ASSOC);
        }
        
        return $questions;
    }
    
    // Add question to quiz
    public function addQuestion($quiz_id, $question_text, $type, $points, $order) {
        $sql = "INSERT INTO {$this->table} (quiz_id, question_text, question_type, points, order_number) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issii", $quiz_id, $question_text, $type, $points, $order);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    // Add option to question
    public function addOption($question_id, $option_text, $is_correct) {
        $sql = "INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $question_id, $option_text, $is_correct);
        return $stmt->execute();
    }
    
    // Delete question
    public function deleteQuestion($question_id) {
        $sql = "DELETE FROM {$this->table} WHERE question_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        return $stmt->execute();
    }

    // Get single question with its options
public function getQuestion($question_id) {
    $sql = "SELECT * FROM {$this->table} WHERE question_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();
    
    if ($question) {
        // Get options for this question
        $sql = "SELECT * FROM question_options WHERE question_id = ? ORDER BY option_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $question['options'] = $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return $question;
}

// Update question
public function updateQuestion($question_id, $question_text, $points) {
    $sql = "UPDATE {$this->table} SET question_text = ?, points = ? WHERE question_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("sii", $question_text, $points, $question_id);
    return $stmt->execute();
}

// Update options for multiple choice
public function updateOptions($question_id, $options, $correct_index) {
    // First, delete existing options
    $sql = "DELETE FROM question_options WHERE question_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    
    // Add new options
    foreach ($options as $index => $option_text) {
        if (!empty(trim($option_text))) {
            $is_correct = ($index == $correct_index) ? 1 : 0;
            $this->addOption($question_id, $option_text, $is_correct);
        }
    }
    return true;
}

// Update true/false options
public function updateTrueFalse($question_id, $correct_value) {
    // Delete existing options
    $sql = "DELETE FROM question_options WHERE question_id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    
    // Add True/False options
    $this->addOption($question_id, 'True', ($correct_value === 'true') ? 1 : 0);
    $this->addOption($question_id, 'False', ($correct_value === 'false') ? 1 : 0);
    return true;
}
}
?>