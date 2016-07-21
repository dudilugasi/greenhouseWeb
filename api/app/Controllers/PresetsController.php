<?php

namespace App\Controllers;

use App\DAO\GreenhouseDAO as DAO;

class PresetsController extends Controller {

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
     * handles get presets
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getPresets($req, $res) {

        $data = $this->dao->getPresets();

        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * handles get preset
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getPreset($req, $res) {

        $id = $req->getAttribute("id");
        $data = $this->dao->getPreset($id);

        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * handles update greenhouses
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function updatePreset($req, $res) {

        $parsedBody = json_decode($req->getBody()->getContents(), true);

        $id = $parsedBody["id"];
        $options = $parsedBody["options"];

        $this->dao->updatePreset($id, $options);

        return $res->withStatus(201);
    }

    /**
     * handles update greenhouses
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function addPreset($req, $res) {

        $parsedBody = json_decode($req->getBody()->getContents(), true);
        
        $name = $parsedBody["name"];
        
        $options = $parsedBody["options"];

        $this->dao->addPreset($name, $options);

        return $res->withStatus(201);
    }

}
