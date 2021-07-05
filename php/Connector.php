<?php
error_reporting( E_ALL);
ini_set("display_errors", 1);
// include("Logging.php" );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php' );
use Phppot\DataSource;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

/**
* @file Connector.php
* @author Tahir M. Zafar
* @date 11 Feb 2020
* @copyright 2020 Tahir M. Zafar
* @brief Class Connector
* @brief Helper class for establishing PDO connections and making queries.
*/
class Connector {
    private $connection;
    private $statement;
    private $dbc;

    /**
    * Connector constructor.
    *
    * @param string $uri
    * @param string $username
    * @param string $password
    */
    public function __construct() {
      $this->getPDOConnection();
    }
    // public function __construct(string $uri, string $username, string $password) {
    //     $this->connection = new PDO( $uri, $username, $password);
    //     $this->connection->exec("set names utf8" );
    // }

    /**
    * @brief Funktion __destruct()
    * @param -
    * @return -
    * @details Schließt die Datenbankverbindung automatisch durch PHP Garbage Collector
    */
     public function __destruct() {
       $this->connection = null;
     }

    /**
    * Execute the passed query as PDO statement.
    *
    * @param string $query
    */
    public function executeQuery( string $query ) {
      $this->statement = $this->connection->prepare( $query );
      $this->statement->execute();
    }

    /**
    * Fetch results of current statement using passed FETCH_ return type.
    *
    * @param int $returnType see: http://php.net/manual/en/pdo.constants.php
    * @return array
    */
    public function fetchAllResults( int $returnType = PDO::FETCH_ASSOC ) {
      $results = $this->statement->fetchAll( $returnType );
      return $results;
    }

    /**
    * Fetch results of current statement using passed FETCH_ return type.
    *
    * @param int $returnType see: http://php.net/manual/en/pdo.constants.php
    * @return array
    */
    public function fetchResults( int $returnType = PDO::FETCH_OBJ ) {
      $results = [];
      while ( $row = $this->statement->fetch( $returnType ) ) {
        $results[] = $row;
      }
      return $results;
    }

