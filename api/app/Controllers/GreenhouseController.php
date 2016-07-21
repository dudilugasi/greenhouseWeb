<?php

namespace App\Controllers;

use App\DAO\GreenhouseDAO as DAO;

class GreenhouseController extends Controller {

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
     * handles get greenhouses
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getGreenhouses($req, $res) {

        $data = $this->dao->getGreenHouses();

        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * handles set greenhouses status route
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function setGreenhouseWorking($req, $res) {

        //parse raw json data
        $parsedBody = json_decode($req->getBody()->getContents(), true);

        $this->dao->setGreenhouseWorking($parsedBody["greenhouse"]);

        return $res->withStatus(201);
    }

    /**
     * handles get greenhouse working route
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getGreenhouse($req, $res) {

        $id = $req->getAttribute('id');

        $data["greenhouse"] = $this->dao->getGreenhouse($id);

        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * 
     * get the actions pagignated
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function getActions($req, $res) {

        $id = $req->getAttribute('id');
        $params = $req->getQueryParams();
        $page = isset($params["page"]) ? $params["page"] : 1;
        $pre_page = isset($params["per_page"]) ? $params["per_page"] : 2;

        $data["actions"] = $this->dao->getActions($id, $page, $pre_page);

        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /**
     * 
     * add action
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function addAction($req, $res) {
        //parse raw json data
        $parsedBody = json_decode($req->getBody()->getContents(), true);

        $greenhouse = $parsedBody["greenhouse"];
        $time = $parsedBody["dateTime"];
        $action = $parsedBody["action"];
        $this->dao->addAction($greenhouse, $time, $action);

        return $res->withStatus(201);
    }

    /**
     * perform action route handler
     * @param Psr\Http\Message\ServerRequestInterface $req
     * @param Psr\Http\Message\ResponseInterface $res
     * @return Psr\Http\Message\ResponseInterface
     */
    public function performAction($req, $res) {
        $greenhouseId = $req->getAttribute('greenhouseId');
        $action = $req->getAttribute('action');
        $on = $req->getAttribute('on');

        $this->dao->performAction($greenhouseId, $action, $on);

        return $res->withStatus(201);
    }

    public function resetPerformActions($req, $res) {
        $greenhouseId = $req->getAttribute('greenhouseId');

        $this->dao->resetPerformActions($greenhouseId);

        return $res->withStatus(201);
    }

    public function getPerformActions($req, $res) {
        $greenhouseId = $req->getAttribute('greenhouseId');
        $data = $this->dao->getPerformActions($greenhouseId);

        return $res->withStatus(201)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

}
