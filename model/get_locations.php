<?php
function get_all_locations($con,$ip_address){
    $sqldata = mysqli_query($con,"SELECT * FROM `location`");
    
    if($sqldata){
        $rows = array();
        $last_arr = [];
        $check = 0;
        while($r = mysqli_fetch_assoc($sqldata)) {
            if($r['ip'] == $ip_address){
                $last_arr = $r;
                $check = 1;
            }else{
                $rows[] = $r;
            }
        }
        if($check == 1){
            $rows[] = $last_arr;
        }
        $indexed = array_map('array_values', $rows);
        //  $array = array_filter($indexed);
        echo json_encode($indexed);
        if (!$rows) {
            return null;
        }
    }else{
        die('Invalid query: ' . mysqli_error($con));
    }
}
?>