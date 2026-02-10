<?php 

    $connect = new PDO("mysql:host=".BD_HOST.";dbname=".BD_NAME,BD_USER,BD_PASSWORD);
    $connect -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

 ?>