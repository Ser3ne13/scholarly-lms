<?php
session_start();
session_destroy();
header('Location: /mywebsite10/views/auth/login.php');
exit();
?>