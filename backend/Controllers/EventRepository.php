<?php

class EventRepository{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll():array{
        $sql = "SELECT *
                FROM eventi";

                $stmt = $this->conn->query($sql);

                $events = [];

                while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                    $events[] = $row;
                }  

                return $events;
            } 

        public function get(string $id): array | false
        {
            $sql = "SELECT *
                    FROM eventi
                    WHERE id = :id";
                    
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data;
        }
        
        public function update(array $current, array $new): int
        {
            $sql = "UPDATE eventi
                    SET attendees = :attendees, nome_evento = :nome_evento, data_evento = :data_evento
                    WHERE id = :id";
                    
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(":attendees", $new["attendees"] ?? $current["attendees"], PDO::PARAM_STR);
            $stmt->bindValue(":nome_evento", $new["nome_evento"] ?? $current["nome_evento"], PDO::PARAM_INT);
            $stmt->bindValue(":data_evento", $new["data_evento"] ?? $current["data_evento"], PDO::PARAM_BOOL);
            
            $stmt->bindValue(":id", $current["id"], PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->rowCount();
        }
        
        public function delete(string $id): int
        {
            $sql = "DELETE FROM eventi
                    WHERE id = :id";
                    
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->rowCount();
        }

    public function Add(array $data):string{  
        $sql = "INSERT INTO eventi (attendees, nome_evento, data_evento)
                VALUES (:attendees, :nome_evento, :data_evento)";
                
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(":attendees", $data["attendees"], PDO::PARAM_STR);
        $stmt->bindValue(":nome_evento", $data["nome_evento"], PDO::PARAM_STR);
        $stmt->bindValue(":data_evento", $data["data_evento"], PDO::PARAM_STR);
        
        $stmt->execute();
        
        return $this->conn->lastInsertId();
    }
}