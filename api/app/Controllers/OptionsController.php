<?php

namespace App\Controllers;

use App\DAO\GreenhouseDAO as DAO;

class OptionsController extends Controller {

    /**
     * GreenhouseDAO
     * @var DAO 
     */
    protected $dao;

    public function __construct($container) {
        parent::__construct($container);

        $this->dao = new DAO();
    }

    /**
     * handles update options
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function updateOptions($req, $res) {

        //parse raw json data
        $parsedBody = json_decode($req->getBody()->getContents(), true);

        $id = $parsedBody["greenhouse_id"];
        $options = $parsedBody["options"];
        
        $this->dao->updateOptions($id, $options);

        return $res->withStatus(201);
    }

    /**
     * handles get options
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getOptions($req, $res) {
        
        $id = $req->getAttribute("greenhouse_id");
        
        $data["options"] = $this->dao->getOptions($id);
        
        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

}
