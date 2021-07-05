/**
* @file processDataWarehouse.js
* @author Tahir M. Zafar
* @date 16 Mar 2020
* @copyright 2020 Tahir M. Zafar
* @brief JS File to upload Files using ETL Process
*/

/**
* @brief document on ready function
* @param -
* @return -
* @details on ready functions / datatbles initialization
*/
$(document).ready(function() {
  let fileName;
  let type;

  /*
  * When file changes, the name gets shown in the input
  */
  $('input[type="file"]').change(function(e){
      fileName = e.target.files[0].name;
			document.getElementById('csvOrExcelLabel').innerHTML = fileName;
			var label = document.getElementById('csvOrExcelLabel');
  });

  /*
  * Before the form gets submitted, a check needs to be made, whether a Mapping exists in the database
  */
  $('#submitFile').click(function(event) {
    event.preventDefault();
    //var fileType = '.' + $("input[name='csvOrExcel']:checked").val().toLowerCase();
    var fileType = '.' + $("#file").val().toLowerCase();
    fileType = fileType.split('.').pop();

    if (fileType === '.xls') {
      fileType = '.xls';
    } else if (fileType === '.xlsx') {
      fileType = '.xlsx';
    }

		$("#response").attr("class", "");
		$("#response").html("");

    var response;
		var regex = new RegExp("([a-zA-Z0-9\s_\\.\-:])+("	+ fileType + ")$");
		if (!regex.test($("#file").val().toLowerCase())) {
			$("#response").addClass("error");
			$("#response").addClass("display-block");
			$("#response").html("Ungültige Datei. Eine: <b>" + fileType + "</b> Datei hochladen.");
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
      $.ajax({
        url:'/php/process.php',
        method:"POST",
        data:{ processType:'getETLConfig', filename:fileName },
        dataType:"json",
        success:function(data) {
          var element = document.getElementById('kundeETL');
          element.innerHTML = data;
          $('#uploadArea').hide();
          $('#extractArea').show();
          if (data === 'Es wurde kein Mapping gefunden!') {
            $('#extractData').hide();
            $('#reload').show();
          }
        }
      })

    }

  });

  /*
  * Starts ETL-Process
  * upon clicking the next button, the file will be uploaded to the database
  */
  $('#extractData').click(function(event) {
    event.preventDefault();
    $('#extractArea').hide();
    $('#loader').show();
    var form = $('#importForm')[0];
    $.ajax({
      url:"/php/process.php",
      method:"POST",
      data:new FormData(form),
      contentType:false,          // The content type used when sending data to the server.
      cache:false,                // To unable request pages to be cached
      processData:false,          // To send DOMDocument or non processed data file it is set to false
      dataType: "json",
      success:function(data) {
        type = data.substr(data.length - 5);
        if (data.includes('Die folgenden Felder müssen alle in der Datei enthalten sein') || data.includes('Fehler')) {
          var element = document.getElementById('kundeETL');
          $('#loader').hide();
          $('#reload').show();
          $('#extractArea').show();
        } else {
          var element = document.getElementById('responseLoader');
          uploadData();
        }
        // data = data.slice(0, -5);
        element.innerHTML = data;
        $('#extractData').hide();
        $('#reload').show();
      }
    })

	});

  /**
* @brief function uploadData()
* @param -
* @return response - if data has been uploaded or not
* @details <details>
*/
  function uploadData() {
    $.ajax({
      url:'/php/process.php',
      method:"POST",
      data:{ processType:'loadData', type:type },
      dataType:"json",
      success:function(data) {
        var element = document.getElementById('kundeETL');
        element.innerHTML = data;
        $('#loader').hide();
        $('#extractArea').show();
      }
    })
  }

  /*
  * Starts ETL-Process
  * upon clicking the next button, the file will be uploaded to the database
  */
  $('#refresh').click(function() {
    location.reload();
  });

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

});
