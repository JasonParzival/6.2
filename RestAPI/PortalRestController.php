<?php

class PortalRestController {

    public PDO $pdo;
    function __construct()
    {
        $this->pdo = new PDO("mysql:host=localhost;dbname=portal_db;charset=utf8", "root", "");
    }

    public function process($id=null) {
        //$method = $_SERVER['REQUEST_METHOD'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        } else {
            $method = $_SERVER['REQUEST_METHOD'];
        }
        
        $data = [];
        if ($id) {
            $query = $this->pdo->prepare("SELECT * FROM portal_characters WHERE id = :id");
            $query->bindParam("id", $id);
            $query->execute();

            $instance = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($method == "GET") {
                $data = $this->retrieve($instance);
            } elseif ($method == "DELETE") {
                $data = $this->remove($instance);
            } elseif ($method == "PUT") {
                $data = $this->update($instance);
            }
        } else {
            if ($method == "GET") {
                $data = $this->list();
            } elseif ($method == "POST") {
                $data = $this->create();
            }
        }
        
        header('Content-type: application/json');
        //echo json_encode($data ?? []);
    }

    public function list() {
        $query = $this->pdo->query("SELECT * FROM portal_characters LIMIT 10");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $title = $data['title'] ?? '';
        $image = $data['image'] ?? '';
        $description = $data['description'] ?? '';
        $info = $data['info'] ?? '';
        $type = $data['type'] ?? '';

        $query = $this->pdo->prepare("
        INSERT INTO portal_characters(title, image, description, info, type)
        VALUES (:title, :image, :description, :info, :type)
        ");
        $query->bindValue("title", $title);
        $query->bindValue("image", $image);
        $query->bindValue("description", $description);
        $query->bindValue("info", $info);
        $query->bindValue("type", $type);

        $query->execute();

        return $this->pdo->lastInsertId();
    }

    public function retrieve($instance) {
        return $instance;
    }

    public function update($instance) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        print_r($data);

        $query = $this->pdo->prepare("
        UPDATE portal_characters 
        SET title = :title, 
            image = :image, 
            description = :description, 
            info = :info, 
            type = :type 
        WHERE id = :id
        ");

        $tmp_name = $_FILES['image']['tmp_name'];
        $name =  $_FILES['image']['name'];
        move_uploaded_file($tmp_name, "../public/media/$name");
        $image_url = "/media/$name" ?? $instance['image'];

        $title = $data['title'] ?? $instance['title'];
        $image = $data['image'] ?? $instance['image'];
        $description = $data['description'] ?? $instance['description'];
        $info = $data['info'] ?? $instance['info'];
        $type = $data['type'] ?? $instance['type'];

        $query->bindParam("title", $title);
        $query->bindParam("image", $image_url);
        $query->bindParam("description", $description);
        $query->bindParam("info", $info);
        $query->bindParam("type", $type);
        $query->bindParam("id", $instance['id']);
        $query->execute();

        $context['message'] = 'Данные успешно обновлены';
        $context['id'] = $instance['id'];

        return $context;
    }

    public function remove($instance) {
        $query = $this->pdo->prepare("DELETE FROM portal_characters WHERE id = :id");
        $query->bindParam("id", $instance['id']);
        $query->execute();
        header("Location: /");
        exit;
        //return ["success" => True];
    }
}