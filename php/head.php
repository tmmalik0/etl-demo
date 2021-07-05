<head>
<title><?php echo $_SESSION['title']; ?></title>
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
  <meta name="language" content="de" />
  <meta name="robots" content="index, follow, noodp, noydir" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- compiled and minified CSS -->
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <!-- Main styles -->
    <link rel="stylesheet" href="/assets/css/style.css" />
    <!-- Fonts -->
    <link rel="stylesheet" href="/assets/css/font-awesome.min.css" />
    <!-- Datatables & Datepicker Stylesheets -->
    <link rel="stylesheet" href="/assets/css/dataTables.bootstrap.min.css" />
    <link rel="stylesheet" href="/assets/css/datepicker.css" />
    <link rel="stylesheet" href="/assets/css/jquery-ui.css" />

    <!-- jquery & dataTables -->
    <script type="text/JavaScript" src="/assets/lib/jquery-3.5.1.js"></script>
    <script type="text/JavaScript" src="/assets/lib/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="/assets/lib/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" src="/assets/lib/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="/assets/lib/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="/assets/lib/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="/assets/lib/buttons.html5.min.js"></script>
    <script type="text/javascript" src="/assets/lib/jszip.min.js"></script>
    <script type="text/javascript" src="/assets/lib/jquery-ui.js"></script>

    <!-- Individual Scripts -->

    <?php if ($_SESSION['title'] === 'ETL Settings') { ?>
      <script src="/assets/lib/sortable.js"></script>
      <script src="/assets/js/processETLEinstellung.js"></script>
    <?php } ?>

    <?php if ($_SESSION['title'] === 'Data Warehouse') { ?>
      <script src="/assets/js/processDataWarehouse.js"></script>
    <?php } ?>

</head>
