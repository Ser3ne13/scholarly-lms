<?php
require_once __DIR__ . '/../config/database.php';

class Section {
    private $conn;
    private $table = 'sections';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Get all sections
    public function getAllSections() {
        $sql = "SELECT * FROM {$this->table} ORDER BY academic_year DESC, year_level ASC, section_name ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get section by ID
    public function getSection($section_id) {
        $sql = "SELECT * FROM {$this->table} WHERE section_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Create section
    public function createSection($data) {
        $sql = "INSERT INTO {$this->table} (section_name, academic_year, year_level) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", 
            $data['section_name'],
            $data['academic_year'],
            $data['year_level']
        );
        return $stmt->execute();
    }
    
    // Update section
    public function updateSection($section_id, $data) {
        $sql = "UPDATE {$this->table} SET section_name=?, academic_year=?, year_level=? WHERE section_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssii", 
            $data['section_name'],
            $data['academic_year'],
            $data['year_level'],
            $section_id
        );
        return $stmt->execute();
    }
    
    // Delete section
    public function deleteSection($section_id) {
        $sql = "DELETE FROM {$this->table} WHERE section_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $section_id);
        return $stmt->execute();
    }
    
    // Get students in section
    public function getSectionStudents($section_id) {
        $sql = "SELECT u.* FROM users u 
                INNER JOIN student_sections ss ON u.user_id = ss.student_id 
                WHERE ss.section_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get section schedule
    public function getSectionSchedule($section_id) {
        $sql = "SELECT ss.*, s.subject_name, s.subject_code, u.first_name, u.last_name 
                FROM section_schedules ss
                INNER JOIN subjects s ON ss.subject_id = s.subject_id
                INNER JOIN users u ON ss.teacher_id = u.user_id
                WHERE ss.section_id = ?
                ORDER BY FIELD(ss.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), ss.start_time";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Add student to section
    public function addStudentToSection($student_id, $section_id) {
        // Check if already in section
        $check_sql = "SELECT * FROM student_sections WHERE student_id = ? AND section_id = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $student_id, $section_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false; // Already in section
        }
        
        $sql = "INSERT INTO student_sections (student_id, section_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $section_id);
        return $stmt->execute();
    }
    
    // Remove student from section
    public function removeStudentFromSection($student_id, $section_id) {
        $sql = "DELETE FROM student_sections WHERE student_id = ? AND section_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $section_id);
        return $stmt->execute();
    }
}
?>