<?php
session_start();
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/Question.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'create':
        handleCreateQuiz();
        break;
    case 'add_question':
        handleAddQuestion();
        break;
    case 'edit':
        handleEditQuiz();
        break;
    case 'update':
        handleUpdateQuiz();
        break;
    case 'delete':
        handleDeleteQuiz();
        break;
    case 'get_question':
    getQuestion();
        break;
    case 'update_question':
        handleUpdateQuestion();
        break;
    case 'delete_question':
        handleDeleteQuestion();
        break;
    default:
        header('Location: /mywebsite10/views/teacher/subjects.php');
        break;
}

function handleCreateQuiz() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /mywebsite10/views/teacher/subjects.php');
        exit();
    }
    
    $quiz = new Quiz();
    $data = [
        'subject_id' => $_POST['subject_id'],
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'time_limit' => $_POST['time_limit'],
        'passing_score' => $_POST['passing_score'],
        'max_attempts' => $_POST['max_attempts']
    ];
    
    $quiz_id = $quiz->createQuiz($data);
    
    if ($quiz_id) {
        $_SESSION['success'] = 'Quiz created successfully! Now add some questions.';
        header("Location: /mywebsite10/views/teacher/edit-quiz.php?id=$quiz_id");
    } else {
        $_SESSION['error'] = 'Failed to create quiz.';
        header("Location: /mywebsite10/views/teacher/view-subject.php?id=" . $_POST['subject_id']);
    }
    exit();
}

function handleAddQuestion() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $quiz_id = $_POST['quiz_id'];
    $question_text = $_POST['question_text'];
    $question_type = $_POST['question_type'];
    $points = $_POST['points'] ?? 1;
    
    // Debug: Check what's being posted
    error_log("Adding question - Type: $question_type");
    error_log("POST data: " . print_r($_POST, true));
    
    $question = new Question();
    
    // Get current max order
    $questions = $question->getQuizQuestions($quiz_id);
    $order = count($questions) + 1;
    
    $question_id = $question->addQuestion($quiz_id, $question_text, $question_type, $points, $order);
    
    if ($question_id) {
        // Add options for multiple choice
        if ($question_type === 'multiple_choice') {
            $options = $_POST['options'] ?? [];
            $correct = $_POST['correct_option'] ?? 0;
            
            foreach ($options as $index => $option_text) {
                if (!empty(trim($option_text))) {
                    $is_correct = ($index == $correct) ? 1 : 0;
                    $question->addOption($question_id, $option_text, $is_correct);
                }
            }
            $_SESSION['success'] = 'Multiple choice question added!';
        }
        
        // Add options for true/false
        if ($question_type === 'true_false') {
            $correct = $_POST['tf_correct'] ?? 'true';
            $question->addOption($question_id, 'True', ($correct === 'true') ? 1 : 0);
            $question->addOption($question_id, 'False', ($correct === 'false') ? 1 : 0);
            $_SESSION['success'] = 'True/False question added!';
        }
    } else {
        $_SESSION['error'] = 'Failed to add question.';
    }
    
    header("Location: /mywebsite10/views/teacher/edit-quiz.php?id=$quiz_id");
    exit();
}


function getQuestion() {
    require_once __DIR__ . '/../models/Question.php';
    $question_id = $_GET['id'] ?? 0;
    $question = new Question();
    $data = $question->getQuestion($question_id);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function handleUpdateQuestion() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    require_once __DIR__ . '/../models/Question.php';
    
    $question_id = $_POST['question_id'];
    $quiz_id = $_POST['quiz_id'];
    $question_text = $_POST['question_text'];
    $points = $_POST['points'];
    $question_type = $_POST['question_type'];
    
    $question = new Question();
    
    // Update question text and points
    $question->updateQuestion($question_id, $question_text, $points);
    
    // Update options based on type
    if ($question_type === 'multiple_choice') {
        $options = $_POST['edit_options'] ?? [];
        $correct = $_POST['edit_correct_option'] ?? 0;
        $question->updateOptions($question_id, $options, $correct);
    } else {
        $correct = $_POST['edit_tf_correct'] ?? 'true';
        $question->updateTrueFalse($question_id, $correct);
    }
    
    $_SESSION['success'] = 'Question updated successfully!';
    header("Location: /mywebsite10/views/teacher/edit-quiz.php?id=$quiz_id");
    exit();
}

function handleDeleteQuestion() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    require_once __DIR__ . '/../models/Question.php';
    
    $question_id = $_GET['id'] ?? 0;
    $quiz_id = $_GET['quiz_id'] ?? 0;
    
    $question = new Question();
    $question->deleteQuestion($question_id);
    
    $_SESSION['success'] = 'Question deleted successfully!';
    header("Location: /mywebsite10/views/teacher/edit-quiz.php?id=$quiz_id");
    exit();
}
?>