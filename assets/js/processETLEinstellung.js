/**
* @file processETL.js
* @author Tahir M. Malik
* @date 05 Jul 2021
* @copyright 2021 Tahir M. Malik
* @brief ETL Relevant Process to create settings for a customer/file
*/
let fileMappingList = []; // field names (headers) from the csv (currrently)
let databaseMappingList = []; // sorted field names in the table (database) only
let databaseHeaders = []; // field names in the table (database) only
let databaseMappingTypes = []; // field names in the table (database), includes datatypes
let databaseFilterTypes = []; // field names in the table (database), includes datatypes
let resultMapping; // object, where the final mapping will be saved into
let resultKurseMapping; // object, where the final kurse mapping will be saved into (currently only used while editing a ETL Filter)
let anzahlTables; // Anzahl Lachen
let kurseHeader;

$(document).ready(function() {
  let processType = $('#processType').val();
  let action = $('#action').val();
  let fileName;

  /*
  * Datatables
  */
  var etlFilenamesData = $('#etlFilenamesTable').DataTable({
  	"lengthChange": false,
  	"processing":true,
  	"order":[],
  	"ajax":{
  		url: "/php/process.php",
  		type: "POST",
  		data: { processType:processType, action:action },
  		dataType: "json"
  	},
  	"columnDefs":[
  		{
  			"targets":[0, 6, 7],
  			"orderable":true,
  		},
  	],
  	"pageLength": 50
  });

  /*
  * Get transformations from the database and save it #ETLFilter dropdown
  */
  $.ajax({
    url: '/php/process.php',
    method: "POST",
    data: { processType:processType, action:'getTransformations' },
    dataType: "json",
    success: function(data) {
      displayTransformations(data);
    }
  })

  /*
  * Make the Modal draggable
  */
  $("#etlEditModal").draggable({
    handle: ".modal-header"
  });

  /*
  * Shows Navigation Area for ETL Process
  */
  $('#addETL').click(function() {
    /*
    * Load Clients: to be displayed in the Dropdown field
    */
    $.ajax({
      url: '/php/process.php',
      method: "POST",
      data: { processType:processType, action:'displayETLFilenamesDropdown' },
      dataType: "json",
      success: function(data) {
        var displayAction = $('#displayAction').val();
        displayETLFilenamesDropdown(data, displayAction);
      }
    })
		$('#etl-nav').show();
		$('#cancel').show();
	});

  /*
  * Adding a new name for the ETL Process
  */
  $('#addETLName').click(function(event) {
    event.preventDefault();
		var etlName = prompt('Bitte einen Namen eingeben:');
    if (etlName != null) {
      $.ajax({
    		url: "/php/process.php",
    		method: "POST",
    		data: { processType:processType, action:'addETLName', etlName:etlName },
    		success: function(data) {
    			alert(data);
          etlFilenamesData.ajax.reload();
          $("#addETL").trigger("click");
    		}
    	})
    }
	});

  /*
  * Editing a filename/mapping from the datatables
  */
  $("#etlFilenamesTable").on('click', '.update', function() {

    var id = $(this).attr("id");
    $('#etl-id').val(id);
    var tables;

    $.ajax({
      url: '/php/process.php',
      method: "POST",
      data: { processType:processType, action:'getETLInfo', id:id },
      dataType: "json",
      success: function(data) {
        createEditTables(data);
      }

    })
  });

  /*
  * deleting a filename/mapping from the datatables
  */
  $("#etlFilenamesTable").on('click', '.delete', function(){
  	var id = $(this).attr("id");
  	if(confirm("Sind Sie sicher, dass Sie dieses Mapping löschen wollen? Diese Aktion kann nicht mehr rückgängig gemacht werden!")) {
  		$.ajax({
  			url: "/php/process.php",
  			method: "POST",
  			data: { processType:processType, action:'deleteETLInfo', id:id },
  			success: function(data) {
  				etlFilenamesData.ajax.reload();
          $("#addETL").trigger("click");
  			}
  		})
  	} else {
  		return false;
  	}
  });

  /*
  * Close Navigation Area for ETL Process
  */
  $('#cancel').click(function() {
		$('#etl-nav').hide();
		$('#cancel').hide();
	});

  /*
  * Getting filename
  */
  $('input[type="file"]').change(function(e){
    fileName = e.target.files[0].name;
		document.getElementById('csvOrExcelLabel').innerHTML = fileName;
		var label = document.getElementById('csvOrExcelLabel');
  });

  /*
  * If the number of worksheets changes
  */
  $('#tabs').change(function(e){
    var tabs = $(this).val();
    anzahlTables = tabs;
    var newHeader = '';

    if (tabs > 1) {

      for (var i = 1; i < tabs; i++) {

        newHeader += '<div class="row"><div class="col-sm-3"></div>';
        newHeader += '<div class="col-sm-2"><input type="number" class="form-control" id="header_' + i + '" name="header_' + i + '" min="1" value="1"></div>';
        newHeader += '<div class="col-sm-2"><input type="number" class="form-control" id="startRow_' + i + '" name="startRow_' + i + '" min="1" value="1"></div>';
        newHeader += '<div class="col-sm-2"></div>';
        newHeader += '<div class="col-sm-2">';
        newHeader += '<div class="form-group">';
        newHeader += '<select class="form-control" id="dbTable_' + i + '" name="dbTable_' + i + '">';
        newHeader += '<option value="swaps">Swaps</option>';
        newHeader += '<option value="zsk">Zinskurven</option>';
        newHeader += '<option value="spreads">Spreads</option>';
        newHeader += '<option value="devisen">Devisen</option>';
        newHeader += '<option value="EQSWAPS">EQSWAPS</option>';
        newHeader += '<option value="EQSWAPE16X">EQSWAPE16X</option>';
        newHeader += '<option value="EQSWAPE18MDX">EQSWAPE18MDX</option>';
        newHeader += '<option value="EQSWAPE20MDX">EQSWAPE20MDX</option>';
        newHeader += '<option value="EQSWAPE35AX">EQSWAPE35AX</option>';
        newHeader += '<option value="EQSWAPE37X">EQSWAPE37X</option>';
        newHeader += '<option value="EQSWAPE40X">EQSWAPE40X</option>';
        newHeader += '<option value="qvs_bonds_staticData">Wertpapiere</option>';
        newHeader += '<option value="kurse">Kurse</option>';
        newHeader += '</select>';
        newHeader += '</div></div>';
        newHeader += '<div class="col-sm-1">';
        newHeader += '<div class="custom-control custom-checkbox">';
        newHeader += '<input type="checkbox" class="custom-control-input" id="kurseCheck_' + i + '">';
        newHeader += '<label class="custom-control-label" for="kurseCheck_' + i + '">Kurse</label>';
        newHeader += '</div></div>';
        newHeader += '</div>';

      }

      $("#multipleTabs").html(newHeader);

    } else if (tabs == 1) {
      $("#multipleTabs").html("");
    }

  });

/**************************************************************************************************************************************************************************
* submitETLFile > extractHeader
**************************************************************************************************************************************************************************/

  /*
  * Before submitting the form (uploading file) / creating the Mapping upon success
  */
  $('#submitETLFile').click(function(event) {
  	event.preventDefault();
    var file = '.' + $("#file").val().toLowerCase();
    file = file.split('.').pop();

		if (file === '.xls') {
      file = '.xls';
    } else if (file === '.xlsx') {
      file = '.xlsx';
    }

		$("#response").attr("class", "");
		$("#response").html("");

    var response;
		var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+("	+ file + ")$");
		if (!regex.test($("#file").val().toLowerCase())) {
			$("#response").addClass("error");
			$("#response").addClass("display-block");
			$("#response").html("Ungültige Datei. Eine: <b>" + file + "</b> Datei hochladen.");
      response = false;
			return response;
		}

    // var patternCheck = regexFilenamePatternCheck(fileName);
    // if (!patternCheck) {
    //   $("#response").addClass("error");
		// 	$("#response").addClass("display-block");
		// 	$("#response").html("Ungültiges Pattern. Ein gültiges Pattern ist im folgenden Format: <b>yymmdd_Market_Data_*</b> oder <b>yymmdd_Master_Data_*</b>");
    //   response = false;
    //   return response;
    // }

    response = true;

    if (response) {

      $('#action').val('extractHeader');
      var form = $('#clientFormETL')[0];
      $.ajax({
        url: "/php/process.php",
        method: "POST",
        data: new FormData(form),
        contentType: false,          // The content type used when sending data to the server.
        cache: false,                // To unable request pages to be cached
        processData: false,          // To send DOMDocument or non processed data file it is set to false
        dataType: "json",
        success: function(data) {
          var response;
          if (Array.isArray(data)) {
            createMappingTable(data); // create mapping table
            response = 'Erfolgreich aktualisiert';
            $('#nav-mapping-tab')[0].click();
            var etlName = $('#etlFilenames').val();
            $('#ETLName').text(etlName);
            var tableName = $('#dbTable_0').val();
            $('#table_0').html('&nbsp Tabelle: <b>' + tableName + '</b>');
          } else {
            response = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut! Fehlermeldung: ' + data;
          }
          alert(response);
        }
      })

      // get the table from the selected name
      var table = $('#dbTable_0').val();
      anzahlTables = $('#tabs').val();

      // get all the required tables for the worksheets
      var tables = [];
      for (var i = 0; i < anzahlTables; i++) {
        tables.push($('#dbTable_' + i).val());
      }

      // if kurse is checked, then add the table 'kurse' to extract header from the db table
      // hardcoded 4 kurs tables allowed
      if ($('#kurseCheck_0').prop('checked') || $('#kurseCheck_1').prop('checked') || $('#kurseCheck_2').prop('checked') || $('#kurseCheck_3').prop('checked')) {
        tables.push('kurse');
      }

      // get the table from the filename
      // if (fileName.includes('Zinskurven')) {
      //   table = 'zsk';
      // } else if (fileName.includes('Spreadkurven')) {
      //   table = 'spreads';
      // } else if (fileName.includes('Fxspots') || fileName.includes('Fxfwdpoints')) {
      //   table = 'devisen';
      // } else if (fileName.includes('Swaps')) {
      //   table = 'swaps';
      // } else {
      //   //if no table can be found, give out an error
      //   $("#mappingError").addClass("error");
  		// 	$("#mappingError").addClass("display-block");
  		// 	$("#mappingError").html("Eine Tabelle konnte nich zugeordnet werden. Stellen Sie sicher, dass die Datei folgende Namen enthält:<br><b>Zinskurven, Spreadkurven, fxSpots, fxfwdPoints, WP oder Swaps</b>.<br>Gehen Sie zurück zum Tab 'Datei und laden Sie erneut hoch.'")
      //   $("#saveMapping").hide();
      //   $("#navigateToSummary").hide();
      //   $("#saveETLSettings").hide();
      // $("#addNewField").hide();
      // $("#showDBFields").hide();
      // }

      // only make a POST Request, if a table can be found in the filename
      if (table) {
        $("#mappingError").html('');
        $("#addNewField").show();
        $("#showDBFields").show();
        $("#saveMapping").show();
        $("#navigateToSummary").show();
        $("#saveETLSettings").show();

        $.ajax({
          url: '/php/process.php',
          method: "POST",
          data: { processType:processType, action:'getDBHeaders', tables:tables },
          dataType: "json",
          success: function(data) {
            console.log(data);
            getDatabaseHeaders(data); // create mapping table
          }
        })
      }

    }
  });

/**************************************************************************************************************************************************************************
* Mapping
**************************************************************************************************************************************************************************/

  /*
  * Create final mapping as an object and display in the ETL Filter Section as a HTML table
  */
  $('#saveMapping').click(function() {
    createMapping('Mapping');
    // if kurse is checked, then also create a mapping for kurse
    if ($('#kurseCheck_0').prop('checked') || $('#kurseCheck_1').prop('checked') || $('#kurseCheck_2').prop('checked') || $('#kurseCheck_3').prop('checked')) {
      createKurseMapping("KurseMapping");
    }

    // get Transformations from the database and display in dropdown
    $.ajax({
      url: '/php/process.php',
      method: "POST",
      data: { processType:processType, action:'getTransformations' },
      dataType: "json",
      success: function(data) {
        fillTransformations();
      }
    })
    $('#nav-etl-filter-tab')[0].click();
  });

  /*
  * Before submitting the form (uploading file) / creating the Mapping upon success
  */
  $('#saveETLSettings').click(function(event) {
  	event.preventDefault();
    $('#action').val('saveETLSettings');
    var form = $('#clientFormETL')[0];
    $.ajax({
      url: "/php/process.php",
      method: "POST",
      data: new FormData(form),
      contentType: false,          // The content type used when sending data to the server.
      cache: false,                // To unable request pages to be cached
      processData: false,          // To send DOMDocument or non processed data file it is set to false
      dataType: "json",
      success: function(data) {
        alert(data);
        etlFilenamesData.ajax.reload();
        //window.location.reload();
      }
    })
  });

  /*
  * Adding a new filed in the Mapping table
  */
  $('#addNewField').click(function() {
    var table = document.getElementById("mappingTable_0");
    var rowCount = table.rows.length; // holt sich die letzte Zeile (Anzahl der existierenden Zeilen)
    var row = table.insertRow(rowCount);

    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    countFields++;

    cell1.innerHTML = '<input type="text" class="form-control" id="newField_' + countFields + '" name="newField_' + countFields + '">';
    cell2.innerHTML = '<input class="form-control ui-autocomplete-input" id="newFieldDB_' + countFields + '" name="newFieldDB_' + countFields + '">';
    // add autocomplete function
    $( "#editMappingNewField_" + countFields ).autocomplete({
      source: databaseHeaders[0]
    });
  });

  /*
  * Adding a new field in the Mapping table in the edit section
  */
  $('#addNewMappingField').click(function() {
    var field = prompt("Bitte Feldname eingeben:");

    if ( field ) {
      var cleaned_field = field.replace(/ /g,"_");

      var table = document.getElementById("editMappingTable");
      var rowCount = table.rows.length; // holt sich die letzte Zeile (Anzahl der existierenden Zeilen)
      var row = table.insertRow(rowCount);

      var cell1 = row.insertCell(0);
      var cell2 = row.insertCell(1);

      cell1.innerHTML = cleaned_field;
      cell2.innerHTML = '<input class="form-control ui-autocomplete-input" id="newMappingFieldDB_' + cleaned_field + '" name="newMappingFieldDB_' + cleaned_field + '">';

      // add autocomplete function
      $( "#editSectionMappingNewField_" + countFields ).autocomplete({
        source: databaseHeaders[0]
      });
    }
  });

  /*
  * Adding a new field in the Tarnsformation table in the edit section
  */
  $('#addNewTransformationField').click(function() {
    var field = prompt("Bitte Feldname eingeben:");

    if ( field ) {
      var cleaned_field = field;

      if (/^ *$/.test(field)) {
        cleaned_field = field.replace(/ /g, "-space-");
      }
      // double check for whitespace character
      if (/\s/.test(field)) {
        cleaned_field = field.replace(/ /g, "-space-");
      }
      // if value contains a forward slash
      if(field.indexOf("/") > -1) {
        cleaned_field = field.replace(/\//g, "-dash-");
      }
      // if value contains a percent
      if(field.indexOf("%") > -1) {
        cleaned_field = field.replace(/%/g, "etlPercent");
      }
      // if value contains a plus
      if(field.indexOf("+") > -1) {
        cleaned_field = field.replace(/\+/g, "-plus-");
      }
      // if value contains a dot
      if(field.indexOf(".") > -1) {
        cleaned_field = field.replace(/\./g, "-dot-");
      }

      var table = document.getElementById("editETLFilterTable");
      var rowCount = table.rows.length; // holt sich die letzte Zeile (Anzahl der existierenden Zeilen)
      var row = table.insertRow(rowCount);

      var cell1 = row.insertCell(0);
      var cell2 = row.insertCell(1);

      cell1.innerHTML = cleaned_field;
      cell2.innerHTML = '<select class="form-control transformations" id="newTransformationFieldDB_' + cleaned_field + '" name="editTransformationDB_' + cleaned_field + '"></select>';

      // append dropdown values to #newTransformationFieldDB_*
      var options = $("#ETLFilter").html();
      $('#newTransformationFieldDB_'+ cleaned_field).append(options);
    }

  });

  /*
  * Adding a new field in the Kurse Mapping table in the edit section
  */
  $('#addNewMappingFieldKurse').click(function() {
    var field = prompt("Bitte Feldname eingeben:");

    if ( field ) {
      var cleaned_field = field.replace(/ /g,"_");

      var table = document.getElementById("editKurseMappingTable");
      var rowCount = table.rows.length; // holt sich die letzte Zeile (Anzahl der existierenden Zeilen)
      var row = table.insertRow(rowCount);

      var cell1 = row.insertCell(0);
      var cell2 = row.insertCell(1);

      cell1.innerHTML = cleaned_field;
      cell2.innerHTML = '<input class="form-control ui-autocomplete-input" id="newKurseMappingFieldDB_' + cleaned_field + '" name="newKurseMappingFieldDB_' + cleaned_field + '">';
      // add autocomplete function
      // $( "#editSectionKurseMappingNewField_" + countFields ).autocomplete({
      //   source: databaseHeaders[0]
      // });
    }
  });

  /*
  * Adding a new field in the Kurse Transformation table in the edit section
  */
  $('#addNewTransformationFieldKurse').click(function() {
    var field = prompt("Bitte Feldname eingeben:");

    if ( field ) {
      var cleaned_field = field;

      if (/^ *$/.test(field)) {
        cleaned_field = field.replace(/ /g, "-space-");
      }
      // double check for whitespace character
      if (/\s/.test(field)) {
        cleaned_field = field.replace(/ /g, "-space-");
      }
      // if value contains a forward slash
      if(field.indexOf("/") > -1) {
        cleaned_field = field.replace(/\//g, "-dash-");
      }
      // if value contains a percent
      if(field.indexOf("%") > -1) {
        cleaned_field = field.replace(/%/g, "etlPercent");
      }
      // if value contains a plus
      if(field.indexOf("+") > -1) {
        cleaned_field = field.replace(/\+/g, "-plus-");
      }
      // if value contains a dot
      if(field.indexOf(".") > -1) {
        cleaned_field = field.replace(/\./g, "-dot-");
      }

      var table = document.getElementById("editKurseETLFilterTable");
      var rowCount = table.rows.length; // holt sich die letzte Zeile (Anzahl der existierenden Zeilen)
      var row = table.insertRow(rowCount);

      var cell1 = row.insertCell(0);
      var cell2 = row.insertCell(1);

      cell1.innerHTML = cleaned_field;
      cell2.innerHTML = '<select class="form-control transformations" id="newKurseTransformationField_' + cleaned_field + '" name="editKurseTransformation_' + cleaned_field + '"></select>';

      // append dropdown values to #newKurseTransformationField_*
      var options = $("#ETLFilter").html();
      $('#newKurseTransformationField_'+ cleaned_field).append(options);
    }

  });

  /*
  * Creating drag & drop options
  */
  // Sortable.create(fileMapping, {
  //   animation: 100,
  //   group: 'list-1',
  //   draggable: '.list-group-item',
  //   handle: '.list-group-item',
  //   sort: true,
  //   filter: '.sortable-disabled',
  //   chosenClass: 'active'
  // });

  // Sortable.create(databaseMapping, {
  //   group: 'list-1',
  //   handle: '.list-group-item'
  // });

/**************************************************************************************************************************************************************************
* Summary
**************************************************************************************************************************************************************************/

  /*
  * Creates final summary page
  */
  $('#navigateToSummary').click(function() {
    createSummaryTable();
    $('#nav-summary-tab')[0].click();
	});

});

/**************************************************************************************************************************************************************************/
//      Edit & Update ETL Settings Section
/**************************************************************************************************************************************************************************/

function submitEditETLForm(){
  createMapping('');
  createKurseMapping('');
  // saving our Mapping to a hidden textarea
  $('#resultEditMapping').val(JSON.stringify(resultMapping));
  $('#resultEditKurseMapping').val(JSON.stringify(resultKurseMapping));

  var form = $('#editETLForm').serialize()
  $.ajax({
    type: "POST",
    url: '/php/process.php',
    data: form,
    success: function(e) {
      alert(e);
      // etlFilenamesData.ajax.reload();
    }
  });
}

/**************************************************************************************************************************************************************************/
//      Javascript functions
/**************************************************************************************************************************************************************************/

/**
* @brief displayETLFilenamesDropdown()
* @param data (Übergabe der Daten, die angezeigt werden sollen) displayAction definiert ob aktualisiert wird oder nicht
* @return Dropdownwerte der Kunden
* @details Zeigt eine HTML formatierte Ansicht der checkboxes der Benutzerrechte im Dialogfenster beim
* hinzufügen oder editieren eines
*/
function displayETLFilenamesDropdown(data, displayAction) {
  $("#etlFilenames").empty();
  var option = '';
  if (displayAction === "aktualisieren") {
    var i = 1;
  } else {
    var i = 0;
  }
  for (i; i < data.length; i++){
     option += '<option id="'+ data[i].transformation_id + '" value="'+ data[i].name + '">' + data[i].name + '</option>';
  }
  $('#etlFilenames').append(option);
}

/**
* @brief createMappingTable()
* @param data
* @return -
* @details creates a mapping table / inserts the data (headers) we fetched from the input file
*/
function createMappingTable(data) {

  for (i=0; i < data.length; i++){
    var table = $( "#dbTable_" + i ).val();

    if (i == 0) {

      $.each(data[i], function (key, value) {
        if (value !== null) {
          var cleaned_value = value;
          cleaned_value = (cleaned_value === '') ? 'null' :cleaned_value;

          // if value has space
          if (/^ *$/.test(cleaned_value)) {
            cleaned_value = value.replace(/ /g, "_");
          }
          // double check for whitespace character
          if (/\s/.test(cleaned_value)) {
            cleaned_value = cleaned_value.replace(/ /g, "_");
          }
          // if value contains a forward slash
          if(cleaned_value.indexOf("/") > -1) {
            cleaned_value = cleaned_value.replace(/\//g, "-dash-");
          }
          // if value contains a percent
          if(cleaned_value.indexOf("%") > -1) {
            cleaned_value = cleaned_value.replace(/%/g, "");
          }
          // if value contains a plus, minus & dot
          if(cleaned_value.indexOf("+") > -1) {
            cleaned_value = cleaned_value.replace(/\+/g, "");
          }
          if(cleaned_value.indexOf(".") > -1) {
            cleaned_value = cleaned_value.replace(/\./g, "");
          }

          var dbHeaderTag = "tag_" + cleaned_value;
          //The HTML of the TR row that we want to add to our table.
          var newTableRow = '<tr><td>' + value + '</td><td><input class="form-control" id="' + dbHeaderTag + '_' + table + '"></td></tr>';

          //Add the HTML after the last row by using tr:last.
          $('#mappingTable_0 tr:last').after(newTableRow);

          /*
          * Autocomplete Database Headings
          */
          $( "#" + dbHeaderTag + '_' + table ).autocomplete({
            source: databaseHeaders[i]
          });

          // fill Kurse Mapping Table, only if Kurse checkbox is checked
          if ($('#kurseCheck_0').prop('checked')) {
            $('#kurseTableName_0').html('&nbsp Tabelle: <b>kurse > ' + table + '</b>');
            $('#mappingTableKurse_0').show();
            var kurseTableRow = '<tr><td>' + value + '</td><td><input class="form-control" id="' + dbHeaderTag + '_kurse_' + table + '"></td></tr>';
            $('#mappingTableKurse_0 tr:last').after(kurseTableRow);

            // get the last element saved in databaseHeaders, as kurse dbHeaders are stored there
            var kurseLength = databaseHeaders.length -1;
            /*
            * Autocomplete Database Headings for Kurse
            */
            $( "#" + dbHeaderTag + '_kurse_' + table ).autocomplete({
              source: databaseHeaders[kurseLength]
            });
          }

        }

      });

    } else {

      // create table and append
      var content = "<hr>";
      content += "&nbsp Tabelle: <b>" + table + "</b>";
      content += "<table>";
      content += '<table class="table table-bordered customTable" id="mappingTable_' + i + '">';
      content += '<thead><tr><th>Felder (Datei)</th><th>Felder (Datenbank)</th></tr></thead>';
      content += '<tbody>';
      content += '</tbody>';
      content += "</table>";

      $('#multiTable').append(content);

      // create kurse table and append
      if ($('#kurseCheck_' + i).prop('checked')) {
        kurseHeader = "&nbsp Tabelle: <b>kurse > " + table + "</b>";
        var content = "<hr>";
        content += kurseHeader;
        content += "<table>";
        content += '<table class="table table-bordered customTable" id="mappingTableKurse_' + i + '">';
        content += '<thead><tr><th>Felder (Datei)</th><th>Felder (Datenbank)</th></tr></thead>';
        content += '<tbody>';
        content += '</tbody>';
        content += "</table>";

        $('#multiKurseTable').append(content);
      }

      $.each(data[i], function (key, value) {
        if (value !== null) {
          var cleaned_value = value;
          cleaned_value = (cleaned_value === '') ? 'null' :cleaned_value;
          // if value has space
          if (/^ *$/.test(cleaned_value)) {
            cleaned_value = value.replace(/ /g,"_");
          }
          // double check for whitespace character
          if (/\s/.test(cleaned_value)) {
            cleaned_value = cleaned_value.replace(/ /g, "_");
          }
          // if value contains a forward slash
          if(cleaned_value.indexOf("/") > -1) {
            cleaned_value = cleaned_value.replace(/\//g, "-dash-");
          }
          // if value contains a percent
          if(cleaned_value.indexOf("%") > -1) {
            cleaned_value = cleaned_value.replace(/%/g, "");
          }

          var dbHeaderTag = "tag_" + cleaned_value;

          //The HTML of the TR row that we want to add to our table.
          var newTableRow = '<tr><td>' + value + '</td><td><input class="form-control" id="' + dbHeaderTag + '_' + table + '"></td></tr>';
          //Add the HTML after the last row by using tr:last.
          $('#mappingTable_' + i + ' tr:last').after(newTableRow);
          /*
          * Autocomplete Database Headings
          */
          $( "#" + dbHeaderTag + '_' + table ).autocomplete({
            source: databaseHeaders[i]
          });

          // fill Kurse Mapping Table, only if Kurse checkbox is checked
          if ($('#kurseCheck_' + i).prop('checked')) {
            var kurseTableRow = '<tr><td>' + value + '</td><td><input class="form-control" id="' + dbHeaderTag + '_kurse_' + table + '"></td></tr>';
            $('#mappingTableKurse_' + i + ' tr:last').after(kurseTableRow);

            // get the last element saved in databaseHeaders, as kurse dbHeaders are stored there
            var kurseLength = databaseHeaders.length -1;
            /*
            * Autocomplete Database Headings for Kurse
            */
            $( "#" + dbHeaderTag + '_kurse_' + table ).autocomplete({
              source: databaseHeaders[kurseLength]
            });
          }

        }

      });

    }

  }
}

/**************************************************************************************************************************************************************************/

/**
* @brief getDatabaseHeaders()
* @param data
* @return -
* @details get the Headers from the database for given tables and save to databaseHeaders
*/
function getDatabaseHeaders(data) {
  const div = document.querySelector('.dropdown-menu');
  var excludeFields = ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'currency_id', 'skadenz_id', 'sector_id', 'subsector_id', 'rating_id', 'filename', 'status'];
  var dbHeaders = [];

  for (var i = 0; i < data.length; i++) {

    var arrayLength = data[i].length;
    databaseHeaders[i] = [];
    var index = 0;

    for (var j = 0; j < arrayLength; j++) {

      var removeDatatypes = data[i][j].substring(0, data[i][j].indexOf(' ['));

      if (excludeFields.includes(removeDatatypes)) {
        if ( j == 0 ) {
          index = j;
        }
      } else {
        var value = data[i][j];
        databaseMappingTypes.push(value);
        value = value.substring(0, value.indexOf(' ['));
        div.innerHTML += `<a class="dropdown-item disabled" href="#">` + value + `</a>`;
        databaseHeaders[i][index] = value;
        index++;
      }

    }
  }



}

/**
* @brief createMapping()
* @param mapping - whether a new or a an existing mapping
* @return -
* @details iterates through table cells and fetch value by id/cell and save it as a object
*/
function createMapping(mapping) {
  // iterate through table cell and get id and then the value of both cells in the table row
  var header;

  if ( mapping === 'Mapping' ) {
    var table;

    for (var i = 0; i < anzahlTables; i++) {
      table = 'mappingTable'
      table += '_' + i;
      // reset the array for each table
      fileMappingList = [];
      databaseFilterTypes = [];
      databaseMappingList = [];

      if( $('#' + table).length ) {

        $('#' + table + ' td:nth-child(2)').each(function() {
          var dbHeaders = $(this)[0].innerHTML;
          var startString = dbHeaders.indexOf("id");
          var endString = dbHeaders.indexOf(" autocomplete");
          var res = dbHeaders.substring(startString, endString);
          res = res.replace("id=", "");
          res = res.replace("\"", "");
          res = res.replace(" ", "-");
          var id = res.substring(0, res.length - 1);

          id = '#' + id;
          header = $(id).val();
          if (header) {
            databaseMappingList.push(header);
          }

          // index of header in databaseMappingTypes
          var index = databaseMappingTypes.findIndex(element => element.includes(header));
          // if header indeed exists in databaseMappingTypes
          var headerExists = databaseMappingTypes.find(element => element.indexOf(header));
          var dbHeader;

          // if (index !== 0) {
          if (headerExists) {

            dbHeader = databaseMappingTypes[index];
            // get value of fileMapping
            var fileHeader = $(this).parents('tr:first').find('td:first').text();
            fileMappingList.push(fileHeader);
            databaseFilterTypes.push(dbHeader);

          }

        });

        // creates an object of the mapping we need in the database
        resultMapping =  databaseMappingList.reduce(function(resultMapping, field, index) {
          resultMapping[fileMappingList[index]] = field;
          return resultMapping;
        }, {})

        // creates an object of the mapping we need to display in table (frontend)
        var tableMapping =  databaseFilterTypes.reduce(function(tableMapping, field, index) {
          tableMapping[fileMappingList[index]] = field;
          return tableMapping;
        }, {})

        // creating the ETL-Filter table
        createETLMappingTable(tableMapping, table);

        // saving our Mapping to a hidden textarea
        if (i == 0) {
          $('#resultMapping_0').val(JSON.stringify(resultMapping));
        } else {
          // create hidden textarea for multiple mappings
          var content = '<textarea class="form-control rounded-0" name="resultMapping_' + i + '" id="resultMapping_' + i + '" rows="10" style="display: none;"></textarea>';
          $('#multipleMappings').append(content);
          $('#resultMapping_' + i).val(JSON.stringify(resultMapping));
        }

      }

    }

  } else {

    // reset the array
    resultMapping = [];

    $('#editMappingTable td:nth-child(2)').each(function() {
      var dbHeaders = $(this)[0].innerHTML;
      var startString = dbHeaders.indexOf("id");
      var endString = dbHeaders.indexOf(" name");
      var res = dbHeaders.substring(startString, endString);
      res = res.replace("id=", "");
      res = res.replace("\"", "");
      res = res.replace(" ", "-");
      var id = res.substring(0, res.length - 1);

      id = '#' + id;
      header = $(id).val();
      if (header) {
        databaseMappingList.push(header);
      }

      var index = databaseMappingTypes.findIndex(element => element.includes(header));
      var dbHeader;
      if (index !== 0) {
        dbHeader = databaseMappingTypes[index];

        // get value of fileMapping
        var fileHeader = $(this).parents('tr:first').find('td:first').text();
        fileMappingList.push(fileHeader);
        databaseFilterTypes.push(dbHeader);
      }

    });

    // creates an object of the mapping we need in the database
    resultMapping =  databaseMappingList.reduce(function(resultMapping, field, index) {
      resultMapping[fileMappingList[index]] = field;
      return resultMapping;
    }, {})

  }

}

/**
* @brief createKurseMapping()
* @param kurseMapping - whether a new or a an existing mapping
* @return -
* @details iterates through table cells and fetch value by id/cell and save it as a object
*/
function createKurseMapping(kurseMapping) {
  // iterate through table cell and get id and then the value of both cells in the table row
  var header;
  var table;

  if (kurseMapping === 'KurseMapping') {

    for (var i = 0; i < anzahlTables; i++) {
      table = 'mappingTableKurse'
      table += '_' + i;
      // reset the array for each table
      fileMappingList = [];
      databaseFilterTypes = [];
      databaseMappingList = [];
      var isChecked = 'kurseCheck_' + i;

      // only create table if #kurseCheck_ is checked
      if( $('#' + isChecked).prop('checked') ) {

        $('#' + table + ' td:nth-child(2)').each(function() {
          var dbHeaders = $(this)[0].innerHTML;
          var startString = dbHeaders.indexOf("id");
          var endString = dbHeaders.indexOf(" autocomplete");
          var res = dbHeaders.substring(startString, endString);
          res = res.replace("id=", "");
          res = res.replace("\"", "");
          res = res.replace(" ", "-");
          var id = res.substring(0, res.length - 1);

          id = '#' + id;
          header = $(id).val();
          if (header) {
            databaseMappingList.push(header);
          }

          var index = databaseMappingTypes.findIndex(element => element.includes(header));
          var dbHeader;
          if (index !== 0) {
            dbHeader = databaseMappingTypes[index];

            // get value of fileMapping
            var fileHeader = $(this).parents('tr:first').find('td:first').text();
            fileMappingList.push(fileHeader);
            databaseFilterTypes.push(dbHeader);
          }

        });

        // creates an object of the mapping we need in the database
        resultMapping =  databaseMappingList.reduce(function(resultMapping, field, index) {
          resultMapping[fileMappingList[index]] = field;
          return resultMapping;
        }, {})

        // creates an object of the mapping we need to display in table (frontend)
        var tableMapping =  databaseFilterTypes.reduce(function(tableMapping, field, index) {
          tableMapping[fileMappingList[index]] = field;
          return tableMapping;
        }, {})

        // creating the ETL-Filter table
        createKurseETLMappingTable(tableMapping, table);

        // saving our Mapping to a hidden textarea
        if (i == 0) {
          $('#resultMappingKurse_0').val(JSON.stringify(resultMapping));
        } else {
          // create hidden textarea for multiple mappings
          var content = '<textarea class="form-control rounded-0" name="resultMappingKurse_' + i + '" id="resultMappingKurse_' + i + '" rows="10" style="display: none;"></textarea>';
          $('#multiKurseTable').append(content);
          $('#resultMappingKurse_' + i).val(JSON.stringify(resultMapping));
        }

      }

    }

  } else {

    // reset the array
    resultKurseMapping = [];
    fileMappingList = [];
    databaseFilterTypes = [];
    databaseMappingList = [];

    $('#editKurseMappingTable td:nth-child(2)').each(function() {
      var dbHeaders = $(this)[0].innerHTML;
      var startString = dbHeaders.indexOf("id");
      var endString = dbHeaders.indexOf(" name");
      var res = dbHeaders.substring(startString, endString);
      res = res.replace("id=", "");
      res = res.replace("\"", "");
      res = res.replace(" ", "-");
      var id = res.substring(0, res.length - 1);

      id = '#' + id;
      header = $(id).val();
      if (header) {
        databaseMappingList.push(header);
      }

      var index = databaseMappingTypes.findIndex(element => element.includes(header));
      var dbHeader;
      if (index !== 0) {
        dbHeader = databaseMappingTypes[index];

        // get value of fileMapping
        var fileHeader = $(this).parents('tr:first').find('td:first').text();
        fileMappingList.push(fileHeader);
        databaseFilterTypes.push(dbHeader);
      }

    });

    // creates an object of the mapping we need in the database
    resultKurseMapping =  databaseMappingList.reduce(function(resultKurseMapping, field, index) {
      resultKurseMapping[fileMappingList[index]] = field;
      return resultKurseMapping;
    }, {})
  }

}

/**************************************************************************************************************************************************************************/

/**
* @brief fillTransformations()
* @param -
* @return -
* @details run through Edit/ETL Filter table and fill all dropdown values with transformation values
*/
function fillTransformations() {

  // iterate through table cell and copy and append select options inside table
  // $('#etlTable_0 td:nth-child(2)').each(function() {
  //   var transformationsDropdown = $(this)[0].innerHTML;
  //   var startString = transformationsDropdown.indexOf("id");
  //   var endString = transformationsDropdown.indexOf(" name");
  //   var res = transformationsDropdown.substring(startString, endString);
  //   res = res.replace("id=", "");
  //   res = res.replace("\"", "");
  //   res = res.replace(" ", "-");
  //   transformationsDropdown = res.substring(0, res.length - 1);
  //   //transformationsDropdown = '#' + transformationsDropdown;
  //   var options = $("#ETLFilter").html();
  //   if (transformationsDropdown !== '') {
  //     $('#'+transformationsDropdown).append(options);
  //   }
  // });

  // If there are more than one ETL Filter tables
  for (var i = 0; i < 12; i++) {
    table = 'etlTable'
    table += '_' + i;
    kurseTable = 'etlKurseTable'
    kurseTable += '_' + i;

    if( $('#' + table).length ) {
      // iterate through ETL table cell and copy and append select options inside table
      $('#' + table + ' td:nth-child(2)').each(function() {
        var transformationsDropdown = $(this)[0].innerHTML;
        var startString = transformationsDropdown.indexOf("id");
        var endString = transformationsDropdown.indexOf(" name");
        var res = transformationsDropdown.substring(startString, endString);
        res = res.replace("id=", "");
        res = res.replace("\"", "");
        res = res.replace(" ", "-");
        transformationsDropdown = res.substring(0, res.length - 1);
        var options = $("#ETLFilter").html();
        if (transformationsDropdown !== '') {
          $('#'+transformationsDropdown).append(options);
        }
      });
    }

    if( $('#' + kurseTable).length ) {
      // iterate through kurse ETL table cell and copy and append select options inside table
      $('#' + kurseTable + ' td:nth-child(2)').each(function() {
        var transformationsDropdown = $(this)[0].innerHTML;
        var startString = transformationsDropdown.indexOf("id");
        var endString = transformationsDropdown.indexOf(" name");
        var res = transformationsDropdown.substring(startString, endString);
        res = res.replace("id=", "");
        res = res.replace("\"", "");
        res = res.replace("\+", "");
        res = res.replace("\.", "");
        res = res.replace(" ", "-");
        transformationsDropdown = res.substring(0, res.length - 1);
        var options = $("#ETLFilter").html();
        if (transformationsDropdown !== '') {
          $('#'+transformationsDropdown).append(options);
        }
      });
    }

  }

}

/**
* @brief createETLMappingTable()
* @param data, table
* @return -
* @details creates a tables, based on the mapping selection in the ETL Filter section
*/
function createETLMappingTable(data, table) {
  var etlTable;
  var count = 0;
  var tableName = $( "#dbTable_" + table.slice(-1) ).val();

  // last part is 0 = first table
  if (table.slice(-1) == 0) {
    $("#etlTable_0 tr").remove();
    var headerRow= "<thead><tr><th>Felder (Datei)</th><th>Filter</th><th>Felder (Datenbank)</th></tr></thead>";
    $("#etlTable_0").append(headerRow);
    $("#etlTableHeader").append("&nbsp Tabelle: <b>" + tableName + "</b>");
    etlTable = document.getElementById("etlTable_0");

  } else {
    // create table and append
    var tableNum = table.slice(-1);
    var content = "<hr>";
    content += "&nbsp Tabelle: <b>" + tableName + "</b>";
    content += "<table>";
    content += '<table class="table table-bordered customTable" id="etlTable_' + tableNum + '">';
    content += '<thead><tr><th>Felder (Datei)</th><th>Filter</th><th>Felder (Datenbank)</th></tr></thead>';
    content += '<tbody>';
    content += '</tbody>';
    content += "</table>";

    $('#multiETLTable').append(content);

    etlTable = document.getElementById("etlTable_" + tableNum);
  }

  for (var key in data) {
    var rowCount = etlTable.rows.length;
    var row = etlTable.insertRow(rowCount);

    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    var cell3 = row.insertCell(2);

    // if value has space
    var cleaned_key = key;
    if (/^ *$/.test(cleaned_key)) {
      cleaned_key = value.replace(/ /g, "-space-");
    }
    // double check for whitespace character
    if (/\s/.test(cleaned_key)) {
      cleaned_key = cleaned_key.replace(/ /g, "-space-");
    }
    // if value contains a forward slash
    if(cleaned_key.indexOf("/") > -1) {
      cleaned_key = cleaned_key.replace(/\//g, "-dash-");
    }
    // if value contains a percent
    if(cleaned_key.indexOf("%") > -1) {
      cleaned_key = cleaned_key.replace(/%/g, "etlPercent");
    }
    // if value contains a plus
    if(cleaned_key.indexOf("+") > -1) {
      cleaned_key = cleaned_key.replace(/\+/g, "-plus-");
    }
    // if value contains a dot
    if(cleaned_key.indexOf(".") > -1) {
      cleaned_key = cleaned_key.replace(/\./g, "-dot-");
    }

    cell1.innerHTML = key;
    cell2.innerHTML = '<select class="form-control transformations" id="ETLFilter-' + table + '-' + cleaned_key + '" name="ETLFilter-' + table + '-' + cleaned_key + '"></select>';
    cell3.innerHTML = data[key];

    count++
  }

  // Insert last cell of total amount
  rowCount = etlTable.rows.length;
  row = etlTable.insertRow(rowCount);

  cell1 = row.insertCell(0);
  cell2 = row.insertCell(1);
  cell3 = row.insertCell(2);

  cell1.innerHTML = '<b>Anzahl:</b> ' + count;
  cell3.innerHTML = '<b>Anzahl:</b> ' + count;

}

/**
* @brief createKurseETLMappingTable()
* @param data, table
* @return -
* @details creates a tables, based on the mapping selection in the ETL Filter section
*/
function createKurseETLMappingTable(data, table) {
  var kurseETLTable;
  var count = 0;
  var kurseTableName = $("#kurseTableName_0").html();

  // last part is 0 = first table
  if (table.slice(-1) == 0) {

    $("#etlKurseTable_0").show();
    $("#etlKurseTable_0 tr").remove();
    headerRow= "<thead><tr><th>Felder (Datei)</th><th>Filter</th><th>Felder (Datenbank)</th></tr></thead>";
    $("#etlKurseTable_0").append(headerRow);
    $("#etlKurseTableHeader").append(kurseTableName);
    kurseETLTable = document.getElementById("etlKurseTable_0");

  } else {
    // create table and append
    var tableNum = table.slice(-1);
    var content = "<hr>";
    content += kurseHeader;
    content += "<table>";
    content += '<table class="table table-bordered customTable" id="etlKurseTable_' + tableNum + '">';
    content += '<thead><tr><th>Felder (Datei)</th><th>Filter</th><th>Felder (Datenbank)</th></tr></thead>';
    content += '<tbody>';
    content += '</tbody>';
    content += "</table>";

    $('#multiKurseETLTable').append(content);

    kurseETLTable = document.getElementById("etlKurseTable_" + tableNum);
  }

  for (var key in data) {
    var rowCount = kurseETLTable.rows.length;
    var row = kurseETLTable.insertRow(rowCount);

    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    var cell3 = row.insertCell(2);

    // if value has space
    var cleaned_key = key;
    if (/^ *$/.test(cleaned_key)) {
      cleaned_key = value.replace(/ /g, "-space-");
    }
    // double check for whitespace character
    if (/\s/.test(cleaned_key)) {
      cleaned_key = cleaned_key.replace(/ /g, "-space-");
    }
    // if value contains a forward slash
    if(cleaned_key.indexOf("/") > -1) {
      cleaned_key = cleaned_key.replace(/\//g, "-dash-");
    }
    // if value contains a percent
    if(cleaned_key.indexOf("%") > -1) {
      cleaned_key = cleaned_key.replace(/%/g, "etlPercent");
    }
    // if value contains a plus
    if(cleaned_key.indexOf("+") > -1) {
      cleaned_key = cleaned_key.replace(/\+/g, "-plus-");
    }
    // if value contains a dot
    if(cleaned_key.indexOf(".") > -1) {
      cleaned_key = cleaned_key.replace(/\./g, "-dot-");
    }

    cell1.innerHTML = key;
    cell2.innerHTML = '<select class="form-control transformations" id="ETLFilter-' + table + '-' + cleaned_key + '" name="ETLFilter-' + table + '-' + cleaned_key + '"></select>';
    cell3.innerHTML = data[key];

    count++
  }

  // Insert last cell of total amount
  rowCount = kurseETLTable.rows.length;
  row = kurseETLTable.insertRow(rowCount);

  cell1 = row.insertCell(0);
  cell2 = row.insertCell(1);
  cell3 = row.insertCell(2);

  cell1.innerHTML = '<b>Anzahl:</b> ' + count;
  cell3.innerHTML = '<b>Anzahl:</b> ' + count;

}

/**************************************************************************************************************************************************************************/

/**
* @brief createSummaryTable()
* @param -
* @return -
* @details creates final table, for the summary section
*/
function createSummaryTable() {

  // Iterate through table (less than 12 because we have 12 tables in the database/dropdown)
  for (var i = 0; i < 12; i++) {
    var tableName = $( "#dbTable_" + i ).val();
    var summaryTable = 'summaryTable_' + i;
    var etlTable = 'etlTable_' + i;

    if( $('#' + etlTable).length ) {

      if (i > 0) {
        var content = "<hr>"
        content += "&nbsp Tabelle: <b>" + tableName + "</b>"
        content += "<table>"
        content += '<table class="table table-bordered customTable" id="summaryTable_' + i + '">';
        content += '<thead><tr><th>Felder (Datei)</th><th>Filter</th><th>Felder (Datenbank)</th></tr></thead>';
        content += '<tbody>';
        content += '</tbody>';
        content += "</table>"

        $('#multiSummaryTable').append(content);
      } else {
        $("#summaryTableHeader").append("&nbsp Tabelle: <b>" + tableName + "</b>");
      }

      $("#" + summaryTable + " tr").remove();

      // copy ETL table
      var source = document.getElementById(etlTable);
      var destination = document.getElementById(summaryTable);
      var copy = source.cloneNode(true);
      copy.setAttribute('id', summaryTable);

      destination.parentNode.replaceChild(copy, destination);

      var val = [];
      // iterate through summary table cell and copy the selected transformation and save to array
      $('#' + summaryTable + ' td:nth-child(2)').each(function() {
         var transformationsDropdown = $(this)[0].innerHTML;
         var startString = transformationsDropdown.indexOf("id");
         var endString = transformationsDropdown.indexOf(" name");
         var res = transformationsDropdown.substring(startString, endString);
         res = res.replace("id=", "");
         res = res.replace("\"", "");
         res = res.replace(" ", "-");
         transformationsDropdown = res.substring(0, res.length - 1);

         if (transformationsDropdown !== '') {
           val.push(document.getElementById(transformationsDropdown).value);
         }
      });

      // iterate through summary table cell and paste the selected transformation from the array
      var count = 0;
      $('#' + summaryTable + ' td:nth-child(2)').each(function() {
         $(this).html(val[count]);
         count++
      });
    }

  }
}

/**************************************************************************************************************************************************************************/
/**
* @brief createEditTables()
* @param data
* @return -
* @details creates tables for mapping & transformations as well as for kurse
*/
function createEditTables(data) {

  // parse mapping onto HTML table
  var mapping = data[0].mapping;
  mapping = mapping.replace(/&#34;/g, '"');
  var mappingObj = $.parseJSON(mapping);
  $("#editMappingTable tr").remove();
  var mappingTable = document.getElementById("editMappingTable");
  var table = data[0].table;
  var kurseMapping = data[0].kurse_mapping;
  // var tables = [];
  // tables.push(table);
  // if (kurseMapping) {
  //   tables.push('kurse');
  // }

  // fill Edit Mapping table
  $.each(mappingObj, function(index, item){
    var rowCount = mappingTable.rows.length; // holt sich die letzte Zeile (Anzahl der existierenden Zeilen)
    var row = mappingTable.insertRow(rowCount);

    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);

    cell1.innerHTML = index;
    cell2.innerHTML = '<input class="form-control" id="editMapping_' + item + '" name="editMapping_' + item + '" value="' + item + '">';

    // add autocomplete function
    // unable to create autocomplete function
    // $( "#editMapping_" + item ).autocomplete({
    //   source: databaseHeaders[0]
    // });
  })

  // parse transformations onto HTML table
  var transformationen = data[0].transformationen;
  var transformationenObj = $.parseJSON(transformationen);
  $("#editETLFilterTable tr").remove();
  var transformationsTable = document.getElementById("editETLFilterTable");
  var cleanedItem;

  // fill Edit Transformations Mapping
  $.each(transformationenObj, function(index, item){
    var rowCount = transformationsTable.rows.length; // holt sich die letzte Zeile (Anzahl der existierenden Zeilen)
    var row = transformationsTable.insertRow(rowCount);
    // if index has space
    cleanedIndex = index;
    if (/^ *$/.test(cleanedIndex)) {
      cleanedIndex = index.replace(/ /g, "-space-");
    }
    // double check for whitespace character
    if (/\s/.test(cleanedIndex)) {
      cleanedIndex = cleanedIndex.replace(/ /g, "-space-");
    }
    // if value contains a forward slash
    if(cleanedIndex.indexOf("/") > -1) {
      cleanedIndex = cleanedIndex.replace(/\//g, "-dash-");
    }
    // if value contains a percent
    if(cleanedIndex.indexOf("%") > -1) {
      cleanedIndex = cleanedIndex.replace(/%/g, "etlPercent");
    }
    // if value contains a plus
    if(cleanedIndex.indexOf("+") > -1) {
      cleanedIndex = cleanedIndex.replace(/\+/g, "-plus-");
    }
    // if value contains a dot
    if(cleanedIndex.indexOf(".") > -1) {
      cleanedIndex = cleanedIndex.replace(/\./g, "-dot-");
    }

    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);

    cell1.innerHTML = index;
    cell2.innerHTML = '<select class="form-control transformations" id="editTransformation_' + cleanedIndex + '" name="editTransformation_' + cleanedIndex + '" value="' + item + '"></select>';

    // append dropdown values to #editMapping_*
    var options = $("#ETLFilter").html();
    $('#editTransformation_'+ cleanedIndex).append(options);
    $('#editTransformation_'+ cleanedIndex).val(item);

  })

  // fill Edit Kurse Mapping table & parse Kurse transformations onto HTML table
  if (kurseMapping) {

    kurseMapping = kurseMapping.replace(/&#34;/g, '"');
    var kurseMappingObj = $.parseJSON(kurseMapping);
    $("#editKurseMappingTable tr").remove();
    var kurseMappingTable = document.getElementById("editKurseMappingTable");

    $.each(kurseMappingObj, function(index, item){
      var rowCount = kurseMappingTable.rows.length; // holt sich die letzte Zeile (Anzahl der existierenden Zeilen)
      var row = kurseMappingTable.insertRow(rowCount);

      var cell1 = row.insertCell(0);
      var cell2 = row.insertCell(1);

      cell1.innerHTML = index;
      cell2.innerHTML = '<input class="form-control" id="editKurseMapping_' + item + '" name="editKurseMapping_' + item + '" value="' + item + '">';

      // add autocomplete function
      // unable to create autocomplete function
      // $( "#editKurseMapping_" + item ).autocomplete({
      //   source: databaseHeaders[0]
      // });
    })

    var kurseTransformationen = data[0].kurse_transformationen;
    var kurseTransformationenObj = $.parseJSON(kurseTransformationen);
    $("#editKurseETLFilterTable tr").remove();
    var kurseTransformationsTable = document.getElementById("editKurseETLFilterTable");

    // fill Edit Kurse Transformaions Mapping
    $.each(kurseTransformationenObj, function(index, item){
      var rowCount = kurseTransformationsTable.rows.length; // holt sich die letzte Zeile (Anzahl der existierenden Zeilen)
      var row = kurseTransformationsTable.insertRow(rowCount);
      cleanedIndex = index.replace(/ /g,"_");

      var cell1 = row.insertCell(0);
      var cell2 = row.insertCell(1);

      cell1.innerHTML = index;
      cell2.innerHTML = '<select class="form-control transformations" id="editKurseTransformation_' + cleanedIndex + '" name="editKurseTransformation_' + cleanedIndex + '" value="' + item + '"></select>';

      // append dropdown values to #editMapping_*
      var options = $("#ETLFilter").html();
      $('#editKurseTransformation_'+ cleanedIndex).append(options);
      $('#editKurseTransformation_'+ cleanedIndex).val(item);

    })

  }

  $('#edit-etlFilename').val(data[0].name);
  $('#edit-headerRow').val(data[0].header);
  $('#edit-startRow').val(data[0].row_start);
  $('#edit-kunde').val(data[0].kunde);
  $('#edit-lache').val(data[0].lache);
  $('#edit-ubs').val(data[0].ubs);
  $('#edit-isin').val(data[0].ISIN);
  $('#edit-currency').val(data[0].currency);
  $('#etlEditModal').modal('show');

}

/**************************************************************************************************************************************************************************/

/** DEPRECATED
* @brief createMappingList()
* @param -
* @return -
* @details creates a table, based on the mapping selection in the Mapping section
*/
function createMappingList() {
  // All values inside the #fileMapping by order
  $('#fileMapping li').each(function() {
    var value = $(this)[0].innerText;
    fileMappingList.push(value);
  });

  // creates an object of the mapping we need in the database
  resultMapping =  databaseMappingList.reduce(function(resultMapping, field, index) {
    resultMapping[fileMappingList[index]] = field;
    return resultMapping;
  }, {})

  // merge arrays into one, we need in the database
  // for(item in databaseMappingList) {
  //   resultMapping[fileMappingList[item]] = databaseMappingList[item];
  // }

  // creates an object of the mapping we need to display in table (frontend)
  var tableMapping =  databaseMappingTypes.reduce(function(tableMapping, field, index) {
    tableMapping[fileMappingList[index]] = field;
    return tableMapping;
  }, {})

  // creating the table
  createETLMappingTable(tableMapping);
}

/**
* @brief displayFileMappingList()
* @param data
* @return drag & drop list of the fields to be included in the mapping
* @details drag & drop list of the fields to be included in the mapping
*/
// function displayFileMappingList(data) {
//   $("#fileMapping").empty();
//   var count = 0;
//   $.each(data, function (key, value) {
//       if (value !== null) {
//         value = (value === '') ? 'null' :value;
//         value = value.replace(/ /g,"_");
//         var li = $('<li class="list-group-item" id="' + value + '">' + value + '</li>');
//         $('#fileMapping').append(li);
//         $('#mappingSign').append('<li class="list-group-item">=></li>');
//         count++;
//       }
//
//       for (var i = 0; i < count; i++) {
//         var li = $('<li class="list-group-item" id="' + i + '_null">' + i + '_null</li>');
//         $('#fileMapping').append(li);
//         $('#mappingSign').append('<li class="list-group-item">=></li>');
//       }
//   });
//
// }

/**
* @brief function regexFilenamePatternCheck()
* @param str - a string that needs to be checked
* @return true or false
* @details regex function to check a pattern of yymmdd_Market_Data_* or yymmdd_Master_Data_*
*/
function regexFilenamePatternCheck(str) {
   if (/\b\d{6}(?:_[A-Z][a-z]+){3}\b/.test(str)) {
      return true;
   }
   else {
     return false;
   }
}

/**
* @brief function loadDBHeaders()
* @param -
* @return true or false
* @details Load Database headings: to be displayed in the mappings section
*/
function loadDBHeaders() {
  var f = document.getElementById('csvOrExcelLabel');
  var filename = f.innerHTML;
  var table;

  if (filename.includes('Zinskurven')) {
    table = 'zsk';
  } else if (filename.includes('Spreadkurven')) {
    table = 'spreads';
  } else if (filename.includes('fxSpots') || filename.includes('fxfwdPoints')) {
    table = 'devisen';
  } else if (filename.includes('Swaps')) {
    table = 'swaps';
  }

  $.ajax({
    url: '/php/process.php',
    method: "POST",
    data: { processType:processType, action:'getDBHeaders', table:table },
    dataType: "json",
    success: function(data) {
      displayDatabaseMappingList(data);
    }
  })
}

/**
* @brief displayTransformations()
* @param data
* @return Dropdown values of all the transformations
* @details gets transformations from the databse and displays in a dropdown
*/
function displayTransformations(data) {
  // fill hidden select option
  $("#ETLFilter").empty();
  var option = '';
  for (i = 0; i < data.length; i++) {
     option += '<option id="'+ data[i][0] + '" value="'+ data[i][1] + '">' + data[i][1] + '</option>';
  }
  $('#ETLFilter').append(option);
}
