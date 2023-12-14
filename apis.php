<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

function isAuthenticated() {
    $username = $_SERVER["PHP_AUTH_USER"];
    $password = $_SERVER["PHP_AUTH_PW"];
    if ($username != "username" || $password != "password") {
        return false;
    }
    return true;
}



function handleRequest() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!isset($_SERVER["PHP_AUTH_USER"])) {
            requireAuthentication();
        } else {
            if (!isAuthenticated()) {
                requireAuthentication();
            } else {
                $requestMethod = $_SERVER["REQUEST_METHOD"];

                // Your existing PHP code for handling file uploads, downloads, and getting file content goes here.
                if ($requestMethod == "POST") {
                    if (!empty($_GET["action"])) {
                        switch ($_GET["action"]) {
                            case "upload":
                                if (!empty($_FILES["file"])) {
                                    $filename = basename($_FILES["file"]["name"]);
                                    $destination = "uploads/" . $filename;
                                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $destination)) {
                                        http_response_code(200);
                                        echo json_encode(["status" => "success", "message" => "File uploaded successfully."]);
                                    } else {
                                        http_response_code(500);
                                        echo json_encode(["status" => "error", "message" => "Unable to upload the file."]);
                                    }
                                } else {
                                    http_response_code(400);
                                    echo json_encode(["status" => "error", "message" => "No file provided."]);
                                }
                                break;
                            case "download":
                                $filename = "Sample.txt";
                                $filePath = "uploads/" . $filename;
                                if (file_exists($filePath)) {
                                    header("Content-Description: File Transfer");
                                    header("Content-Type: application/octet-stream");
                                    header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
                                    header("Content-Transfer-Encoding: binary");
                                    header("Expires: 0");
                                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                                    header("Pragma: public");
                                    header("Content-Length: " . filesize($filePath));
                                    ob_clean();
                                    flush();
                                    readfile($filePath);
                                    exit;
                                } else {
                                    http_response_code(404);
                                    echo json_encode(["status" => "error", "message" => "File not found."]);
                                }
                                break;
                            case "get_content":
                                $filename = "Sample.txt";
                                $filePath = "uploads/" . $filename;
                                if (file_exists($filePath)) {
                                    $content = file_get_contents($filePath);
                                    http_response_code(200);
                                    echo json_encode(["status" => "success", "message" => "File content fetched successfully.", "content" => $content]);
                                } else {
                                    http_response_code(404);
                                    echo json_encode(["status" => "error", "message" => "File not found."]);
                                }
                                break;
                            default:
                                http_response_code(400);
                                echo json_encode(["status" => "error", "message" => "Invalid action."]);
                                break;
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(["status" => "error", "message" => "No action provided."]);
                    }
                } else {
                    http_response_code(405);
                    echo json_encode(["status" => "error", "message" => "Method not allowed."]);
                }

            }
        }
    } else {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed."]);
    }
}

function requireAuthentication() {
    header("WWW-Authenticate: Basic realm=\"Private Area\"");
    header("HTTP/1.0 401 Unauthorized");
    echo "Unauthorized access";
    exit;
}

handleRequest();
?>