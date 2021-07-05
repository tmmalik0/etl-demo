<?php
	include_once( $_SERVER['DOCUMENT_ROOT'] . '/php/process.php');
?>
<!DOCTYPE html>
<html lang="de">
<?php
  /**
  * @file etl-settings.php
  * @author Tahir M. Malik
  * @date 05 Jul 2021
  * @copyright 2021 Tahir M. Malik
  * @brief ETL Settings
  */
	$_SESSION['title'] = 'ETL Settings';
	include($_SERVER['DOCUMENT_ROOT'] . '/php/head.php');
	$currentPage = 'ETL Settings';
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
        <div class="container-fluid">

					<!-- Add Button Section Start -->
					<div class="row">
						<div class="col-sm-3">
							<button type="button" name="addETL" id="addETL" class="btn btn-primary btn-xs">Neuer ETL Filter</button>
						</div>
						<div class="col-sm-1 ml-auto">
							<button type="button" id="cancel" class="btn btn-primary btn-xs" style="display: none;"><i class="fa fa-window-close" aria-hidden="true"></i></button>
						</div>
					</div>
					<!-- Add Button Section End -->

					<!-- ETL- Settings Section Start -->
					<form method="post" name="clientFormETL" id='clientFormETL' enctype="multipart/form-data">
	          <input type="hidden" name="processType" id="processType" value="etlSettings"/>
	          <input type="hidden" name="action" id="action" value="displayETLFilenames"/>
						<div class="row">
							<div class="col" id="etl-nav" style="display: none;">
								<nav>
		              <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
		                <a class="nav-item nav-link active" id="nav-etl-tab" data-toggle="tab" href="#nav-etl" role="tab" aria-controls="nav-etl" aria-selected="true">ETL</a>
		                <a class="nav-item nav-link" id="nav-datei-tab" data-toggle="tab" href="#nav-datei" role="tab" aria-controls="nav-datei" aria-selected="false">Datei</a>
		                <a class="nav-item nav-link" id="nav-mapping-tab" data-toggle="tab" href="#nav-mapping" role="tab" aria-controls="nav-mapping" aria-selected="false">Mapping</a>
		                <a class="nav-item nav-link" id="nav-etl-filter-tab" data-toggle="tab" href="#nav-etl-filter" role="tab" aria-controls="nav-etl-filter" aria-selected="false">ETL Filter</a>
		                <a class="nav-item nav-link" id="nav-summary-tab" data-toggle="tab" href="#nav-summary" role="tab" aria-controls="nav-summary" aria-selected="false">Summary</a>
		              </div>
		            </nav>

								<!-- Nav-TabContent Start -->
								<div class="tab-content py-3 px-3 px-sm-0 listener" id="nav-tabContent">
		              <!-- Kunden Start-->
		              <div class="tab-pane fade show active" id="nav-etl" role="tabpanel" aria-labelledby="nav-etl-tab">
										<div class="container-fluid">
		                  <div class="row">
												<div class="col">
													<div class="row">
														<div class="col-sm-4">
															<div class="form-group">
																<label for="etlFilenames">Name:</label>
																<select class="form-control" id="etlFilenames" name="etlFilenames" required>
																</select>
															</div>
														</div>
														<div class="col-sm-2 mr-auto" id="addETLNameButton">
															<button type="button" class="btn btn-info" title="Neuen Kunden hinzufügen" id="addETLName">
									              <span class="fa fa-plus"></span>
									            </button>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<!-- Kunden End-->

									<!-- Datei Start-->
		              <div class="tab-pane fade" id="nav-datei" role="tabpanel" aria-labelledby="nav-datei-tab">
										<div class="container-fluid">

											<div class="row">
												<div class="col-sm-3">
													<div class="form-group">
														<label for="kunde">Kunde:</label>
														<input type="text" class="form-control" id="kunde" name="kunde" value="QuantVS">
													</div>
												</div>
												<div class="col-sm-2" id="headerRow">
													<label for="header">Header in Zeile:</label>
													<input type="number" class="form-control" id="header" name="header_0" min="1" value="1">
												</div>
												<div class="col-sm-2" id="startRow">
													<label for="startRow">Start-Zeile:</label>
													<input type="number" class="form-control" id="startRow" name="startRow_0" min="1" value="1">
												</div>
												<div class="col-sm-2" id="tabsRow">
													<label for="header">Anzahl Lachen:</label>
													<input type="number" class="form-control" id="tabs" name="tabs" min="1" value="1">
												</div>
												<div class="col-sm-2">
													<div class="form-group">
														<label for="dbTable">Tabelle:</label>
														<select class="form-control" id="dbTable_0" name="dbTable_0">
															<option value="swaps">Swaps</option>
															<option value="zsk">Zinskurven</option>
															<option value="spreads">Spreads</option>
															<option value="devisen">Devisen</option>
															<option value="EQSWAPS">EQSWAPS</option>
															<option value="EQSWAPE16X">EQSWAPE16X</option>
															<option value="EQSWAPE18MDX">EQSWAPE18MDX</option>
															<option value="EQSWAPE20MDX">EQSWAPE20MDX</option>
															<option value="EQSWAPE35AX">EQSWAPE35AX</option>
															<option value="EQSWAPE37X">EQSWAPE37X</option>
															<option value="EQSWAPE40X">EQSWAPE40X</option>
															<option value="qvs_bonds_staticData">Wertpapiere</option>
															<option value="kurse">Kurse</option>
														</select>
													</div>
												</div>
												<div class="col-sm-1">
													<div class="custom-control custom-checkbox kurse-control">
												    <input type="checkbox" class="custom-control-input" id="kurseCheck_0">
												    <label class="custom-control-label" for="kurseCheck_0">Kurse</label>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-sm-3">
													<div class="form-group">
														<label for="isin">ISIN:</label>
														<input type="text" class="form-control" id="isin" name="isin">
													</div>
												</div>
												<div class="col-sm-3">
													<div class="form-group">
														<label for="currency">Währung:</label>
														<input type="text" class="form-control" id="currency" name="currency">
													</div>
												</div>
												<div class="col-sm-6"></div>
											</div>

											<div id="multipleTabs"></div>

		                  <div class="row">
												<div class="col-sm-8 mr-4">
													<p>Dateiformat auswählen:</p>
													<div class="custom-control custom-radio custom-control-inline">
														<input type="radio" class="custom-control-input" id="csv" value="CSV" name="csvOrExcel" onClick="displayUploadElement()">
														<label class="custom-control-label" for="csv">CSV</label>
													</div>
													<div class="custom-control custom-radio custom-control-inline">
														<input type="radio" class="custom-control-input" id="excel" value="Excel" name="csvOrExcel" onClick="displayUploadElement()">
														<label class="custom-control-label" for="excel">Excel</label>
													</div>

													<div class="input-group" id="importForm" style="display: none;">
													  <div class="input-group-prepend">
															<button type="submit" class="input-group-text" id="submitETLFile" name="import">Hochladen</button>
													  </div>
													  <div class="custom-file">
													    <label class="custom-file-label" for="file" id="csvOrExcelLabel">Datei auswählen</label>
															<input type="file" class="custom-file-input" id="file" name="file" aria-describedby="submit" accept=".csv">
													  </div>
													</div>
													<div id="response"></div>
												</div>
											</div>

										</div>
									</div>
									<!-- Datei End-->

									<!-- Mapping Start-->
		              <div class="tab-pane fade" id="nav-mapping" role="tabpanel" aria-labelledby="nav-mapping-tab">
										<div class="container-fluid">
											<div class="row">
												<div id="mappingError"></div>
												<div class="col-sm-3">
													<button type="button" id="addNewField" class="btn btn-primary btn-xs" style="display: none;">Neues Feld</button>
												</div>
												<div class="col-sm-9 mx-auto text-right">
													<div class="dropdown">
														<button type="button" id="showDBFields" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" style="display: none;">DB Felder</button>
														<div class="dropdown-menu"></div>
													</div>

													<button type="button" name="saveMapping" id="saveMapping" class="btn btn-primary btn-xs" style="display: none;">Weiter</button>
												</div>
											</div>

											<div class="row">
												<div id="table_0"></div>
												<table class="table table-bordered customTable" id="mappingTable_0">
													<thead>
														<tr>
															<th>Felder (Datei)</th>
															<th>Felder (Datenbank)</th>
														</tr>
													</thead>
												  <tbody></tbody>
												</table>
											</div>
											<div id="multiTable"></div>

											<hr>
											<div class="row">
												<div id="kurseTableName_0"></div>
												<table class="table table-bordered customTable" id="mappingTableKurse_0" style="display: none;">
													<thead>
														<tr>
															<th>Felder (Datei)</th>
															<th>Felder (Datenbank)</th>
														</tr>
													</thead>
												  <tbody></tbody>
												</table>
											</div>
											<div id="multiKurseTable"></div>

										</div>
									</div>
									<!-- Mapping End-->

									<!-- ETL Filter Start-->
		              <div class="tab-pane fade" id="nav-etl-filter" role="tabpanel" aria-labelledby="nav-etl-filter-tab">
										<div class="container-fluid">
											<div class="row">
												<div class="form-group">
													<select class="form-control" id="ETLFilter" name="ETLFilter" style="display: none;"></select>
												</div>
												<div class="col mx-auto text-right">
													<button type="button" name="navigateToSummary" id="navigateToSummary" class="btn btn-primary btn-xs" style="display: none;">Weiter</button>
												</div>
											</div>
		                  <div class="row">
												<div class="col">
													<div id="etlTableHeader"></div>
													<table class="table table-bordered customTable" id="etlTable_0">
												    <thead>
												      <tr>
												        <th>Felder (Datei)</th>
												        <th>Filter</th>
												        <th>Felder (Datenbank)</th>
												      </tr>
												    </thead>
														<tbody></tbody>
													</table>
												</div>
											</div>
											<div id="multiETLTable"></div>

											<hr>
											<div class="row">
												<div id="etlKurseTableHeader"></div>
												<table class="table table-bordered customTable" id="etlKurseTable_0" style="display: none;">
													<thead>
														<tr>
															<th>Felder (Datei)</th>
															<th>Filter</th>
															<th>Felder (Datenbank)</th>
														</tr>
													</thead>
												  <tbody></tbody>
												</table>
											</div>
											<div id="multiKurseETLTable"></div>

										</div>
									</div>
									<!-- ETL Filter End-->

									<!-- Summary Start-->
		              <div class="tab-pane fade" id="nav-summary" role="tabpanel" aria-labelledby="nav-summary-tab">
										<div class="container-fluid">
											<div class="row">
												<div class="col-sm-6">
													<h5>ETL Filter Name:</h5><p id="ETLName"></p>
												</div>
												<div class="col-sm-6">
													<div class="col mx-auto text-right">
														<button type="button" name="saveETLSettings" id="saveETLSettings" class="btn btn-primary btn-xs" style="display: none;">Speichern</button>
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col">
													<h5>Mapping Übersicht:</h5>
												</div>
											</div>
		                  <div class="row">
												<div class="col">
													<textarea class="form-control rounded-0" name="resultMapping_0" id="resultMapping_0" rows="10" style="display: none;"></textarea>
													<textarea class="form-control rounded-0" name="resultMappingKurse_0" id="resultMappingKurse_0" rows="10" style="display: none;"></textarea>
													<div id="multipleMappings"></div>
													<div id="multipleKurseMappings"></div>
													<div id="summaryTableHeader"></div>
													<table class="table table-bordered customTable" id="summaryTable_0">
												    <thead>
												      <tr>
												        <th>Felder (Datei)</th>
																<th>Filter</th>
												        <th>Felder (Datenbank)</th>
												      </tr>
												    </thead>
														<tbody></tbody>
													</table>
												</div>
											</div>
											<div id="multiSummaryTable"></div>
										</div>
									</div>
									<!-- Summary End-->

								</div>
								<!-- Nav-TabContent End -->
							</div>
						</div>
					</form>
					<!-- ETL- Settings Section End -->

					<div class="row">
						<!-- Table View Start -->
						<div class="col">
							<table id="etlFilenamesTable" class="table table-bordered table-striped table-hover customTable">
								<thead>
									<tr>
										<th>Name</th>
										<th>Dateiname</th>
										<th>Kunde</th>
										<th>Tabelle</th>
										<th>Header</th>
										<th>Start-Zeile</th>
										<th>Lache</th>
										<th>Kurse</th>
										<th></th>
										<th></th>
									</tr>
								</thead>
							</table>
						</div>
						<!-- Table View End -->

						<!-- Modal Start -->
						<div id="etlEditModal" class="modal fade">
							<div class="modal-dialog modal-dialog-centered">
								<form method="post" id="editETLForm">
									<input type="hidden" name="processType" value="etlSettings"/>
				          <input type="hidden" name="action" value="updateETLInfo"/>
									<div class="modal-content" style="width: 950px;">
										<div class="modal-header">
											<h4 class="modal-title"><i class="fa fa-plus"></i> ETL Profil aktualisieren</h4>
										</div>
										<div class="modal-body">

											<div class="alert alert-info">
											  <strong>Achtung!</strong> Beim hinzufügen eines neuen Feldes, müssen Feldnamen manuell eingetragen werden.
												Wenn ein neues Feld im Mapping hinzugefügt wurde, muss dasselbe Feld in ETL Filter (Transformation) hinterlegt werden.
											</div>

											<div class="row">
												<div class="col-sm-3">
													<div class="form-group">
														<label for="edit-etlFilename" class="control-label">Name</label>
														<input type="text" class="form-control" id="edit-etlFilename" name="edit-etlFilename" required>
													</div>
												</div>
												<div class="col-sm-2">
													<div class="form-group">
														<label for="edit-headerRow" class="control-label">Header</label>
														<input type="number" class="form-control" id="edit-headerRow" name="edit-headerRow" required>
													</div>
												</div>
												<div class="col-sm-2">
													<div class="form-group">
														<label for="edit-startRow" class="control-label">Start</label>
														<input type="number" class="form-control" id="edit-startRow" name="edit-startRow" required>
													</div>
												</div>
												<div class="col-sm-3">
													<div class="form-group">
														<label for="edit-kunde">Kunde:</label>
														<input type="text" class="form-control" id="edit-kunde" name="edit-kunde">
													</div>
												</div>
												<div class="col-sm-2">
													<div class="form-group">
														<label for="edit-kunde">Lache:</label>
														<input type="number" class="form-control" id="edit-lache" name="edit-lache">
													</div>
												</div>
											</div>

											<div class="row">
												<div class="col-sm-3">
													<div class="form-group">
														<label for="isin">ISIN:</label>
														<input type="text" class="form-control" id="edit-isin" name="isin">
													</div>
												</div>
												<div class="col-sm-3">
													<div class="form-group">
														<label for="currency">Währung:</label>
														<input type="text" class="form-control" id="edit-currency" name="currency">
													</div>
												</div>
												<div class="col-sm-2">
													<div class="form-group">
														<label for="ubs">UBS:</label>
														<input type="text" class="form-control" id="edit-ubs" name="ubs">
													</div>
												</div>
												<div class="col-sm-4"></div>
											</div>

											<nav>
					              <div class="nav nav-tabs nav-fill" id="nav-edit-tab" role="tablist">
					                <a class="nav-item nav-link active" id="nav-edit-mapping-tab" data-toggle="tab" href="#nav-edit-mapping" role="tab" aria-controls="nav-edit-mapping" aria-selected="true">Mapping</a>
					                <a class="nav-item nav-link" id="nav-edit-filter-tab" data-toggle="tab" href="#nav-edit-filter" role="tab" aria-controls="nav-edit-filter" aria-selected="false">ETL Filter</a>
					                <a class="nav-item nav-link" id="nav-edit-kurse-mapping-tab" data-toggle="tab" href="#nav-edit-kurse-mapping" role="tab" aria-controls="nav-edit-kurse-mapping" aria-selected="false">Kurse Mapping</a>
					                <a class="nav-item nav-link" id="nav-edit-kurse-filter-tab" data-toggle="tab" href="#nav-edit-kurse-filter" role="tab" aria-controls="nav-edit-kurse-filter" aria-selected="false">Kurse ETL Filter</a>
					              </div>
					            </nav>

											<!-- Nav-TabContent Start -->
											<div class="tab-content py-3 px-3 px-sm-0 listener" id="nav-tabContent">
					              <!-- Edit Mapping Start-->
					              <div class="tab-pane fade show active" id="nav-edit-mapping" role="tabpanel" aria-labelledby="nav-edit-mapping-tab">
													<div class="container-fluid">

														<div class="row">
															<div class="col-sm-3 mr-auto">
																<button type="button" id="addNewMappingField" class="btn btn-primary btn-xs">Neues Feld</button>
															</div>
														</div>
														<div class="row">
															<div class="col">
																<table class="table table-bordered customTable" id="editMappingTable">
																	<thead>
																		<tr>
																			<th>Felder (Datei)</th>
																			<th>Felder (Datenbank)</th>
																		</tr>
																	</thead>
																  <tbody></tbody>
																</table>
															</div>
														</div>
													</div>
												</div>
												<!-- Edit Mapping End-->

					              <!-- Edit ETL Filter Start-->
					              <div class="tab-pane fade" id="nav-edit-filter" role="tabpanel" aria-labelledby="nav-edit-filter-tab">
													<div class="container-fluid">

														<div class="row">
															<div class="col-sm-3 mr-auto">
																<button type="button" id="addNewTransformationField" class="btn btn-primary btn-xs">Neues Feld</button>
															</div>
														</div>
														<div class="row">
															<div class="col">
																<table class="table table-bordered customTable" id="editETLFilterTable">
																	<thead>
																		<tr>
																			<th>Felder (Datei)</th>
																			<th>Filter</th>
																		</tr>
																	</thead>
																  <tbody></tbody>
																</table>
															</div>
														</div>
													</div>
												</div>
												<!-- Edit ETL Filter End-->
												<!-- Edit Kurse Mapping Start-->
					              <div class="tab-pane fade show" id="nav-edit-kurse-mapping" role="tabpanel" aria-labelledby="nav-edit-kurse-mapping-tab">
													<div class="container-fluid">

														<div class="row">
															<div class="col-sm-3 mr-auto">
																<button type="button" id="addNewMappingFieldKurse" class="btn btn-primary btn-xs">Neues Feld</button>
															</div>
														</div>
														<div class="row">
															<div class="col">
																<table class="table table-bordered customTable" id="editKurseMappingTable">
																	<thead>
																		<tr>
																			<th>Felder (Datei)</th>
																			<th>Felder (Datenbank)</th>
																		</tr>
																	</thead>
																  <tbody></tbody>
																</table>
															</div>
														</div>
													</div>
												</div>
												<!-- Edit Kurse Mapping End-->

					              <!-- Edit Kurse ETL Filter Start-->
					              <div class="tab-pane fade" id="nav-edit-kurse-filter" role="tabpanel" aria-labelledby="nav-edit-kurse-filter-tab">
													<div class="container-fluid">

														<div class="row">
															<div class="col-sm-3 mr-auto">
																<button type="button" id="addNewTransformationFieldKurse" class="btn btn-primary btn-xs">Neues Feld</button>
															</div>
														</div>
														<div class="row">
															<div class="col">
																<table class="table table-bordered customTable" id="editKurseETLFilterTable">
																	<thead>
																		<tr>
																			<th>Felder (Datei)</th>
																			<th>Filter</th>
																		</tr>
																	</thead>
																  <tbody></tbody>
																</table>
															</div>
														</div>
													</div>
												</div>
												<!-- Edit Kurse ETL Filter End-->

											</div>
											<!-- Nav-TabContent End -->

										</div>
										<div class="modal-footer">
											<input type="hidden" name="id" id="etl-id"/>
											<textarea class="form-control rounded-0" name="resultEditMapping" id="resultEditMapping" rows="10" style="display: none;"></textarea>
											<textarea class="form-control rounded-0" name="resultEditKurseMapping" id="resultEditKurseMapping" rows="10" style="display: none;"></textarea>
											<button type="button" name="save-edit" class="btn btn-primary" onclick="submitEditETLForm()">Aktualisieren</button>
											<button type="button" class="btn btn-default" data-dismiss="modal">Schließen</button>
										</div>
									</div>
								</form>
							</div>
						</div>
						<!-- Modal End -->
					</div>

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

	</body>
</html>
