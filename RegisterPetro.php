<?php

$fields = ['coins' => ["PTR", "BTC"] , 'fiats' => ["USD", "BS"]];

$driver = "mysql";
$host = "192.168.0.15";
$user = "sysprim";
$password = "2020localsemat";
$dbname = "sysprim_palavecino";

date_default_timezone_set('America/Caracas');
$executionDate="15:34";



function sendMessage($phone, $text)
{
    $user = 'reyhaus@gmail.com';
    $password = '7fs9HX3ceF';

    $parametros = "usuario=" . $user . "&clave=" . $password . "&texto=" . $text . "&telefonos=" . $phone . "&api=json";
    $url = "http://sms.adclichosting.com/webservices/SendSms";
    $handler = curl_init();
    curl_setopt($handler, CURLOPT_URL, $url);
    curl_setopt($handler, CURLOPT_POST, true);
    curl_setopt($handler, CURLOPT_POSTFIELDS, $parametros);
    curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($handler);
    curl_close($handler);

}



if($executionDate==date('H:i')) {
    try {


        $fields_string = http_build_query($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://petroapp-price.petro.gob.ve/price/");
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        $dataNew = json_decode($data, TRUE);
        curl_close($ch);
        $petro = $dataNew["data"];

        $dbConexion = new PDO("$driver:host=$host;dbname=" . $dbname, $user, $password);
        $dbConexion->beginTransaction();
        $dbConexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $fiscalPeriod = date('Y-d-m');
        $value = $petro["PTR"]["BS"];
        $dateInsert = date('Y-d-m H:i:s');

        $resultRegister = $dbConexion->query("
                INSERT INTO foreign_exchange (name,fiscal_period,value ,created_at,updated_at)
                VALUES ( 'Petro','$fiscalPeriod','$value','$dateInsert','$dateInsert')");
        $dbConexion->commit();

        try {
            sendMessage("+584141585586", "El valor del petro ha sido registrado con éxito.Tasa del dia " . $fiscalPeriod . " : " . number_format($petro["PTR"]["BS"], 2) . ", Equipo Sysprim(SEMAT).");
            // sendMessage("+584145515706", "El valor del petro ha sido registrado con éxito.Tasa del dia ".$fiscalPeriod.":" .number_format($petro["PTR"]["BS"],2).", Equipo Sysprim(SEMAT).");
        } catch (Exception $e) {

        }

    } catch (Exception $e) {
        $dbConexion->rollBack();
        sendMessage("+584141585586", "Ocurrio un error al registrar el valor del petro " . $data . " , por favor ingreselo manualmente, Equipo Sysprim(SEMAT).");
    }



}
