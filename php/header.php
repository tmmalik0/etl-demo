<!-- Header start -->
<header>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">

      <!-- Button Toggle start -->
      <button type="button" id="sidebarCollapse" class="btn btn-info" data-target="#menu-content" title="Toggle Menu">
        <i class="fa fa-align-left"></i>
      </button>
      <!-- Button Toggle End -->

      <?php echo("<h3>" . $currentPage . "</h3>"); ?>
      <!-- Personalized Dropdpwn start -->
      <ul class="nav navbar-nav navbar-right">
        <li class="right-nav">
          <a href="#">
            <button type="button" class="btn btn-info" title="Anleitung">
              <span class="fa fa-question-circle-o"></span>
            </button>
          </a>
        </li>
        <li class="right-nav">
          <a href="#">
            <button type="button" class="btn btn-info" title="Notifications">
              <span class="fa fa-bell-o"></span>
            </button>
          </a>
        </li>
        <li class="right-nav">
          <button type="button" id="img-avatar" class="btn btn-info img-avatar dropdown">
  					<img id="avatar" src="/assets/images/placeholder.png" />
  					<div class="dropdown-content">
  						<a href="#"><i class="fa fa-user fa-lg"></i>  Profil</a>
  						<a href="#"><i class="fa fa-sign-out fa-lg"></i>  Ausloggen</a>
  					</div>
  				</button>
        </li>
      </ul>
      <!-- Personalized Dropdpwn End -->
    </div>
  </nav>
</header>
<!-- Header End -->
