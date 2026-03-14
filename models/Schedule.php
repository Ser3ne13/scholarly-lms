<?php
require_once __DIR__ . '/../config/database.php';

class Schedule {
    private $conn;
    private $table = 'room_assignments';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Get teacher schedule
    public function getTeacherSchedule($teacher_id, $academic_year = null, $semester = null) {
        $sql = "SELECT ra.*, s.subject_name, s.subject_code 
                FROM {$this->table} ra
                INNER JOIN subjects s ON ra.subject_id = s.subject_id
                WHERE ra.teacher_id = ?";
        
        $params = [$teacher_id];
        $types = "i";
        
        if ($academic_year) {
            $sql .= " AND ra.academic_year = ?";
            $params[] = $academic_year;
            $types .= "s";
        }
        
        if ($semester) {
            $sql .= " AND ra.semester = ?";
            $params[] = $semester;
            $types .= "s";
        }
        
        $sql .= " ORDER BY FIELD(ra.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), ra.start_time";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get room schedule
    public function getRoomSchedule($room, $academic_year = null, $semester = null) {
        $sql = "SELECT ra.*, s.subject_name, s.subject_code, u.first_name, u.last_name 
                FROM {$this->table} ra
                INNER JOIN subjects s ON ra.subject_id = s.subject_id
                INNER JOIN users u ON ra.teacher_id = u.user_id
                WHERE ra.room = ?";
        
        $params = [$room];
        $types = "s";
        
        if ($academic_year) {
            $sql .= " AND ra.academic_year = ?";
            $params[] = $academic_year;
            $types .= "s";
        }
        
        if ($semester) {
            $sql .= " AND ra.semester = ?";
            $params[] = $semester;
            $types .= "s";
        }
        
        $sql .= " ORDER BY FIELD(ra.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), ra.start_time";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Add room assignment
    public function addRoomAssignment($data) {
        $sql = "INSERT INTO {$this->table} 
                (subject_id, teacher_id, room, day_of_week, start_time, end_time, academic_year, semester) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissssss", 
            $data['subject_id'],
            $data['teacher_id'],
            $data['room'],
            $data['day_of_week'],
            $data['start_time'],
            $data['end_time'],
            $data['academic_year'],
            $data['semester']
        );
        
        return $stmt->execute();
    }
    
    // Check for conflicts
    public function checkConflicts($teacher_id, $room, $day, $start_time, $end_time, $academic_year, $semester, $exclude_id = null) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE ((teacher_id = ? OR room = ?) 
                AND day_of_week = ? 
                AND academic_year = ? 
                AND semester = ?
                AND ((start_time <= ? AND end_time > ?) 
                     OR (start_time < ? AND end_time >= ?)
                     OR (start_time >= ? AND end_time <= ?)))";
        
        $params = [$teacher_id, $room, $day, $academic_year, $semester, 
                   $end_time, $start_time, $end_time, $start_time, $start_time, $end_time];
        $types = "iissssssssss";
        
        if ($exclude_id) {
            $sql .= " AND assignment_id != ?";
            $params[] = $exclude_id;
            $types .= "i";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>