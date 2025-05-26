<?php
require_once 'config/database.php';

class Plan {
    private $conn;
    private $table = 'plans';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllPlans() {
        $query = "SELECT * FROM " . $this->table . " WHERE status = 'active' ORDER BY price ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPlanById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getUserSubscription($user_id) {
        $query = "SELECT s.*, p.name as plan_name, p.features 
                 FROM user_subscriptions s 
                 JOIN plans p ON s.plan_id = p.id 
                 WHERE s.user_id = :user_id AND s.status = 'active' AND s.expires_at > NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function createSubscription($user_id, $plan_id) {
        $plan = $this->getPlanById($plan_id);
        if (!$plan) return false;

        $expires_at = date('Y-m-d H:i:s', time() + ($plan['duration_days'] * 24 * 3600));
        
        $query = "INSERT INTO user_subscriptions (user_id, plan_id, expires_at) 
                 VALUES (:user_id, :plan_id, :expires_at)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':plan_id', $plan_id);
        $stmt->bindParam(':expires_at', $expires_at);
        
        return $stmt->execute();
    }
}
?>