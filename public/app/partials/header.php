<!--Start sidebar-wrapper-->
   <div id="sidebar-wrapper" data-simplebar="" data-simplebar-auto-hide="true">
     <div class="brand-logo">
      <a href="index.php">
       <img src="assets/images/logo.png" alt="logo icon" width="90px">
     </a>
   </div>
   <ul class="sidebar-menu do-nicescrol">
      <li class="sidebar-header">ADMINISTRA TU CUENTA HOGAR</li>
      <li class="<?php if($active==1){ echo "active";} ?>">
        <a href="dashboard.php" id="dashboard">
          <i class="fa fa-home"></i> <span>Inicio</span>
        </a>
      </li>

      <li class="<?php if($active==2){ echo "active";} ?>">
        <a href="plan.php">
          <i class="fa fa-tachometer"></i> <span>Consulta tu plan detallado</span>
        </a>
      </li>
      
      <li id="div_wifi" class="">
        <a href="wifi.php">
          <i class="fa fa-wifi"></i> <span>Cambiar contraseña WIFI</span>
        </a>
      </li>

      <li class="<?php if($active==3){ echo "active";} ?>">
        <a href="invoice.php">
          <i class="fa fa-usd"></i> <span>Paga tu factura</span>
        </a>
      </li>
    </ul>
   
   </div>
   <!--End sidebar-wrapper-->

<!--Start topbar header-->
<header class="topbar-nav">
 <nav class="navbar navbar-expand fixed-top">
  <ul class="navbar-nav mr-auto align-items-center">
    <li class="nav-item">
      <a class="nav-link toggle-menu" href="javascript:void();">
       <i class="icon-menu menu-icon"></i>
     </a>
    </li>
    <li class="nav-item d-none">
      <form class="search-bar">
        <input type="text" class="form-control" placeholder="Buscar" style="width: 70%;">
         <a href="javascript:void();"><i class="icon-magnifier"></i></a>
      </form>
    </li>
  </ul>
     
  <ul class="navbar-nav align-items-center right-nav-link">
   <p style="font-size: 12px;">Bienvenido: <?php echo $usuario_actual; ?></p>
    <li class="nav-item">
      <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-toggle="dropdown" href="#">
        <span class="user-profile"><i class="fa fa-user"></i></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-right">
        <li class="dropdown-divider"></li>
        <li class="dropdown-item"><i class="icon-user mr-2"></i> Perfil</li>
        <li class="dropdown-divider"></li>
        <li class="dropdown-item"><i class="icon-settings mr-2"></i> Configuración</li>
        <li class="dropdown-divider"></li>
        <a href="exit.php"><li class="dropdown-item"><i class="icon-power mr-2"></i> Cerrar Sesión</li></a>
      </ul>
    </li>
  </ul>
</nav>
</header>
<!--End topbar header-->