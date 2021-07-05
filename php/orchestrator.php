<?php
  error_reporting( E_ALL);
  require_once( $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php' );
  use Phppot\DataSource;
  use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

  /**
  * @brief Private Funktion getPDOConnection()
  * @param -
  * @return -
  * @details Verbindung mit der Datenbank herstellen
  */
  private function getPDOConnection() {
      // Überprüfung, ob die Verbindung !leer ist && ob die Konfigurationsdatei existiert
      if ( $this->connection == NULL ) {
        if ( !file_exists( dirname( $_SERVER['DOCUMENT_ROOT'] ) . '/inc/settings.config.php' ) ) {
          throw new Exception( "No config file found!", 1001 );
        }

        require( $_SERVER['DOCUMENT_ROOT'] . '/inc/settings.config.php' );

        $this->dbc = $databaseConfig;

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

  /**
  * @brief Funktion destructConnection()
  * @param -
  * @return -
  * @details Schließt die Datenbankverbindung automatisch durch PHP Garbage Collector
  */
   public function destructConnection() {
     $this->connection = null;
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
          $query = "INSERT INTO `etl` (`name`, `created_by`) VALUES ( '" . $name . "', '" . $_SESSION["username"] . "' )";
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

?>
