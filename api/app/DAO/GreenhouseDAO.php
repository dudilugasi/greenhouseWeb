<?php

namespace App\DAO;

use Carbon\Carbon;
use App\Models\Preset;
use App\Models\Action;
use App\Models\Option;
use App\Models\Greenhouse;
use App\Models\PresetOption;
use App\Models\GreenhouseData;
use App\Models\PerformAction;

class GreenhouseDAO {

    /**
     * insert greenhouse data to db
     * @param mixed $greenhouses
     * @return mixed $data
     */
    public function insertGreenhouseData($greenhouses) {

        //iterate the set of greenhouses
        foreach ($greenhouses as $greenhouse) {

            //iterate the data inside the greenhouse
            foreach ($greenhouse["data"] as $ghdata) {

                //add greenhouseData to DB
                $greenhouseData = new GreenhouseData;
                $greenhouseData->greenhouse_id = $greenhouse["id"];
                $greenhouseData->key = $ghdata["key"];
                $greenhouseData->value = $ghdata["value"];
                $greenhouseData->time = date("Y-m-d H:i:s", mktime(
                                $ghdata["dateTime"]["hour"], $ghdata["dateTime"]["minutes"], $ghdata["dateTime"]["seconds"], $ghdata["dateTime"]["month"], $ghdata["dateTime"]["date"], $ghdata["dateTime"]["year"]
                        )
                );

                $greenhouseData->save();
            }
        }
    }

    /**
     * get the latest greenhouse data
     * @param int $greenhouse_id
     * @return mixed $data
     */
    public function getGreenHouseData($greenhouse_id) {
        global $capsule;

        $data = array();

        $sql = "SELECT ordered.* FROM (
              SELECT * FROM `greenhouse_data`
              WHERE `greenhouse_id` = :greenouse_id
              ORDER BY  `key` ASC, `time` DESC ) as ordered
            GROUP BY ordered.`key`";

        $greenhouseDatas = $capsule::select($sql, array('greenouse_id' => $greenhouse_id));

        foreach ($greenhouseDatas as $greenhouseData) {
            $data[$greenhouseData->key] = $greenhouseData->value;
        }

