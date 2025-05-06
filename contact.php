<?php
$user="root";
$password="";
$host="localhost";
$db="testing";
$port="3308";
$dph = "mysql:host=".$host.";port=".$port.";dbname=".$db.";charset=utf8";
$pdo=new PDO($dph,$user,$password);

$city = $_POST["city"];
$phone = $_POST["phone"];
$email = $_POST["email"];
$row="UPDATE contact SET city=:city,phone=:phone,email=:email";
$query=$pdo->prepare($row);
$query->execute(["city"=>$city, "phone"=>$phone, "email"=>$email,]);
echo '<meta HTTP-EQUIV="Refresh" Content="0; URL=/admin/contact.php">';
?>