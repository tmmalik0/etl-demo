<?php
	include_once( dirname( dirname( $_SERVER['DOCUMENT_ROOT'] ) ) . '/inc/autoloader.php');
	$user = new cUser();
	$user -> sec_session_start();
?>
<!DOCTYPE html>
<html lang="de">
<?php
  /**
  * @file data-warehouse.php
  * @author Tahir M. Malik
  * @date 05 Jul 2021
  * @copyright 2021 Tahir M. Malik
  * @brief Data Warehouse: Page to upload files
  */
	$_SESSION['title'] = 'Data Warehouse';
	include($_SERVER['DOCUMENT_ROOT'] . '/php/head.php');
	$currentPage = 'Data Warehouse';
?>

  <body>
    <!-- Wrapper Start -->
		<div class="wrapper">
			<!-- Sidebar start -->
			<?php include($_SERVER['DOCUMENT_ROOT'] . '/php/sidebar.php') ?>
			<!-- Sidebar end -->

			<!-- Page Content Start -->
	    <div id="content">
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/php/header.php') ?>

				<!-- Container Start -->
        <div class="container-fluid centerPage">

					<!-- Upload File Area Start -->
					<div class="col-sm-6" id="uploadArea">
						<p>Dateiformat auswählen um einen Import zu starten:</p>
						<div class="custom-control custom-radio custom-control-inline">
							<input type="radio" class="custom-control-input" id="csv" value="CSV" name="csvOrExcel" onClick="displayUploadElement()">
							<label class="custom-control-label" for="csv">CSV</label>
						</div>
						<div class="custom-control custom-radio custom-control-inline">
							<input type="radio" class="custom-control-input" id="excel" value="Excel" name="csvOrExcel" onClick="displayUploadElement()">
							<label class="custom-control-label" for="excel">Excel</label>
						</div>

						<form class="form-horizontal" method="post" name="uploadFile" enctype="multipart/form-data" id="importForm" style="display: none;">
							<input type="hidden" name="processType" id="processType" value="extractData"/>
							<div class="input-group">
							  <div class="input-group-prepend">
									<button class="input-group-text" id="submitFile" name="import">Hochladen</button>
							  </div>
							  <div class="custom-file">
							    <label class="custom-file-label" for="file" id="csvOrExcelLabel">Datei auswählen</label>
									<input type="file" class="custom-file-input" id="file" name="file" aria-describedby="submit" accept=".csv">
							  </div>
							</div>
						</form>
						<div id="response"></div>
					</div>
					<!-- Upload File Area End -->

					<!-- Upload DB Block Start -->
					<div class="col-sm-6" id="extractArea" style="display: none;">
						<p id="kundeETL"></p>
						<input type="hidden" name="object" id="object" />

						<div class="row">
							<div class="col">
								<button type="button" name="extractData" id="extractData" class="btn btn-primary btn-xs">Importieren</button>
							</div>
						</div>
						<div class="row" id="reload" style="display: none;">
							<div class="col">
								<button type="button" id="refresh" class="btn btn-primary btn-xs">Zurück</button>
							</div>
						</div>
					</div>
					<!-- Upload DB Block End -->

      	</div>
				<!-- Container End -->

				<!-- Loader Start -->
				<div class="row" id="loader" style="display: none;">
				  <div class="container centerPage">
						<div class="col-sm-6">
							<div class="d-flex justify-content-center">
								<div class="spinner-border text-primary" style="width: 50px; height: 50px;" role="status">
									<span class="sr-only">Laden...</span>
								</div>
								<div class="row">
									<div class="col">
										<div class="text-primary" id="responseLoader">Daten werden geladen und transformiert...</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-6"></div>
					</div>
				</div>
				<!-- Loader End -->

	    </div>
			<!-- Page Content End -->
		</div>
		<!-- Wrapper End -->

    <!-- Scripts -->
		<script type="text/JavaScript" src="/assets/lib/popper.min.js"></script>
  	<script type="text/JavaScript" src="/assets/lib/bootstrap.min.js"></script>
  	<script type="text/JavaScript" src="/assets/lib/counter.js"></script>

	</body>
</html>