        return $data;
    }

    /**
     * retrieve daily data
     * @param int $greenhouse_id
     * @param string $key
     * @param string $date
     * @return mixed $data
     */
    public function getGreenHouseDailyData($greenhouse_id, $key, $date) {

        $data = array();

        if (!$date) {
            $date = Carbon::today()->toDateString();
        }

        if ($key == "soilHumidity") {
            $greenhouseDatas = GreenhouseData::where('greenhouse_id', $greenhouse_id)
                    ->where(function ($query) use ($key) {
                        $query->where('key', $key . '1')
                        ->orWhere('key', $key . '2');
                    })
                    ->whereDate('time', '=', $date)
                    ->get();
        } else {

            $greenhouseDatas = GreenhouseData::where('greenhouse_id', $greenhouse_id)
                    ->where('key', $key)
                    ->whereDate('time', '=', $date)
                    ->get();
        }


        foreach ($greenhouseDatas as $greenhouseData) {
            if ($greenhouseData->key == "soilHumidity1") {
                $value = 'value1';
            } else if ($greenhouseData->key == "soilHumidity2") {
                $value = 'value2';
            } else {
                $value = 'value';
            }
            $data[] = array(
                $value => $greenhouseData->value,
                "time" => $greenhouseData->time
            );
        }

        return $data;
    }

    /**
     * get greenhouse monthly aggregated data
     * @param type $greenhouse_id
     * @param type $key
     * @param type $date
     */
    public function getGreenHouseMonthlyData($greenhouse_id, $key, $date) {
        global $capsule;

        $data = array();

        if (!$date) {
            $date = Carbon::today()->toDateString();
        }

        $sql = "select DATE(`time`) as `date`, SUM(`value`) as `sum` 
            from `greenhouse_data` 
            where `greenhouse_id` = :greenhouseId
            and `key` = :key
            and month(`time`) = :date
            group by `date` 
            order by `date` desc";

        $vars = array(
            "greenhouseId" => $greenhouse_id,
            "key" => $key,
            "date" => $date
        );

        $greenhouseDatas = $capsule::select($sql, $vars);

        foreach ($greenhouseDatas as $greenhouseData) {
            $data[] = array(
                "time" => $greenhouseData->date,
                "value" => $greenhouseData->sum
            );
        }

        return $data;
    }

    /**
     * get all greenhouses
     * @return mixed $data
     */
    public function getGreenhouses() {

        $data = array();

        $greenhouses = Greenhouse::all();

        foreach ($greenhouses as $greenhouseData) {
            $data[] = $greenhouseData->toArray();
        }

        return $data;
    }

    /**
     * set greenhouse working state
     * @param int $id
     */
    public function setGreenhouseWorking($id) {

        $greenhouse = Greenhouse::where('greenhouse_id', $id)->first();

        $greenhouse->working = !$greenhouse->working;

        $greenhouse->save();
    }

    /**
     * return greenhouse by id
     * @param int $id
     * @return array
     */
    public function getGreenhouse($id) {

        $greenhouse = Greenhouse::where('greenhouse_id', $id)->first();

        return $greenhouse->toArray();
    }

    /**
     * get actions paginated
     * @param int $id
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function getActions($id, $page, $per_page) {

        $data = array();

        $offset = $page == 1 ? 0 : ($page - 1 ) * $per_page;

        $actions = Action::where('greenhouse_id', $id)
                ->orderBy('time','DESC')
                ->limit($per_page)
                ->offset($offset)
                ->get();

        foreach ($actions as $action) {
            $data[] = $action->toArray();
        }

        return $data;
    }

    /**
     * add action to database
     * @param int $id
     * @param string $time
     * @param string $actionname
     */
    public function addAction($id, $time, $actionname) {

        $action = new Action;
        $action->greenhouse_id = $id;
        $action->action = $actionname;
        $action->time = date("Y-m-d H:i:s", mktime(
                        $time["hour"], $time["minutes"], $time["seconds"], $time["month"], $time["date"], $time["year"]
                )
        );

        $action->save();
    }

    /**
     * get all the presets
     * @return array
     */
    public function getPresets() {

        $data = array();

        $presets = Preset::all();

        foreach ($presets as $preset) {
            $data[] = $preset->toArray();
        }

        return $data;
    }

    /**
     * get single preset by id
     * @param int $id
     * @return array
     */
    public function getPreset($id) {

        $data = array();

        $options = PresetOption::where("preset_id", $id)->get();

        foreach ($options as $option) {
            $data[] = $option->toArray();
        }

        return $data;
    }

    /**
     * update preset options
     * @param int $id
     * @param array $options
     */
    public function updatePreset($id, $options) {
        foreach ($options as $option) {

            $preset_option = PresetOption::where("preset_id", $id)
                    ->where("key", $option["key"])
                    ->first();

            if (!$preset_option) {
                $preset_option = new PresetOption;
                $preset_option->key = $option["key"];
                $preset_option->preset_id = $id;
            }

            $preset_option->maxValue = $option["maxValue"];
            $preset_option->minValue = $option["minValue"];

            $preset_option->save();
        }
    }

    /**
     * add a new preset to database
     * @param string $name
     * @param array $options
     */
    public function addPreset($name, $options) {
        $preset = new Preset;
        $preset->name = $name;

        if ($preset->save()) {
            foreach ($options as $option) {

                $preset_option = new PresetOption;
                $preset_option->key = $option["key"];
                $preset_option->preset_id = $preset->id;
                $preset_option->maxValue = $option["maxValue"];
                $preset_option->minValue = $option["minValue"];
                $preset_option->save();
            }
        }
    }

    /**
     * update existing preset
     * @param type $id
     * @param type $options
     */
    public function updateOptions($id, $options) {

        foreach ($options as $option) {

            $greenhouseOption = Option::where("greenhouse_id", $id)
                    ->where("key", $option["key"])
                    ->first();

            if (!$greenhouseOption) {
                $greenhouseOption = new Option;
                $greenhouseOption->key = $option["key"];
                $greenhouseOption->greenhouse_id = $id;
            }

            $greenhouseOption->minValue = $option["minValue"];
            $greenhouseOption->maxValue = $option["maxValue"];

            $greenhouseOption->save();
        }
    }

    /**
     * get options by greenhouse
     * @param int $id
     * @return array
     */
    public function getOptions($id) {

        $data = array();

        $options = Option::where("greenhouse_id", $id)->get();

        foreach ($options as $option) {
            $data[] = $option->toArray();
        }

        return $data;
    }

    /**
     * change action status in DB
     * @param int $greenhouseId
     * @param string $action
     */
    public function performAction($greenhouseId, $action, $on) {
        $performAction = PerformAction::where('greenhouseId', $greenhouseId)
                ->where('action', $action)
                ->first();

        $performAction->on = $on;

        $performAction->save();
    }

    public function resetPerformActions($greenhouseId) {
        PerformAction::where('greenhouseId', '=', $greenhouseId)
                ->update(['on' => 0]);
    }

    public function getPerformActions($greenhouseId) {

        $data = array();

        $performActions = PerformAction::where('greenhouseId', $greenhouseId)
                ->get();

        foreach ($performActions as $action) {
            $data[] = $action->toArray();
        }

        return $data;
    }

}
