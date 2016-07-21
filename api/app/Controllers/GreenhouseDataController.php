<?php

namespace App\Controllers;

use App\DAO\GreenhouseDAO as DAO;

class GreenhouseDataController extends Controller {

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
     * handles insert greenhouse data route
     * @param ServerRequestInterface $req
     * @param ResponseInterface $res
     * @return ResponseInterface
     */
    public function insert($req, $res) {

        $data = array();

        //parse raw json data
        $parsedBody = json_decode($req->getBody()->getContents(), true);

        $this->dao->insertGreenhouseData($parsedBody["greenhouses"]);

        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * handles the get latest data route
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getGreenHouseData($req, $res) {

        $data = $this->dao->getGreenHouseData($req->getAttribute('greenhouse_id'));

        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * handles the get daily data route
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getDailyGreenHouseData($req, $res) {

        $id = $req->getAttribute('greenhouse_id');
        $key = $req->getAttribute('key');
        $date = $req->getAttribute('date');
        $data = $this->dao->getGreenHouseDailyData($id, $key, $date);

        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * handles the get monthly data route
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getMonthlyGreenHouseData($req, $res) {

        $id = $req->getAttribute('greenhouse_id');
        $key = $req->getAttribute('key');
        $date = $req->getAttribute('date');
        $data = $this->dao->getGreenHouseMonthlyData($id, $key, $date);

        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

}
