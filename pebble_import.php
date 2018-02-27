<?php
define ("PEBBLE_DATA_DIR","data/pebble_data");
define ("PEBBLE_DATA_EXTRACT","/([0-9-T:Z]+),([0-9]+),([0-9]+),([0-9]+),([0-9]+),([0-9]+),([0-9]+),([0-9]+)/");
define ("DATABASE","sqlite:data/dkh_db.sqlite3");
define ("PEBBLE_BACKUP_FILE","data/pebble_backup.mcb");
$pebble_files=array_diff(scandir(PEBBLE_DATA_DIR), array('..', '.'));

date_default_timezone_set('UTC');
try {
    /**************************************
    * Create databases and                *
    * open connections                    *
    **************************************/
	
    // Create (connect to) SQLite database in file
    $file_db = new PDO(DATABASE);
    // Set errormode to exceptions
    $file_db->setAttribute(PDO::ATTR_ERRMODE, 
                            PDO::ERRMODE_EXCEPTION);
							
    /**************************************
    * Create tables                       *
    **************************************/
 
    // Create table messages
    $file_db->exec("CREATE TABLE IF NOT EXISTS pebble_health (
                    time INTEGER PRIMARY KEY, 
                    steps INTEGER,
					yaw INTEGER,
					pitch INTEGER,
					vmc INTEGER,					
                    ambient_light INTEGER, 
                    activity_mask INTEGER,
					heart_beats INTEGER
					)");
  }
  catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
  }
  
try {
//Prepare INSERT statement to SQLite3 file db
$insert = "INSERT OR IGNORE INTO pebble_health (time, steps, yaw,pitch,vmc,ambient_light,activity_mask,heart_beats) 
			VALUES (:time, :steps, :yaw,:pitch,:vmc,:ambient_light,:activity_mask,:heart_beats)";
$stmt = $file_db->prepare($insert);

// Bind parameters to statement variables
$stmt->bindParam(':time', $time);
$stmt->bindParam(':steps', $steps);
$stmt->bindParam(':yaw', $yaw);
$stmt->bindParam(':pitch',$pitch);
$stmt->bindParam(':vmc', $vmc);
$stmt->bindParam(':ambient_light', $ambient_light);
$stmt->bindParam(':activity_mask', $activity_mask);
$stmt->bindParam(':heart_beats',$heart_beats);
	
foreach($pebble_files as $file){
	$data=file_get_contents(PEBBLE_DATA_DIR."/".$file);
	$a_data=explode("null",urldecode($data));
	
    // Loop thru all messages and execute prepared insert statement
    foreach ($a_data as $line){
		$treffer=array();
		if(preg_match(PEBBLE_DATA_EXTRACT, $line, $treffer)){
			$time=strtotime($treffer[1]);
			$steps=$treffer[2];
			$yaw=$treffer[3];
			$pitch=$treffer[4];
			$vmc=$treffer[5];
			$ambient_light=$treffer[6];
			$activity_mask=$treffer[7];
			$heart_beats=$treffer[8];
			// Execute statement
			$stmt->execute();
		}
    }
	//backup line and delete file
	$backup = file_put_contents(PEBBLE_BACKUP_FILE, $data , FILE_APPEND | LOCK_EX);
	unlink(PEBBLE_DATA_DIR."/".$file);
	}
	}
	catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
	}
	echo "Import abgeschlossen!";

?>