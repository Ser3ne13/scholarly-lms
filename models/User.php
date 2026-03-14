<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = 'users';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function login($email, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? AND is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                $this->updateLastLogin($user['user_id']);
                return $user;
            }
        }
        return false;
    }
    
    public function register($data) {
        if ($this->emailExists($data['email'])) {
            return false;
        }
        
        $sql = "INSERT INTO {$this->table} (email, password_hash, first_name, last_name, role) 
                VALUES (?, ?, ?, ?, ?)";
        
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", 
            $data['email'], 
            $hashedPassword, 
            $data['first_name'], 
            $data['last_name'], 
            $data['role']
        );
        
        return $stmt->execute();
    }
    
    private function emailExists($email) {
        $sql = "SELECT user_id FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    private function updateLastLogin($userId) {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    public function getUserById($userId) {
        $sql = "SELECT user_id, email, first_name, last_name, role, profile_picture, created_at, last_login 
                FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
        public function getAllStudents() {
            $sql = "SELECT user_id, email, first_name, last_name, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC";
            $result = $this->conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    
    public function getStudentsBySubject($subjectId) {
        $sql = "SELECT u.* FROM users u 
                INNER JOIN enrollments e ON u.user_id = e.student_id 
                WHERE e.subject_id = ? AND u.role = 'student' AND u.is_active = 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $subjectId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
        public function getAllTeachers() {
            $sql = "SELECT user_id, email, first_name, last_name FROM users WHERE role = 'teacher' ORDER BY first_name";
            $result = $this->conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    
    public function updateProfile($userId, $data) {
        $allowed = ['first_name', 'last_name', 'bio', 'profile_picture'];
        $updateData = array_intersect_key($data, array_flip($allowed));
        
        return $this->update($userId, $updateData);
    }
    
    public function changePassword($userId, $oldPassword, $newPassword) {
        $user = $this->getUserById($userId);
        
        if (!$user) {
            $this->errors[] = "User not found";
            return false;
        }
        
        if (!password_verify($oldPassword, $user['password_hash'])) {
            $this->errors[] = "Current password is incorrect";
            return false;
        }
        
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql = "UPDATE {$this->table} SET password_hash = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $newHash, $userId);
        
        return $stmt->execute();
    }
    
    public function getTeacherStats($teacherId) {
        $stats = [];
        
        $sql = "SELECT COUNT(*) as total FROM subjects WHERE teacher_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_subjects'] = $result->fetch_assoc()['total'];
        
        $sql = "SELECT COUNT(DISTINCT e.student_id) as total 
                FROM enrollments e 
                INNER JOIN subjects s ON e.subject_id = s.subject_id 
                WHERE s.teacher_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_students'] = $result->fetch_assoc()['total'];
        
        return $stats;
    }
    
    public function getStudentStats($studentId) {
        $stats = [];
        
        $sql = "SELECT COUNT(*) as total FROM enrollments WHERE student_id = ? AND status = 'enrolled'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['enrolled_subjects'] = $result->fetch_assoc()['total'];
        
        return $stats;
    }

    // Add this method to get all users (for admin)
public function getAllUsers() {
    $sql = "SELECT user_id, email, first_name, last_name, role, created_at, last_login, is_active FROM users ORDER BY created_at DESC";
    $result = $this->conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Add this method to update user (for admin)
public function updateUser($user_id, $data) {
    $sql = "UPDATE users SET first_name=?, last_name=?, email=?, role=? WHERE user_id=?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssssi", 
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['role'],
        $user_id
    );
    return $stmt->execute();
}

// Add this method to update password
public function updatePassword($user_id, $new_password) {
    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
    $sql = "UPDATE users SET password_hash=? WHERE user_id=?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("si", $password_hash, $user_id);
    return $stmt->execute();
}

// Add this method to delete user
public function deleteUser($user_id) {
    $sql = "DELETE FROM users WHERE user_id=?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

// Add this method to update user status
public function updateUserStatus($user_id, $status) {
    $is_active = ($status === 'active') ? 1 : 0;
    $sql = "UPDATE users SET is_active=? WHERE user_id=?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $is_active, $user_id);
    return $stmt->execute();
}

public function getEnrolledStudentsCount($subject_id) {
    $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE subject_id = ? AND status = 'enrolled'";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}
}
?>