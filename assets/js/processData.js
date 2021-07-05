/**
* @file processDevisen.js
* @author Tahir M. Malik
* @date 05 Jul 2021
* @update
* @copyright 2021 WebSoft Ops - Tahir M. Malik
* @brief Functions for data processing files
*/

/**
* @brief document on ready function
* @param -
* @return -
* @details on ready functions / datatbles initialization
*/
$(document).ready(function() {

	// Define processType, depending on the page
	var processType = 'listDevisen';

	var devisenData = $('#devisenTable').DataTable({
  	"lengthChange": false,
  	"processing":true,
  	"order":[],
  	"ajax":{
  		url:"/php/process.php",
  		type:"POST",
  		data:{ processType:processType },
  		dataType:"json"
  	},
  	"columnDefs":[
  		{
  			"targets":[0, 8, 9],
  			"orderable":true,
  		},
  	],
  	"pageLength": 10,
		dom: 'Bfrtip',
    buttons: [
      'csv', 'excel'
    ]
  });

	/*
  * Make the Modal draggable
  */
  $("#devisenModal").draggable({
    handle: ".modal-header"
  });

	/*
	*	Datepicker function
	*/
	$.fn.dataTable.ext.search.push (
		function ( settings, data, dataIndex ) {
				// var min = $('#min').datepicker("getDate");
				var min = $('#min').val();
				// var max = $('#max').datepicker("getDate");
				var max = $('#max').val();

				// Datum Spalte
				var startDate = data[2];

				if ( !min && !max ) {
					return true;
				} else if ( !min && startDate <= max ) {
					return true;
				} else if ( !max && startDate >= min ) {
					return true;
				} else if ( startDate >= min && startDate <= max ) {
					return true;
				} else {
					return false;
				}
		}
	);

	$("#min").datepicker({ onSelect: function () { table.draw(); }, changeMonth: true, changeYear: true, dateFormat:"yy-mm-dd"});
	$("#max").datepicker({ onSelect: function () { table.draw(); }, changeMonth: true, changeYear: true, dateFormat:"yy-mm-dd"});
	var table = $('#devisenTable').DataTable();

	// Event listener to the two range filtering inputs to redraw on input
	$('#min, #max').change(function () {
			table.draw();
	});

	/**
	* @brief updating a row
	* @param -
	* @return -
	* @details function to edit selected row
	*/
	$("#devisenTable").on('click', '.update', function() {
		var id = $(this).attr("id");
		var processType = $('#processType').val();
		var action = 'getDevisen';
		$.ajax({
			url:'/php/process.php',
			method:"POST",
			data:{ processType:processType, action:action, id:id },
			dataType:"json",
			success:function(data) {
				$('#id').val(data[0].id);
				$('#text').val(data[0].text);
				$('#dezimal').val(data[0].dezimal);
				$('#datum').val(data[0].datum);
				$('#kurs_quelle').val(data[0].kurs_quelle);
				$('#filename').val(data[0].filename);
				$('#filedate').val(data[0].filedate);
				$('#status').val(data[0].status);
				$('.modal-title').html("<i class='fa fa-user'></i> Devisen aktualisieren");
				$('input[name=save]').val('Aktualisieren');
				$('#action').val('updateDevisen');
				$('#devisenModal').modal('show');
			}
		})
	});

	/**
  * @brief deleting a row
  * @param -
  * @return -
  * @details function to delete a row from the database
  */
  $("#devisenTable").on('click', '.delete', function() {
  	var id = $(this).attr("id");
  	if(confirm("Sind Sie sicher, dass Sie diesen Eintrag löschen wollen?")) {
  		$.ajax({
  			url:"/php/process.php",
  			method:"POST",
  			data:{ processType:'deleteDataSet', table:"devisen", id:id },
  			success:function(data) {
  				devisenData.ajax.reload();
  			}
  		})
  	} else {
  		return false;
  	}
  });

	/**
	* @brief submit form / update
  * @param -
  * @return -
  * @details saving the update on the database
  */
  $("#devisenModal").on('submit','#devisenForm', function(event){
    event.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url:"/php/process.php",
      method:"POST",
      data: formData ,
      dataType:"json",
      success:function(data){
         window.alert(data);
         $('#devisenModal').modal('hide');
         devisenData.ajax.reload();
      }
    })
  });

});
