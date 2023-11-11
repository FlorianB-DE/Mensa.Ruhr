<?php

include_once 'config.php';

function getCurrentDayConfiguration() {
  global $dataDirectory;
  global $endHour;

  if (date("N") == 6) {
    $date = date("Y-m-d", time() + 60 * 60 * 24 * 2);
    $humanReadableDay = "Montag";
  } else if (date("N") == 7) {
    $date = date("Y-m-d", time() + 60 * 60 * 24);
    $humanReadableDay = "Montag";
  } else if (date("N") == 5 && date("H") >= $endHour) {
    $date = date("Y-m-d", time() + 60 * 60 * 24 * 3);
    $humanReadableDay = "Montag";
  } else if (date("H") >= $endHour) {
    $date = date("Y-m-d", time() + 60 * 60 * 24);
    $humanReadableDay = "Morgen";
  } else {
    $date = date("Y-m-d");
    $humanReadableDay = "Heute";
  }

  $filename = $dataDirectory . "/" . $date . ".db";
  return [
    'filename' => $filename,
    'date' => $date,
    'humanReadableDay' => $humanReadableDay
  ];
}

function checkForDBEntryOfUser($filename, $user_id) {
  $fh = fopen($filename,'r');
  while ($line = fgets($fh)) {
    if (str_replace("\n", "", explode(",", $line)[2]) == $user_id) return $line;
  }
  fclose($fh);
  return FALSE;
}

function replaceLine($filename, $line1, $line2) {
    $content = file_get_contents($filename);
    $content = str_replace($line1, $line2, $content);
    file_put_contents($filename, $content);
}

function appendLine($filename, $line) {
    $fh = fopen($filename, "a") or die("Unable to open database!");
	fwrite($fh, $line."\n");
	fclose($fh);
}

function getAttendance($filename, $get_confidential=FALSE) {
  global $verified_colors;

  $attendance = [];

  $fh = fopen($filename, 'r');
  while ($line = fgets($fh)) {
    $data = explode(",", str_replace("\n", "", $line));

    $attendanceData = array(
      "name" => $data[0],
      "name_modifiers" => $data[4],
      "time" => $data[1],
      "canteen" => $data[3],
      "color" => ($data[2] != "" && array_key_exists($data[2], $verified_colors)) ? $verified_colors[$data[2]] : "#000000"
    );

    if ($get_confidential) {
      $attendanceData["user_id"] = $data[2];
    }

    $attendance[] = $attendanceData;
  }

  return $attendance;
}