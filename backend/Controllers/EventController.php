<?php

    class EventController{
        public function __construct(private EventRepository $eventRepository,private AuthRepository $authRepository){
            
        }
        public function processRequest(string $method, ?string $id): void
        {
            if ($id) {            
                $this->GetEventById($method, $id);                
            } else {                
                $this->GetOrAddEvents($method);                
            }
        }

        private function GetEventById(string $method, string $id): void{
            $event = $this->eventRepository->get($id);
        
            if ( ! $event) {
                 
                echo json_encode(["message" => "Event not found"]);
                return;
            }
    
            switch ($method) {
                case "GET":
                    $user = $this->VerifySession();
                    echo json_encode($event);
                    break;
                case "POST"://join event
                    $user = $this->VerifySession();
                    $email = $_SESSION["email"];
                    $event["attendees"] += `{$email},`;

                    $rows = $this->eventRepository->update($event, $event);
                    
                    http_response_code(200);
                    echo json_encode([
                        "message" => "Event $id updated",
                        "rows" => $rows
                    ]);

                case "PATCH"://edit event
                    $user = $this->VerifySession();
                    if (!$user["role"] == "admin") {
                        http_response_code(401);
                        return;
                    }
                    $data = (array) json_decode(file_get_contents("php://input"), true);
                    
                    $errors = $this->getValidationErrors($data, true);
                    
                    if ( ! empty($errors)) {
                        http_response_code(400);
                        echo json_encode(["errors" => $errors]);
                        break;
                    }
                    
                    $rows = $this->eventRepository->update($event, $data);
                    
                    echo json_encode([
                        "message" => "Event $id updated",
                        "rows" => $rows
                    ]);
                    break;
                    

                case "DELETE":
                    $user = $this->VerifySession();
                    if (!$user["role"] == "admin") {
                        http_response_code(401);
                        return;
                    }
                    $rows = $this->eventRepository->delete($id);
                    
                    echo json_encode([
                        "message" => "Event $id deleted",
                        "rows" => $rows
                    ]);
                    break;
                    
                default:
                    http_response_code(405);
                    header("Allow: GET, PATCH, DELETE");
            }
        }

        private function GetOrAddEvents(string $method): void{
            switch($method){
                case "GET":
                    echo json_encode($this->eventRepository->getAll());
                    break;

                case "POST":
                    $user = $this->VerifySession();
                    if (!$user["role"] == "admin") {
                        http_response_code(401);
                        return;
                    }
                    $data = (array) json_decode(file_get_contents("php://input"), true);
                
                    $errors = $this->getValidationErrors($data);
                    
                    if ( ! empty($errors)) {
                        http_response_code(400);
                        echo json_encode(["errors" => $errors]);
                        break;
                    }

                    $data = (array)json_decode(file_get_contents("php://input"), true);
                    
                    $this->eventRepository->Add($data);
                    http_response_code(201);
                    echo json_encode("success");
                    break;

                default:
                http_response_code(405);
                header("Allow: GET, PATCH, DELETE");
            }
        }

        private function getValidationErrors(array $data, bool $is_new = true): array
        {
            $errors = [];
            
            if ($is_new && empty($data["nome_evento"])) {
                $errors[] = "nome_evento is required";
            }

            if ($is_new && empty($data["data_evento"])) {
                $errors[] = "data_evento is required";
            }
            
            return $errors;
        }

        private function VerifySession(){
            $sessionId = $_SESSION["sessionId"];
            $email=$_SESSION["email"];
            if (!empty($sessionId)){
                $user =  $this->authRepository->GetUserBySession($sessionId);
                if(!empty($user)) {
                    if($email == $user["email"]){
                        return $user;
                    }                   
                }     
            }
            http_response_code(401);
        }
    }
