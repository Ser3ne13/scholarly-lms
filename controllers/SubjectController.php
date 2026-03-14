    <?php
session_start();
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Enrollment.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/LessonProgress.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'create':
        handleCreateSubject();
        break;
    case 'view':
        handleViewSubject();
        break;
    case 'update':
        handleUpdateSubject();
        break;
    case 'delete':
        handleDeleteSubject();
        break;
    case 'enroll':
        handleEnroll();
        break;
    case 'unenroll':
        handleUnenroll();
        break;
    default:
        header('Location: /mywebsite10/views/teacher/subjects.php');
        break;
}

function handleCreateSubject() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /mywebsite10/views/teacher/create-subject.php');
        exit();
    }
    
    $subject = new Subject();
    $data = [
        'subject_code' => $_POST['subject_code'],
        'subject_name' => $_POST['subject_name'],
        'description' => $_POST['description'],
        'teacher_id' => $_SESSION['user_id'],
        'schedule' => $_POST['schedule'],
        'room' => $_POST['room'],
        'academic_year' => $_POST['academic_year'],
        'semester' => $_POST['semester']
    ];
    
    if ($subject->createSubject($data)) {
        $_SESSION['success'] = 'Subject created successfully!';
    } else {
        $_SESSION['error'] = 'Failed to create subject.';
    }
    
    header('Location: /mywebsite10/views/teacher/subjects.php');
    exit();
}

function handleViewSubject() {
    $subject_id = $_GET['id'] ?? 0;
    $subject = new Subject();
    $lesson = new Lesson();
    
    $subject_data = $subject->getSubject($subject_id);
    $lessons = $lesson->getSubjectLessons($subject_id);
    
    if ($_SESSION['role'] === 'student') {
        $progress = new LessonProgress();
        $lessons = $progress->getStudentProgress($_SESSION['user_id'], $subject_id);
    }
    
    // Store in session for view
    $_SESSION['view_subject'] = $subject_data;
    $_SESSION['view_lessons'] = $lessons;
    
    if ($_SESSION['role'] === 'teacher') {
        $enrollment = new Enrollment();
        $students = $enrollment->getEnrolledStudents($subject_id);
        $_SESSION['enrolled_students'] = $students;
        header("Location: /mywebsite10/views/teacher/view-subject.php?id=$subject_id");
    } else {
        header("Location: /mywebsite10/views/student/view-subject.php?id=$subject_id");
    }
    exit();
}

function handleUpdateSubject() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $subject_id = $_POST['subject_id'];
    $subject = new Subject();
    
    $data = [
        'subject_code' => $_POST['subject_code'],
        'subject_name' => $_POST['subject_name'],
        'description' => $_POST['description'],
        'schedule' => $_POST['schedule'],
        'room' => $_POST['room'],
        'academic_year' => $_POST['academic_year'],
        'semester' => $_POST['semester']
    ];
    
    if ($subject->updateSubject($subject_id, $data)) {
        $_SESSION['success'] = 'Subject updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update subject.';
    }
    
    header("Location: /mywebsite10/views/teacher/view-subject.php?id=$subject_id");
    exit();
}

function handleDeleteSubject() {
    if ($_SESSION['role'] !== 'teacher') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $subject_id = $_GET['id'] ?? 0;
    $subject = new Subject();
    
    if ($subject->deleteSubject($subject_id)) {
        $_SESSION['success'] = 'Subject deleted successfully!';
    } else {
        $_SESSION['error'] = 'Failed to delete subject.';
    }
    
    header('Location: /mywebsite10/views/teacher/subjects.php');
    exit();
}

function handleEnroll() {
    if ($_SESSION['role'] !== 'student') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $subject_id = $_GET['id'] ?? 0;
    $enrollment = new Enrollment();
    
    if ($enrollment->enroll($_SESSION['user_id'], $subject_id)) {
        $_SESSION['success'] = 'Successfully enrolled in subject!';
    } else {
        $_SESSION['error'] = 'Already enrolled or enrollment failed.';
    }
    
    header('Location: /mywebsite10/views/student/subjects.php');
    exit();
}

function handleUnenroll() {
    if ($_SESSION['role'] !== 'student') {
        header('Location: /mywebsite10/index.php');
        exit();
    }
    
    $subject_id = $_GET['id'] ?? 0;
    $enrollment = new Enrollment();
    
    if ($enrollment->dropSubject($_SESSION['user_id'], $subject_id)) {
        $_SESSION['success'] = 'Successfully dropped subject.';
    } else {
        $_SESSION['error'] = 'Failed to drop subject.';
    }
    
    header('Location: /mywebsite10/views/student/subjects.php');
    exit();
}
?>