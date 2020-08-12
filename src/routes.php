<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

    $app->get('/about/', function (Request $request, Response $response, array $args) {
        // send message to log
        $this->logger->info("someone accessed /about/");
        // show message
        echo "it's an about page!";
    });

    $app->get("/coffees/", function (Request $request, Response $response){
        $sql = "SELECT * FROM coffees";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });

    $app->get("/coffees/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $sql = "SELECT * FROM coffees WHERE coffee_id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([":id" => $id]);
        $result = $stmt->fetch();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });

    $app->get("/coffees/search/", function (Request $request, Response $response, $args){
        $keyword = $request->getQueryParam("keyword");
        $sql = "SELECT * FROM coffees WHERE name LIKE '%$keyword%' OR desc LIKE '%$keyword%' OR source LIKE '%$keyword%'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });

    $app->post("/coffees/", function (Request $request, Response $response){

        $new_book = $request->getParsedBody();
    
        $sql = "INSERT INTO coffees (name, source, desc) VALUE (:name, :source, :desc)";
        $stmt = $this->db->prepare($sql);
    
        $data = [
            ":name" => $new_book["name"],
            ":source" => $new_book["source"],
            ":desc" => $new_book["desc"]
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });

    $app->put("/coffees/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $new_book = $request->getParsedBody();
        $sql = "UPDATE coffees SET name=:name, source=:source, desc=:desc WHERE coffee_id=:id";
        $stmt = $this->db->prepare($sql);
        
        $data = [
            ":id" => $id,
            ":name" => $new_book["name"],
            ":source" => $new_book["source"],
            ":desc" => $new_book["desc"]
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });

    $app->delete("/coffee/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $sql = "DELETE FROM coffee WHERE coffee_id=:id";
        $stmt = $this->db->prepare($sql);
        
        $data = [
            ":id" => $id
        ];
    
        if($stmt->execute($data))
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });
};
