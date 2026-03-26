<?php
class User {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function register($data) {
        if ($this->emailExists($data['email'])) {
            return "Email is already registered.";
        }
        
        $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
        $status = $data['status'] ?? 'active';
        
        try {
            $stmt = $this->db->prepare("INSERT INTO users (first_name, last_name, email, password, gender, role, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $hashed,
                $data['gender'],
                $data['role'],
                $data['address'],
                $status
            ]);
            return true;
        } catch (PDOException $e) {
            return "Database error: " . $e->getMessage();
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT id, first_name, password, role, status, login_attempts FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'error' => "Email does not exist."];
            }

            if ($user['status'] === 'inactive') {
                return ['success' => false, 'error' => "Account is locked or inactive. Please contact the administrator."];
            }

            if (password_verify($password, $user['password'])) {
                $this->resetLoginAttempts($user['id']);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['role'] = $user['role'];

                return ['success' => true, 'role' => $user['role']];
            } else {
                $attempts = $user['login_attempts'] + 1;
                $status = ($attempts >= 3) ? 'inactive' : 'active';
                $error = ($attempts >= 3) ? "Incorrect password. Your account has been locked." : "Incorrect password. Attempt $attempts of 3.";
                
                $this->updateLoginAttempts($user['id'], $attempts, $status);
                
                return ['success' => false, 'error' => $error];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'error' => "Database error: " . $e->getMessage()];
        }
    }

    private function resetLoginAttempts($id) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET login_attempts = 0 WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            // Handle error silently
        }
    }

    private function updateLoginAttempts($id, $attempts, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET login_attempts = ?, status = ? WHERE id = ?");
            $stmt->execute([$attempts, $status, $id]);
        } catch (PDOException $e) {
            // Handle error silently
        }
    }

    public function deleteUser($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function toggleStatus($id, $currentStatus) {
        try {
            $new_status = $currentStatus === 'active' ? 'inactive' : 'active';
            $stmt = $this->db->prepare("UPDATE users SET status = ?, login_attempts = 0 WHERE id = ?");
            return $stmt->execute([$new_status, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getCounts() {
        try {
            $total = $this->db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
            $active = $this->db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch()['count'];
            $inactive = $this->db->query("SELECT COUNT(*) as count FROM users WHERE status = 'inactive'")->fetch()['count'];
            $admin = $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch()['count'];
            
            return [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'admin' => $admin
            ];
        } catch (PDOException $e) {
            return ['total' => 0, 'active' => 0, 'inactive' => 0, 'admin' => 0];
        }
    }

    public function getUsers($search = '', $limit = 5, $offset = 0) {
        try {
            $sql = "SELECT * FROM users";
            $hasSearch = !empty($search);
            
            // Apply search filter if exists
            if ($hasSearch) {
                $sql .= " WHERE first_name LIKE :search1 OR last_name LIKE :search2 OR email LIKE :search3 OR address LIKE :search4";
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            
            // Explicitly bind string parameters for SEARCH
            if ($hasSearch) {
                $searchTerm = "%$search%";
                $stmt->bindValue(':search1', $searchTerm, PDO::PARAM_STR);
                $stmt->bindValue(':search2', $searchTerm, PDO::PARAM_STR);
                $stmt->bindValue(':search3', $searchTerm, PDO::PARAM_STR);
                $stmt->bindValue(':search4', $searchTerm, PDO::PARAM_STR);
            }
            
            // Explicitly bind integer parameters for LIMIT and OFFSET
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getUsersCount($search = '') {
        try {
            $sql = "SELECT COUNT(*) as count FROM users";
            
            if (!empty($search)) {
                $sql .= " WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR address LIKE ?";
                $searchTerm = "%$search%";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            } else {
                $stmt = $this->db->query($sql);
            }
            
            return $stmt->fetch()['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function updateUser($id, $data) {
        try {
            if ($this->emailExists($data['email'], $id)) {
                return "Email is already taken by another user.";
            }

            if (!empty($data['new_password'])) {
                $hashed = password_hash($data['new_password'], PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE users SET first_name=?, last_name=?, email=?, password=?, gender=?, role=?, status=?, address=? WHERE id=?");
                $stmt->execute([
                    $data['first_name'],
                    $data['last_name'],
                    $data['email'],
                    $hashed,
                    $data['gender'],
                    $data['role'],
                    $data['status'],
                    $data['address'],
                    $id
                ]);
            } else {
                $stmt = $this->db->prepare("UPDATE users SET first_name=?, last_name=?, email=?, gender=?, role=?, status=?, address=? WHERE id=?");
                $stmt->execute([
                    $data['first_name'],
                    $data['last_name'],
                    $data['email'],
                    $data['gender'],
                    $data['role'],
                    $data['status'],
                    $data['address'],
                    $id
                ]);
            }

            return true;
        } catch (PDOException $e) {
            return "Error updating profile: " . $e->getMessage();
        }
    }

    public function updateProfileBasic($id, $data) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET first_name=?, last_name=?, address=? WHERE id=?");
            return $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['address'],
                $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updatePassword($id, $newPassword) {
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password=? WHERE id=?");
            return $stmt->execute([$hashed, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>