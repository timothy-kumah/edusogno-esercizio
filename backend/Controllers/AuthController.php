<?php

    class AuthController{
        public function __construct(private AuthRepository $authRepository){
            
        }
        public function processRequest(string $method,string $path): void
        {
            switch ($method) {
                case "POST":
                    if ($path == "login"){
                        $data = (array) json_decode(file_get_contents("php://input"), true);
                        $errors = $this->getValidationErrors($data, false);

                        if ( ! empty($errors)) {
                            http_response_code(400);
                            echo json_encode(["errors" => $errors]);
                            break;
                        }

                        $userData = $this->authRepository->GetUserByEmail($data["email"]);
 
                        if ($userData && password_verify($data["password"], $userData['password'])) {
                            $userData["sessionId"] = session_create_id($userData["email"]);
                            $this->authRepository->UpdateUser($userData);
                            echo json_encode([
                                "sessionId" => $userData["sessionId"],
                                "nome"=>$userData["nome"],
                                "email"=>$userData["email"],
                                "cognome"=>$userData["cognome"]
                            ]);
                        }else{
                            http_response_code(401);
                            echo json_encode([
                                "message" => "Wrong Credentials",
                            ]);
                        }
                    }elseif($path == "register"){
                        $data = (array) json_decode(file_get_contents("php://input"), true);
                        
                        $errors = $this->getValidationErrors($data, true);
                        
                        if ( ! empty($errors)) {
                            http_response_code(400);
                            echo json_encode(["errors" => $errors]);
                            break;
                        }
                        
                        $data["password"] = password_hash($data["password"], PASSWORD_DEFAULT);
                        $this->authRepository->register($data);

                        echo json_encode([
                            "message" => "User Created",
                        ]);
                    }elseif($path == "forgot-password"){
                        $data = (array) json_decode(file_get_contents("php://input"), true);
                        if(!empty($data["email"])){
                            $user = $this->authRepository->GetUserByEmail($data["email"]);
                            if(!empty($user)){
                                $user["sessionId"] = session_create_id($user["email"]);
                                $this->authRepository->UpdateUser($user);

                                //sendmail
                            }                            
                        }
                    }elseif($path == "change-password"){
                        $urlPath = $_SERVER["REQUEST_METHOD"];
                        $sessionId = $urlPath[5];
                        $data = (array) json_decode(file_get_contents("php://input"), true);
                        if(empty($sessionId)){
                            http_response_code(401);
                            return;
                        }

                        if(!empty($data["password"])){
                            $user = $this->authRepository->GetUserBySession($sessionId);
                            if(!empty($user)){
                                $user["password"] = password_hash($data["password"], PASSWORD_DEFAULT);
                                $user["sessionId"] = session_create_id($user["email"]);
                                $this->authRepository->UpdateUser($user);
                            }                            
                        }
                    }
                    break;

                case "GET":
                    if($path == "dashboard"){
                       // $this->authRepository->dashboard();
                    }
                    break;    
                    
                default:
                http_response_code(405);
                header("Allow: GET,POST");
            }
        }

        private function getValidationErrors(array $data, bool $is_new = true): array
        {
            $errors = [];
            
            if ($is_new && empty($data["nome"])) {
                $errors[] = "nome is required";
            }

            if (empty($data["email"])) {
                $errors[] = "email is required";
            }

            if ($is_new && empty($data["cognome"])) {
                $errors[] = "cognome is required";
            }

            if (empty($data["password"])) {
                $errors[] = "password is required";
            }
            
            return $errors;
        }

    }