<?php
require_once __DIR__.'/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application(['debug' => true]);

function getConnection() {
  $dbhost="127.0.0.1";
  $dbuser="root";
  $dbpass="";
  $dbname="wines";
  $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $dbh;
}

$app->get('/wines', function () use ($app) {
  //$wines = "<b>First wine</b>";
  try {
    $wines = json_encode(listWine());
  } catch (Exception $e) {
    echo $e->getMessage();
  }
  return $wines;
});

$app->get('/wines/{id}', function ($id) use ($app) {
  $wines = "<b>First wine with id </b>".$id;
  return $wines;
});

$app->post('/wines', function (Request $request) use ($app) {
  $data['title'] = $request->get('title');
  $data['grapes'] = $request->get('grapes');
  $data['country'] = $request->get('country');
  $data['price'] = $request->get('price');
  //$wines = "<b>First wine with id </b>".$title."-".$grapes."-".$country."-".$price;

  addWine($data);
  return json_encode($data); 
});

function listWine() {
  try {
    $sql = "select * from wines";
    $db = getConnection();
    $stm = $db->query($sql);
    $wines = $stm->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    return $wines;
  } catch (PDOException $e) {
    echo $e->getMessage();
  }
}

function addWine($data) {
  $properties = ['title', 'grapes', 'country', 'price'];
  $prepare_columns = implode(',', $properties);
  $prepare_values = ':' . implode(', :', $properties);
  try {
    $sql = "insert into wines(" . $prepare_columns . ") values (" . $prepare_values . ")";
    $db = getConnection();
    $stm = $db->prepare($sql);
    bindData($stm, $data, $properties);
    $stm->execute();
  } catch (PDOException $e) {
    echo $e->getMessage();
  }
}

function bindData($statement, $data, $properties) {
  foreach ($properties as $property_name) {
    $statement->bindParam($property_name, $data[$property_name]);
  }
}

$app->run();

