<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../models/QuizAttempt.php';
require_once __DIR__ . '/../models/Question.php';

$action = $_GET['action'] ?? '';

if ($action === 'submit') {
    handleQuizSubmit();
}

function handleQuizSubmit() {
    if ($_SESSION['role'] !== 'student') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $attempt_id = $_POST['attempt_id'];
    $quiz_id = $_POST['quiz_id'];
    
    $attemptModel = new QuizAttempt();
    $questionModel = new Question();
    
    // Get all questions for this quiz
    $questions = $questionModel->getQuizQuestions($quiz_id);
    
    $total_score = 0;
    $total_points = 0;
    
    // Grade each question
    foreach ($questions as $question) {
        $answer_key = 'question_' . $question['question_id'];
        $selected_option_id = $_POST[$answer_key] ?? null;
        $total_points += $question['points'];
        
        if ($selected_option_id) {
            // Find if the selected option is correct
            $is_correct = false;
            foreach ($question['options'] as $option) {
                if ($option['option_id'] == $selected_option_id && $option['is_correct']) {
                    $is_correct = true;
                    break;
                }
            }
            
            // Save the answer
            $attemptModel->saveAnswer($attempt_id, $question['question_id'], $selected_option_id, $is_correct);
            
            if ($is_correct) {
                $total_score += $question['points'];
            }
        }
    }
    
    // Complete the attempt with final score
    $attemptModel->completeAttempt($attempt_id, $total_score, $total_points);
    
    // Redirect to results page
    header("Location: /mywebsite10/views/student/quiz-results.php?attempt_id=$attempt_id");
    exit();
}
?>