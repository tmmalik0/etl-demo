<?php
  /**
  * @file process.php
  * @author Tahir M. Malik
  * @date 05 Jul 2021
  * @copyright 2022 Tahir M. Malik
  * @brief Genereschiches PHP Script um verschiedene Prozesse zu verarbeiten
  */
  require_once( dirname( dirname( $_SERVER['DOCUMENT_ROOT'] ) ) . '/inc/autoloader.php' );
  include( $_SERVER['DOCUMENT_ROOT'] . '/php/Connector.php' );
  $user = new cUser();

  if ( isset( $_POST ) ) {
    if ( isset( $_POST['processType'] ) ) {
      $processType = $_POST['processType'];
    } else {
      $processType = '';
    }

      switch ( $processType ) {
      case "login":
        if ( isset( $_POST['email'], $_POST['p'] ) ) {
          $email = filter_var( $_POST['email'], FILTER_SANITIZE_STRING );
          $password = filter_var( $_POST['p'], FILTER_SANITIZE_STRING );
          $remember = ( isset( $_POST['rememberMe'] ) ) ? filter_var( $_POST['rememberMe'], FILTER_SANITIZE_STRING ) : false;

          $response = $user -> login_user( $email, $password, $remember );

          if ( $response === true ) {
            // Login erfolgreich
            exit( header( 'Location: /index.php' ) );
          } else {
            // Login fehlgeschlagen
            exit( header( 'Location: /error.php?error=' . $response ) );
          }
        } else {
          // Die korrekten POST-Variablen wurden nicht zu dieser Seite geschickt.
          echo 'Etwas ist schief gelaufen!';
        }
      break;
      case "getETLConfig":
        getETLConfig();
      break;
      case "extractData":
        readFileContent();
      break;
      case "loadData":
        loadData();
      break;
      case "listSwap":
        displayData( 'swaps' );
      break;
      case "listZsk":
        displayData( 'zsk' );
      break;
      case "listSpreads":
        displayData( 'spreads' );
      break;
      case "listDevisen":
        displayData( 'devisen' );
      break;
      case "listKurse":
        displayData( 'kurse' );
      break;
      case "swapDbTable":
        displayDataFields( 'swapDbTable' );
      break;
      case "zskDbTable":
        displayDataFields( 'zskDbTable' );
      break;
      case "spreadsDbTable":
        displayDataFields( 'spreadsDbTable' );
      break;
      case "devisenDbTable":
        displayDataFields( 'devisenDbTable' );
      break;
      case "currencyDbTable":
        displayDataFields( 'currencyDbTable' );
      break;
      case "ratingDbTable":
        displayDataFields( 'ratingDbTable' );
      break;
      case "sectorDbTable":
        displayDataFields( 'sectorDbTable' );
      break;
      case "subsectorDbTable":
        displayDataFields( 'subsectorDbTable' );
      break;
      case "etlDbTable":
        displayDataFields( 'etlDbTable' );
      break;
      case "nameConventionsDbTable":
        displayDataFields( 'nameConventionsDbTable' );
      break;
      case "listLogSwaps":
        displayData( 'logSwaps' );
      break;
      case "listLogZsk":
        displayData( 'logZsk' );
      break;
      case "listLogSpreads":
        displayData( 'logSpreads' );
      break;
      case "listLogDevisen":
        displayData( 'logDevisen' );
      break;
      case "listLogKurse":
        displayData( 'logKurse' );
      break;
      case "listWertpapiere":
        displayData( 'wertpapiere' );
      break;
      case "listHSBCINKA":
        displayData( 'HSBCINKA' );
      break;
      case "listOptionen":
        displayData( 'optionen' );
      break;
      case "listLogWertpapiere":
        displayData( 'logWertpapiere' );
      break;
      case "listLogHSBCINKA":
        displayData( 'logHSBCINKA' );
      break;
      case "listLogOptionen":
        displayData( 'logOptionen' );
      break;
      case "listEQSWAPE16X":
        displayData( 'EQSWAPE16X' );
      break;
      case "listEQSWAPE18MDX":
        displayData( 'EQSWAPE18MDX' );
      break;
      case "listEQSWAPE20MDX":
        displayData( 'EQSWAPE20MDX' );
      break;
      case "listEQSWAPE35AX":
        displayData( 'EQSWAPE35AX' );
      break;
      case "listEQSWAPE37X":
        displayData( 'EQSWAPE37X' );
      break;
      case "listEQSWAPE40X":
        displayData( 'EQSWAPE40X' );
      break;
      case "listEQSWAPS":
        displayData( 'EQSWAPS' );
      break;
      case "userProfile":
        if ( $_POST['action'] === 'displayUserInformation' ) {
          displayUserInformation();
        } elseif ( $_POST['action'] === 'updateProfilePicture' ) {
          updateProfilePicture();
        } elseif ( $_POST['action'] === 'updateUserProfile' ) {
          updateUserProfile();
        } elseif ( $_POST['action'] === 'loadAvatar' ) {
          loadAvatar();
        }
        if ( $_POST['action'] === 'updatePW' ) {
          updatePW();
        }
      break;
      case "user":
        if( !empty($_POST['action']) && $_POST['action'] === 'displayUsers' ) {
          if ( ( $key = array_search( 'displayUsers', $_POST ) ) !== false ) {
            unset($_POST[$key]);
          }
          displayUsers();
        }
        if(!empty( $_POST['action'] ) && $_POST['action'] === 'addUser' ) {
          addUser();
        }
        if(!empty( $_POST['action'] ) && $_POST['action'] === 'getUser' ) {
          getUser();
        }
        if(!empty( $_POST['action'] ) && $_POST['action'] === 'updateUser' ) {
          updateUser();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'deleteUser' ) {
          deleteUser();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'displayUserRoles' ) {
          displayroles( 'displayUserRoles' );
        }
      break;
      case "roles":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'displayRoles' ) {
          displayroles( '' );
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'addRole' ) {
          addRole();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getRoles' ) {
          getRoles();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateRole' ) {
          updateRole();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'deleteRole' ) {
          deleteRole();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'rolePermission' ) {
          getPermissions();
        }
      break;
      case "etlSettings":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'displayETLFilenames' ) {
          displayETLFilenames();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'displayETLFilenamesDropdown' ) {
          displayETLFilenamesDropdown();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'addETLName' ) {
          addETLName();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'extractHeader' ) {
          extractHeader( $_FILES );
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getDBHeaders' ) {
          getDBHeaders();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getTransformations' ) {
          getTransformations( '' );
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'saveETLSettings' ) {
          saveETLSettings();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getETLInfo' ) {
          getETLInfo();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'deleteETLInfo' ) {
          deleteETLInfo();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateETLInfo' ) {
          updateETLInfo();
        }
      break;
      case "transformations":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'displayTransformations' ) {
          displayTransformations();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'addTransformation' ) {
          addTransformation();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getTransformations' ) {
          getTransformations( 'aktualisieren' );
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateTransformation' ) {
          updateTransformation();
        }
      break;
      case "datenbank":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'addField' ) {
          addField();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateField' ) {
          updateField();
        }
      break;
      case "nameConventions":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'displayNameConventions' ) {
          displayNameConventions();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getNameConvention' ) {
          getNameConvention();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'addNameConvention' ) {
          addNameConvention();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateNameConvention' ) {
          updateNameConvention();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'deleteNameConvention' ) {
          deleteNameConvention();
        }
      break;
      case "swaps":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getSwap' ) {
          getSwap();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateSwap' ) {
          updateSwap();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'deleteSwap' ) {
          deleteSwap();
        }
      break;
      case "wertpapiere":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getWertpapier' ) {
          getWertpapier();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateWertpapier' ) {
          updateWertpapier();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'deleteWertpapier' ) {
          deleteWertpapier();
        }
      break;
      case "hsbcinka":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getHSBCINKA' ) {
          getHSBCINKA();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateHSBCINKA' ) {
          updateHSBCINKA();
        }
      break;
      case "zsk":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getZsk' ) {
          getZsk();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateZsk' ) {
          updateZsk();
        }
      break;
      case "spreads":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getSpreads' ) {
          getSpreads();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateSpreads' ) {
          updateSpreads();
        }
      break;
      case "devisen":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getDevisen' ) {
          getDevisen();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateDevisen' ) {
          updateDevisen();
        }
      break;
      case "kurse":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getKurse' ) {
          getKurse();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateKurse' ) {
          updateKurse();
        }
      break;
      case "EQSWAPE16X":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getEQSWAPE16X' ) {
          getEQSWAPE16X();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateEQSWAPE16X' ) {
          updateEQSWAPE16X();
        }
      break;
      case "EQSWAPE18MDX":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getEQSWAPE18MDX' ) {
          getEQSWAPE18MDX();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateEQSWAPE18MDX' ) {
          updateEQSWAPE18MDX();
        }
      break;
      case "EQSWAPE20MDX":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getEQSWAPE20MDX' ) {
          getEQSWAPE20MDX();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateEQSWAPE20MDX' ) {
          updateEQSWAPE20MDX();
        }
      break;
      case "EQSWAPE35AX":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getEQSWAPE35AX' ) {
          getEQSWAPE35AX();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateEQSWAPE35AX' ) {
          updateEQSWAPE35AX();
        }
      break;
      case "EQSWAPE37X":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getEQSWAPE37X' ) {
          getEQSWAPE37X();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateEQSWAPE37X' ) {
          updateEQSWAPE37X();
        }
      break;
      case "EQSWAPE40X":
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'getEQSWAPE40X' ) {
          getEQSWAPE40X();
        }
        if( !empty( $_POST['action'] ) && $_POST['action'] === 'updateEQSWAPE40X' ) {
          updateEQSWAPE40X();
        }
      break;
      case "deleteDataSet":
        deleteDataSet();
      break;
      }
    }
    exit;
?>