    /**
    * Fetch results of current statement using passed FETCH_ return type.
    *
    * @param int $returnType see: http://php.net/manual/en/pdo.constants.php
    * @return array
    */
    public function fetchInformation( int $returnType = PDO::FETCH_ASSOC, $type ) {
        $results = [];
        switch ( $type ) {
          case "loadAvatar":
            while ( $row = $this->statement->fetch( $returnType ) ) {
                $results[] = 'data:' . $row["photo_type"] . ';base64,' . base64_encode( $row["profile_photo"] ) . '';
            }
          break;
          case "displayUserInformation":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $results[] = $row['user_id'];
              $results[] = $row['username'];
              $results[] = $row['first_name'];
              $results[] = $row['last_name'];
              $results[] = $row['position'];
              $results[] = $row['email'];
              $results[] = 'data:' . $row["photo_type"] . ';base64,' . base64_encode( $row["profile_photo"] ) . '';
            }
          break;
          case "getDBHeaders":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $results[] = $row['Field'] . ' [' . $row['Type'] . ']';
            }
          break;
          case "getTransformations":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['transformation_id'];
              $dataRows[] = $row['name'];
              $dataRows[] = $row['script'];
              $results[] = $dataRows;
            }
          break;
          case "getNameConvention":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['name'];
              $dataRows[] = $row['convention'];
              $results[] = $dataRows;
            }
          break;
          default:
        }
        return $results;
    }

    /**
    * @brief public function fetchLastInsertId()
    * @param -
    * @return last insert ID in database
    * @details returns the last_insert_id from the database
    */
    public function fetchLastInsertId() {
        $results = $this->connection->lastInsertId();
        return $results;
    }

    /**
    * Fetch results of current statement using passed FETCH_ return type.
    *
    * @param int $returnType see: http://php.net/manual/en/pdo.constants.php
    * @return array
    */
    public function fetchMessage( $type ) {
      $message = null;
      switch ( $type ) {
        case "addRole":
          if ( $this->statement->rowCount() === 1 ) {
            $message = 'Eine Rolle mit der Eingabe existiert schon.';
          }
          break;
        case "addUser":
          if ( $this->statement->rowCount() === 1 ) {
            $message = 'Ein Benutzer existiert schon bereits mit dieser Email Adresse.';
          }
          break;
        case "updateProfilePicture":
          if ( $this->statement->rowCount() === 1 ) {
            $message = 'Bild wurde erfolgreich aktualisiert!';
          } else {
            $message = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut!';
          }
          break;
        case "updateUserProfile":
          if ( $this->statement->rowCount() === 1 ) {
            $message = 'Daten wurden erfolgreich aktualisiert!';
          } else {
            $message = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut!';
          }
          break;
        case "getETLConfig":
          if ( $this->statement->rowCount() >= 1 ) {
            $message = 'Ein Mapping wurde gefunden: ';
          } else {
            $message = 'Es wurde kein Mapping gefunden!';
          }
          break;
        case "getMarketDataInfo":
          if ( $this->statement->rowCount() === 1 ) {
            $message = 'Datei erkannt: ';
          } else {
            $message = 'Unbekannte Datei.';
          }
          break;
        case "update":
          /*
          * ON DUPLICATE KEY UPDATE, the affected-rows value per row is 1 if the row is inserted as a new row,
          * 2 if an existing row is updated and 0 if the existing row is set to its current values.
          */
          $message = 'Erfolgreich aktualisiert!';
          break;
        case "getClients":
          if ( $this->statement->rowCount() === 1 ) {
            $message = 'exists';
          }
          break;
        case "loadData":
            $message = $this->statement->rowCount();
          break;
        default:
          if ( $this->statement->rowCount() === 1 ) {
            $message = 'Erfolgreich hinzugefuegt!';
          } else {
            $message = 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut!';
          }
      }

      return $message;

    }

    /**
    * Fetch results of current statement using passed FETCH_ return type.
    *
    * @param int $returnType see: http://php.net/manual/en/pdo.constants.php
    * @param int $type, whether
    * @return array
    */
    public function fetchDataTablesRequest( int $returnType = PDO::FETCH_ASSOC, $type ) {
        $results = [];
        switch ( $type ) {
          case "data":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['id'];
              $dataRows[] = $row['text'];
              $dataRows[] = $row['dezimal'];
              $dataRows[] = $row['datum'];
              $dataRows[] = $row['filename'];
              $dataRows[] = $row['filedate'];
          		$dataRows[] = $row['status'];
          		$dataRows[] = $row['db_status'];
          		$dataRows[] = $row['created_at'];
          		$dataRows[] = $row['created_by'];
          		$dataRows[] = $row['updated_at'];
          		$dataRows[] = $row['updated_by'];
              $dataRows[] = '<button type="button" name="update" id="'.$row["id"].'" class="btn btn-info update" title="Aktualisieren"><span class="fa fa-edit"></button>';
              $dataRows[] = '<button type="button" name="delete" id="'.$row["id"].'" class="btn btn-danger delete" title="Löschen"><span class="fa fa-trash"></button>';
              $results[] = $dataRows;
            }
            break;
          case "displayUsers":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['user_id'];
              $dataRows[] = ucfirst( $row['username'] );
              $dataRows[] = $row['email'];
              $dataRows[] = '<button type="button" name="update" id="'.$row["user_id"].'" class="btn btn-info update" title="Aktualisieren"><span class="fa fa-edit"></button>';
              $dataRows[] = '<button type="button" name="delete" id="'.$row["user_id"].'" class="btn btn-danger delete" title="Löschen"><span class="fa fa-trash"></button>';
              $results[] = $dataRows;
            }
            break;
          case "loadETLFilenames":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['name'];
              $dataRows[] = $row['filename'];
              $dataRows[] = $row['kunde'];
              $dataRows[] = $row['table'];
              $dataRows[] = $row['header'];
              $dataRows[] = $row['row_start'];
              $dataRows[] = $row['lache'];
              $dataRows[] = $row['kurse'];
              $dataRows[] = '<button type="button" name="update" id="'.$row["id"].'" class="btn btn-info update" title="Aktualisieren"><span class="fa fa-edit"></button>';
              $dataRows[] = '<button type="button" name="delete" id="'.$row["id"].'" class="btn btn-danger delete" title="Löschen"><span class="fa fa-trash"></button>';
              $results[] = $dataRows;
            }
            break;
          case "displayRoles":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['role_id'];
              $dataRows[] = $row['role_name'];
              $dataRows[] = '<button type="button" name="update" id="'.$row["role_id"].'" class="btn btn-info update" title="Aktualisieren"><span class="fa fa-edit"></span></button>';
              $dataRows[] = '<button type="button" name="delete" id="'.$row["role_id"].'" class="btn btn-danger delete" title="Löschen"><span class="fa fa-trash"></button>';
              $results[] = $dataRows;
            }
            break;
          case "getPermissions":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['perm_id'];
              $dataRows[] = $row['perm_desc'];
              $results[] = $dataRows;
            }
            break;
          case "displayUserInformation":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['user_id'];
              $dataRows[] = $row['username'];
              $dataRows[] = $row['first_name'];
              $dataRows[] = $row['last_name'];
              $dataRows[] = $row['position'];
              $dataRows[] = $row['email'];
              $dataRows[] = 'data:' . $row["photo_type"] . ';base64,' . base64_encode( $row["profile_photo"] ) . '';
              $results[] = $dataRows;
            }
            break;
          case "displayTransformations":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['transformation_id'];
              $dataRows[] = $row['name'];
              $dataRows[] = $row['script'];
              $dataRows[] = '<button type="button" name="update" id="' . $row["transformation_id"] . '" class="btn btn-info update" title="Aktualisieren"><span class="fa fa-edit"></span></button>';
              $results[] = $dataRows;
            }
            break;
          case "dbTableFields":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['Field'];
              $dataRows[] = $row['Type'];
              $dataRows[] = $row['Null'];
              $dataRows[] = $row['Key'];
              $dataRows[] = $row['Default'];
              $dataRows[] = $row['Extra'];
              $dataRows[] = '<button type="button" name="update" id="' . $row["Table"] . '-' . $row["Field"] . '-' . $row["Type"] . '-' . $row["Null"] . '-' . $row["Default"] . '" class="btn btn-info update" title="Aktualisieren"><span class="fa fa-edit"></span></button>';
              $results[] = $dataRows;
            }
            break;
          case "displayNameConventions":
            while ( $row = $this->statement->fetch( $returnType ) ) {
              $dataRows = array();
              $dataRows[] = $row['name'];
              $dataRows[] = $row['convention'];
              $dataRows[] = '<button type="button" name="update" id="' . $row["id"] . '" class="btn btn-info update" title="Aktualisieren"><span class="fa fa-edit"></span></button>';
              $dataRows[] = '<button type="button" name="delete" id="'.$row["id"].'" class="btn btn-danger delete" title="Löschen"><span class="fa fa-trash"></button>';
              $results[] = $dataRows;
            }
            break;
          default:
            // while ( $row = $this->statement->fetch( $returnType ) ) {
            //   $dataRows = array();
            //   $dataRows[] = $row['filename'];
            //   $dataRows[] = '<button type="button" name="update" id="'.$row["kunde_id"].'" class="btn btn-warning btn-xs update">Aktualisieren</button>';
           	// 	$dataRows[] = '<button type="button" name="delete" id="'.$row["kunde_id"].'" class="btn btn-danger btn-xs delete" >Löschen</button>';
            //   $results[] = $dataRows;
            // }
        }

        $numRows = $this->statement -> rowCount();

        $output = array(
       		"draw"				      =>	$numRows,
     		  "recordsTotal"  	  =>  $numRows,
       		"recordsFiltered" 	=> 	$numRows,
       		"data"    			    => 	$results
       	);

        return $output;
    }

    /**
    * @brief Private Funktion getPDOConnection()
    * @param -
    * @return -
    * @details Verbindung mit der Datenbank herstellen
    */
    private function getPDOConnection() {
        // Überprüfung, ob die Verbindung !leer ist && ob die Konfigurationsdatei existiert
        if ( $this->connection == NULL ) {
          if ( !file_exists( dirname( dirname( $_SERVER['DOCUMENT_ROOT'] ) ) . '/inc/settings.config.php' ) ) {
            throw new Exception( "No config file found!", 1001 );
          }

          require( dirname( dirname( $_SERVER['DOCUMENT_ROOT'] ) ) . '/inc/settings.config.php' );

          $this->dbc = $databaseETLConfig;

            // Verbindung herstellen
            $dsn = "" .
                $this->dbc['driver'] .
                ":host=" . $this->dbc['host'] .
                ";port=" . $this->dbc['port'] .
                ";dbname=" . $this->dbc['dbname'];
            try {
                $this->connection = new PDO( $dsn, $this->dbc[ 'username' ], $this->dbc[ 'password' ] );
                $this->connection->exec( "set names utf8" );
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } catch( PDOException $e ) {
                echo __LINE__.$e->getMessage();
            }
        }
    }
}

  /**
  * @brief executeStandardQueries()
  * @param -
  * @return -
  * @details automated call for functions, upon calling the class
  */
  function executeStandardQueries() {
    //outputSwapList();

    // Logging::LineSeparator("INVALID PASSWORD" );
    // invalidPassword();
    //
    // Logging::LineSeparator("INVALID DATABASE" );
    // invalidDatabase();
  }

  /**
  * @brief outputSwapList()
  * @param -
  * @return -
  * @details displays the swap list from the DB
  */
  function outputSwapList() {
      try {
          $dbc = new Connector();
          // Prepared statement
          $query = 'SELECT * FROM `log_swaps` ORDER BY `created_at` DESC LIMIT 5';
          // Execute query.
          $dbc->executeQuery( $query );
          // Fetch results of query.
          $results = $dbc->fetchResults();
          // Output results.
          // Logging::Log( $results );
      } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
      }
  }

  /*---------------------------------------------------------------------
  |
  |
  |                       ETL Settings Section
  |
  |
  *-------------------------------------------------------------------*/

  /**
  * @brief displayETLFilenames()
  * @param -
  * @return -
  * @details displays the filenames of the clients as a list from the database
  */
  function displayETLFilenames() {
      try {
          $dbc = new Connector();
          $query = 'SELECT `id`, `name`, `header`, `kunde`, `row_start`, `filename`, `table`, `lache`, `kurse` FROM `etl`';
          $dbc->executeQuery( $query );
          $results = $dbc->fetchDataTablesRequest( PDO::FETCH_ASSOC, 'loadETLFilenames' );
          echo json_encode( $results );
          exit;
          // Logging::Log( $results );
      } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
      }
  }

  /**
  * @brief displayETLFilenamesDropdown()
  * @param -
  * @return -
  * @details displays the client list from the DB as a dropdown
  */
  function displayETLFilenamesDropdown() {
      try {
          $dbc = new Connector();
          $query = 'SELECT `id`, `name` FROM `etl`';
          $dbc->executeQuery( $query );
          $results = $dbc->fetchResults();
          echo json_encode( $results );
          exit;
          // Logging::Log( $results );
      } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
      }
  }


  /**
  * @brief addETLName()
  * @param -
  * @return -
  * @details function to add new name for the ETL to the database
  */
  function addETLName() {
      try {
          $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
          $name = $_POST['etlName'];

          $dbc = new Connector();
          $query = "INSERT INTO `etl` (`name`, `created_by`) VALUES ( '" . $name . "', 'TestBenutzer' )";
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( '' );
          echo json_encode( $results );
          // Logging::Log( $results );
      } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
      }
  }

  /**
  * @brief getETLInfo()
  * @param -
  * @return -
  * @details function to edit the filename & mapping through datatables
  */
  function getETLInfo() {
      try {
        if ( $_POST["id"] ) {
          $dbc = new Connector();
          $query = "SELECT `name`, `header`, `row_start`, `kunde`, `table`, `mapping`, `transformationen`, `kurse_mapping`,
                    `kurse_transformationen`, `lache`, `ubs`, `ISIN`, `currency`
                    FROM `etl` WHERE `id` = '" . $_POST["id"] . "'";
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
        }
        // Logging::Log( $results );
      } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
      }
  }

  /**
  * @brief deleteETLInfo()
  * @param -
  * @return -
  * @details function to delete roles from the datatables
  */
  function deleteETLInfo() {
    $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
      try {
        $dbc = new Connector();
        $query = "DELETE FROM etl WHERE id = " . $_POST["id"] . "";
        $dbc->executeQuery( $query );
        // Logging::Log( $results );
      } catch ( PDOException $exception ) {
        // Output expected PDOException.
        var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
        // Output unexpected Exceptions.
        var_dump( $exception->getMessage() );
      }
  }

  /**
  * @brief updateETLInfo()
  * @param -
  * @return -
  * @details function to update the ETL Settings in the database
  */
  function updateETLInfo() {
    $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
    // print("<pre>".print_r($_POST,true)."</pre>");
      try {
        // Loop through $_POST and get all param for transformations and save off to an array
        foreach ( $_POST as $name => $val ) {
          // transformation fields
          if (strpos( $name, "editTransformation_" ) !== false ) {
            unset( $_POST[$name] );
            $name = substr( $name, 19 );
            // $name = str_replace( '_', ' ', $name );

            // if name contains a space, then add it back
            // frontend uses "-space-"
            if ( strpos( $name, "-space-" ) !== false ) {
              $name = str_replace( '-space-', ' ', $name );
            }
            // if name contains a "%" sign, then add it back include
            // frontend uses "etlPercent"
            if ( strpos( $name, "etlPercent" ) !== false ) {
              $name = str_replace( 'etlPercent', '%', $name );
            }
            // if name contains a "/" sign, then add it back include
            // frontend uses "-dash-"
            if ( strpos( $name, "-dash-" ) !== false ) {
              $name = str_replace( '-dash-', '/', $name );
            }
            // if name contains a "+" sign, then add it back include
            // frontend uses "-plus-"
            if ( strpos( $name, "-plus-" ) !== false ) {
              $name = str_replace( '-plus-', '+', $name );
            }
            // if name contains a "." sign, then add it back include
            // frontend uses "-dot-"
            if ( strpos( $name, "-dot-" ) !== false ) {
              $name = str_replace( '-dot-', '.', $name );
            }

            $transformation[$name] = $val;
          }

          // for new field
          if (strpos( $name, "editTransformationDB_" ) !== false ) {
            unset( $_POST[$name] );
            $name = substr( $name, 21 );

            // if name contains a space, then add it back
            // frontend uses "-space-"
            if ( strpos( $name, "-space-" ) !== false ) {
              $name = str_replace( '-space-', ' ', $name );
            }
            // if name contains a "%" sign, then add it back include
            // frontend uses "etlPercent"
            if ( strpos( $name, "etlPercent" ) !== false ) {
              $name = str_replace( 'etlPercent', '%', $name );
            }
            // if name contains a "/" sign, then add it back include
            // frontend uses "-dash-"
            if ( strpos( $name, "-dash-" ) !== false ) {
              $name = str_replace( '-dash-', '/', $name );
            }
            // if name contains a "+" sign, then add it back include
            // frontend uses "-plus-"
            if ( strpos( $name, "-plus-" ) !== false ) {
              $name = str_replace( '-plus-', '+', $name );
            }
            // if name contains a "." sign, then add it back include
            // frontend uses "-dot-"
            if ( strpos( $name, "-dot-" ) !== false ) {
              $name = str_replace( '-dot-', '.', $name );
            }

            $transformation[$name] = $val;
          }

          // Kurse transformationen
          if (strpos( $name, "editKurseTransformation_" ) !== false ) {
            unset( $_POST[$name] );
            $name = substr( $name, 24 );

            // if name contains a space, then add it back
            // frontend uses "-space-"
            if ( strpos( $name, "-space-" ) !== false ) {
              $name = str_replace( '-space-', ' ', $name );
            }
            // if name contains a "%" sign, then add it back include
            // frontend uses "etlPercent"
            if ( strpos( $name, "etlPercent" ) !== false ) {
              $name = str_replace( 'etlPercent', '%', $name );
            }
            // if name contains a "/" sign, then add it back include
            // frontend uses "-dash-"
            if ( strpos( $name, "-dash-" ) !== false ) {
              $name = str_replace( '-dash-', '/', $name );
            }
            // if name contains a "+" sign, then add it back include
            // frontend uses "-plus-"
            if ( strpos( $name, "-plus-" ) !== false ) {
              $name = str_replace( '-plus-', '+', $name );
            }
            // if name contains a "." sign, then add it back include
            // frontend uses "-dot-"
            if ( strpos( $name, "-dot-" ) !== false ) {
              $name = str_replace( '-dot-', '.', $name );
            }

            $kurseTransformationen[$name] = $val;
          }

        }

        // convert Array to JSON to save into database
        $transformationen = json_encode( $transformation );
        if ( isset( $kurseTransformationen ) ) {
          $kurseTransformationen = json_encode( $kurseTransformationen );
        } else {
          $kurseTransformationen = '{}';
        }

        $dbc = new Connector();
    		$query = "UPDATE `etl`
               		SET `name` = '" . $_POST["edit-etlFilename"] . "', `header` = '" . $_POST["edit-headerRow"] . "', `row_start` = '" . $_POST["edit-startRow"] . "',
                  `kunde` = '" . $_POST["edit-kunde"] . "', `lache` = '" . $_POST["edit-lache"] . "', `mapping` = '" . addslashes( $_POST["resultEditMapping"] ) . "',
                  `kurse_mapping` = '" . addslashes( $_POST["resultEditKurseMapping"] ) . "', `transformationen` = '" . $transformationen . "', `kurse_transformationen` = '" . $kurseTransformationen . "',
                  `updated_by` = '" . $_SESSION["username"] . "' WHERE `id` ='" . $_POST["id"] . "'";
    		$dbc->executeQuery( $query );
        $results = $dbc->fetchMessage( 'update' );
        echo json_encode( $results );
        // Logging::Log( $results );
      } catch ( PDOException $exception ) {
        // Output expected PDOException.
        var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
        // Output unexpected Exceptions.
        var_dump( $exception->getMessage() );
      }
  }


  /*---------------------------------------------------------------------
  |
  |
  |                       Swap Section
  |
  |
  *-------------------------------------------------------------------*/
  /**
  * @brief displayData()
  * @param -
  * @return -
  * @details function to display user Information from datatables
  */
  function displayData( $type ) {
      try {
        $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
        if ( $type === 'swaps' ) {
          $query = "SELECT * FROM swaps AS s";
          $searchField1 = 's.swap_id';
          $searchField2 = 's.segment_id';
          $searchField3 = 's.contract_type';
        } elseif ( $type === 'zsk' ) {
          $query = "SELECT * FROM zsk AS s";
          $searchField1 = 's.datum';
          $searchField2 = 's.currency_id';
          $searchField3 = 's.skadenz_id';
        } elseif ( $type === 'spreads' ) {
          $query = "SELECT * FROM spreads AS s";
          $searchField1 = 's.datum';
          $searchField2 = 's.sector_id';
          $searchField3 = 's.subsector_id';
        } elseif ( $type === 'data' ) {
          $query = "SELECT * FROM data AS s";
          $searchField1 = 's.text';
        } elseif ( $type === 'kurse' ) {
          $query = "SELECT * FROM kurse AS s";
          $searchField1 = 's.kurs';
          $searchField2 = 's.kurs_dirty';
          $searchField3 = 's.provider';
        } elseif ( $type === 'logSwaps' ) {
          $query = "SELECT * FROM log_swaps AS s";
          $searchField1 = 's.swap_id';
          $searchField2 = 's.segment_id';
          $searchField3 = 's.contract_type';
          $type = "swaps";
        } elseif ( $type === 'logZsk' ) {
          $query = "SELECT * FROM log_zsk AS s";
          $searchField1 = 's.datum';
          $searchField2 = 's.currency_id';
          $searchField3 = 's.skadenz_id';
          $type = "zsk";
        } elseif ( $type === 'logSpreads' ) {
          $query = "SELECT * FROM log_spreads AS s";
          $searchField1 = 's.datum';
          $searchField2 = 's.sector_id';
          $searchField3 = 's.subsector_id';
          $type = "spreads";
        } elseif ( $type === 'logDevisen' ) {
          $query = "SELECT * FROM log_devisen AS s";
          $searchField1 = 's.quote';
          $type = "devisen";
        } elseif ( $type === 'logKurse' ) {
          $query = "SELECT * FROM log_kurse AS s";
          $searchField1 = 's.base';
          $searchField2 = 's.spot';
          $searchField3 = 's.datum';
          $type = "kurse";
        } elseif ( $type === 'wertpapiere' ) {
          $query = "SELECT * FROM qvs_bonds_staticData AS s";
          $searchField1 = 's.client';
          $searchField2 = 's.isin';
          $searchField3 = 's.issue_date';
          $type = "wertpapiere";
        } elseif ( $type === 'optionen' ) {
          $query = "SELECT * FROM qvs_optionen_staticData AS s";
          $searchField1 = 's.client';
          $searchField2 = 's.option_id';
          $searchField3 = 's.option_description';
          $type = "optionen";
        } elseif ( $type === 'EQSWAPE16X' ) {
          $query = "SELECT * FROM EQSWAPE16X AS s";
          $searchField1 = 's.AsOfDate';
          $searchField2 = 's.AccountNumber';
          $searchField3 = 's.AccountName';
          $type = "EQSWAPE16X";
        } elseif ( $type === 'EQSWAPE18MDX' ) {
          $query = "SELECT * FROM EQSWAPE18MDX AS s";
          $searchField1 = 's.AsOfDate';
          $searchField2 = 's.AccountNumber';
          $searchField3 = 's.AccountName';
          $type = "EQSWAPE18MDX";
        } elseif ( $type === 'EQSWAPE20MDX' ) {
          $query = "SELECT * FROM EQSWAPE20MDX AS s";
          $searchField1 = 's.AsOfDate';
          $searchField2 = 's.AccountNumber';
          $searchField3 = 's.AccountName';
          $type = "EQSWAPE20MDX";
        } elseif ( $type === 'EQSWAPE35AX' ) {
          $query = "SELECT * FROM EQSWAPE35AX AS s";
          $searchField1 = 's.AsOfDate';
          $searchField2 = 's.AccountNumber';
          $searchField3 = 's.AccountName';
          $type = "EQSWAPE35AX";
        } elseif ( $type === 'EQSWAPE37X' ) {
          $query = "SELECT * FROM EQSWAPE37X AS s";
          $searchField1 = 's.AsOfDate';
          $searchField2 = 's.AccountNumber';
          $searchField3 = 's.AccountName';
          $type = "EQSWAPE37X";
        } elseif ( $type === 'EQSWAPE40X' ) {
          $query = "SELECT * FROM EQSWAPE40X AS s";
          $searchField1 = 's.AsOfDate';
          $searchField2 = 's.AccountNumber';
          $searchField3 = 's.AccountName';
          $type = "EQSWAPE40X";
        } elseif ( $type === 'EQSWAPS' ) {
          $query = "SELECT * FROM EQSWAPS AS s";
          $searchField1 = 's.AsOfDate';
          $searchField2 = 's.AccountNumber';
          $searchField3 = 's.AccountName';
          $type = "EQSWAPS";
        } elseif ( $type === 'logWertpapiere' ) {
          $query = "SELECT * FROM log_qvs_bonds_staticData AS s";
          $searchField1 = 's.client';
          $searchField2 = 's.isin';
          $searchField3 = 's.issue_date';
          $type = "wertpapiere";
        } elseif ( $type === 'HSBCINKA' ) {
          $query = "SELECT * FROM hsbc_inka AS s";
          $searchField1 = 's.client';
          $searchField2 = 's.isin';
          $searchField3 = 's.issue_date';
          $type = "wertpapiere"; // same structure as wertpopiere
        } elseif ( $type === 'logHSBCINKA' ) {
          $query = "SELECT * FROM log_hsbc_inka AS s";
          $searchField1 = 's.client';
          $searchField2 = 's.isin';
          $searchField3 = 's.issue_date';
          $type = "wertpapiere"; // same structure as wertpopiere
        }

        if( !empty( $_POST["order"] ) ){
      		$query .= 'ORDER BY ' . $_POST['order']['0']['column'] . ' ' . $_POST['order']['0']['dir'] . ' ';
      	} else {
      		$query .= ' ORDER BY s.id ASC ';
      	}

        if( !empty( $_POST["search"]["value"] ) ){
          $_POST["search"]["value"] = filter_var( $_POST["search"]["value"], FILTER_SANITIZE_STRING );

          $query .= ' where( ' . $searchField1 . ' LIKE "%' . $_POST["search"]["value"] . '%" ';
          if ( isset( $searchField2 )) {
            $query .= ' OR ' . $searchField2 . ' LIKE "%' . $_POST["search"]["value"] . '%" ';
          }
          if ( isset( $searchField3 )) {
            $query .= ' OR ' . $searchField3 . ' LIKE "%' . $_POST["search"]["value"] . '%" ';
          }
        }

        $dbc = new Connector();
        $dbc->executeQuery( $query );
        $results = $dbc->fetchDataTablesRequest( PDO::FETCH_ASSOC, $type );
        echo json_encode( $results );
        // Logging::Log( $results );
      } catch ( PDOException $exception ) {
        // Output expected PDOException.
        var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
        // Output unexpected Exceptions.
        var_dump( $exception->getMessage() );
      }
  }


  /*---------------------------------------------------------------------
  |
  |
  |                       Database Section
  |
  |
  *-------------------------------------------------------------------*/

  /**
  * @brief displayDataFields()
  * @param -
  * @return -
  * @details function to display user Information from datatables
  */
  function displayDataFields( $type ) {
      try {
        $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
        if ( $type === 'swapDbTable' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'swaps';";
        } elseif ( $type === 'zskDbTable' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'zsk';";
        } elseif ( $type === 'spreadsDbTable' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'spreads';";
        } elseif ( $type === 'devisenDbTable' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'devisen';";
        } elseif ( $type === 'currencyDbTable' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'currency';";
        } elseif ( $type === 'ratingDbTable' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'rating';";
        } elseif ( $type === 'sectorDbTable' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'sector';";
        } elseif ( $type === 'subsectorDbTable' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'subsector';";
        } elseif ( $type === 'etlDbTable' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'etl';";
        } elseif ( $type === 'nameConventionsDbTable' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'name_conventions';";
        } elseif ( $type === 'listLogSwaps' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'log_swaps';";
        } elseif ( $type === 'listLogZsk' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'log_zsk';";
        } elseif ( $type === 'listLogSpreads' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'log_spreads';";
        } elseif ( $type === 'listLogDevisen' ) {
          $query = "SELECT COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra', TABLE_NAME 'Table' FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'qvs' AND TABLE_NAME = 'log_devisen';";
        }

        $dbc = new Connector();
        $dbc->executeQuery( $query );
        $results = $dbc->fetchDataTablesRequest( PDO::FETCH_ASSOC, 'dbTableFields' );
        echo json_encode( $results );
        // Logging::Log( $results );
      } catch ( PDOException $exception ) {
        // Output expected PDOException.
        var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
        // Output unexpected Exceptions.
        var_dump( $exception->getMessage() );
      }
  }

  /**
  * @brief addField()
  * @param -
  * @return -
  * @details function to add a new field to a specific table in the database
  */
  function addField() {
      try {
        $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
        $query = "ALTER TABLE `qvs`.`" . $_POST['tableName'] . "` ADD " . $_POST['fieldName'] . " " . $_POST['fieldType'] . "";

        if ( !empty( $_POST['fieldLength'] ) ) {
          $query .= "(" . $_POST['fieldLength'] . " )";
        }

        if ( $_POST['fieldNull'] === 'NO' ) {
          $query .= " NOT NULL";
        }

        if ( !empty( $_POST['fieldDefaultValue'] ) ) {
          $query .= " DEFAULT '" . $_POST['fieldDefaultValue'] . "'";
        }

        $dbc = new Connector();
        $dbc->executeQuery( $query );
        echo json_encode("Erfolgreich hinzugefügt!" );
        // Logging::Log( $results );
      } catch ( PDOException $exception ) {
        // Output expected PDOException.
        var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
        // Output unexpected Exceptions.
        var_dump( $exception->getMessage() );
      }
  }

  /**
  * @brief updateField()
  * @param -
  * @return -
  * @details function to update a field in a specific table in the database
  */
  function updateField() {
      try {
        $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
        $query = "ALTER TABLE `qvs`.`" . $_POST['updateTable'] . "` MODIFY  " . $_POST['fieldName'] . " " . $_POST['fieldType'] . "";

        if ( !empty( $_POST['fieldLength'] ) ) {
          $query .= "(" . $_POST['fieldLength'] . " )";
        }

        if ( $_POST['fieldNull'] === 'NO' ) {
          $query .= " NOT NULL";
        }

        if ( !empty( $_POST['fieldDefaultValue'] ) ) {
          $query .= " DEFAULT '" . $_POST['fieldDefaultValue'] . "'";
        }

        $dbc = new Connector();
        $dbc->executeQuery( $query );
        echo json_encode("Erfolgreich aktualisiert!" );
        // Logging::Log( $results );
      } catch ( PDOException $exception ) {
        // Output expected PDOException.
        var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
        // Output unexpected Exceptions.
        var_dump( $exception->getMessage() );
      }
  }

  /*---------------------------------------------------------------------
  |
  |
  |                       Misc Section
  |
  |
  *-------------------------------------------------------------------*/

  /**
  * @brief getPermissions()
  * @param -
  * @return -
  * @details function to get all permissions from the datatables
  */
  function getPermissions() {
      try {
        $dbc = new Connector();
        $query = "SELECT perm_id, perm_desc FROM permissions";
        $dbc->executeQuery( $query );
        $results = $dbc->fetchDataTablesRequest( PDO::FETCH_ASSOC, 'getPermissions' );
        echo json_encode( $results );
        // Logging::Log( $results );
      } catch ( PDOException $exception ) {
        // Output expected PDOException.
        var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
        // Output unexpected Exceptions.
        var_dump( $exception->getMessage() );
      }
  }

  /*---------------------------------------------------------------------
  |
  |
  |                       ETL Upload Section
  |
  |
  *-------------------------------------------------------------------*/

  /**
  * @brief function getETLConfig()
  * @param $filename
  * @return response, whether a mapping has been found or not
  * @details fetches to see, if an ETL Setting has been found in the database
  * @details only for swap files
  */
   function getETLConfig() {

     $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
     $filename = $_POST['filename'];
     $dbc = new Connector();
     // removes all numbers before or after an underscore
     $filename = preg_replace( '/_[^_]+\d|\d+_/', '', $filename );
     $ext = pathinfo( $filename, PATHINFO_EXTENSION );

     // Check if filename is an EQSWAPS, if yes, then keep the EQSWAP Name and cut the rest
     if ( strpos( $filename, '-' ) !== false ) {

       $cleanedFilename = explode( "-", $filename, 2 );
       $filenameTemp = $cleanedFilename[0];
       if ( strpos( $filename, 'EQSWAPE16X' ) !== false ||
       strpos( $filename, 'EQSWAPE18MDX' ) !== false ||
       strpos( $filename, 'EQSWAPE20MDX' ) !== false ||
       strpos( $filename, 'EQSWAPE35AX' ) !== false ||
       strpos( $filename, 'EQSWAPE37X' ) !== false ||
       strpos( $filename, 'EQSWAPE40X' ) !== false ) {

         $file = $filenameTemp;
         $filename = str_replace( ' ', '', $file );
         $filename .= "." . $ext;
       }

     }

     $query = "SELECT `name`, `table`, `kunde`, `header`, `row_start`, `lache`, `mapping`, `kurse_mapping`, `transformationen`, `kurse_transformationen`
               FROM `etl` WHERE `filename` = '" . $filename . "'";

     try {
       $dbc->executeQuery( $query );
       $res = $dbc->fetchResults();
       $result = $dbc->fetchMessage( 'getETLConfig' );
       $client = '';

       // if a mapping has been found, then fill our session variables
       if ( $res ) {
         $client = html_entity_decode( $res[0]->name );

         // loop through the results and save them to session variables
         for ( $i=0; $i < count( $res ); $i++ ) {
           $mappingDB = html_entity_decode( $res[$i]->mapping); // get mapping object
           $mapping[$i] = json_decode( $mappingDB, true ); // create mapping array
           $kurseMappingDB = html_entity_decode( $res[$i]->kurse_mapping); // get mapping object
           $kurseMapping[$i] = json_decode( $kurseMappingDB, true ); // create mapping array
           $transformationenDB = html_entity_decode( $res[$i]->transformationen); // get object
           $transformationen[$i] = json_decode( $transformationenDB, true ); // get array
           $kurseTransformationenDB = html_entity_decode( $res[$i]->kurse_transformationen); // get object
           $kurseTransformationen[$i] = json_decode( $kurseTransformationenDB, true ); // get array
           $table[$i] = html_entity_decode( $res[$i]->table );
           $header[$i] = html_entity_decode( $res[$i]->header );
           $rowStart[$i] = html_entity_decode( $res[$i]->row_start );
           $kunde[$i] = html_entity_decode( $res[$i]->kunde );
           $lache[$i] = html_entity_decode( $res[$i]->lache );
         }

         $_SESSION["mapping"] = $mapping;
         $_SESSION["kurseMapping"] = $kurseMapping;
         $_SESSION["transformationen"] = $transformationen;
         $_SESSION["kurseTransformationen"] = $kurseTransformationen;
         $_SESSION["table"] = $table;
         $_SESSION["header"] = $header;
         $_SESSION["rowStart"] = $rowStart;
         $_SESSION["kunde"] = $kunde;
         $_SESSION["lache"] = $lache;
         $_SESSION["anzahl"] = count( $res );
       }

       if ( $client ) {
         $result .= '<strong> '. $client . '</strong>';
       }

       echo json_encode( $result );

     } catch ( PDOException $exception ) {
       // Output expected PDOException.
       var_dump( $exception->getMessage() );
     } catch ( Exception $exception ) {
       // Output unexpected Exceptions.
       var_dump( $exception->getMessage() );
     }

   }

  /**
  * @brief function readFileContent()
  * @param -
  * @return response - whether data is being uploaded or not
  * @details fetches mapping & transformations from frontend and saves the content of the file into an array
  * @details function for both csv & excel
  */
   function readFileContent() {
     // Allowed mime types
     $allowedFileType = ['application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                         'text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain'];
   	 // $limit = ini_get('memory_limit');
     // echo $limit;
     // print("<pre>".print_r($_FILES,true)."</pre>");
     // echo $upload_max_size = ini_get('upload_max_filesize') . '<br>';
     // echo $post_max_size=ini_get('post_max_size');

     // Check what type of file it is
     if ( !empty( $_FILES['file']['name'] ) ) {

       $mapping = $_SESSION["mapping"];
       $transformationen = $_SESSION["transformationen"];
       $kurseMapping = $_SESSION["kurseMapping"];
       $kurseTransformationen = $_SESSION["kurseTransformationen"];
       $table = $_SESSION["table"];
       $header = $_SESSION["header"];
       $rowStart = $_SESSION["rowStart"];
       $lache = $_SESSION["lache"];
       $fileName = $_FILES["file"]["tmp_name"];
       $filename = $_FILES["file"]["name"];
       $_SESSION["filename"] = $filename;

     } else {
       echo json_encode( 'Datei konnte nicht gelesen werden. Bitte versuchen Sie es erneut oder wenden Sie sich an Ihren Systemadministrator.' );
       exit();
     }

       // Excel & CSV Import
     if ( in_array( $_FILES['file']['type'], $allowedFileType ) ) {

        if ( is_uploaded_file( $fileName ) && $_FILES["file"]["size"] > 0 ) {

          $anz = $_SESSION["anzahl"];

          // extract & transform data
          for ( $i=0; $i < $anz ; $i++ ) {

            // if ( $table[$i] === 'zsk' || $table[$i] === 'spreads' || $table[$i] === 'devisen' ) {
            //   $_SESSION["fileType"] = 'market';
            // } elseif ( $table[$i] === 'swaps' ) {
            //   $_SESSION["fileType"] = 'swaps';
            // } elseif ( $table[$i] === 'EQSWAPS' || $table[$i] === 'EQSWAPE16X' || $table[$i] === 'EQSWAPE18MDX' || $table[$i] === 'EQSWAPE20MDX' || $table[$i] === 'EQSWAPE35AX' || $table[$i] === 'EQSWAPE37X' || $table[$i] === 'EQSWAPE40X' ) {
            //   $_SESSION["fileType"] = 'eqswaps';
            // } elseif ( $table[$i] === 'qvs_bonds_staticData' ) {
            //   $_SESSION["fileType"] = 'bonds';
            // }

              // needed to extract Data
              $_SESSION["currentHeader"] = $header[$i];
              $_SESSION["currentRowStart"] = $rowStart[$i];
              $_SESSION["currentTable"] = $table[$i];
              $_SESSION["currentLache"] = $lache[$i];

              $extractData = extractFileData( $filename, $mapping[$i], $anz, $i );
              $transformData[$i] = transformData( $extractData, $transformationen[$i] );

              // print("<pre>".print_r($extractData,true)."</pre>");
              // print("<pre>".print_r($transformData[$i],true)."</pre>");

              // Kurse extract & transformations ill be implemented first, before file is moved in function below
              if ( !empty( $kurseMapping[$i] ) ) {
                $extractKurseData = extractFileDataKurse( $filename, $kurseMapping[$i], $anz, $i );
                $transformKurseData[$i] = transformKurseData( $extractKurseData, $kurseTransformationen[$i] );
              }

              unset( $_SESSION['fileType'] );
              unset( $_SESSION['currentHeader'] );
              unset( $_SESSION['currentRowStart'] );
              unset( $_SESSION['currentTable'] );
              unset( $_SESSION['currentLache'] );
          }

          if ( count( $transformData ) == $anz ) {
            $result = 'Daten werden importiert...';
          } else {
            $result = 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.';
          }

          $_SESSION["transformedData"] = $transformData;
          if ( isset( $transformKurseData ) ) {
            $_SESSION["transformedKurseData"] = $transformKurseData;
          }

          unset( $_SESSION['transformationen'] );
          unset( $_SESSION['kurseTransformationen'] );

          echo json_encode( $result );
        } else {
          echo json_encode( 'Fehler bei Datei einlesen. Bitte versuchen Sie erneut.' );
          exit();
        }

       // No match at all
     } else {
       echo json_encode( 'Das Datenformat stimmt nicht überein. Bitte versuchen Sie es erneut oder wenden Sie sich an Ihren Systemadministrator.' );
     }

   }

  /**
  * @brief extractCsvData()
  * @param $fileName - path to the file
  * @return $mappingValues
  * @details check to see if all our mapping keys are present in the file
  * @details fetches values from database to the mapping keys
  */
   function extractCsvData( $filename, $mapping) {

     $fileType = $_SESSION["fileType"];
     $table = ucfirst( $_SESSION["table"] );
     if ( $table === 'Zsk' ) {
       $table = 'Zinskurven';
     } elseif ( $table === 'Spreads' ) {
       $table = 'Spreadkurven';
     }
     unset( $_SESSION['fileType'] );
     $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/files/' . $fileType . '/' . $table . '/csv/' . $filename;

     if ( !move_uploaded_file( $_FILES['file']['tmp_name'], $targetPath ) ) {
       echo json_encode( 'Fehler bei Dateiübertragung.' );
       exit();
     }

     $file = fopen( $targetPath, "r" );
     // new array to store the "mapping"
     $map = array();
     $mappingValues = array();
     $mappingKeys = array_keys( $mapping );
     $row = fgetcsv( $file, 10000, ';' );

     // loop over our mapping keys, assign the column index to map array
     foreach( $mappingKeys AS $name ) {
        // array_search will find the field name in the row array and return the index
        $index = array_search( strtolower( $name ), array_map( 'strtolower', $row ) );
        if ( FALSE !== $index ) {
           $map[ $index ] = $name;
        }
     }

     // if not all fields present, error and exit
     // if ( count( $map ) < count( $mappingKeys ) ) {
     //   echo json_encode( 'Die folgenden Felder müssen alle in der Datei enthalten sein: <br>' . implode( ', ', $mappingKeys ));
     //   die();
     // }

     while ( $data = fgetcsv( $file, 10000, ";", '"' ) ) {
        $row = array();
        // loop over known fields / index and assign to record
        foreach( $map AS $index => $field ) {
           // $index is the column number / index
           // $field is the name of the field
           $row[ $field ] = $data[ $index ];
        }
        $mappingValues[] = $row;
    }

    fclose( $file );

    return $mappingValues;
  }

  /**
  * @brief extractExcelData()
  * @param $fileName, $mapping, $anz, $index
  * @return $mappingValues
  * @details check to see if all our mapping keys are present in the file
  * @details fetches values from database to the mapping keys
  */
   function extractExcelData( $filename, $mapping, $anz, $index ) {

     $header = $_SESSION["currentHeader"];
     $lache = $_SESSION["currentLache"];
     $fileExtension = pathinfo( $filename, PATHINFO_EXTENSION );

     // if there is only one file, but multiple imports, then use only one folder
     if ( $anz > 1 ) {
       $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/files/market/excel/' . $filename;
       // below is the one used for docker container
       // $targetPath = $_SERVER['DOCUMENT_ROOT'] . 'files/market/excel/' . $filename;
     } else {
       $fileType = $_SESSION["fileType"];
       $table = ucfirst( $_SESSION["currentTable"] );

       if ( $table === 'Spreads' ) {
         $table = 'Spreadkurven';
       }
       if ( $table === 'Zsk' ) {
         $table = 'Zinskurven';
       }

       $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/files/' . $fileType . '/' . $table . '/excel/' . $filename;
     }

     $_SESSION['targetPath'] = $targetPath;

     unset( $_SESSION['fileType'] );
     unset( $_SESSION['currentHeader'] );
     unset( $_SESSION['currentTable'] );
     unset( $_SESSION['currentLache'] );

     if ( !move_uploaded_file( $_FILES['file']['tmp_name'], $targetPath ) ) {

       if ( $index < $anz ) {
         $targetPath = $_SESSION['targetPath'];
       } else {
         echo json_encode( 'Fehler bei Dateiübertragung.' );
         exit();
       }

     }

     if ( $fileExtension === 'xls' ) {
       $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
     } else {
       $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
     }

     try {
       $Reader->setReadDataOnly( false );
       $spreadSheet = $Reader->load( $targetPath );
       if ( $anz === 1 ) {
         $excelSheet = $spreadSheet->getActiveSheet();
       } else {
         $excelSheet = $spreadSheet->getSheet( $lache );
       }
       $spreadSheetAry = $excelSheet->toArray();
       $sheetCount = count( $spreadSheetAry ) -1;
       // new array to store the "mapping"
       $map = array();
       $mappingValues = array();
       $mappingKeys = array_keys( $mapping );
       $maxCell = $excelSheet->getHighestRowAndColumn();
       $data = $excelSheet->rangeToArray( 'A1:' . $maxCell['column'] . $maxCell['row'] );
       $data = array_map( 'array_filter', $data );
       $data = array_filter( $data );
       $activeRow = --$header;
       $row = $spreadSheetAry[$activeRow];

       // loop over our mapping keys, assign the column index to map array
       foreach( $mappingKeys AS $name ) {
          // array_search will find the field name in the row array and return the index
          $index = array_search( strtolower( $name ), array_map( 'strtolower', $row ) );
          if ( FALSE !== $index ) {
             $map[ $index ] = $name;
          }
       }

       // $result = !count( array_intersect( $map, $mappingKeys ) );
       // echo $result;

       // if not all fields present, error and exit
       // if ( count( $map ) < count( $mappingKeys ) ) {
       //   echo json_encode( 'Die folgenden Felder müssen alle in der Datei enthalten sein: <br>' . implode( ', ', $mappingKeys ));
       //   die();
       // }

       // instead of a while loop like in the csv import, we use a for loop
       for ( $i = $activeRow+1; $i <= $sheetCount; $i++ ) {
         $data = $spreadSheetAry[$i];
         $row = array();
         // loop over known fields / index and assign to record
         foreach( $map AS $index => $field ) {
            // $index is the column number / index
            // $field is the name of the field
            $row[ $field ] = $data[ $index ];
         }
         $mappingValues[] = $row;
        }

      return $mappingValues;

     } catch (\Exception $e) {
       echo json_encode( "Ein Fehler ist beim einlesen der Datei aufgetreten: " .$e->getMessage() );
       die();
     }

  }

  /**
  * @brief extractFileData()
  * @param $fileName, $mapping, $anz, $index
  * @return $mappingValues
  * @details check to see if all our mapping keys are present in the file
  * @details used for both excel & csv upload
  */
   function extractFileData( $filename, $mapping, $anz, $index ) {

     $header = $_SESSION["currentHeader"];
     $rowStart = $_SESSION["currentRowStart"];
     $lache = $_SESSION["currentLache"];
     $fileExtension = pathinfo( $filename, PATHINFO_EXTENSION );

     $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/files/upload/' . $filename;
     $_SESSION['targetPath'] = $targetPath;

     if ( !move_uploaded_file( $_FILES['file']['tmp_name'], $targetPath ) ) {

       if ( $index < $anz ) {
         $targetPath = $_SESSION['targetPath'];
       } else {
         echo json_encode( 'Fehler bei Dateiübertragung.' );
         exit();
       }

     }

     if ( $fileExtension === 'xls' ) {
       $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
     } elseif ( $fileExtension === 'xlsx' ) {
       $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
     } else {
       $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
     }

     try {
       $Reader->setReadDataOnly( false );
       $spreadSheet = $Reader->load( $targetPath );
       if ( $anz === 1 ) {
         $excelSheet = $spreadSheet->getActiveSheet();
       } else {
         $excelSheet = $spreadSheet->getSheet( $lache );
       }
       $spreadSheetAry = $excelSheet->toArray();
       $sheetCount = count( $spreadSheetAry ) -1;
       // new array to store the "mapping"
       $map = array();
       $mappingValues = array();
       $mappingKeys = array_keys( $mapping );
       $maxCell = $excelSheet->getHighestRowAndColumn();
       $data = $excelSheet->rangeToArray( 'A1:' . $maxCell['column'] . $maxCell['row'] );
       $data = array_map( 'array_filter', $data );
       $data = array_filter( $data );
       // $activeRow = --$header;
       $activeRow = --$rowStart;
       $row = $spreadSheetAry[$activeRow];

       // print("<pre>".print_r($data,true)."</pre>");

       // loop over our mapping keys, assign the column index to map array
       foreach( $mappingKeys AS $name ) {
          // array_search will find the field name in the row array and return the index
          $index = array_search( strtolower( $name ), array_map( 'strtolower', $row ) );
          if ( FALSE !== $index ) {
             $map[ $index ] = $name;
          }
       }

       // $result = !count( array_intersect( $map, $mappingKeys ) );
       // echo $result;

       // if not all fields present, error and exit
       // if ( count( $map ) < count( $mappingKeys ) ) {
       //   echo json_encode( 'Die folgenden Felder müssen alle in der Datei enthalten sein: <br>' . implode( ', ', $mappingKeys ));
       //   die();
       // }

       // instead of a while loop like in the csv import, we use a for loop
       for ( $i = $activeRow+1; $i <= $sheetCount; $i++ ) {
         // $data = $spreadSheetAry[$i];
         $sheetData = $spreadSheetAry[$i];
         $row = array();
         // loop over known fields / index and assign to record
         foreach( $map AS $index => $field ) {
            // $index is the column number / index
            // $field is the name of the field
            $row[ $field ] = $sheetData[ $index ];
         }
         $mappingValues[] = $row;
        }

      return $mappingValues;

     } catch (\Exception $e) {
       echo json_encode( "Ein Fehler ist beim einlesen der Datei aufgetreten: " .$e->getMessage() );
       die();
     }

  }

  /**
  * @brief extractFileDataKurse()
  * @param $filename, $mapping, $anz, $index
  * @return $mappingValues
  * @details check to see if all our mapping keys are present in the file
  * @details used for both excel & csv upload
  */
   function extractFileDataKurse( $filename, $mapping, $anz, $index ) {

     $header = $_SESSION["currentHeader"];
     $lache = $_SESSION["currentLache"];
     $fileExtension = pathinfo( $filename, PATHINFO_EXTENSION );
     $targetPath = $_SESSION['targetPath'];

     if ( $fileExtension === 'xls' ) {
       $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
     } elseif ( $fileExtension === 'xlsx' ) {
       $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
     } else {
       $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
     }

     try {
       $Reader->setReadDataOnly( false );
       $spreadSheet = $Reader->load( $targetPath );

       if ( $anz === 1 ) {
         $excelSheet = $spreadSheet->getActiveSheet();
       } else {
         $excelSheet = $spreadSheet->getSheet( $lache );
       }

       $spreadSheetAry = $excelSheet->toArray();
       $sheetCount = count( $spreadSheetAry ) -1;
       // new array to store the "mapping"
       $map = array();
       $mappingValues = array();
       $mappingKeys = array_keys( $mapping );
       $maxCell = $excelSheet->getHighestRowAndColumn();
       $data = $excelSheet->rangeToArray( 'A1:' . $maxCell['column'] . $maxCell['row'] );
       $data = array_map( 'array_filter', $data );
       $data = array_filter( $data );
       $activeRow = --$header;
       $row = $spreadSheetAry[$activeRow];

       // loop over our mapping keys, assign the column index to map array
       foreach( $mappingKeys AS $name ) {
          // array_search will find the field name in the row array and return the index
          $index = array_search( strtolower( $name ), array_map( 'strtolower', $row ) );
          if ( FALSE !== $index ) {
             $map[ $index ] = $name;
          }
       }

       // instead of a while loop like in the csv import, we use a for loop
       for ( $i = $activeRow+1; $i <= $sheetCount; $i++ ) {
         $data = $spreadSheetAry[$i];
         $row = array();
         // loop over known fields / index and assign to record
         foreach( $map AS $index => $field ) {
            // $index is the column number / index
            // $field is the name of the field
            $row[ $field ] = $data[ $index ];
         }
         $mappingValues[] = $row;
        }

      return $mappingValues;

     } catch (\Exception $e) {
       echo json_encode( "Ein Fehler ist beim einlesen der Datei aufgetreten: " .$e->getMessage() );
       die();
     }

  }

  /**
  * @brief transformData()
  * @param $extractData - path to the file | $transformationen - array of all the transformations
  * @return $extractData - transformed data
  * @details gets transformation function name from the database and transforms data in a loop
  */
   function transformData( $extractData, $transformationen ) {

     $transformationKeys = array_keys( $transformationen );
     $transformationValues = array_values( $transformationen );
     $dbc = new Connector();
     // print("<pre>".print_r($extractData,true)."</pre>");

     // loop over our mapping keys, transform the $extractDataValues
     for ( $i = 0; $i < count( $extractData ) ; $i++ ) {
       $extractDataKeys = array_keys( $extractData[$i] );
       $extractDataValues = array_values( $extractData[$i] );
       // loop through our sub array
       for ( $j = 0; $j < count( $extractDataKeys ) ; $j++ ) {
         // search if the current key ( $extractDataKeys ) exists in the $transformationKeys from the database
         $index = array_search( strtolower( $extractDataKeys[$j] ), array_map( 'strtolower', $transformationKeys ) );
         // echo $extractDataKeys[$j] . ': index= ' . $index;

         // if there is a transformation, then transform the value
         if ( $transformationValues[$index] !== 'Keine' ) {
           $query = 'SELECT `script` FROM `transformations` WHERE `name` = "' . $transformationValues[$index] . '" ';
           try {
              $dbc->executeQuery( $query );
              $result = $dbc->fetchResults( PDO::FETCH_OBJ );
              $script = $result[0]->script;
              if ( strlen( $extractDataValues[$j] ) >= 1 && $extractDataValues[$j] !== ' ' ) {
                // echo "Before: " . $extractDataKeys[$j] . ' ' . $extractDataValues[$j] . ' length: ' . strlen($extractDataValues[$j]) .'<br>';
                $extractDataValues[$j] = call_user_func( $script, $extractDataValues[$j] );
                // echo "After: " . $extractDataKeys[$j] . '' . $extractDataValues[$j] . '<br>';
              } else {
                // empty values set to null for the database
                $extractDataValues[$j] = 'null';
              }

            } catch ( PDOException $exception ) {
              // Output expected PDOException.
              var_dump( $exception->getMessage() );
            } catch ( Exception $exception ) {
              // Output unexpected Exceptions.
              var_dump( $exception->getMessage() );
            }
         } else {
           if ( strlen( $extractDataValues[$j] ) < 1 ) {
             // empty values set to null for the database
             $extractDataValues[$j] = 'null';
           } else {
             $extractDataValues[$j] = "'" . $extractDataValues[$j] . "'";
           }
         }
         $extractData[$i] = array_combine( $extractDataKeys , $extractDataValues );
       }

     }
    // print("<pre>".print_r($extractData,true)."</pre>" );
    return $extractData;
  }

  /**
  * @brief transformKurseData()
  * @param $extractData - path to the file | $transformationen - array of all the transformations
  * @return $extractData - transformed data
  * @details gets transformation function name from the database and transforms data in a loop
  */
   function transformKurseData( $extractData, $transformationen ) {

     $transformationKeys = array_keys( $transformationen );
     $transformationValues = array_values( $transformationen );
     $dbc = new Connector();

     // loop over our mapping keys, transform the $extractDataValues
     for ( $i = 0; $i < count( $extractData ) ; $i++ ) {
       $extractDataKeys = array_keys( $extractData[$i] );
       $extractDataValues = array_values( $extractData[$i] );
       // loop through our sub array
       for ( $j = 0; $j < count( $extractDataKeys ) ; $j++ ) {
         // search if the current key ( $extractDataKeys ) exists in the $transformationKeys from the database
         $index = array_search( strtolower( $extractDataKeys[$j] ), array_map( 'strtolower', $transformationKeys ) );

         // if there is a transformation, then transform the value
         if ( $transformationValues[$index] !== 'Keine' ) {
           $query = 'SELECT `script` FROM `transformations` WHERE `name` = "' . $transformationValues[$index] . '" ';
           try {
              $dbc->executeQuery( $query );
              $result = $dbc->fetchResults( PDO::FETCH_OBJ );
              $script = $result[0]->script;
              if ( strlen( $extractDataValues[$j] ) >= 1) {
                $extractDataValues[$j] = call_user_func( $script, $extractDataValues[$j] );
              } else {
                // empty values set to null for the database
                $extractDataValues[$j] = 'null';
              }

            } catch ( PDOException $exception ) {
              // Output expected PDOException.
              var_dump( $exception->getMessage() );
            } catch ( Exception $exception ) {
              // Output unexpected Exceptions.
              var_dump( $exception->getMessage() );
            }
         } else {
           if ( strlen( $extractDataValues[$j] ) < 1 ) {
             // empty values set to null for the database
             $extractDataValues[$j] = 'null';
           } else {
             $extractDataValues[$j] = "'" . $extractDataValues[$j] . "'";
           }
         }
         $extractData[$i] = array_combine( $extractDataKeys , $extractDataValues );
       }

     }
    // print("<pre>".print_r($extractData,true)."</pre>" );
    return $extractData;
  }

 /**
 * @brief loadData()
 * @param -
 * @return response
 * @details check to see if all our mapping keys are present in the file
 * @details fetches values from database to the mapping keys
 */
  function loadData() {
    // unset previous session variables
    unset( $_SESSION['targetPath'] );

    $table = $_SESSION['table'];
    $transformedData = $_SESSION["transformedData"];
    $transformedKurseData = ( isset( $_SESSION["transformedKurseData"] ) ) ? $_SESSION["transformedKurseData"] : '';
    $mapping = $_SESSION["mapping"];
    $kurseMapping = $_SESSION["kurseMapping"];
    $filename = $_SESSION["filename"];
    $anz = $_SESSION["anzahl"];
    $kunde = $_SESSION["kunde"];
    unset( $_SESSION['kunde'] );
    unset( $_SESSION['anzahl'] );
    unset( $_SESSION['table'] );
    unset( $_SESSION['filename'] );
    unset( $_SESSION['transformedData'] );
    unset( $_SESSION['transformedKurseData'] );
    unset( $_SESSION['mapping'] );
    unset( $_SESSION['kurseMapping'] );

    // get filedate from fileName
    // $filedate_lastPart = substr( strrchr( rtrim( $filename, '_' ), '_' ), 1 );
    // $filedate_firstPart = substr( $filename, 0, strpos( $filename, '_' ) );
    //
    // if ( preg_match( '#[^0-9]#', $filedate_lastPart ) ) {
    //   $filedate = $filedate_lastPart;
    //   $filedate = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $filedate );
    // } elseif ( preg_match( '#[^0-9]#', $filedate_firstPart ) ) {
    //   $filedate = $filedate_firstPart;
    // } else {
    //   $filedate = 'not readable';
    // }

    $filedate_lastPart = substr( strrchr( rtrim( pathinfo( $filename, PATHINFO_FILENAME ), '_' ), '_' ), 1 );
    $filedate_firstPart = substr( $filename, 0, strpos( $filename, '_' ) );

    if ( ctype_digit( $filedate_lastPart ) ) {
      $filedate = $filedate_lastPart;
      $filedate = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $filedate );
    } elseif ( ctype_digit( $filedate_firstPart ) ) {
      $filedate = $filedate_firstPart;
    } else {
      $filedate = NULL;
    }

    $res = array();
    $dbc = new Connector();

    for ( $c=0; $c < $anz; $c++ ) {

      // new array to store the "mapping"
      $insertInto = array();
      $insertValues = array();
      $duplicateKeyUpdate = array();
      $insertIntoKurse = array();
      $insertValuesKurse = array();
      $duplicateKeyUpdateKurse = array();
      $errorLog = array();
      $excLog = array();
      $errorLogKurse = array();
      $excLogKurse = array();
      $mappingKeys = array_keys( $mapping[$c] );
      $mappingValues = array_values( $mapping[$c] );
      if ( !empty( $kurseMapping[$c] ) ) {
        $kurseMappingKeys = array_keys( $kurseMapping[$c] );
        $kurseMappingValues = array_values( $kurseMapping[$c] );
      }
      $q = "INSERT INTO `" . $table[$c] . "`";
      $kq = "INSERT INTO kurse";
      $log = "INSERT INTO `log_" . $table[$c] . "`";
      $klog = "INSERT INTO `log_kurse`";
      $count = 0;
      $kurseCount = 0;
      $updateCount = 0;
      $updateKurseCount = 0;
      $errors = 0;
      $kurseErrors = 0;
      $timestamp = date( 'd-m-Y_H:i:s', time() );

      // echo 'keys: ' . $c . ':<br>';
      // print("<pre>".print_r($mappingKeys,true)."</pre>");
      // echo 'values: ' . $c . ':<br>';
      // print("<pre>".print_r($mappingValues,true)."</pre>");

      // loop over our mapping keys, transform the $extractDataValues
      for ( $i = 0; $i < count( $transformedData[$c] ) ; $i++ ) {
        $transformedDataKeys = array_keys( $transformedData[$c][$i] );
        $transformedDataValues = array_values( $transformedData[$c][$i] );

        // loop through our sub array
        for ( $j = 0; $j < count( $transformedDataKeys ) ; $j++) {
          $index = array_search( strtolower( $transformedDataKeys[$j] ), array_map( 'strtolower', $mappingKeys ) );
          if ( FALSE !== $index ) {
             $insertInto[ $index ] = '`' . $mappingValues[ $index ] . '`';
             $insertValues[ $index ] = utf8_encode( $transformedDataValues[ $j ] );
             $duplicateKeyUpdate [ $index ] = '`' . $mappingValues[ $index ] . '` =' . utf8_encode( $transformedDataValues[ $j ] );
          }
        }

        // add "client" to $insertInto & $duplicateKeyUpdate array,
        if ( $table[$c] === 'swaps' || $table[$c] === 'qvs_bonds_staticData' || $table[$c] === 'EQSWAPE16X'
             || $table[$c] === 'EQSWAPE18MDX' || $table[$c] === 'EQSWAPE20MDX' || $table[$c] === 'EQSWAPE35AX'
             || $table[$c] === 'EQSWAPE37X' || $table[$c] === 'EQSWAPE40X' ) {

               // search the array for client & insert if not present
               $index = array_search( "`client`", $insertInto );
               if ( $index !== false ) {
                 // $insertInto[ $index ] = "`client`";
                 $insertValues[ $index ] = "'" . utf8_encode( $kunde[0] ) . "'";
                 $duplicateKeyUpdate [ $index ] = "`client` = '" . utf8_encode( $kunde[0] ) . "'";
               } else {
                 array_push( $insertInto, "`client`" );
                 array_push( $insertValues, "'" . utf8_encode( $kunde[0] ) . "'" );
                 array_push( $duplicateKeyUpdate, "`client` = '" . utf8_encode( $kunde[0] ) . "'" );
               }

        } elseif ( $table[$c] === 'devisen' || $table[$c] === 'spreads' || $table[$c] === 'zsk' ) {

          // search the array for client & insert if not present
          $index = array_search( "`kurs_quelle`", $insertInto );
          if ( $index !== false ) {
            $insertValues[ $index ] = "'" . utf8_encode( $kunde[0] ) . "'";
            $duplicateKeyUpdate [ $index ] = "`kurs_quelle` = '" . utf8_encode( $kunde[0] ) . "'";
          } else {
            array_push( $insertInto, "`kurs_quelle`" );
            array_push( $insertValues, "'" . utf8_encode( $kunde[0] ) . "'" );
            array_push( $duplicateKeyUpdate, "`kurs_quelle` = '" . utf8_encode( $kunde[0] ) . "'" );
          }

        }

        try {
          $query = $q . '( ' . implode( ',', $insertInto ) . ',`filename`,`filedate`,`db_status`,`created_by` ) VALUES ( ' . implode( ',', $insertValues ) . ', "' . $filename . '", "' . $filedate . '", "INSERT", "TestBenutzer" )';
          // $log_query = $log . '( ' . implode( ',', $insertInto ) . ',`filename`,`filedate`,`db_status`,`created_by` ) VALUES ( ' . implode( ',', $insertValues ) . ', "' . $filename . '", "' . $filedate . '", "INSERT", "TestBenutzer" )';
          $query = $query . ' ON DUPLICATE KEY UPDATE ' . implode( ',', $duplicateKeyUpdate ) . ", `filename` = '" . $filename . "', `filedate` = '" . $filedate . "', `db_status` = 'UPDATE', `updated_at` = NOW(), `updated_by` = 'TestBenutzer'";

          // $dbc->executeQuery( $log_query );
          $dbc->executeQuery( $query );
          $result = $dbc->fetchMessage( 'loadData' );
          $last_insert_id = $dbc->fetchLastInsertId();

          if ( $result === 1 ) {
            $count = $count + $result;
          } elseif ( $result === 2 ) {
            $updateCount++;
          } else {
            $errorLog[] = $query;
            $errorFile = $_SERVER['DOCUMENT_ROOT'] . '/files/logs/' . $timestamp . '_' . $table[$c] . '_error_log.txt';
            file_put_contents( $errorFile, print_r( $errorLog, true ) );
          }

         } catch ( PDOException $exception ) {
           // Output expected PDOException.
           $excLog[] = $query;
           $excLog[] = $exception->getMessage();
           $excFile = $_SERVER['DOCUMENT_ROOT'] . '/files/logs/' . $timestamp . '_' . $table[$c] . '_exception_log.txt';
           file_put_contents( $excFile, print_r( $excLog, true ) );
           $errors++;
         } catch ( Exception $exception ) {
           // Output unexpected Exceptions.
           var_dump( $exception->getMessage() );
         }
      }

      // KURSE: loop over our mapping keys, transform the $extractDataValues
      if ( !empty( $transformedKurseData[$c] ) ) {

        for ( $i = 0; $i < count( $transformedKurseData[$c] ) ; $i++ ) {
          $transformedKurseDataKeys = array_keys( $transformedKurseData[$c][$i] );
          $transformedKurseDataValues = array_values( $transformedKurseData[$c][$i] );

          // loop through our sub array
          for ( $j = 0; $j < count( $transformedKurseDataKeys ) ; $j++) {
            $indexKurse = array_search( strtolower( $transformedKurseDataKeys[$j] ), array_map( 'strtolower', $kurseMappingKeys ) );
            if ( FALSE !== $indexKurse ) {
               $insertIntoKurse[ $indexKurse ] = '`' . $kurseMappingValues[ $indexKurse ] . '`';
               $insertValuesKurse[ $indexKurse ] = utf8_encode( $transformedKurseDataValues[ $j ] );
               $duplicateKeyUpdateKurse[ $indexKurse ] = '`' . $kurseMappingValues[ $indexKurse ] . '` =' . utf8_encode( $transformedKurseDataValues[ $j ] );
            }
          }

          // search the array for client & insert if not present
          $index = array_search( "`provider`", $insertIntoKurse );
          if ( $index !== false ) {
            $insertValuesKurse[ $index ] = "'" . utf8_encode( $kunde[0] ) . "'";
            $duplicateKeyUpdateKurse [ $index ] = "`provider` = '" . utf8_encode( $kunde[0] ) . "'";
          } else {
            array_push( $insertIntoKurse, "`provider`" );
            array_push( $insertValuesKurse, "'" . utf8_encode( $kunde[0] ) . "'" );
            array_push( $duplicateKeyUpdateKurse, "`provider` = '" . utf8_encode( $kunde[0] ) . "'" );
          }

          try {
            $queryKurse = $kq . '( ' . implode( ',', $insertIntoKurse ) . ',`kurse`,`filename`,`filedate`,`db_status`,`created_by` ) VALUES ( ' . implode( ',', $insertValuesKurse ) . ', "ja", "' . $filename . '", "' . $filedate . '", "INSERT", "' . $_SESSION["username"] . '" )';
            $log_queryKurse = $klog.'( ' . implode( ',', $insertIntoKurse ) . ',`kurse`,`filename`,`filedate`,`db_status`,`created_by` ) VALUES ( ' . implode( ',', $insertValuesKurse ) . ', "ja","' . $filename . '", "' . $filedate . '", "INSERT", "' . $_SESSION["username"] . '" )';
            $queryKurse = $queryKurse . ' ON DUPLICATE KEY UPDATE ' . implode( ',', $duplicateKeyUpdateKurse ) . ", `kurse` = 'ja', `filename` = '" . $filename . "', `filedate` = '" . $filedate . "', `db_status` = 'UPDATE', `updated_at` = NOW(), `updated_by` = '" . $_SESSION["username"] . "'";

            $dbc->executeQuery( $log_queryKurse );
            $dbc->executeQuery( $queryKurse );
            $result = $dbc->fetchMessage( 'loadData' );

            if ( $result === 1 ) {
              $kurseCount = $kurseCount + $result;
            } elseif ( $result === 2 ) {
              $updateKurseCount++;
            } else {
              $errorLogKurse[] = $queryKurse;
              $errorFile = $_SERVER['DOCUMENT_ROOT'] . '/files/logs/' . $timestamp . '_kurse_error_log.txt';
              file_put_contents( $errorFile, print_r( $errorLogKurse, true ) );
            }

           } catch ( PDOException $exception ) {
             // Output expected PDOException.
             $excLogKurse[] = $queryKurse;
             $excLogKurse[] = $exception->getMessage();
             $excFile = $_SERVER['DOCUMENT_ROOT'] . '/files/logs/' . $timestamp . '_kurse_exception_log.txt';
             file_put_contents( $excFile, print_r( $excLogKurse, true ) );
             $kurseErrors++;
           } catch ( Exception $exception ) {
             // Output unexpected Exceptions.
             var_dump( $exception->getMessage() );
           }
        }

          $res[] = "Import in Tabelle: <b>" . $table[$c] . "</b><br>Anzahl Imports: " . $count . "<br>Anzahl Updates: " . $updateCount .
                   "<br>Anzahl Datei:" . count( $transformedData[$c] ) . "<br>Errors: " . $errors . "<br>Import in <b>Kurse:
                   </b><br>Anzahl Imports: " . $kurseCount . "<br>Anzahl Kurse Updates: " . $updateKurseCount . "<br>Errors: " . $kurseErrors . "<br>";
      } else {
        $res[] = "Import in Tabelle: <b>" . $table[$c] . "</b><br>Anzahl Imports: " . $count . "<br>Anzahl Updates: " . $updateCount .
        "<br>Anzahl Datei:" . count( $transformedData[$c] ) . "<br>Errors: " . $errors . "<br>";
      }


    }

    // if ( $count === count( $transformedData ) ) {
    //   $result = '<b>Erfolg!</b><br>Anzahl Imports: ' . $count . '<br>Anzahl Datei: ' . count( $transformedData );
    // } else {
    //   $result = 'Anzahl Imports: ' . $count . '<br>Anzahl Datei: ' . count( $transformedData );
    // }

    echo json_encode( $res );
 }

 /**
 * @brief function getDifferences()
 * @param $query, $table, $last_insert_id
 * @return -
 * @details function that gets the difference before an insert or update and highlights the changes in the log tables
 */
 function getDifferences( $query, $table, $last_insert_id ) {
   try {
     //Get the column data in the array. Before update.
     $beforeQuery = "SLECT * FROM '" . $table . "' WHERE id = '" . $last_insert_id . "' ";
     $dbc = new Connector();
     $dbc->executeQuery( $beforeQuery );
     $prev = $dbc->fetchAllResults( PDO::FETCH_ASSOC );

     // Update data

   } catch ( PDOException $exception ) {
     // Output expected PDOException.
     echo json_encode( $exception->getMessage() );
   } catch ( Exception $exception ) {
     // Output unexpected Exceptions.
     var_dump( $exception->getMessage() );
   }

 }

  /**
  * @brief function importCSV()
  * @param -
  * @return -
  * @details CSV Data gets import into the database and gets displayed in datatables
  * @details DEPRECATED
  */
   function importCSV() {
     // Allowed mime types
     $csvMimes = array( 'text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain' );
     $allowedFileType = ['application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
     // Validate whether selected file is a CSV file
     if( !empty( $_FILES['file']['name'] ) && in_array( $_FILES['file']['type'], $csvMimes ) ){
     $fileName = $_FILES["file"]["tmp_name"];
     // print("<pre>".print_r( $_FILES,true)."</pre>" );
     if( is_uploaded_file( $fileName ) ) {

         if ( $_FILES["file"]["size"] > 0 ) {
            $file = fopen( $fileName, "r" );
            // Skip the first line
            fgetcsv( $file );

            // Parse data from CSV file line by line
            while( ( $line = fgetcsv( $file, 10000, ";" ) ) !== false ) {
              // Get row data
              $custodian = (strlen( $line[0] ) <= 1) ? 'null' : '"'. $line[0] .'"';
              $swap_id = '"'. $line[1] .'"';
              $segment_id = (strlen( $line[2] ) <= 1) ? 'null' : '"'. $line[2] .'"';
              $contract_type = (strlen( $line[3] ) <= 1) ? 'null' : '"'. $line[3] .'"';
              $contract_style = (strlen( $line[4] ) <= 1) ? 'null' : '"'. $line[4] .'"';
              $start_date = (strlen( $line[5] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[5] ) ))) .'"';
              $maturity = (strlen( $line[6] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[6] ) ))) .'"';
              $clean_dirty = (strlen( $line[7] ) <= 1) ? 'null' : '"'. $line[7] .'"';
              $quotation = (strlen( $line[8] ) <= 1) ? 'null' : '"'. $line[8] .'"';
              $currency = (strlen( $line[9] ) <= 1) ? 'null' : '"'. $line[9] .'"';
              $notional = (strlen( $line[10] ) <= 1) ? 'null' : '"'. number_format((float)str_replace( ',', '.', $line[10] ), 2, '.', '' ) .'"';
              $notional_factor = '"'. number_format((float)str_replace( ',', '.', $line[11] ), 2, '.', '' ) .'"';
              $sro_isin = (strlen( $line[12] ) <= 1) ? 'null' : '"'. $line[12] .'"';
              $underlying_isin = (strlen( $line[13] ) <= 1) ? 'null' : '"'. $line[13] .'"';
              $seniority = (strlen( $line[14] ) <= 1) ? 'null' : '"'. $line[14] .'"';
              $six_telekurs_id =(strlen( $line[15] ) <= 1) ? 'null' : '"'. $line[15] .'"';
              $bloomberg_id_1 = (strlen( $line[16] ) <= 1) ? 'null' : '"'. $line[16] .'"';
              $bloomberg_key = (strlen( $line[17] ) <= 1) ? 'null' : '"'. $line[17] .'"';
              $reuters_ric = (strlen( $line[18] ) <= 1) ? 'null' : '"'. $line[18] .'"';
              $redpaircode = (strlen( $line[19] ) <= 1) ? 'null' : '"'. $line[19] .'"';
              $cusip = (strlen( $line[20] ) <= 1) ? 'null' : '"'. $line[20] .'"';
              $spread_id = (strlen( $line[21] ) <= 1) ? 'null' : '"'. $line[21] .'"';
              $product_type = (strlen( $line[22] ) <= 1) ? 'null' : '"'. $line[22] .'"';
              $currency_underlying = (strlen( $line[23] ) <= 1) ? 'null' : '"'. $line[23] .'"';
              $dealspread = '"'. number_format((float)str_replace( ',', '.', $line[24] ), 2, '.', '' ) .'"';
              $usanz = (strlen( $line[25] ) <= 1) ? 'null' : '"'. $line[25] .'"';
              $skadenz = ( Empty( $line[26] ) ) ? 'null' : $line[26];
              $recovery_rate = (strlen( $line[27] ) <= 1) ? 'null' : '"'. number_format((float)str_replace( ',', '.', $line[27] ), 2, '.', '' ) .'"';
              $fix_recovery_rate = (strlen( $line[28] ) <= 1) ? 'null' : '"'. number_format((float)str_replace( ',', '.', $line[28] ), 2, '.', '' ) .'"';
              $linked_swap_id = (strlen( $line[29] ) <= 1) ? 'null' : '"'. $line[29] .'"';
              $cds_attachment = '"'. number_format((float)str_replace( ',', '.', $line[30] ), 2, '.', '' ) .'"';
              $cds_detachment = '"'. number_format((float)str_replace( ',', '.', $line[31] ), 2, '.', '' ) .'"';
              $credit_events = (strlen( $line[32] ) <= 1) ? 'null' : '"'. $line[32] .'"';
              $implied_loss = '"'. number_format((float)str_replace( ',', '.', $line[33] ), 2, '.', '' ) .'"';
              $sector = (strlen( $line[34] ) <= 1) ? 'null' : '"'. $line[34] .'"';
              $subsector = (strlen( $line[35] ) <= 1) ? 'null' : '"'. $line[35] .'"';
              $rating = (strlen( $line[36] ) <= 1) ? 'null' : '"'. $line[36] .'"';
              $start_date_leg_1 = (strlen( $line[37] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[37] ) ))) .'"';
              $notional_leg_1 = (strlen( $line[38] ) <= 1) ? "null" : '"'. number_format((float)$line[38], 2, '.', '' ) .'"';
              $currency_leg_1 = (strlen( $line[39] ) <= 1) ? 'null' : '"'. $line[39] .'"';
              $contract_style_leg_1 = (strlen( $line[40] ) <= 1) ? 'null' : '"'. $line[40] .'"';
              $coupon_leg_1 = '"'. number_format((float)$line[41], 2, '.', '' ) .'"';
              $contract_position_leg_1 = (strlen( $line[42] ) <= 1) ? 'null' : '"'. $line[42] .'"';
              $quoted_margin_leg_1 = '"'. number_format((float)$line[43], 2, '.', '' ) .'"';
              $usanz_leg_1 = (strlen( $line[44] ) <= 1) ? 'null' : '"'. $line[44] .'"';
              $reset_skadenz_leg_1 = (strlen( $line[45] ) <= 1) ? 'null' : '"'. $line[45] .'"';
              $business_day_convention_leg_1 = (strlen( $line[46] ) <= 1) ? 'null' : '"'. $line[46] .'"';
              $pay_method_leg_1 = (strlen( $line[47] ) <= 1) ? 'null' : '"'. $line[47] .'"';
              $compounding_leg_1 = (strlen( $line[48] ) <= 1) ? 'null' : '"'. $line[48] .'"';
              $last_period_leg_1 = (strlen( $line[49] ) <= 1) ? 'null' : '"'. $line[49] .'"';
              $coupon_date_1st_leg_1 = (strlen( $line[50] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[50] ) ))) .'"';
              $pay_skadenz_leg_1 = ( Empty( $line[51] ) ) ? 'null' : $line[51];
              $lag_leg_1 = ( Empty( $line[52] ) ) ? 'null' : $line[52];
              $start_date_leg_2 = (strlen( $line[53] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[53] ) ))) .'"';
              $notional_leg_2 = (strlen( $line[54] ) <= 1) ? 'null' : '"'. number_format((float)str_replace( ',', '.', $line[54] ), 2, '.', '' ) .'"';
              $currency_leg_2 = (strlen( $line[55] ) <= 1) ? 'null' : '"'. $line[55] .'"';
              $contract_style_leg_2 = (strlen( $line[56] ) <= 1) ? 'null' : '"'. $line[56] .'"';
              $coupon_leg_2 = '"'. number_format((float)str_replace( ',', '.', $line[57] ), 2, '.', '' ) .'"';
              $contract_position_leg_2 = (strlen( $line[58] ) <= 1) ? 'null' : '"'. $line[58] .'"';
              $quoted_margin_leg_2 = '"'. number_format((float)str_replace( ',', '.', $line[59] ), 2, '.', '' ) .'"';
              $usanz_leg_2 = (strlen( $line[60] ) <= 1) ? 'null' : '"'. $line[60] .'"';
              $reset_skadenz_leg_2 = ( Empty( $line[61] ) ) ? 'null' : $line[61];
              $business_day_convention_leg_2 = (strlen( $line[62] ) <= 1) ? 'null' : '"'. $line[62] .'"';
              $pay_method_leg_2 = (strlen( $line[63] ) <= 1) ? 'null' : '"'. $line[63] .'"';
              $compounding_leg_2 = (strlen( $line[64] ) <= 1) ? 'null' : '"'. $line[64] .'"';
              $last_period_leg_2 = (strlen( $line[65] ) <= 1) ? 'null' : '"'. $line[65] .'"';
              $coupon_date_1st_leg_2 = (strlen( $line[66] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[66] ) ))) .'"';
              $pay_skadenz_leg_2 = ( Empty( $line[67] ) ) ? 'null' : $line[67];
              $lag_leg_2 = ( Empty( $line[68] ) ) ? 'null' : $line[68];
              $reset_date = (strlen( $line[69] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[69] ) ))) .'"';
              $reset_value = '"'. number_format((float)str_replace( ',', '.', $line[70] ), 2, '.', '' ) .'"';
              $start_index_level = '"'. number_format((float)str_replace( ',', '.', $line[71] ), 2, '.', '' ) .'"';
              $calendar = (strlen( $line[72] ) <= 1) ? 'null' : '"'. $line[72] .'"';
              $isda_definition = ( Empty( $line[73] ) ) ? 'null' : $line[73];
              $fx_rate = '"'. number_format((float)str_replace( ',', '.', $line[74] ), 2, '.', '' ) .'"';
              $volatility_strike = '"'. number_format((float)str_replace( ',', '.', $line[75] ), 2, '.', '' ) .'"';
              $volatility_cap = '"'. number_format((float)str_replace( ',', '.', $line[76] ), 2, '.', '' ) .'"';
              $variance_strike = '"'. number_format((float)str_replace( ',', '.', $line[77] ), 2, '.', '' ) .'"';
              $collateral = (strlen( $line[78] ) <= 1) ? 'null' : '"'. $line[78] .'"';
              $currency_csa = (strlen( $line[79] ) <= 1) ? 'null' : '"'. $line[79] .'"';
              $coupon_1 = (strlen( $line[80] ) < 1) ? 'null' : '"'. number_format((float)str_replace( ',', '.', $line[80] ), 2, '.', '' ) .'"';
              $coupon_2 = (strlen( $line[81] ) < 1) ? 'null' : '"'. number_format((float)str_replace( ',', '.', $line[81] ), 2, '.', '' ) .'"';
              $coupon_3 = (strlen( $line[82] ) < 1) ? 'null' : '"'. number_format((float)str_replace( ',', '.', $line[82] ), 2, '.', '' ) .'"';
              $poolfactor = (strlen( $line[83] ) < 1) ? 'null' : '"'. number_format((float)str_replace( ',', '.', $line[83] ), 2, '.', '' ) .'"';
              $purchase_rate = (strlen( $line[84] ) < 1) ? 'null' : '"'. number_format((float)str_replace( ',', '.', $line[84] ), 2, '.', '' ) .'"';
              $purchase_date = (strlen( $line[85] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[85] ) ))) .'"';
              $coupon_date_2nd = (strlen( $line[86] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[86] ) ))) .'"';
              $coupon_date_3rd = (strlen( $line[87] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[87] ) ))) .'"';
              $coupon_date_4th = (strlen( $line[88] ) <= 1) ? 'null' : '"'. implode( '-', array_reverse( Explode( '-', str_replace( '.', '-', $line[88] ) ))) .'"';
              $pay_off_function = (strlen( $line[89] ) <= 1) ? 'null' : '"'. $line[89] .'"';
              $counterparty_description = (strlen( $line[90] ) <= 1) ? 'null' : '"'. $line[90] .'"';

              // Check whether data already exists in the database
              $dbc = new Connector();
              $query = "SELECT swap_id FROM swap WHERE swap_id = $line[1]";
              $result = $dbc->executeQuery( $query );

              if ( $result->num_rows > 0){
                $query = "UPDATE swap SET custodian = $custodian, segment_id = $segment_id, contract_type = $contract_type WHERE swap_id = $swap_id";
              } else {
                $query = "INSERT INTO swap (
                  custodian, swap_id, segment_id, contract_type, contract_style, start_date, maturity, clean_dirty, quotation, currency, notional, notional_factor,
                  sro_isin, underlying_isin, seniority, six_telekurs_id, bloomberg_id_1, bloomberg_key, reuters_ric, redpaircode, cusip, spread_id, product_type,
                  currency_underlying, dealspread, usanz, skadenz, recovery_rate, fix_recovery_rate, linked_swap_id, cds_attachment, cds_detachment, credit_events,
                  implied_loss, sector, subsector, rating, start_date_leg_1, notional_leg_1, currency_leg_1, contract_style_leg_1, coupon_leg_1,
                  contract_position_leg_1, quoted_margin_leg_1, usanz_leg_1, reset_skadenz_leg_1, business_day_convention_leg_1, pay_method_leg_1, compounding_leg_1,
                  last_period_leg_1, coupon_date_1st_leg_1, pay_skadenz_leg_1, lag_leg_1, start_date_leg_2, notional_leg_2, currency_leg_2, contract_style_leg_2,
                  coupon_leg_2, contract_position_leg_2, quoted_margin_leg_2, usanz_leg_2, reset_skadenz_leg_2, business_day_convention_leg_2, pay_method_leg_2,
                  compounding_leg_2, last_period_leg_2, coupon_date_1st_leg_2, pay_skadenz_leg_2, lag_leg_2, reset_date, reset_value, start_index_level, calendar,
                  isda_definition, fx_rate, volatility_strike, volatility_cap, variance_strike, collateral, currency_csa, coupon_1, coupon_2, coupon_3, poolfactor,
                  purchase_rate, purchase_date, coupon_date_2nd, coupon_date_3rd, coupon_date_4th, pay_off_function, counterparty_description)
                  VALUES ( $custodian, $swap_id, $segment_id, $contract_type, $contract_style, $start_date, $maturity,
                  $clean_dirty, $quotation, $currency, $notional, $notional_factor, $sro_isin, $underlying_isin, $seniority,
                  $six_telekurs_id, $bloomberg_id_1, $bloomberg_key, $reuters_ric, $redpaircode, $cusip, $spread_id, $product_type,
                  $currency_underlying, $dealspread, $usanz, $skadenz, $recovery_rate, $fix_recovery_rate, $linked_swap_id,
                  $cds_attachment, $cds_detachment, $credit_events, $implied_loss, $sector, $subsector, $rating, $start_date_leg_1,
                  $notional_leg_1, $currency_leg_1, $contract_style_leg_1, $coupon_leg_1, $contract_position_leg_1, $quoted_margin_leg_1,
                  $usanz_leg_1, $reset_skadenz_leg_1, $business_day_convention_leg_1, $pay_method_leg_1, $compounding_leg_1, $last_period_leg_1,
                  $coupon_date_1st_leg_1, $pay_skadenz_leg_1, $lag_leg_1, $start_date_leg_2, $notional_leg_2, $currency_leg_2, $contract_style_leg_2,
                  $coupon_leg_2, $contract_position_leg_2, $quoted_margin_leg_2, $usanz_leg_2, $reset_skadenz_leg_2, $business_day_convention_leg_2,
                  $pay_method_leg_2, $compounding_leg_2, $last_period_leg_2, $coupon_date_1st_leg_2, $pay_skadenz_leg_2, $lag_leg_2, $reset_date,
                  $reset_value, $start_index_level, $calendar, $isda_definition, $fx_rate, $volatility_strike, $volatility_cap,
                  $variance_strike, $collateral, $currency_csa, $coupon_1, $coupon_2, $coupon_3, $poolfactor, $purchase_rate,
                  $purchase_date, $coupon_date_2nd, $coupon_date_3rd, $coupon_date_4th, $pay_off_function, $counterparty_description)";
              }

              try {
                $result = $dbc->executeQuery( $query );

                // if (!$result->execute()) {
                //     print_r( $result->errorInfo());
                // }
                $results = $dbc->fetchMessage( '' );
              } catch ( PDOException $e) {
                if ( $e->getCode() == 1062) {
                  // Take some action if there is a key constraint violation, i.e. duplicate name
                  echo $e;
                } else {
                  throw $e;
                }
              }

            }

            // Close opened CSV file
            fclose( $file );
            echo $exitMessage;
            echo "<a href=\"javascript:history.go(-1)\">Zurück</a>";
            exit;
         }
       }
     }
   }

   /**
   * @brief function extractHeader()
   * @param $elements - file details
   * @return -
   * @details extracts data header from a file
   */
    function extractHeader( $elements ) {
      // Allowed mime types
      $allowedFileType = ['application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                          'text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'text/plain'];

      // print("<pre>".print_r($_FILES,true)."</pre>");
      // echo $upload_max_size = ini_get('upload_max_filesize') . '<br>';
      // echo $post_max_size=ini_get('post_max_size');

      $filename = $_FILES["file"]["name"];
      $fileExtension = pathinfo( $filename, PATHINFO_EXTENSION );
      $file = $_FILES["file"]["tmp_name"];

      // Excel & CSV Single Tab
    if ( !empty( $filename ) && in_array( $_FILES['file']['type'], $allowedFileType ) ) {

        if ( is_uploaded_file( $file ) && $_FILES["file"]["size"] > 0 ) {
          try {

            if ( $fileExtension === 'xls' ) {
              $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } elseif ( $fileExtension === 'xlsx' ) {
              $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } elseif ( $fileExtension === 'csv' ) {
              $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            }

            $Reader->setReadDataOnly( false );

            try {
              $spreadSheet = $Reader->load( $file );
            } catch (\Exception $e) {
              echo json_encode( $e->getMessage() );
              exit;
            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
              echo json_encode( $e->getMessage() );
              exit;
            }

            // Single or multiple worksheets or single
            if ( $_POST['tabs'] === 1 ) {
              // Note that sheets are indexed from 0
              $excelSheet = $spreadSheet->getActiveSheet();
              $spreadSheetAry = $excelSheet->toArray();
              $maxCell = $excelSheet->getHighestRowAndColumn();
              $data = $excelSheet->rangeToArray( 'A1:' . $maxCell['column'] . $maxCell['row'] );
              $data = array_map( 'array_filter', $data );
              $data = array_filter( $data );
              $activeRow = $_POST['header_0'];
              --$activeRow;
              $header[] = $spreadSheetAry[$activeRow];

              // print("<pre>".print_r($data,true)."</pre>");

            // Excel & CSV Multi Tab
            } else {

              for ( $i=0; $i < $_POST['tabs']; $i++ ) {
                $headerFitter = 'header_' . $i;
                $headerRow = $_POST[$headerFitter];
                $excelSheet = $spreadSheet->getSheet($i);
                $spreadSheetAry = $excelSheet->toArray();
                $maxCell = $excelSheet->getHighestRowAndColumn();
                $data = $excelSheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row']);
                $data = array_map('array_filter', $data);
                $data = array_filter($data);
                $activeRow = $headerRow;
                --$activeRow;
                $header[] = $spreadSheetAry[$activeRow];

              }

            }

            echo json_encode( $header );
            exit;

          } catch (\Exception $exception) {
            echo json_encode( $exception->getMessage() );
          }

        } else {
          echo json_encode( "Datei konnte nicht hochgeladen werden!" );
        }

      } else {
        echo json_encode( "Ein Fehler ist beim lesen der Datei aufgetreten. Bitte versuchen Sie es erneut!" );
      }

    }

    /**
    * @brief saveETLSettings()
    * @param -
    * @return -
    * @details function to save the client/name to etl incl. the mapping
    */
    function saveETLSettings() {
        $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
        $etlName = $_POST["etlFilenames"];
        $filename = $_FILES['file']['name'];
        $ext = pathinfo( $filename, PATHINFO_EXTENSION );

        // removes all numbers before or after an underscore
        $filename = preg_replace( '/_[^_]+\d|\d+_/', '', $filename );

        // Check if filename is an EQSWAPS, if yes, then keep the EQSWAP Name and cut the rest
        if ( strpos( $filename, '-' ) !== false ) {

          $cleanedFilename = explode( "-", $filename, 2 );
          $filenameTemp = $cleanedFilename[0];
          if ( strpos( $filename, 'EQSWAPE16X' ) !== false ||
               strpos( $filename, 'EQSWAPE18MDX' ) !== false ||
               strpos( $filename, 'EQSWAPE20MDX' ) !== false ||
               strpos( $filename, 'EQSWAPE35AX' ) !== false ||
             	 strpos( $filename, 'EQSWAPE37X' ) !== false ||
               strpos( $filename, 'EQSWAPE40X' ) !== false ) {

              $file = $filenameTemp;
              $filename = str_replace( ' ', '', $file );
              $filename .= "." . $ext;
          }

        }

        $kunde = $_POST["kunde"];
        $isin = $_POST["isin"];
        $currency = $_POST["currency"];
        $header = $_POST["header_0"];
        if ( $kurseMapping ) { $kurse_yes_no = 'ja'; } else { $kurse_yes_no = ''; }

        // get number of tables for multi settings ETL
        // $anz = substr( $header, -1 );
        $anz = $_POST["tabs"];
        $finalResults = array();
        // fetchAndDeleteETLName( $etlName );

        for ( $i=0; $i < $anz; $i++ ) {
          $table = $_POST["dbTable_" . $i];
          $header = $_POST["header_" . $i];
          $startRow = $_POST["startRow_" . $i];
          $mapping = $_POST["resultMapping_" . $i];
          if ( isset( $_POST["resultMappingKurse_" . $i] ) ) {
            $kurseMapping = $_POST["resultMappingKurse_" . $i];
          } else {
            $kurseMapping = '';
          }
          $arrKurseName = 'kurseTransformation' . $i;
          $arrName = 'transformation' . $i;

          // if main table is kurse, then also set $kurse_yes_no to $kurse_yes_no
          if ( $table === 'kurse' || $kurseMapping ) { $kurse_yes_no = 'ja'; } else { $kurse_yes_no = ''; }

          // test if the mapping is in JSON format
          // if ( json_last_error() === JSON_ERROR_NONE || json_last_error() === 0 ) {
          //   echo gettype( $mapping);
          // }

          try {
            // Loop through $_POST and get all param for the Kurse transformations first and save off to an array
            foreach ( $_POST as $name => $val ) {
              if ( strpos( $name, "ETLFilter-mappingTableKurse" ) !== false ) {

                // get table number off variable
                $tableNum = substr( $name, 28, 1 );
                if ( $tableNum === substr( $arrKurseName, -1 ) ) {
                  // get name of the transformation, starting from the 30th character
                  $name = substr( $name, 30 );
                  // if name contains a space, then add it back
                  // frontend uses "-space-"
                  if ( strpos( $name, "-space-" ) !== false ) {
                    $name = str_replace( '-space-', ' ', $name );
                  }
                  // if name contains a "%" sign, then add it back
                  // frontend uses "etlPercent"
                  if ( strpos( $name, "etlPercent" ) !== false ) {
                    $name = str_replace( 'etlPercent', '%', $name );
                  }
                  // if name contains a "/" sign, then add it back
                  // frontend uses "-dash-"
                  if ( strpos( $name, "-dash-" ) !== false ) {
                    $name = str_replace( '-dash-', '/', $name );
                  }
                  // if name contains a "+" sign, then add it back
                  // frontend uses "-plus-"
                  if ( strpos( $name, "-plus-" ) !== false ) {
                    $name = str_replace( '-plus-', '+', $name );
                  }
                  // if name contains a "." sign, then add it back
                  // frontend uses "-dot-"
                  if ( strpos( $name, "-dot-" ) !== false ) {
                    $name = str_replace( '-dot-', '.', $name );
                  }
                  $$arrKurseName[$name] = $val;
                }
              }
            }

            // Loop through $_POST and get all param for transformations and save off to an array
            foreach ( $_POST as $name => $val ) {
              if ( strpos( $name, "ETLFilter-" ) !== false ) {

                // get table number off variable
                $tableNum = substr( $name, 23, 1 );
                if ( $tableNum === substr( $arrName, -1 ) ) {
                  // get name of the transformation, starting from the 25th character
                  $name = substr( $name, 25 );
                  // if name contains a space, then add it back
                  // frontend uses "-space-"
                  if ( strpos( $name, "-space-" ) !== false ) {
                    $name = str_replace( '-space-', ' ', $name );
                  }
                  // if name contains a "%" sign, then add it back include
                  // frontend uses "etlPercent"
                  if ( strpos( $name, "etlPercent" ) !== false ) {
                    $name = str_replace( 'etlPercent', '%', $name );
                  }
                  // if name contains a "/" sign, then add it back include
                  // frontend uses "-dash-"
                  if ( strpos( $name, "-dash-" ) !== false ) {
                    $name = str_replace( '-dash-', '/', $name );
                  }
                  // if name contains a "+" sign, then add it back include
                  // frontend uses "-plus-"
                  if ( strpos( $name, "-plus-" ) !== false ) {
                    $name = str_replace( '-plus-', '+', $name );
                  }
                  // if name contains a "." sign, then add it back include
                  // frontend uses "-dot-"
                  if ( strpos( $name, "-dot-" ) !== false ) {
                    $name = str_replace( '-dot-', '.', $name );
                  }
                  $$arrName[$name] = $val;
                }
              }
            }

            //convert Array to JSON to save into database
            // $transformationen = json_encode( $transformation );
            $transformationen = json_encode( ${'transformation'.$i} );

            if ( isset( ${'kurseTransformation'.$i} ) ) {
              $kurse_transformationen = json_encode( ${'kurseTransformation'.$i} );
            } else {
              $kurse_transformationen = '';
            }

            $dbc = new Connector();

            // check if empty name exists
            $query = "SELECT `name` FROM `etl` WHERE `name` = '" . $etlName . "' AND `table` IS NULL AND `kunde` IS NULL AND `header` IS NULL AND `row_start` IS NULL";
            $dbc->executeQuery( $query );
            $results = $dbc->fetchMessage( 'getClients' );
            // insert or update query
            if ( $results === 'exists' ) {
              $query = "UPDATE `etl` SET `filename` = '" . $filename . "', `table` = '" . $table . "', `kunde` = '" . $kunde . "', `header` = '" . $header . "', `row_start` = '" . $startRow . "', `lache` = '" . $i . "',
                        `mapping` = '" . $mapping . "', `kurse_mapping` = '" . $kurseMapping . "', `transformationen` = '" . $transformationen . "', `kurse_transformationen` = '" . $kurse_transformationen . "',
                        `kurse` = '" . $kurse_yes_no . "', `ISIN` = '" . $isin . "', `currency` = '" . $currency . "', `updated_by` = '" . $_SESSION["username"] . "'
                        WHERE `name` = '" . $etlName . "' ";
            } else {

              $query = "SELECT `name` FROM `etl` WHERE `name` = '" . $etlName . "' AND `table` = '" . $table . "' AND `kunde` = '" . $kunde . "' AND `header` = '" . $header . "' AND `row_start` = '" . $startRow . "' ";
              $dbc->executeQuery( $query );
              $results = $dbc->fetchMessage( 'getClients' );
              // insert or update query
              if ( $results === 'exists' ) {
                $query = "UPDATE `etl` SET `filename` = '" . $filename . "', `table` = '" . $table . "', `kunde` = '" . $kunde . "', `header` = '" . $header . "', `row_start` = '" . $startRow . "', `lache` = '" . $i . "',
                          `mapping` = '" . $mapping . "', `kurse_mapping` = '" . $kurseMapping . "', `transformationen` = '" . $transformationen . "', `kurse_transformationen` = '" . $kurse_transformationen . "',
                          `kurse` = '" . $kurse_yes_no . "', `ISIN` = '" . $isin . "', `currency` = '" . $currency . "', `updated_by` = '" . $_SESSION["username"] . "'
                          WHERE `name` = '" . $etlName . "' AND `table` = '" . $table . "' AND `kunde` = '" . $kunde . "' AND `header` = '" . $header . "' AND `row_start` = '" . $startRow . "' ";
              } else {
                $query = "INSERT INTO etl (`name`, `filename`, `table`, `kunde`, `header`, `row_start`, `lache`, `mapping`, `kurse_mapping`, `transformationen`, `kurse_transformationen`, `kurse`, `ISIN`, `currency`, `created_by`)
                VALUES ( '" . $etlName . "', '" . $filename . "', '" . $table . "', '" . $kunde . "', '" . $header . "', '" . $startRow . "', '" . $i . "', '" . $mapping . "', '" . $kurseMapping . "',
                         '" . $transformationen . "', '" . $kurse_transformationen . "', '" . $kurse_yes_no . "', '" . $isin . "', '" . $currency . "', '" . $_SESSION["username"] . "' )";
              }

            }

            try {
              $dbc->executeQuery( $query );
              array_push( $finalResults, $dbc->fetchMessage( '' ) );
            } catch ( PDOException $exception ) {
              // Output expected PDOException.
              echo json_encode( $exception->getMessage() );
            } catch ( Exception $exception ) {
              // Output unexpected Exceptions.
              var_dump( $exception->getMessage() );
            }

            // Logging::Log( $results );
          } catch ( PDOException $exception ) {
            // Output expected PDOException.
            var_dump( $exception->getMessage() );
          } catch ( Exception $exception ) {
            // Output unexpected Exceptions.
            var_dump( $exception->getMessage() );
          }

        }
        unset( $_POST[$name] );

        $count = 0;
        foreach( $finalResults as $result ) {
          if ( $result === 'Erfolgreich hinzugefuegt!' ) {
            $count++;
          }
        }
        $results = $count . ' von ' . $anz . ' Einstellung/en erfolgreich angelegt!';
        echo json_encode( $results );

    }

    /**
    * @brief getDBHeaders()
    * @param -
    * @return -
    * @details function to get all the headers from the databse from a specific table
    */
    function getDBHeaders() {
        $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
        $dbc = new Connector();
        $tables = $_POST["tables"];

        foreach ($tables as $table) {
          $query = "SHOW COLUMNS FROM " . $table;

          try {
            $dbc->executeQuery( $query );
            $results[] = $dbc->fetchInformation( PDO::FETCH_ASSOC, 'getDBHeaders' );

            // Logging::Log( $results );
          } catch ( PDOException $exception ) {
            // Output expected PDOException.
            var_dump( $exception->getMessage() );
          } catch ( Exception $exception ) {
            // Output unexpected Exceptions.
            var_dump( $exception->getMessage() );
          }
        }

        echo json_encode( $results );
    }

    /**
    * @brief fetchAndDeleteETLName()
    * @param $filename
    * @return -
    * @details function to check for an empty name for the ETL Setting and delete it before inserting
    */
    function fetchAndDeleteETLName( $filename ) {
      $dbc = new Connector();
      $query = "SELECT `id` FROM qvs.etl WHERE `name`='" . $filename . "' AND `filename` IS NULL AND `table`IS NULL AND `kunde` IS NULL AND `header` IS NULL";

      try {
        $dbc->executeQuery( $query );
        $res = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
        if ( $res ) {
          $id = $res[0]['id'];

          if ( $id ) {
            $query = "DELETE FROM qvs.etl WHERE `id`='" . $id . "'";

            try {
              $dbc->executeQuery( $query );
              // Logging::Log( $results );
            } catch ( PDOException $exception ) {
              // Output expected PDOException.
              echo json_encode( $exception->getMessage() );
            } catch ( Exception $exception ) {
              // Output unexpected Exceptions.
              var_dump( $exception->getMessage() );
            }
          }
        }
        // Logging::Log( $results );
      } catch ( PDOException $exception ) {
        // Output expected PDOException.
        echo json_encode( $exception->getMessage() );
      } catch ( Exception $exception ) {
        // Output unexpected Exceptions.
        var_dump( $exception->getMessage() );
      }

    }

    /*---------------------------------------------------------------------
    |
    |
    |                       Transformation Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief displayTransformations()
    * @param -
    * @return -
    * @details function to display transformations from the database and display in datatables
    */
    function displayTransformations() {
        try {
            $dbc = new Connector();
            $query = "SELECT * FROM transformations";
            $dbc->executeQuery( $query );

            $results = $dbc->fetchDataTablesRequest( PDO::FETCH_ASSOC, 'displayTransformations' );
            echo json_encode( $results );
            // Logging::Log( $results );
        } catch ( PDOException $exception ) {
            // Output expected PDOException.
            var_dump( $exception->getMessage() );
        } catch ( Exception $exception ) {
            // Output unexpected Exceptions.
            var_dump( $exception->getMessage() );
        }
    }

    /**
    * @brief addTransformation()
    * @param -
    * @return -
    * @details function to add transformation to the database
    */
    function addTransformation() {
      try {
          $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
          $dbc = new Connector();
          $query = "INSERT INTO transformations (name, script) VALUES ( '" . $_POST["transformationName"] . "', '" . $_POST["transformationCode"] . "' )";
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( '' );
          echo json_encode( $results );
          // Logging::Log( $results );
      } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
      }
    }

    /**
    * @brief getTransformations()
    * @param -
    * @return -
    * @details function to get all transformations from the database into a dropdown
    */
    function getTransformations( $type ) {
        try {
          $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
          $dbc = new Connector();
          if ( $type === 'aktualisieren' ) {
            $query = "SELECT transformation_id, name, script FROM transformations WHERE transformation_id = '" . $_POST["transformation_id"] . "'";
          } else {
            $query = "SELECT transformation_id, name, script FROM transformations";
          }
          $dbc->executeQuery( $query );
          $results = $dbc->fetchInformation( PDO::FETCH_ASSOC, 'getTransformations' );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
        }
      }

    /**
    * @brief updateTransformation()
    * @param -
    * @return -
    * @details function to update a transformations from the database
    */
    function updateTransformation() {
        try {
          $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
          $dbc = new Connector();
          $query = "UPDATE transformations SET name = '" . $_POST["transformationName"] . "', script = '" . $_POST["transformationCode"] . "', updated_by = '" . $_SESSION['username'] . "' WHERE transformation_id = '" . $_POST["transformation_id"] . "'";
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( '' );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
        }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       Name Convention Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief displayNameConventions()
    * @param -
    * @return -
    * @details function to display name conventions from the database and display in datatables
    */
    function displayNameConventions() {
        try {
            $dbc = new Connector();
            $query = "SELECT * FROM name_conventions";
            $dbc->executeQuery( $query );

            $results = $dbc->fetchDataTablesRequest( PDO::FETCH_ASSOC, 'displayNameConventions' );
            echo json_encode( $results );
            // Logging::Log( $results );
        } catch ( PDOException $exception ) {
            // Output expected PDOException.
            var_dump( $exception->getMessage() );
        } catch ( Exception $exception ) {
            // Output unexpected Exceptions.
            var_dump( $exception->getMessage() );
        }
    }

    /**
    * @brief getNameConvention()
    * @param -
    * @return -
    * @details function to get all transformname conventions from the database into fields
    */
    function getNameConvention() {
        try {
          $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
          $dbc = new Connector();
          $query = "SELECT name, convention FROM name_conventions WHERE id = '" . $_POST["id"] . "'";

          $dbc->executeQuery( $query );
          $results = $dbc->fetchInformation( PDO::FETCH_ASSOC, 'getNameConvention' );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
        }
      }

    /**
    * @brief addNameConvention()
    * @param -
    * @return -
    * @details function to add name convention to the database
    */
    function addNameConvention() {
      try {
          $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
          $dbc = new Connector();
          $query = "INSERT INTO name_conventions (name, convention) VALUES ( '" . $_POST["nameConventionsName"] . "', '" . $_POST["nameConventions"] . "' )";
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( '' );
          echo json_encode( $results );
          // Logging::Log( $results );
      } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
      }
    }

    /**
    * @brief updateNameConvention()
    * @param -
    * @return -
    * @details function to update a name convention from the database
    */
    function updateNameConvention() {
        try {
          $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
          $dbc = new Connector();
          $query = "UPDATE name_conventions SET name = '" . $_POST["nameConventionsName"] . "', convention = '" . $_POST["nameConventions"] . "', updated_by = '" . $_SESSION['username'] . "' WHERE id = '" . $_POST["id"] . "'";
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( '' );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
        }
    }

    /**
    * @brief deleteNameConvention()
    * @param -
    * @return -
    * @details function to a name convention from the datatables
    */
    function deleteNameConvention() {
        try {
          include_once( dirname( $_SERVER['DOCUMENT_ROOT'] ) ."/inc/role.inc.php" );
          include_once( dirname( $_SERVER['DOCUMENT_ROOT'] ) ."/inc/priviliged-user.inc.php" );
          $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

          if ( isset( $_SESSION["user_id"] ) ) {
            $privUser = new PrivilegedUser();
            $u = $privUser->getByUserID( $_SESSION["user_id"] );
          }

          if ( $u->hasRole( "Admin" )) {
            $dbc = new Connector();
            $query = "DELETE FROM name_conventions WHERE id = " . $_POST["id"] . "";
            $dbc->executeQuery( $query );
          }
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
        }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       Wertpapiere Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getWertpapier()
    * @param -
    * @return -
    * @details function to get selected Wertpapier from the database
    */
    function getWertpapier() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM qvs_bonds_staticData WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateWertpapier()
    * @param -
    * @return -
    * @details function to update wertpapier in the database
    */
    function updateWertpapier() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE qvs_bonds_staticData
        SET client = '" . $_POST["client"] . "', isin = '" . $_POST["isin"] . "',  initial_notional = '" . $_POST["initial_notional"] . "',
        issue_currency = '" . $_POST["issue_currency"] . "', issue_date = '" . $_POST["issue_date"] . "', maturity = '" . $_POST["maturity"] . "',
        coupon = '" . $_POST["coupon"] . "', z_spread = '" . $_POST["z_spread"] . "', l_spread = '" . $_POST["l_spread"] . "',
        margin = '" . $_POST["margin"] . "', dirty_clean = '" . $_POST["dirty_clean"] . "', listing = '" . $_POST["listing"] . "',
        period = '" . $_POST["period"] . "', day_count = '" . $_POST["day_count"] . "', instrument = '" . $_POST["instrument"] . "',
        security_type2 = '" . $_POST["security_type2"] . "', sector = '" . $_POST["sector"] . "', subsector = '" . $_POST["subsector"] . "',
        rating = '" . $_POST["rating"] . "', seniority = '" . $_POST["seniority"] . "', spread_ticker = '" . $_POST["spread_ticker"] . "',
        poolfactor = '" . $_POST["poolfactor"] . "', security_description = '" . $_POST["security_description"] . "', initial_value = '" . $_POST["initial_value"] . "',
        closing_date = '" . $_POST["closing_date"] . "', coupon_date = '" . $_POST["coupon_date"] . "', pay_off = '" . $_POST["pay_off"] . "',
        pay_off_method = '" . $_POST["pay_off_method"] . "', drowdown_dates = '" . $_POST["drowdown_dates"] . "', drowdown_notional = '" . $_POST["drowdown_notional"] . "',
        filename = '" . $_POST["filename"] . "', filedate = '" . $_POST["filedate"] . "', status = '" . $_POST["status"] . "',
        updated_by = '" . $_SESSION['username'] . "' WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }

    }

    /**
    * @brief deleteWertpapier()
    * @param -
    * @return -
    * @details function to delete wertpapier from the datatables
    */
    function deleteWertpapier() {
      include_once( $_SERVER['DOCUMENT_ROOT'] ."/inc/role.inc.php" );
      include_once( $_SERVER['DOCUMENT_ROOT'] ."/inc/priviliged-user.inc.php" );
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_SESSION["user_id"] ) ) {
        $privUser = new PrivilegedUser();
        $u = $privUser->getByUserID( $_SESSION["user_id"] );
      }

      if ( $u->hasRole( "Admin" ) || $u->hasRole( "Manager" ) ) {
        $database = new cDBConnection();

        $query = "DELETE FROM qvs_bonds_staticData WHERE id = " . $_POST["id"] . "";

        try {
          $stmt = $database->dbc->prepare( $query );
          $stmt->execute();
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
        }

      } else {
        echo json_encode( 'Sie verfuegen nicht die Berechtigung eine Loeschung durchzufuehren!' );
      }

    }

    /*---------------------------------------------------------------------
    |
    |
    |                       HSBC INKA Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getHSBCINKA()
    * @param -
    * @return -
    * @details function to get selected HSBCINKA from the database
    */
    function getHSBCINKA() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM hsbc_inka WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateHSBCINKA()
    * @param -
    * @return -
    * @details function to update HSBCINKA in the database
    */
    function updateHSBCINKA() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE hsbc_inka
        SET client = '" . $_POST["client"] . "', isin = '" . $_POST["isin"] . "',  initial_notional = '" . $_POST["initial_notional"] . "',
        issue_currency = '" . $_POST["issue_currency"] . "', issue_date = '" . $_POST["issue_date"] . "', maturity = '" . $_POST["maturity"] . "',
        coupon = '" . $_POST["coupon"] . "', z_spread = '" . $_POST["z_spread"] . "', l_spread = '" . $_POST["l_spread"] . "',
        margin = '" . $_POST["margin"] . "', dirty_clean = '" . $_POST["dirty_clean"] . "', listing = '" . $_POST["listing"] . "',
        period = '" . $_POST["period"] . "', day_count = '" . $_POST["day_count"] . "', instrument = '" . $_POST["instrument"] . "',
        security_type2 = '" . $_POST["security_type2"] . "', sector = '" . $_POST["sector"] . "', subsector = '" . $_POST["subsector"] . "',
        rating = '" . $_POST["rating"] . "', seniority = '" . $_POST["seniority"] . "', spread_ticker = '" . $_POST["spread_ticker"] . "',
        poolfactor = '" . $_POST["poolfactor"] . "', security_description = '" . $_POST["security_description"] . "', initial_value = '" . $_POST["initial_value"] . "',
        closing_date = '" . $_POST["closing_date"] . "', coupon_date = '" . $_POST["coupon_date"] . "', pay_off = '" . $_POST["pay_off"] . "',
        pay_off_method = '" . $_POST["pay_off_method"] . "', drowdown_dates = '" . $_POST["drowdown_dates"] . "', drowdown_notional = '" . $_POST["drowdown_notional"] . "',
        filename = '" . $_POST["filename"] . "', filedate = '" . $_POST["filedate"] . "', status = '" . $_POST["status"] . "',
        updated_by = '" . $_SESSION['username'] . "' WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       Optionen Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getOptionen()
    * @param -
    * @return -
    * @details function to get selected HSBCINKA from the database
    */
    function getOptionen() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM qvs_optionen_staticData WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateOptionen()
    * @param -
    * @return -
    * @details function to update HSBCINKA in the database
    */
    function updateOptionen() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE qvs_optionen_staticData
        SET `client` = '" . $_POST["client"] . "', `option_id` = '" . $_POST["option_id"] . "', `option_currency` = '" . $_POST["option_currency"] . "',
        `issue_currency` = '" . $_POST["issue_currency"] . "', `underlying_description` = '" . $_POST["underlying_description"] . "', `underlying_id` = '" . $_POST["underlying_id"] . "',
        `underlying_dividends` = '" . $_POST["underlying_dividends"] . "', `contract_type_option` = '" . $_POST["contract_type_option"] . "', `options_type` = '" . $_POST["options_type"] . "',
        `option_style` = '" . $_POST["option_style"] . "', `strike` = '" . $_POST["strike"] . "', `settle_date_option` = '" . $_POST["settle_date_option"] . "',
        `maturity_option` = '" . $_POST["maturity_option"] . "', `security_type` = '" . $_POST["security_type"] . "', `start_date_swap` = '" . $_POST["start_date_swap"] . "',
        `maturity_swap` = '" . $_POST["maturity_swap"] . "', `contract_type_swap` = '" . $_POST["contract_type_swap"] . "', `day_count_swap_leg1` = '" . $_POST["day_count_swap_leg1"] . "',
        `spread_swap_leg1` = '" . $_POST["spread_swap_leg1"] . "', `period_swap_leg2` = '" . $_POST["period_swap_leg2"] . "', `day_count_swap_leg2` = '" . $_POST["day_count_swap_leg2"] . "',
        `spread_swap_leg2` = '" . $_POST["spread_swap_leg2"] . "', `contract_size` = '" . $_POST["contract_size"] . "', `quotation` = '" . $_POST["quotation"] . "',
        `barrier_type` = '" . $_POST["barrier_type"] . "', `barrier_direction` = '" . $_POST["barrier_direction"] . "', `barrier_1` = '" . $_POST["barrier_1"] . "',
        `barrier_2` = '" . $_POST["barrier_2"] . "', `window_start_date` = '" . $_POST["window_start_date"] . "', `barrier_hit_date_1` = '" . $_POST["barrier_hit_date_1"] . "',
        `barrier_hit_date_2` = '" . $_POST["barrier_hit_date_2"] . "', `rebate1` = '" . $_POST["rebate1"] . "', `rebate2` = '" . $_POST["rebate2"] . "',
        `trade_date_2` = '" . $_POST["trade_date_2"] . "', `trade_price` = '" . $_POST["trade_price"] . "', `counter_party` = '" . $_POST["counter_party"] . "',
        `filename` = '" . $_POST["filename"] . "', `filedate` = '" . $_POST["filedate"] . "', `status` = '" . $_POST["status"] . "',
        `updated_by` = '" . $_SESSION["username"] . "' WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       Zinskurven Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getZsk()
    * @param -
    * @return -
    * @details function to get selected ZSK from the database
    */
    function getZsk() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM zsk WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateZsk()
    * @param -
    * @return -
    * @details function to update ZSK in the database
    */
    function updateZsk() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE zsk
        SET `datum` = '" . $_POST["datum"] . "', `currency` = '" . $_POST["currency"] . "', `currency_id` = '" . $_POST["currency_id"] . "',
        `skadenz` = '" . $_POST["skadenz"] . "', `skadenz_id` = '" . $_POST["skadenz_id"] . "', `ON` = '" . $_POST["ON"] . "',
        `TN` = '" . $_POST["TN"] . "', `1W` = '" . $_POST["1W"] . "', `1M` = '" . $_POST["1M"] . "', `2M` = '" . $_POST["2M"] . "',
        `3M` = '" . $_POST["3M"] . "', `6M` = '" . $_POST["6M"] . "', `9M` = '" . $_POST["9M"] . "', `1Y` = '" . $_POST["1Y"] . "',
        `1Y3` = '" . $_POST["1Y3"] . "', `1Y6` = '" . $_POST["1Y6"] . "', `1Y9` = '" . $_POST["1Y9"] . "', `2Y` = '" . $_POST["2Y"] . "',
        `2Y3` = '" . $_POST["2Y3"] . "', `2Y6` = '" . $_POST["2Y6"] . "', `2Y9` = '" . $_POST["2Y9"] . "', `3Y` = '" . $_POST["3Y"] . "',
        `3Y3` = '" . $_POST["3Y3"] . "', `3Y6` = '" . $_POST["3Y6"] . "', `3Y9` = '" . $_POST["3Y9"] . "', `4Y` = '" . $_POST["4Y"] . "',
        `4Y3` = '" . $_POST["4Y3"] . "', `4Y6` = '" . $_POST["4Y6"] . "', `4Y9` = '" . $_POST["4Y9"] . "', `5Y` = '" . $_POST["5Y"] . "',
        `5Y3` = '" . $_POST["5Y3"] . "', `5Y6` = '" . $_POST["5Y6"] . "', `5Y9` = '" . $_POST["5Y9"] . "', `6Y` = '" . $_POST["6Y"] . "',
        `6Y3` = '" . $_POST["6Y3"] . "', `6Y6` = '" . $_POST["6Y6"] . "', `6Y9` = '" . $_POST["6Y9"] . "', `7Y` = '" . $_POST["7Y"] . "',
        `7Y3` = '" . $_POST["7Y3"] . "', `7Y6` = '" . $_POST["7Y6"] . "', `7Y9` = '" . $_POST["7Y9"] . "', `8Y` = '" . $_POST["8Y"] . "',
        `8Y3` = '" . $_POST["8Y3"] . "', `8Y6` = '" . $_POST["8Y6"] . "', `8Y9` = '" . $_POST["8Y9"] . "', `9Y` = '" . $_POST["9Y"] . "',
        `9Y3` = '" . $_POST["9Y3"] . "', `9Y6` = '" . $_POST["9Y6"] . "', `9Y9` = '" . $_POST["9Y9"] . "', `10Y` = '" . $_POST["10Y"] . "',
        `11Y` = '" . $_POST["11Y"] . "', `12Y` = '" . $_POST["12Y"] . "', `13Y` = '" . $_POST["13Y"] . "', `14Y` = '" . $_POST["14Y"] . "',
        `15Y` = '" . $_POST["15Y"] . "', `20Y` = '" . $_POST["20Y"] . "', `25Y` = '" . $_POST["25Y"] . "', `30Y` = '" . $_POST["30Y"] . "',
        `40Y` = '" . $_POST["40Y"] . "', `50Y` = '" . $_POST["50Y"] . "', `filename` = '" . $_POST["filename"] . "', `filedate` = '" . $_POST["filedate"] . "',
        `status` = '" . $_POST["status"] . "', `updated_by` = '" . $_SESSION['username'] . "' WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       Spreads Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getSpreads()
    * @param -
    * @return -
    * @details function to get selected Spreads from the database
    */
    function getSpreads() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM spreads WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateSpreads()
    * @param -
    * @return -
    * @details function to update Spreads in the database
    */
    function updateSpreads() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE spreads
        SET `datum` = '" . $_POST["datum"] . "', `sector` = '" . $_POST["sector"] . "',  `sector_id` = '" . $_POST["sector_id"] . "',
        `subsector` = '" . $_POST["subsector"] . "', `subsector_id` = '" . $_POST["subsector_id"] . "', `currency` = '" . $_POST["currency"] . "',
        `currency_id` = '" . $_POST["currency_id"] . "', `rating` = '" . $_POST["rating"] . "', `rating_id` = '" . $_POST["rating_id"] . "',
        `3M` = '" . $_POST["3M"] . "', `6M` = '" . $_POST["6M"] . "', `1Y` = '" . $_POST["1Y"] . "', `2Y` = '" . $_POST["2Y"] . "',
        `3Y` = '" . $_POST["3Y"] . "', `4Y` = '" . $_POST["4Y"] . "', `5Y` = '" . $_POST["5Y"] . "', `6Y` = '" . $_POST["6Y"] . "',
        `7Y` = '" . $_POST["7Y"] . "', `8Y` = '" . $_POST["8Y"] . "', `9Y` = '" . $_POST["9Y"] . "', `10Y` = '" . $_POST["10Y"] . "',
        `12Y` = '" . $_POST["12Y"] . "', `15Y` = '" . $_POST["15Y"] . "', `20Y` = '" . $_POST["20Y"] . "', `25Y` = '" . $_POST["25Y"] . "',
        `30Y` = '" . $_POST["30Y"] . "', `50Y` = '" . $_POST["50Y"] . "', `kurs_quelle` = '" . $_POST["kurs_quelle"] . "', `filename` = '" . $_POST["filename"] . "',
        `filedate` = '" . $_POST["filedate"] . "', `status` = '" . $_POST["status"] . "', `updated_by` = '" . $_SESSION['username'] . "' WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       Devisen Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getDevisen()
    * @param -
    * @return -
    * @details function to get selected Devisen from the database
    */
    function getDevisen() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM data WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateDevisen()
    * @param -
    * @return -
    * @details function to update Devisen in the database
    */
    function updateDevisen() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE devisen
        SET base = '" . $_POST["base"] . "', quote = '" . $_POST["quote"] . "',  spot = '" . $_POST["spot"] . "',
        datum = '" . $_POST["datum"] . "', kurs_quelle = '" . $_POST["kurs_quelle"] . "', filename = '" . $_POST["filename"] . "',
        filedate = '" . $_POST["filedate"] . "', status = '" . $_POST["status"] . "', updated_by = '" . $_SESSION['username'] . "'
        WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       Kurse Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getKurse()
    * @param -
    * @return -
    * @details function to get selected Kurs from the database
    */
    function getKurse() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM kurse WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateKurse()
    * @param -
    * @return -
    * @details function to update Kurs in the database
    */
    function updateKurse() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE kurse
        SET `datum` = '" . $_POST["datum"] . "', `kurs` = '" . $_POST["kurs"] . "', `kurs_dirty` = '" . $_POST["kurs_dirty"] . "',
        `currency` = '" . $_POST["currency"] . "', `provider` = '" . $_POST["provider"] . "', `assetklasse` = '" . $_POST["assetklasse"] . "',
        `difference` = '" . $_POST["difference"] . "', `filename` = '" . $_POST["filename"] . "', `filedate` = '" . $_POST["filedate"] . "',
        `status` = '" . $_POST["status"] . "', `updated_by` = '" . $_SESSION["username"] . "' WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /**
    * @brief deleteKurse()
    * @param -
    * @return -
    * @details function to delete Kurs from the datatables
    */
    function deleteKurse() {
      include_once( $_SERVER['DOCUMENT_ROOT'] ."/inc/role.inc.php" );
      include_once( $_SERVER['DOCUMENT_ROOT'] ."/inc/priviliged-user.inc.php" );
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_SESSION["user_id"] ) ) {
        $privUser = new PrivilegedUser();
        $u = $privUser->getByUserID( $_SESSION["user_id"] );
      }

      if ( $u->hasRole( "Admin" ) || $u->hasRole( "Manager" ) ) {
        $database = new cDBConnection();

        $query = "DELETE FROM qvs_bonds_staticData WHERE id = " . $_POST["id"] . "";

        try {
          $stmt = $database->dbc->prepare( $query );
          $stmt->execute();
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          var_dump( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          var_dump( $exception->getMessage() );
        }

      } else {
        echo json_encode( 'Sie verfuegen nicht die Berechtigung eine Loeschung durchzufuehren!' );
      }

    }


    /*---------------------------------------------------------------------
    |
    |
    |                       EQSWAPE16X Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getEQSWAPE16X()
    * @param -
    * @return -
    * @details function to get selected EQSWAPE16X from the database
    */
    function getEQSWAPE16X() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM EQSWAPE16X WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateEQSWAPE16X()
    * @param -
    * @return -
    * @details function to update EQSWAPE16X in the database
    */
    function updateEQSWAPE16X() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE EQSWAPE16X
        SET `client` = '" . $_POST["client"] . "', `AccountNumber` = '" . $_POST["AccountNumber"] . "', `AsOfDate` = '" . $_POST["AsOfDate"] . "',
        `AccountName` = '" . $_POST["AccountName"] . "', `SwapAccountNumber` = '" . $_POST["SwapAccountNumber"] . "', `SwapAccountName` = '" . $_POST["SwapAccountName"] . "',
        `SwapNumber` = '" . $_POST["SwapNumber"] . "', `BasketId` = '" . $_POST["BasketId"] . "', `BasketCurrency` = '" . $_POST["BasketCurrency"] . "',
        `SwapDescription` = '" . $_POST["SwapDescription"] . "', `LegType` = '" . $_POST["LegType"] . "', `SEDOL` = '" . $_POST["SEDOL"] . "',
        `Stock` = '" . $_POST["Stock"] . "', `Underlyer` = '" . $_POST["Underlyer"] . "', `CUSIP` = '" . $_POST["CUSIP"] . "',
        `StockDescription` = '" . $_POST["StockDescription"] . "', `TradeDate` = '" . $_POST["TradeDate"] . "', `EffectiveDate` = '" . $_POST["EffectiveDate"] . "',
        `ListingCcy` = '" . $_POST["ListingCcy"] . "', `SwapSettlementCurrency` = '" . $_POST["SwapSettlementCurrency"] . "', `OpenQuantity` = '" . $_POST["OpenQuantity"] . "',
        `AveragePrice` = '" . $_POST["AveragePrice"] . "', `Notional` = '" . $_POST["Notional"] . "', `MarkPrice` = '" . $_POST["MarkPrice"] . "',
        `MarkFX` = '" . $_POST["MarkFX"] . "', `MarkNotional` = '" . $_POST["MarkNotional"] . "', `Performance` = '" . $_POST["Performance"] . "',
        `PendingPerformance` = '" . $_POST["PendingPerformance"] . "', `Spread` = '" . $_POST["Spread"] . "', `Rate` = '" . $_POST["Rate"] . "',
        `TotalRate` = '" . $_POST["TotalRate"] . "', `DayCount` = '" . $_POST["DayCount"] . "', `Interest` = '" . $_POST["Interest"] . "',
        `PendingInterest` = '" . $_POST["PendingInterest"] . "', `Dividend` = '" . $_POST["Dividend"] . "', `PendingDividend` = '" . $_POST["PendingDividend"] . "',
        `DividendCurrency` = '" . $_POST["DividendCurrency"] . "', `DiscreteFee` = '" . $_POST["DiscreteFee"] . "', `FeeCurrency` = '" . $_POST["FeeCurrency"] . "',
        `MTMTotal` = '" . $_POST["MTMTotal"] . "', `MTMTotalBase` = '" . $_POST["MTMTotalBase"] . "', `Price` = '" . $_POST["Price"] . "',
        `Quantity` = '" . $_POST["Quantity"] . "', `Short` = '" . $_POST["Short"] . "', `SwapSettleCurrencyLong` = '" . $_POST["SwapSettleCurrencyLong"] . "',
        `MaturityDate` = '" . $_POST["MaturityDate"] . "', `IssueType` = '" . $_POST["IssueType"] . "', `ISIN` = '" . $_POST["ISIN"] . "',
        `RICCode` = '" . $_POST["RICCode"] . "', `RestrictedQuantity` = '" . $_POST["RestrictedQuantity"] . "', `TradableQty` = '" . $_POST["TradableQty"] . "',
        `NumberOfContracts_Qty` = '" . $_POST["NumberOfContracts_Qty"] . "', `ContractSize` = '" . $_POST["ContractSize"] . "', `RestrictedFlag` = '" . $_POST["RestrictedFlag"] . "',
        `MTMTotal_Exposure` = '" . $_POST["MTMTotal_Exposure"] . "', `Long_Short` = '" . $_POST["Long_Short"] . "', `filename` = '" . $_POST["filename"] . "',
        `filedate` = '" . $_POST["filedate"] . "', `status` = '" . $_POST["status"] . "', `updated_by` = '" . $_SESSION["username"] . "' WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       EQSWAPE18MDX Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getEQSWAPE18MDX()
    * @param -
    * @return -
    * @details function to get selected EQSWAPE18MDX from the database
    */
    function getEQSWAPE18MDX() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM EQSWAPE18MDX WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateEQSWAPE18MDX()
    * @param -
    * @return -
    * @details function to update EQSWAPE18MDX in the database
    */
    function updateEQSWAPE18MDX() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE EQSWAPE18MDX
        SET `client` = '" . $_POST["client"] . "', `AccountNumber` = '" . $_POST["AccountNumber"] . "', `AccountName` = '" . $_POST["AccountName"] . "',
        `AsOfDate` = '" . $_POST["AsOfDate"] . "', `SwapAccountNumber` = '" . $_POST["SwapAccountNumber"] . "', `SwapAccountName` = '" . $_POST["SwapAccountName"] . "',
        `SwapNumber` = '" . $_POST["SwapNumber"] . "', `SwapDescription` = '" . $_POST["SwapDescription"] . "', `BasketId` = '" . $_POST["BasketId"] . "',
        `Short` = '" . $_POST["Short"] . "', `LegType` = '" . $_POST["LegType"] . "', `SEDOL` = '" . $_POST["SEDOL"] . "', `ListingCcy` = '" . $_POST["ListingCcy"] . "',
        `SwapSettlementCurrency` = '" . $_POST["SwapSettlementCurrency"] . "', `SwapSettleCurrencyLong` = '" . $_POST["SwapSettleCurrencyLong"] . "',
        `Underlyer` = '" . $_POST["Underlyer"] . "', `CUSIP` = '" . $_POST["CUSIP"] . "', `StockDescription` = '" . $_POST["StockDescription"] . "',
        `StartDate` = '" . $_POST["StartDate"] . "', `EndDate` = '" . $_POST["EndDate"] . "', `ValueDate` = '" . $_POST["ValueDate"] . "',
        `Price` = '" . $_POST["Price"] . "', `QuantityDP` = '" . $_POST["QuantityDP"] . "', `Quantity` = '" . $_POST["Quantity"] . "',
        `ListingMarkPrice1` = '" . $_POST["ListingMarkPrice1"] . "', `MarkFX1` = '" . $_POST["MarkFX1"] . "', `MarkPrice1` = '" . $_POST["MarkPrice1"] . "',
        `MarkEquityNotional1` = '" . $_POST["MarkEquityNotional1"] . "', `ListingMarkPrice2` = '" . $_POST["ListingMarkPrice2"] . "',
        `MarkFX2` = '" . $_POST["MarkFX2"] . "', `MarkPrice2` = '" . $_POST["MarkPrice2"] . "', `MarkEquityNotional2` = '" . $_POST["MarkEquityNotional2"] . "',
        `PerformanceAmount` = '" . $_POST["PerformanceAmount"] . "', `MarkFinNotional` = '" . $_POST["MarkFinNotional"] . "', `DayCount` = '" . $_POST["DayCount"] . "',
        `Basis` = '" . $_POST["Basis"] . "', `GrossRate` = '" . $_POST["GrossRate"] . "', `Spread` = '" . $_POST["Spread"] . "', `NetRate` = '" . $_POST["NetRate"] . "',
        `InterestAmount` = '" . $_POST["InterestAmount"] . "', `Dividend` = '" . $_POST["Dividend"] . "', `DividendCurrency` = '" . $_POST["DividendCurrency"] . "',
        `Fee` = '" . $_POST["Fee"] . "', `FeeCurrency` = '" . $_POST["FeeCurrency"] . "', `Total` = '" . $_POST["Total"] . "', `RICCode` = '" . $_POST["RICCode"] . "',
        `ISIN` = '" . $_POST["ISIN"] . "', `CountryOfIssue` = '" . $_POST["CountryOfIssue"] . "', `IssueType` = '" . $_POST["IssueType"] . "',
        `ISODomicile` = '" . $_POST["ISODomicile"] . "', `Divisor` = '" . $_POST["Divisor"] . "', `ContractSize` = '" . $_POST["ContractSize"] . "',
        `MTMTotalBase` = '" . $_POST["MTMTotalBase"] . "', `Event` = '" . $_POST["Event"] . "', `DividendEvent` = '" . $_POST["DividendEvent"] . "',
        `RealisedInterestNonLocal` = '" . $_POST["RealisedInterestNonLocal"] . "', `Unwind_ResetFXFIN` = '" . $_POST["Unwind_ResetFXFIN"] . "',
        `RealisedPerformanceNonLocal` = '" . $_POST["RealisedPerformanceNonLocal"] . "', `Unwind_ResetFXEQ` = '" . $_POST["Unwind_ResetFXEQ"] . "',
        `TaxAmount` = '" . $_POST["TaxAmount"] . "', `filename` = '" . $_POST["filename"] . "', `filedate` = '" . $_POST["filedate"] . "',
        `status` = '" . $_POST["status"] . "', `updated_by` = '" . $_SESSION["username"] . "' WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       EQSWAPE20MDX Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getEQSWAPE20MDX()
    * @param -
    * @return -
    * @details function to get selected EQSWAPE20MDX from the database
    */
    function getEQSWAPE20MDX() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM EQSWAPE20MDX WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateEQSWAPE20MDX()
    * @param -
    * @return -
    * @details function to update EQSWAPE20MDX in the database
    */
    function updateEQSWAPE20MDX() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE EQSWAPE20MDX
        SET `client` = '" . $_POST["client"] . "', `AccountNumber` = '" . $_POST["AccountNumber"] . "', `AsOfDate` = '" . $_POST["AsOfDate"] . "',
        `AccountName` = '" . $_POST["AccountName"] . "', `SwapNumber` = '" . $_POST["SwapNumber"] . "', `TradeDate` = '" . $_POST["TradeDate"] . "',
        `Event` = '" . $_POST["Event"] . "', `PamountLEQ` = '" . $_POST["PamountLEQ"] . "', `PamountLFI` = '" . $_POST["PamountLFI"] . "',
        `PamountLDV` = '" . $_POST["PamountLDV"] . "', `PamountLEE` = '" . $_POST["PamountLEE"] . "', `PamountSEQ` = '" . $_POST["PamountSEQ"] . "',
        `PamountSFI` = '" . $_POST["PamountSFI"] . "', `PamountSDV` = '" . $_POST["PamountSDV"] . "', `PamountLSEE` = '" . $_POST["PamountLSEE"] . "',
        `SettleDate` = '" . $_POST["SettleDate"] . "', `SettleCurrency` = '" . $_POST["SettleCurrency"] . "', `TotalAmount` = '" . $_POST["TotalAmount"] . "',
        `filename` = '" . $_POST["filename"] . "', `filedate` = '" . $_POST["filedate"] . "', `status` = '" . $_POST["status"] . "',
        `updated_by` = '" . $_SESSION["username"] . "' WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       EQSWAPE35AX Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getEQSWAPE35AX()
    * @param -
    * @return -
    * @details function to get selected EQSWAPE35AX from the database
    */
    function getEQSWAPE35AX() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM EQSWAPE35AX WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateEQSWAPE35AX()
    * @param -
    * @return -
    * @details function to update EQSWAPE35AX in the database
    */
    function updateEQSWAPE35AX() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE EQSWAPE35AX
        SET `client` = '" . $_POST["client"] . "', `AsOfDate` = '" . $_POST["AsOfDate"] . "', `SwapAccountNumber` = '" . $_POST["SwapAccountNumber"] . "',
        `AccountNumber` = '" . $_POST["AccountNumber"] . "', `AccountName` = '" . $_POST["AccountName"] . "', `SwapCurrency` = '" . $_POST["SwapCurrency"] . "',
        `SwapNumber` = '" . $_POST["SwapNumber"] . "', `BasketDescription` = '" . $_POST["BasketDescription"] . "', `BasketId` = '" . $_POST["BasketId"] . "',
        `Security` = '" . $_POST["Security"] . "', `ExchangeId` = '" . $_POST["ExchangeId"] . "', `CUSIP` = '" . $_POST["CUSIP"] . "', `SEDOL` = '" . $_POST["SEDOL"] . "',
        `DividendCurrency` = '" . $_POST["DividendCurrency"] . "', `RecordDate` = '" . $_POST["RecordDate"] . "', `ExDate` = '" . $_POST["ExDate"] . "', `PayDate` = '" . $_POST["PayDate"] . "',
        `GrossDividendPerShare` = '" . $_POST["GrossDividendPerShare"] . "', `Quantity` = '" . $_POST["Quantity"] . "', `DividendEntitlementPercent` = '" . $_POST["DividendEntitlementPercent"] . "',
        `Dividend` = '" . $_POST["Dividend"] . "', `DividendFxRate` = '" . $_POST["DividendFxRate"] . "', `RealizedQuantity` = '" . $_POST["RealizedQuantity"] . "',
        `Event` = '" . $_POST["Event"] . "', `DividendPaymentCurrency` = '" . $_POST["DividendPaymentCurrency"] . "', `RealizedDividend` = '" . $_POST["RealizedDividend"] . "',
        `RealizedDividendValueDate` = '" . $_POST["RealizedDividendValueDate"] . "', `RICCode` = '" . $_POST["RICCode"] . "', `ISIN` = '" . $_POST["ISIN"] . "',
        `ListingCurrency` = '" . $_POST["ListingCurrency"] . "', `CountryOfIssue` = '" . $_POST["CountryOfIssue"] . "', `IssueType` = '" . $_POST["IssueType"] . "',
        `Divisor` = '" . $_POST["Divisor"] . "', `ContractSize` = '" . $_POST["ContractSize"] . "', `TaxAmount` = '" . $_POST["TaxAmount"] . "',
        `filename` = '" . $_POST["filename"] . "', `filedate` = '" . $_POST["filedate"] . "', `status` = '" . $_POST["status"] . "', `updated_by` = '" . $_SESSION["username"] . "'
        WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       EQSWAPE37X Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getEQSWAPE37X()
    * @param -
    * @return -
    * @details function to get selected EQSWAPE37X from the database
    */
    function getEQSWAPE37X() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM EQSWAPE37X WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateEQSWAPE37X()
    * @param -
    * @return -
    * @details function to update EQSWAPE37X in the database
    */
    function updateEQSWAPE37X() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE EQSWAPE37X
        SET `client` = '" . $_POST["client"] . "', `AsOfDate` = '" . $_POST["AsOfDate"] . "', `SwapAccountNumber` = '" . $_POST["SwapAccountNumber"] . "',
        `AccountNumber` = '" . $_POST["AccountNumber"] . "', `AccountName` = '" . $_POST["AccountName"] . "', `SwapCurrency` = '" . $_POST["SwapCurrency"] . "',
        `SwapNumber` = '" . $_POST["SwapNumber"] . "', `BasketDescription` = '" . $_POST["BasketDescription"] . "', `BasketId` = '" . $_POST["BasketId"] . "',
        `TradeDate` = '" . $_POST["TradeDate"] . "', `SettleDate` = '" . $_POST["SettleDate"] . "', `CUSIP` = '" . $_POST["CUSIP"] . "', `SEDOL` = '" . $_POST["SEDOL"] . "',
        `RICCode` = '" . $_POST["RICCode"] . "', `IssueCurrency` = '" . $_POST["IssueCurrency"] . "', `Description` = '" . $_POST["Description"] . "',
        `Buy_Sell` = '" . $_POST["Buy_Sell"] . "', `Quantity` = '" . $_POST["Quantity"] . "', `ExecutionPrice` = '" . $_POST["ExecutionPrice"] . "', `FXRate` = '" . $_POST["FXRate"] . "',
        `Commission` = '" . $_POST["Commission"] . "', `Embedded` = '" . $_POST["Embedded"] . "', `NetPrice` = '" . $_POST["NetPrice"] . "', `Spread` = '" . $_POST["Spread"] . "',
        `DividendEntitlementPercentage` = '" . $_POST["DividendEntitlementPercentage"] . "', `Cost` = '" . $_POST["Cost"] . "', `MdSymbol` = '" . $_POST["MdSymbol"] . "',
        `SecurityId` = '" . $_POST["SecurityId"] . "', `ExchangeId` = '" . $_POST["ExchangeId"] . "', `ISIN` = '" . $_POST["ISIN"] . "', `Quick` = '" . $_POST["Quick"] . "',
        `Valoren` = '" . $_POST["Valoren"] . "', `ListingCcy` = '" . $_POST["ListingCcy"] . "', `CountryOfIssue` = '" . $_POST["CountryOfIssue"] . "',
        `IssueType` = '" . $_POST["IssueType"] . "', `ISODomicile` = '" . $_POST["ISODomicile"] . "', `Divisor` = '" . $_POST["Divisor"] . "', `ContractSize` = '" . $_POST["ContractSize"] . "',
        `TradeReference` = '" . $_POST["TradeReference"] . "', `Sequence` = '" . $_POST["Sequence"] . "', `SwapAccountName` = '" . $_POST["SwapAccountName"] . "',
        `RestrictedPosition` = '" . $_POST["RestrictedPosition"] . "', `NumberOfContracts_Qty` = '" . $_POST["NumberOfContracts_Qty"] . "', `SwapDescription` = '" . $_POST["SwapDescription"] . "',
        `CommissionInSwapCurrency` = '" . $_POST["CommissionInSwapCurrency"] . "', `Alt_QtyB__S_` = '" . $_POST["Alt_QtyB__S_"] . "', `SwapMaturityDate` = '" . $_POST["SwapMaturityDate"] . "',
        `Strategy` = '" . $_POST["Strategy"] . "', `AdhocFee` = '" . $_POST["AdhocFee"] . "', `USI` = '" . $_POST["USI"] . "', `UTI` = '" . $_POST["UTI"] . "',
        `LotSize` = '" . $_POST["LotSize"] . "', `ContainerSwapNumber` = '" . $_POST["ContainerSwapNumber"] . "', `TaxlotGroupID` = '" . $_POST["TaxlotGroupID"] . "',
        `AcquisitionDate` = '" . $_POST["AcquisitionDate"] . "', `TradeSubType` = '" . $_POST["TradeSubType"] . "', `FloatingRateIndex` = '" . $_POST["FloatingRateIndex"] . "',
        `Open_Close` = '" . $_POST["Open_Close"] . "', `Long_Short` = '" . $_POST["Long_Short"] . "', `ExecutingBroker` = '" . $_POST["ExecutingBroker"] . "',
        `filename` = '" . $_POST["filename"] . "', `filedate` = '" . $_POST["filedate"] . "', `status` = '" . $_POST["status"] . "', `updated_by` = '" . $_SESSION["username"] . "'
        WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       EQSWAPE40X Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief getEQSWAPE40X()
    * @param -
    * @return -
    * @details function to get selected EQSWAPE40X from the database
    */
    function getEQSWAPE40X() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( $_POST["id"]) {
        $dbc = new Connector();
        $query = "SELECT * FROM EQSWAPE40X WHERE id = '" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchAllResults( PDO::FETCH_ASSOC );
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein fehler ist augetreten!" );
      }
    }

    /**
    * @brief updateEQSWAPE40X()
    * @param -
    * @return -
    * @details function to update EQSWAPE40X in the database
    */
    function updateEQSWAPE40X() {
      $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

      if ( isset( $_POST["id"] ) ) {
        $dbc = new Connector();
        $query = "UPDATE EQSWAPE40X
        SET `client` = '" . $_POST["client"] . "', `AsOfDate` = '" . $_POST["AsOfDate"] . "', `SwapAccountNumber` = '" . $_POST["SwapAccountNumber"] . "',
        `AccountNumber` = '" . $_POST["AccountNumber"] . "', `AccountName` = '" . $_POST["AccountName"] . "', `SwapNumber` = '" . $_POST["SwapNumber"] . "',
        `BasketDescription` = '" . $_POST["BasketDescription"] . "', `Description` = '" . $_POST["Description"] . "', `SEDOL` = '" . $_POST["SEDOL"] . "',
        `CUSIP` = '" . $_POST["CUSIP"] . "', `Stock` = '" . $_POST["Stock"] . "', `TradeDate` = '" . $_POST["TradeDate"] . "', `SettleDate` = '" . $_POST["SettleDate"] . "',
        `ListingCcy` = '" . $_POST["ListingCcy"] . "', `Event` = '" . $_POST["Event"] . "', `NetPrice` = '" . $_POST["NetPrice"] . "', `Quantity` = '" . $_POST["Quantity"] . "',
        `Notional` = '" . $_POST["Notional"] . "', `NotionalChange` = '" . $_POST["NotionalChange"] . "', `OpenQuantity` = '" . $_POST["OpenQuantity"] . "',
        `DayCount` = '" . $_POST["DayCount"] . "', `Spread` = '" . $_POST["Spread"] . "', `Rate` = '" . $_POST["Rate"] . "', `Interest` = '" . $_POST["Interest"] . "',
        `RealizedInterest` = '" . $_POST["RealizedInterest"] . "', `RealizedInterestValueDate` = '" . $_POST["RealizedInterestValueDate"] . "', `VarTIP` = '" . $_POST["VarTIP"] . "',
        `RICCode` = '" . $_POST["RICCode"] . "', `SecurityId` = '" . $_POST["SecurityId"] . "', `ExchangeId` = '" . $_POST["ExchangeId"] . "', `ISIN` = '" . $_POST["ISIN"] . "',
        `CountryOfIssue` = '" . $_POST["CountryOfIssue"] . "', `IssueType` = '" . $_POST["IssueType"] . "', `ISODomicile` = '" . $_POST["ISODomicile"] . "', `Divisor` = '" . $_POST["Divisor"] . "',
        `ContractSize` = '" . $_POST["ContractSize"] . "', `SwapSettlementCurrency` = '" . $_POST["SwapSettlementCurrency"] . "', `InterestRateConvention` = '" . $_POST["InterestRateConvention"] . "',
        `SpreadIn_` = '" . $_POST["SpreadIn_"] . "', `filename` = '" . $_POST["filename"] . "', `filedate` = '" . $_POST["filedate"] . "', `status` = '" . $_POST["status"] . "',
        `updated_by` = '" . $_SESSION["username"] . "' WHERE id ='" . $_POST["id"] . "'";

        try {
          $dbc->executeQuery( $query );
          $results = $dbc->fetchMessage( 'updateRole' ); // same funcitonality
          echo json_encode( $results );
          // Logging::Log( $results );
        } catch ( PDOException $exception ) {
          // Output expected PDOException.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        } catch ( Exception $exception ) {
          // Output unexpected Exceptions.
          // var_dump( $exception->getMessage() );
          echo json_encode( $exception->getMessage() );
        }

      } else {
        echo json_encode( "Ein Fehler ist aufgetreten!");
      }
    }

    /*---------------------------------------------------------------------
    |
    |
    |                       Misc Section
    |
    |
    *-------------------------------------------------------------------*/

    /**
    * @brief deleteDataSet()
    * @param -
    * @return -
    * @details dynamic function to delete a dataset from the database and table
    */
    function deleteDataSet() {
      try {
        include_once( dirname( $_SERVER['DOCUMENT_ROOT'] ) ."/inc/role.inc.php" );
        include_once( dirname( $_SERVER['DOCUMENT_ROOT'] ) ."/inc/priviliged-user.inc.php" );
        $_POST = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
        $table = $_POST['table'];

        if ( isset( $_SESSION["user_id"] ) ) {
          $privUser = new PrivilegedUser();
          $u = $privUser->getByUserID( $_SESSION["user_id"] );
        }

        if ( $u->hasRole( "Admin" )) {
          $dbc = new Connector();
          $query = "DELETE FROM " . $_POST["table"] . " WHERE id = " . $_POST["id"] . "";
          $dbc->executeQuery( $query );
        }
        // Logging::Log( $results );
      } catch ( PDOException $exception ) {
        // Output expected PDOException.
        var_dump( $exception->getMessage() );
      } catch ( Exception $exception ) {
        // Output unexpected Exceptions.
        var_dump( $exception->getMessage() );
      }
    }

    /**
    * @brief twoDecimalPlaces()
    * @param -
    * @return -
    * @details function to convert a $param to a float, with 2 decimals
    */
    function twoDecimalPlaces( $param ) {
      $param = number_format( (float)str_replace( ",", ".", $param ), 2, ".", "" );
      $param = "'" . $param . "'";
      return $param;
    }

    /**
    * @brief fourDecimalPlaces()
    * @param -
    * @return -
    * @details function to convert a $param to a float, with 2 decimals
    */
    function fourDecimalPlaces( $param ) {
      $param = number_format( (float)str_replace( ",", ".", $param ), 4, ".", "" );
      $param = "'" . $param . "'";
      return $param;
    }

    /**
    * @brief sixDecimalPlaces()
    * @param -
    * @return -
    * @details function to convert a $param to a float, with 6 decimals
    */
    function sixDecimalPlaces( $param ) {
      $param = number_format( (float)str_replace( ",", ".", $param ), 6, ".", "" );
      $param = "'" . $param . "'";
      return $param;
    }

    /**
    * @brief sevenDecimalPlaces()
    * @param -
    * @return -
    * @details function to convert a $param to a float, with 7 decimals
    */
    function sevenDecimalPlaces( $param ) {
      $param = number_format( (float)str_replace( ",", ".", $param ), 7, ".", "" );
      $param = "'" . $param . "'";
      return $param;
    }

    /**
    * @brief eightDecimalPlaces()
    * @param -
    * @return -
    * @details function to convert a $param to a float, with 7 decimals
    */
    function eightDecimalPlaces( $param ) {
      $param = number_format( (float)str_replace( ",", ".", $param ), 8, ".", "" );
      $param = "'" . $param . "'";
      return $param;
    }

    /**
    * @brief thirteenDecimalPlaces()
    * @param -
    * @return -
    * @details function to convert a $param to a float, with 7 decimals
    */
    function thirteenDecimalPlaces( $param ) {
      $param = number_format( (float)str_replace( ",", ".", $param ), 13, ".", "" );
      $param = "'" . $param . "'";
      return $param;
    }

    /**
    * @brief formatDateDB()
    * @param -
    * @return -
    * @details function to convert to a MySQL format Date
    */
    function formatDateDB( $param ) {
      $backup = $param;
      // echo "param before: " . $param . '<br>';

      if ( strpos( $param, '/' ) !== false ) {
        // $param = implode( "-", array_reverse( explode( "-", str_replace( "/", "-", $param ) ) ) );
        $param = strtotime( $param );
        $param = date( 'Y-m-d', $param );
        // echo "/" . $param . '<br>';
      } elseif ( strpos( $param, '.' ) !== false ) {
        //$param = implode( "-", array_reverse( explode( "-", str_replace( ".", "-", $param ) ) ) );
        $param = strtotime( $param );
        $param = date( 'Y-m-d', $param );
        // echo "dot" . $param . '<br>';
      } elseif ( strpos( $param, '-' ) !== false ) {
        $param = date( "Y-m-d", strtotime( $param ) );
        // echo "-" . $param . '<br>';
      } else {
        if ( is_int( $param ) ) {
          // echo $param . " is int<br>";
          $param = gmdate( "Y-m-d", ( $param - 25569 ) * 86400 );
          // echo $param . " changed?<br>";
        } else {
          $param = date( "Y-m-d", ( strtotime( $param )-25569 )*86400 );
        }
      }

      // used when there is no UNIX date example: '20210130'
      if( !validateDate( $param ) ) {
        $date = substr( $backup, 0, 4 ) . '-' . substr( $backup , 4, 2 ) . '-' . substr( $backup, 6, 2 ) ;
        $param = $date;
      }

      $param = "'" . $param . "'";
      return $param;
    }

    /**
    * @brief validateDate()
    * @param $date
    * @return -
    * @details checks whether given string is a date
    */
    function validateDate( $date, $format = 'Y-m-d' ){
      $d = DateTime::createFromFormat( $format, $date );
      return $d && $d->format( $format ) === $date;
    }

executeStandardQueries();
