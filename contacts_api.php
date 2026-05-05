<?php
require_once 'config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $conn->query("SELECT * FROM contacts ORDER BY id DESC");
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);
}

if ($method === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTOS contacts (name, email, phone) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $phone);
    $stmt->execute();

    echo json_encode(["status" => "success"]);
}

if ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);

    $id = $_PUT['id'];
    $name = $_PUT['name'];
    $email = $_PUT['email'];
    $phone = $_PUT['phone'];

    $stmt = $conn->prepare("UPDATE contacts SET name=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $phone, $id);
    $stmt->execute();

    echo json_encode(["status" => "updated"]);
}

if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);

    $id = $_DELETE['id'];

    $stmt = $conn->prepare("DELETE FROM contacts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["status" => "deleted"]);
}
?>