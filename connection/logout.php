<?php
session_start();
session_destroy();
header("location: /vendormanagementsystem/login/backend/login.php");

?>