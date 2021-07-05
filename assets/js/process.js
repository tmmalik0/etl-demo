/**
* @file process.js
* @author Tahir M. Zafar
* @date 06 Dez 2019
* @update
* @copyright 2019 Tahir M. Zafar
* @brief Funktionen for data processing
*/

/**
* @brief jQuery Functions
* @param -
* @return -
* @details automated functions
*/
$(document).ready(function() {

  $('#sidebarCollapse').on('click', function () {
		$('#sidebar').toggleClass('active');
	});

  // Dynamische class active für die sidebar
	$('.components li').each(function(){
		var current_title = $(document).attr('title');
		var liText = $(this).text();
    if (liText.indexOf(current_title) > 0 || liText == current_title) {
			 $(this).addClass('active').siblings().removeClass('active');
       if ($(this).parent()) {
         $(this).parent().collapse('toggle');
       }
		}
	});

});


/**
* @brief displayUploadElement()
* @param -
* @return -
* @details Funktion um das Input Element der zu hochgeladene Datei anzeigen
*/
function displayUploadElement() {
  var csvOrExcel = document.getElementsByName('csvOrExcel');
  for(i = 0; i < csvOrExcel.length; i++) {
    if (csvOrExcel[i].checked) {
      var csvOrExcelValue = csvOrExcel[i].value;
      if (csvOrExcelValue === 'CSV') {
        $('#file').attr('accept', '.csv');
      } else {
        $('#file').attr('accept', '.xlsx, .xls');
      }
      document.getElementById('csvOrExcelLabel').innerHTML = csvOrExcelValue.concat(' ', 'Datei auswählen');
    }
  }

  $("#importForm").show();
}
