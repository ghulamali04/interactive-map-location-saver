<?php
if (isset($_POST['description']) && isset($_POST['lat']) && isset($_POST['lng']) ) {
    include '../function/ip_address.php';
    include '../db.php';
    //get ip address
    $ip_address = get_client_ip();
    $uploadDir = '../media/';
    add_location($con, $ip_address, $uploadDir);
}
function add_location($con, $ip_address, $uploadDir)
{
    $lat = mysqli_real_escape_string($con, $_POST['lat']);
    $lng = mysqli_real_escape_string($con, $_POST['lng']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $uploadStatus = 1;

    // Upload file 
    $uploadedFile = '';
    if (!empty($_FILES["visitor_photo"]["name"])) {

        // File path config 
        $fileName = basename($_FILES["visitor_photo"]["name"]);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        // Allow certain file formats 
        $allowTypes = array('jpg', 'png', 'jpeg');
        if (in_array($fileType, $allowTypes)) {
            //rename files and change extension(changes to jpeg after compression)
            $fileName = 'image_'.date('Y-m-d-H-i-s').'_'.uniqid().'.jpeg';
            $targetFilePath = $uploadDir . $fileName; 
            //compress and move image file
            compressImage($_FILES["visitor_photo"]["tmp_name"],$targetFilePath, 60); //$targetFilePath will save/move output file
            $uploadedFile = $fileName;
        } else {
            $uploadStatus = 0;
            //echo 'Sorry, only PDF, DOC, JPG, JPEG, & PNG files are allowed to upload.'; 
        }
        
    }

    if ($uploadStatus == 1) {
        $check = mysqli_query($con, "SELECT `id`,`photo` FROM `location` WHERE `ip`='$ip_address'");
        $row = mysqli_fetch_array($check);
        $query = null;
        if ($row) {
            // update existing row if ip in db.
            if($uploadedFile != ''){
                //remove existing file
                if($row['photo']){
                    unlink($uploadDir.$row['photo']);
                }
                $query = "UPDATE `location` SET `lat`='$lat', `lang`='$lng', `description`='$description', `photo`='$uploadedFile' WHERE `ip`='$ip_address'";
            }else{
                $query = "UPDATE `location` SET `lat`='$lat', `lang`='$lng', `description`='$description' WHERE `ip`='$ip_address'";
            }
            
        } else {
            // Inserts new row with place data if ip not in db.
            $query = "INSERT INTO `location`(`id`, `ip`, `lat`, `lang`, `description`, `status`, `photo`) VALUES (null, '$ip_address','$lat','$lng','$description',1,'$uploadedFile')";
        }

        $result = mysqli_query($con, $query);
        if (!$result) {
            die('Invalid query: ' . mysqli_error($con));
        } else {
            $sqldata = mysqli_query($con, "SELECT * FROM `location`");

            if ($sqldata) {
                $rows = array();
                $last_arr = [];
                $check = 0;
                while ($r = mysqli_fetch_assoc($sqldata)) {
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
            } else {
                die('Invalid query: ' . mysqli_error($con));
            }
        }
    }
}

// Compress image
function compressImage($source, $destination, $quality) {

    $info = getimagesize($source);
  
    if ($info['mime'] == 'image/jpeg') 
      $image = imagecreatefromjpeg($source);
  
    elseif ($info['mime'] == 'image/png') 
      $image = imagecreatefrompng($source);
  
    imagejpeg($image, $destination, $quality);
  
  }