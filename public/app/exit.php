<?php
session_start();
if (isset($_SESSION['logueado']) && $_SESSION['logueado']) {
  $_SESSION['logueado'] = false;
  unset($_SESSION['username']);
}
?>
<html>
<head>
<script language='javascript' type='text/javascript'>
      var formulario = document.getElementById("hidden1"); // el id del formulario
      var redirect = function(){
          setTimeout('document.getElementById("hidden1").submit()',10)
      }
</script>
</head>
<body onload="redirect()">
<form method="post" action="index.php" name="hidden1" id="hidden1">
  <input type="hidden" name="logout" id="logout" value="logout"></input>
</form>
</body>
</html>