<?php
namespace Stanford\TimestampsfromLogs;

use \REDCap as REDCap;
use \Plugin as Plugin;

class TimestampsFromLogs extends \ExternalModules\AbstractExternalModule {

    public function getTimestampsFromLogs($pid) {
        $proxy_field = $this->getProjectSetting('proxy-field');
        $target_field = $this->getProjectSetting('target-field');
        $target_check_field = $this->getProjectSetting('target-checkbox-field');
        echo "<br>proxy field: ".$proxy_field;
        echo "<br>target field: ".$target_field;
        echo "<br>target check field: ".$target_check_field;

        $sql = sprintf( "select ts, event_id, pk from redcap_log_event where project_id = '%s' ".
            //"and data_values like '%%%s %%' ".
            "and object_type = 'redcap_data' and sql_log like 'INSERT INTO %%' and ".
            " sql_log like '%%''%s''%%' ".
            "order by ts asc;",
            prep($pid),
            prep($proxy_field));


        echo "<br>".$sql;
        $result = db_query($sql);





        while(($row = db_fetch_assoc($result)) != NULL) {
            $data = array();
            $ts = $row['ts'];
            $pk = $row['pk'];
            $event_id = $row['event_id'];
            $event_name = REDCap::getEventNames(true, false,$event_id);

            if (is_array($event_name)) {
                Plugin::log($event_id, "DEBUG", "Event id does not exist");
                continue;
            }

            $t = strtotime($ts);
            $date = date('Y-m-d',$t);

            echo "<br> $date : $pk  : $event_id";
            $data = array(
                REDCap::getRecordIdField() => $pk,
                'redcap_event_name' => $event_name,
                $target_field => $date);

            //if checkbox field is specified
            if (isset($target_check_field)) {
                $data[$target_check_field.'___1']=1;
            }
            //Plugin::log($data, "DEBUG", "DATA");

            //saving the data one at a time as there can be multiple updates.
            //saving the first entry since not OVERWRITE and sort by ascending

            $q = REDCap::saveData('json', json_encode(array($data)));
            if (count($q['errors']) > 0) {
                $msg = "Error saving response for ".$data['record_id'];
                Plugin::log($q, "ERROR", $msg);
            }

            echo "<br>".print_r($q, true);


        }
        //echo "<br>DATA: ".print_r($data, true);


        /**
        */

    }
}
