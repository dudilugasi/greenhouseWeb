<?php

// data roues
$app->post('/data',"GreenhouseDataController:insert");

$app->get('/data/{greenhouse_id}','GreenhouseDataController:getGreenHouseData');

$app->get('/data/daily/{greenhouse_id}/{key}/{date}',
        'GreenhouseDataController:getDailyGreenHouseData');

$app->get('/data/monthly/{greenhouse_id}/{key}/{date}',
        'GreenhouseDataController:getMonthlyGreenHouseData');


//greenhouse routes
$app->get('/greenhouse','GreenhouseController:getGreenhouses');
$app->get('/greenhouse/{id}','GreenhouseController:getGreenhouse');
$app->post('/greenhouse/working','GreenhouseController:setGreenhouseWorking');


//actions routes
$app->get('/action/{id}','GreenhouseController:getActions');
$app->post('/action','GreenhouseController:addAction');
$app->post('/action/perform/reset/{greenhouseId}','GreenhouseController:resetPerformActions');
$app->post('/action/perform/{greenhouseId}/{action}/{on}','GreenhouseController:performAction');
$app->get('/action/perform/{greenhouseId}','GreenhouseController:getPerformActions');

//presets routes
$app->get('/presets','PresetsController:getPresets');
$app->get('/preset/{id}','PresetsController:getPreset');
$app->post('/presets','PresetsController:updatePreset');
$app->post('/presets/add','PresetsController:addPreset');

//options routes
$app->post('/options','OptionsController:updateOptions');
$app->get('/options/{greenhouse_id}','OptionsController:getOptions');