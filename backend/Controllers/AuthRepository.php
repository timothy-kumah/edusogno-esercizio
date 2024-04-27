<?php

class AuthRepository{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    public function GetUserByEmail(string $email): array | false
{
    $sql = "SELECT *
            FROM utenti
            WHERE email = :email";
            
    $stmt = $this->conn->prepare($sql);
    
    $stmt->bindValue(":email", $email, PDO::PARAM_STR);
    
    $stmt->execute();
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $data;
}

public function GetUserBySession(string $sessionId): array | false
{
    $sql = "SELECT *
            FROM utenti
            WHERE sessionId = :sessionId";
            
    $stmt = $this->conn->prepare($sql);
    
    $stmt->bindValue(":sessionId", $sessionId, PDO::PARAM_STR);
    
    $stmt->execute();
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $data;
}

    public function Register(array $data):string{  
        $sql = "INSERT INTO utenti (nome, cognome, email,password)
                VALUES (:nome, :cognome, :email, :password)";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":nome", $data["nome"], PDO::PARAM_STR);
        $stmt->bindValue(":cognome", $data["cognome"], PDO::PARAM_STR);
        $stmt->bindValue(":email", $data["email"], PDO::PARAM_STR);
        $stmt->bindValue(":password", $data["password"], PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $this->conn->lastInsertId();
    }

    public function UpdateUser(array $data):int{  
        $sql = "INSERT INTO utenti (nome, cognome, email,password)
                VALUES (:nome, :cognome, :email, :password)";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":nome", $data["nome"], PDO::PARAM_STR);
        $stmt->bindValue(":cognome", $data["cognome"], PDO::PARAM_STR);
        $stmt->bindValue(":email", $data["email"], PDO::PARAM_STR);
        $stmt->bindValue(":sessionId", $data["sessionId"], PDO::PARAM_STR);
        $stmt->bindValue(":password", $data["password"], PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}