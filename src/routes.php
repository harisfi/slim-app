<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/[{nama}]', function (Request $request, Response $response, array $args) use ($container) {
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
        $sql = "SELECT * FROM coffees WHERE nama LIKE '%$keyword%' OR rincian LIKE '%$keyword%' OR source LIKE '%$keyword%'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $response->withJson(["status" => "success", "data" => $result], 200);
    });

    $app->post("/coffees/", function (Request $request, Response $response){

        $new_entry = $request->getParsedBody();
    
        $sql = "INSERT INTO coffees (nama, source, rincian) VALUE (:nm, :src, :dsc)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":nm", $new_entry["nama"], PDO::PARAM_STR);
        $stmt->bindParam(":src", $new_entry["source"], PDO::PARAM_STR);
        $stmt->bindParam(":dsc", $new_entry["rincian"], PDO::PARAM_STR);
        // $data = [
        //     ":nm" => $new_entry["nama"],
        //     ":src" => $new_entry["source"],
        //     ":dsc" => $new_entry["rincian"]
        // ];
    
        if($stmt->execute())
           return $response->withJson(["status" => "success", "data" => "1"], 200);
        
        return $response->withJson(["status" => "failed", "data" => "0"], 200);
    });

    $app->put("/coffees/{id}", function (Request $request, Response $response, $args){
        $id = $args["id"];
        $new_book = $request->getParsedBody();
        $sql = "UPDATE coffees SET nama=:nama, source=:source, rincian=:rincian WHERE coffee_id=:id";
        $stmt = $this->db->prepare($sql);
        
        $data = [
            ":id" => $id,
            ":nama" => $new_book["nama"],
            ":source" => $new_book["source"],
            ":rincian" => $new_book["rincian"]
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

    $app->post('/coffees/photo/{id}', function(Request $request, Response $response, $args) {
        $uploadedFiles = $request->getUploadedFiles();

        $uploadedFile = $uploadedFiles['photo'];
        if ($uploadedFile->getError() == UPLOAD_ERR_OK) {
            $ext = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $filename = sprintf('%s.%0.8s', $args["id"], $ext);
            
            $dir = $this->get('settings')['uploadDirectory'];
            $uploadedFile->moveTo($dir . DIRECTORY_SEPARATOR . $filename);

            $sql = "UPDATE coffees SET photo=:photo WHERE coffee_id=:id";
            $stmt = $this->db->prepare($sql);
            $params = [
                ":id" => $args["id"],
                ":photo" => $filename
            ];

            if($stmt->execute($params)){
                $url = $request->getUri()->getBaseUrl()."/uploads/".$filename;
                return $response->withJson(["status" => "success", "data => $url"], 200);
            }
            return $response->withJson(["status" => "failed", "data" => "0"], 200);
        }
    });
};
