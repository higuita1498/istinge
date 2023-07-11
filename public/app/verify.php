<?php
    include "conexion.php";
    if($_POST){
        $nit = strip_tags($_POST['nit']);
        
        //si el nit existe en el carrito se hace una sumatoria si no se agrega el nuevo
        $nit_existe = "SELECT c.id FROM contactos AS c JOIN contracts AS cs ON cs.client_id = c.id WHERE c.nit = '$nit' AND c.status = 1 AND cs.status = 1";
        
        $result_nit = mysqli_query($con,$nit_existe);
        $num_nits = mysqli_num_rows($result_nit);
        
        if ($num_nits > 0) {
            echo "";
        }else{
            echo  "Disculpe, debe poseer un contrato activo para hacer uso de Mi Intercarnet";
        }
    }
?>