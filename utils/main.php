<?php

function create_data($connection, $table, $data) {
  $columns = implode(", ", array_keys($data));
  $placeholders = implode(", ", array_fill(0, count($data), '?'));

  try {
      $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
      $stmt = pg_prepare($connection, "insert_query", $query);
      $result = pg_execute($connection, "insert_query", array_values($data));

      if (!$result) {
          echo "Error in inserting data: " . pg_last_error($connection);
      }
      return $result;
  } catch (\Throwable $th) {
      return "Internal server error: " . $th->getMessage();
  }
}

function get_all_data($connection, $table) {
  try {
    $query = "SELECT * FROM $table";
    $result = pg_query($connection, $query);
  
    if (!$result) {
        echo "Error in reading data: " . pg_last_error();
        return [];
    }
  
    return pg_fetch_all($result);
  } catch (\Throwable $th) {
    return "internal server error ";
  }
}

function find_data($connection, $table, $where) {
  try {
    $query = "SELECT * FROM $table WHERE $where";
    $result = pg_query($connection, $query);
  
    if(!$result){
      echo 'Error in reading data: '. pg_last_error();
      return [];
    }
    return pg_fetch_assoc($result);
  } catch (\Throwable $th) {
    return "internal server error ";
  }
}


function update_data($connection, $table, $data, $condition) {
  $setClause = implode(", ", array_map(function($key, $value) {
      return "$key = '" . pg_escape_string($value) . "'";
  }, array_keys($data), $data));

  try {
    $query = "UPDATE $table SET $setClause WHERE $condition";
    $result = pg_query($connection, $query);
  
    if (!$result) {
        echo "Error in updating data: " . pg_last_error();
    }
    return $result;
  } catch (\Throwable $th) {
    return 'internal server error';
  }
}

function delete_data($connection, $table, $condition) {
  try {
    $query = "DELETE FROM $table WHERE $condition";
    $result = pg_query($connection, $query);
  
    if (!$result) {
        echo "Error in deleting data: " . pg_last_error();
    }
    return $result;
  }catch (\Throwable $th) {
    return 'internal server error';
  }
}


?>