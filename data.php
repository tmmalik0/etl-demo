<?php
	include_once( dirname( $_SERVER['DOCUMENT_ROOT'] ) . '/inc/autoloader.php' );
	$user = new cUser();
	$user -> sec_session_start();
?>
<!DOCTYPE html>
<html lang="de">
<?php
  /**
  * @file data.php
  * @author Tahir M. Malik
  * @date 05 Jul 2021
  * @copyright 2021 Tahir M. Malik
  * @brief Data: Page to display/edit/delete data from the database (tableview)
  */
	$_SESSION['title'] = 'Data';
	include($_SERVER['DOCUMENT_ROOT'] . '/php/head.php');
	$currentPage = 'Data';
?>
  <body>
  	<?php
  	if ($user -> login_check() == true) : ?>

    <!-- Wrapper Start -->
		<div class="wrapper">
			<!-- Sidebar start -->
			<?php include($_SERVER['DOCUMENT_ROOT'] . '/php/sidebar.php') ?>
			<!-- Sidebar end -->

      <!-- Page Content Start -->
	    <div id="content">
				<?php include($_SERVER['DOCUMENT_ROOT'] . '/php/header.php') ?>

				<!-- Container Start -->
        <div class="container-fluid">

					<!-- Date range Filter Start -->
	        <div class="row">
						<div class="col-sm-3">
							<div class="input-group">
								<label for="min">Von:</label>
								<input type="text" name="min" id="min" class="form-control" autocomplete="off"/>
								<div class="input-group-append">
									<button class="btn border border-left-0" type="button"><i class="fa fa-calendar"></i></button>
								</div>
							</div>
						</div>
						<div class="col-sm-3">
							<div class="input-group">
								<label for="max">Bis:</label>
								<input type="text" name="max" id="max" class="form-control" autocomplete="off"/>
								<div class="input-group-append">
									<button class="btn border border-left-0" type="button"><i class="fa fa-calendar"></i></button>
								</div>
							</div>
						</div>
					</div>
					<!-- Date range Filter End -->

					<!-- Table View Start -->
	        <div class="row">
						<div class="col">
							<table id="devisenTable" class="table table-bordered table-striped table-hover customTable">
								<thead>
									<tr>
										<th>ID</th>
										<th>Text</th>
										<th>Dezimal</th>
										<th>Datum</th>
										<th>Dateiname</th>
										<th>Datei Datum</th>
										<th>Status</th>
										<th>DB Status</th>
										<th>Erstellt am:</th>
										<th>Erstellt von:</th>
										<th>Aktualisiert am:</th>
										<th>Aktualisiert von:</th>
										<th></th>
										<th></th>
									</tr>
								</thead>
							</table>
						</div>
	        </div>
					<!-- Table View End -->

					<!-- Modal Start -->
					<div id="devisenModal" class="modal fade">
						<div class="modal-dialog-full-width modal-dialog">
							<form method="post" id="devisenForm">
								<input type="hidden" name="processType" id="processType" value="devisen"/>
								<div class="modal-content-full-width modal-content">
									<div class="modal-header-full-width modal-header">
										<h4 class="modal-title"><i class="fa fa-plus"></i> Devisen editieren</h4>
									</div>
									<div class="modal-body">

										<div class="row">
											<div class="col-sm-3">
												<div class="form-group">
													<label for="base" class="control-label">base</label>
													<input type="text" class="form-control" id="base" name="base">
												</div>
											</div>
											<div class="col-sm-3">
												<div class="form-group">
													<label for="quote" class="control-label">quote</label>
													<input type="text" class="form-control" id="quote" name="quote">
												</div>
											</div>
											<div class="col-sm-3">
												<div class="form-group">
													<label for="spot" class="control-label">spot</label>
													<input type="text" class="form-control" id="spot" name="spot">
												</div>
											</div>
											<div class="col-sm-3">
												<div class="form-group">
													<label for="datum" class="control-label">datum</label>
													<input type="text" class="form-control" id="datum" name="datum">
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-sm-3">
												<div class="form-group">
													<label for="kurs_quelle" class="control-label">kurs_quelle</label>
													<input type="text" class="form-control" id="kurs_quelle" name="kurs_quelle">
												</div>
											</div>
											<div class="col-sm-3">
												<div class="form-group">
													<label for="filename" class="control-label">filename</label>
													<input type="text" class="form-control" id="filename" name="filename">
												</div>
											</div>
											<div class="col-sm-3">
												<div class="form-group">
													<label for="filedate" class="control-label">filedate</label>
													<input type="text" class="form-control" id="filedate" name="filedate">
												</div>
											</div>
											<div class="col-sm-3 mr-6">
												<div class="form-group">
													<!-- <label for="status" class="control-label">status</label>
													<input type="text" class="form-control" id="status" name="status"> -->

													<label for="status">status:</label>
	 												<select class="form-control" id="status" name="status">
	 													 <option value="aktiv">aktiv</option>
	 											  	 <option value="inaktiv">Inaktiv</option>
	 												</select>
												</div>
											</div>
										</div>

									</div>

									<div class="modal-footer-full-width modal-footer">
										<input type="hidden" name="id" id="id" />
										<input type="hidden" name="action" id="action" value="" />
										<input type="submit" name="save" class="btn btn-primary" value="Speichern"/></button>
										<button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
									</div>
								</div>
							</form>
						</div>
					</div>
					<!-- Modal End -->

      	</div>
				<!-- Container End -->

	    </div>
	    <!-- Page Content End -->
		</div>
		<!-- Wrapper End -->

		<!-- Scripts -->
		<script type="text/JavaScript" src="/assets/lib/popper.min.js"></script>
  	<script type="text/JavaScript" src="/assets/lib/bootstrap.min.js"></script>
  	<script type="text/JavaScript" src="/assets/lib/counter.js"></script>

    <?php else : ?>
    <p>
      <span class="error">Sie sind nicht berechtigt auf diese Seite zugreifen zu können.</span> Bitte <a href="/login.php">einloggen</a>.
    </p>
   <?php endif; ?>
  </body>
</html>
