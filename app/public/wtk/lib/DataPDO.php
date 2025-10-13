<?PHP
/**
* This contains all Wizard's Toolkit functions that involve database access.
* This is the PDO version.
*
* All rights reserved.
*
* This file is only usable by subscribers of the Wizard's Toolkit.  It may also
* be used while testing on localhost but not deployed to a production server until
* subscription is active.  You may not, except with our express written permission,
* distribute or commercially exploit the content.  Nor may you transmit it or store
* it in any other website or other form of electronic retrieval system.
*
* The above copyright notice and this permission notice shall be included
* in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
* OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
* IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
* CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
* TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
* SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*
* @author      Programming Labs <support@programminglabs.com>
* @license     Copyright 2021-2025, All rights reserved.
* @link        Official website: https://wizardstoolkit.com
* @version     2.0
*/

$gloConnected = false;
$gloConnectedRO = false;
$gloWTKobjConn = '';
$gloWTKobjConnRO = '';
$gloPDOrow = array();
$gloSkipColInfo = false;

/**
* Connect to Database using PDO
*
* @global boolean $gloConnected - set to true when connected
* @global array $gloWTKobjConn connection to data object
* @global string $gloServer1 defined in /wtk/wtkServerInfo.php
* @global string $gloDb1 defined in /wtk/wtkServerInfo.php
* @global string $gloUser1 defined in /wtk/wtkServerInfo.php
* @global string $gloPassword1 defined in /wtk/wtkServerInfo.php
*/
function wtkConnectToDB($fncReadOnly = false) {
    global $gloDriver1, $gloServer1, $gloServerRO, $gloUser1, $gloPassword1, $gloDb1;
    if ($gloServer1 == $gloServerRO): // no difference so use main connection
        $fncReadOnly = false;
    endif;
    $fncPDOoptions = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ];
    if ($fncReadOnly == true): // read-only connection
        global $gloConnectedRO, $gloWTKobjConnRO;
        if ($gloConnectedRO == false):
            $gloConnectedRO = true;
            $fncDSN = "$gloDriver1:host=$gloServerRO;dbname=$gloDb1";
            switch ($gloDriver1):
                case 'mysql':
                case 'mysqli':
                    $fncDSN .= ';charset=utf8mb4';
                    break;
                case 'pgsql' :
                    $fncDSN .= ';port=5432';
                    break;
            endswitch;
            try {
                 $gloWTKobjConnRO = new PDO($fncDSN, $gloUser1, $gloPassword1, $fncPDOoptions);
            } catch (\PDOException $e) {
                 throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        endif;  // $gloConnectedRO == false
    else: // not read-only connection
        global $gloConnected, $gloWTKobjConn;
        if ($gloConnected == false):
            $gloConnected = true;
            $fncDSN = "$gloDriver1:host=$gloServer1;dbname=$gloDb1";
            switch ($gloDriver1):
                case 'mysql' :
                case 'mysqli' :
                    $fncDSN .= ';charset=utf8mb4';
                    break;
                case 'pgsql' :
                    $fncDSN .= ';port=5432';
                    break;
            endswitch;
            try {
                 $gloWTKobjConn = new PDO($fncDSN, $gloUser1, $gloPassword1, $fncPDOoptions);
            } catch (\PDOException $e) {
                 throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        endif;  // $gloConnected == false
    endif; // not read-only connection
}  // end of wtkConnectToDB

/**
* Disconnect from Database
*
* @global boolean $gloConnected
* @global array $gloWTKobjConn connection to data object
*/
function wtkDisconnectToDB() {
    global $gloConnected, $gloConnectedRO, $gloWTKobjConn, $gloWTKobjConnRO;
    if ($gloConnected == true):
        $gloConnected = false;
        $gloWTKobjConn = null;
    endif;  // $gloConnected == false
    if ($gloConnectedRO == true):
        $gloConnectedRO = false;
        $gloWTKobjConnRO = null;
    endif;  // $gloConnectedRO == false
}  // end of wtkDisconnectToDB

/**
* SQL Get One Result
*
* @returns string returns a single value
* @global array $gloWTKobjConn connection to data object
* @param string $fncSQL SQL Query that should return a single result
* @param array  $fncSqlFilter array that has PDO names of fields and their values
* @param string $fncDefault Defaults to blank but can pass in the desired default to return if no rows are found.
*/
function wtkSqlGetOneResult($fncSQL, $fncSqlFilter, $fncDefault = '', $fncForceWriteDB = false) {  // return a single value
    global $gloServer1, $gloServerRO, $gloWTKobjConn, $gloWTKobjConnRO;
    $fncSQL = wtkSqlPrep($fncSQL);
    if (($fncForceWriteDB == true) || ($gloServer1 == $gloServerRO)): // no difference so use main connection
        $fncPDO = $gloWTKobjConn->prepare($fncSQL);
    else:
        wtkConnectToDB(true);
        $fncPDO = $gloWTKobjConnRO->prepare($fncSQL);
    endif;
    $fncPDO->execute($fncSqlFilter);
    if ($fncPDO->rowCount() > 0):
        $fncResult = $fncPDO->fetchColumn();
    else:
        $fncResult = $fncDefault;
    endif;
    $fncPDO->closeCursor();
    unset($fncPDO);
    return ($fncResult);
}  // end of wtkSqlGetOneResult

/**
* SQL Get Row of data
*
* @global array $gloWTKobjConn connection to data object
* @global array $gloPDOrow stores first row's data into $gloPDOrow
* @param string $fncSQL SQL Query that returns set of results
* @param array  $fncSqlFilter array that has PDO names of fields and their values
*/
function wtkSqlGetRow($fncSQL, $fncSqlFilter) {
    global $gloServer1,$gloServerRO,$gloWTKobjConn,$gloWTKobjConnRO,$gloPDOrow;
    $fncSQL = wtkSqlPrep($fncSQL);
    wtkConnectToDB(true);
    if ($gloServer1 == $gloServerRO): // no difference so use main connection
        $fncPDO = $gloWTKobjConn->prepare($fncSQL);
    else:
        $fncPDO = $gloWTKobjConnRO->prepare($fncSQL);
    endif;
    // wtkTimeTrack('wtkSqlGetRow: $fncSQL = ' . $fncSQL);
    // wtkTimeTrack($fncSqlFilter);
    $fncPDO->execute($fncSqlFilter);
    $gloPDOrow = $fncPDO->fetch(PDO::FETCH_ASSOC);
    if (!is_array($gloPDOrow)):
        return 'no data';
    endif;
}  // end of wtkSqlGetRow

/**
* Returns SQL Value from result set for single column's value.  Will return '' blank if data is NULL.
*
* If page is in 'ADD' mode as per $gloWTKmode = 'ADD' then will return blank ''.
*
* @global string $gloWTKmode will be 'ADD' or 'EDIT'.
* @global array $gloPDOrow stores a row's data
* @param string $fncColName Field name in a query
* @returns string value of a field in a SQL query
*/
function wtkSqlValue($fncColName) {
    global $gloWTKmode, $gloPDOrow; // ABS 08/28/20
    if ($gloWTKmode == 'ADD'):
        return('');
    else:
        if (is_null($gloPDOrow)):
            return('');
        else:   // Not $gloPDOrow == ''
            if (is_null($gloPDOrow[$fncColName])):
                return('');
            else:
                return($gloPDOrow[$fncColName]);
            endif;
        endif;  // $gloPDOrow == ''
    endif;
}  // end of wtkSqlValue

/**
* SQL Exec
*
* Execute a SQL call (update, insert, delete, etc.) that does not return a result
*
* @global boolean $gloConnected
* @global array $gloWTKobjConn connection to data object
* @param string $fncSQL SQL Query
* @param array  $fncSqlFilter array that has PDO names of fields and their values
* @param boolean $fncShowError defaults to true; if pass false then suppresses error and continues with code
*/
function wtkSqlExec($fncSQL, $fncSqlFilter, $fncShowError = true) {
    global $gloWTKobjConn, $gloConnected;
    if ($gloConnected == false):
        echo 'Exec SQL called but SQL connection is not open.' . "\n\n";
//      print_r($fncSqlFilter);
        exit;
    endif;
    $fncSQL = wtkSqlPrep($fncSQL);
    $fncNull = null;
    try {
        $fncReady = $gloWTKobjConn->prepare($fncSQL);
        foreach ($fncSqlFilter as $k => $v):
            if (!isset($v)):
                $fncReady->bindParam(':'.$k, $fncNull, PDO::PARAM_NULL);
            else:
                if (strtoupper($v) == 'NULL'):
                    $fncReady->bindParam(':'.$k, $fncNull, PDO::PARAM_NULL);
                else:
                    $fncReady->bindValue(':'.$k,$v);
                endif;
            endif;
        endforeach;
        try {
            $fncReady->execute();
        } catch (PDOException $e) {
        // Handle the error gracefully or ignore it
            if ($fncShowError == true):
                echo $e;
            endif;
        }
    } catch (PDOException $e) {
        // Handle the error gracefully or ignore it
        if ($fncShowError == true):
            wtkExceptionHandler($e);
        endif;
    }
}  // end of wtkSqlExec

/**
* SQL Prep
*
* change ` to DB_COL_QUOTE so can use same code for MySQL, PostgreSQL or MS SQL
*
* @param string $fncSQL Pass in SQL Query to be modified
*/
function wtkSqlPrep($fncSQL) {
    global $gloDriver1;
    if ($gloDriver1 == 'pgsql'):
        $fncSQL = wtkReplace($fncSQL, "\'", "''");
        $fncSQL = wtkReplace($fncSQL, 'DATE_FORMAT(', 'to_char(');
    else:
        $fncSQL = wtkReplace($fncSQL, 'CAST(`UID` AS VARCHAR)', 'CAST(`UID` AS CHAR)'); // MySQL prefers CHAR for CAST
    endif;
    if (DB_COL_QUOTE != '`'):
        $fncSQL = wtkReplace($fncSQL, '`',DB_COL_QUOTE);
    endif;
    return $fncSQL;
}  // end of wtkSqlPrep

/**
* Get Select Options
*
* Pass in SELECT statement to retrieve value and display values for drop list.  Sends back <option> tags only.
*
* @global array $gloWTKobjConn connection to data object
* @param string $fncSQL SQL Query
* @param array  $fncSqlFilter array that has PDO names of fields and their values
* @param string $fncDisplayField Display Field for example 'Male', 'Female'
* @param string $fncValueField Value Field for example 'M', 'F'
* @param string $fncCurrentValue Current Value so knows which value to mark as SELECTED
* @returns string Return HTML built SELECT drop down menu
*/
function wtkGetSelectOptions($fncSQL, $fncSqlFilter, $fncDisplayField, $fncValueField, $fncCurrentValue) {
    global $gloServer1, $gloServerRO, $gloWTKobjConn, $gloWTKobjConnRO;
    $fncSQL = wtkSqlPrep($fncSQL);
    if ($gloServer1 == $gloServerRO): // no difference so use main connection
        $fncObjRS = $gloWTKobjConn->prepare($fncSQL);
    else:
        wtkConnectToDB(true);
        $fncObjRS = $gloWTKobjConnRO->prepare($fncSQL);
    endif;
    $fncOptions = '';
    $fncObjRS->execute($fncSqlFilter);
    while ($fncRow = $fncObjRS->fetch()):
        $fncValue = $fncRow[$fncValueField];
        $fncOptions .= '<option value="' . $fncValue . '"';
        if ($fncCurrentValue == $fncValue):
            $fncOptions .= ' SELECTED';
        endif;
        $fncOptions .= '>' . $fncRow[$fncDisplayField] . "</option>\n";
    endwhile;
    unset($fncObjRS);
    return($fncOptions);
}//wtkGetSelectOptions($fncSQL, $fncDisplayField, $fncValueField, $fncCurrentValue)

/**
 * Prepares the string to be used in either a MySQL or PostgreSQL DB.
 * Replaces single quote with two single quotes.
 *
 * @global string $gloDriver1 used to determine whether to use pg_escape_string or str_replace
 * @param string $fncRawString
 * @return boolean
 */
function wtkEscapeStringForDB($fncRawString) {
    global $gloDriver1;
    switch ($gloDriver1):
        case 'mysql' :
        case 'mysqli' :
//            return mysql_real_escape_string($fncRawString);
            return str_replace( "'", "''", $fncRawString );
            break;
        case 'mssql' :
            return str_replace( "'", "''", $fncRawString );
            break;
        default :
            return pg_escape_string($fncRawString);
    endswitch; // gloDriver1
} // finish function wtkEscapeStringForDB

/**
* SQL Date Format
*
* Used in SELECT statements so common code can be used which will format date
* appropriately regardless of whether MySQL or PostgreSQL database.
*
* @global string $gloDriver1 used to determine whether to use DATE_FORMAT or to_char
* @global string $gloSqlDateTime is set in wtk/wtkServerInfo.php ; choose your default datetime format
* @param string $fncColName
* @param string $fncAlias Defaults to column name if blank
* @param string $fncFormat Defaults to use $gloSqlDateTime if not passed or blank
*/
function wtkSqlDateFormat($fncColName, $fncAlias = '', $fncFormat = '') {
    global $gloDriver1, $gloSqlDateTime;
    if ($fncFormat == ''):
        $fncFormat = $gloSqlDateTime;
    endif;  // $fncFormat == ''
    if ($fncAlias == ''):
        $fncAlias = $fncColName;
    endif;  // $fncAlias == ''
    if (strpos($fncColName, '`') === false):
        $fncColName = '`' . $fncColName . '`';
    endif;  // strpos($fncColName, '`') === false
    switch (strtolower(substr($gloDriver1, 0, 5))):
        case 'mysql' :  // will also catch mysqli
            $fncResult = " DATE_FORMAT(" . $fncColName . ", '" . $fncFormat . "') AS '" . $fncAlias . "'";
            break;
        case 'postg' :
        case 'pgsql' :
            $fncResult = " to_char(" . $fncColName . ", '" . $fncFormat . "') AS `" . $fncAlias . "`";
            break;
        default :
            $fncResult = $fncColName . ' AS `' . $fncAlias . '`';
    endswitch; // SUBSTR ( $gloDriver1 , 1 , 5 )
    return $fncResult;
}  // end of wtkSqlDateFormat

function wtkSqlDateSub($fncDate, $fncInterval, $fncUnitOfTime){
    global $gloDriver1;
    if ($gloDriver1 == 'pgsql'):
        $fncResult = "($fncDate - INTERVAL '$fncInterval' $fncUnitOfTime)";
    else: // mysQL syntax
        $fncResult = "DATE_SUB($fncDate, INTERVAL $fncInterval $fncUnitOfTime)";
    endif;
    return $fncResult;
}


/**
* Prep Value for INSERT or UPDATE to validate and format value
*
* Currently this is only verified to work with MySQL.
* Contact info@wizardstoolkit.com for development of PostgreSQL or other DB version.
*
* Pass in the table, field and value parameters.
* This truncates strings that are too long, and removes $ and commas for NUMERIC column types
*
* @param string $fncTable Database Table Name
* @param string $fncField Database Field Name
* @param string $fncValue Database Value
*/
function wtkPrepSQLValue($fncTable, $fncField, $fncValue){
    global $gloDriver1, $gloWTKobjConn;
    $fncValue = trim($fncValue);
    if (($fncValue != 'NULL') && (strtolower(substr($gloDriver1, 0, 5)) == 'mysql')):
        $fncQuery  = 'SELECT ' . DB_COL_QUOTE.$fncField.DB_COL_QUOTE . ' FROM ';
        $fncQuery .= DB_COL_QUOTE.$fncTable.DB_COL_QUOTE . ' LIMIT 1';
        $fncSelect = $gloWTKobjConn->query($fncQuery);
        $fncColInfo = $fncSelect->getColumnMeta(0);
        $fncDateType = '';
        $fncDateType = $fncColInfo['native_type'];
        wtkTimeTrack('wtkPrepSQLValue: ' . $fncField . ' is a ' . $fncDateType);
        switch (strtoupper($fncDateType)):
            case 'SHORT':
            case 'INT24':
            case 'LONG':
            case 'LONGLONG':
            case 'NEWDECIMAL':
            case 'FLOAT':
            case 'DOUBLE':
                $fncValue = str_replace(',', '', $fncValue);
                $fncValue = str_replace('$', '', $fncValue);
                if ($fncValue == ''):
                    $fncValue = 'NULL';
                endif;
                break;
            case 'DATE':
                wtkTimeTrack('before reformat: fncValue = ' . $fncValue);
                $fncValue = wtkFormatDateTime('Y-m-d', $fncValue);
                wtkTimeTrack('after reformat: fncValue = ' . $fncValue);
                break;
            case 'TIME':
                $fncValue = wtkFormatDateTime('H:i:s', $fncValue);
                break;
            case 'TIMESTAMP':
            case 'DATETIME':  // changed these to PDO
                wtkTimeTrack('before T reformat: fncValue = ' . $fncValue);
                $fncValue = wtkFormatDateTime('Y-m-d H:i:s', $fncValue);
                wtkTimeTrack('after T reformat: fncValue = ' . $fncValue);
                break;
            case 'VAR_STRING':
            case 'STRING':
                $fncSQL = 'SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS where COLUMN_NAME = ? and TABLE_NAME = ?';
                $fncColLength = wtkSqlGetOneResult($fncSQL, [$fncField, $fncTable]);
                if (($fncColLength > 0) && (strlen($fncValue) > $fncColLength)):
                    $fncValue = substr($fncValue, 0, $fncColLength);
                endif;  // strlen($fncValue) > $fncColInfo->wtkGetTextLength()
                break;
        endswitch; // fncDateType
    endif;  // $fncValue == 'NULL'
    return $fncValue;
} // wtkPrepSQLValue

$pgInsertColumns = '';
$pgInsertValues  = '';
$pgUpdateStr = '';
$pgUpdateLog = '';
$pgPDOvalues = array();
/**
* Build Insert SQL
*
* Pass in the table, field and value parameters which are added to variables to construct an INSERT statement.
* This is called by Save.php.  It saves the information in $pgInsertColumns, $pgInsertValues, $pgPDOvalues;
*
* @param string $fncTable Database Table Name
* @param string $fncField Database Field Name
* @param string $fncValue Database Value
* @uses function wtkPrepSQLValue to validate and format value
*/
function wtkBuildInsertSQL($fncTable, $fncField, $fncValue) {
    global $pgInsertColumns, $pgInsertValues, $pgPDOvalues;
    $fncValue = wtkPrepSQLValue($fncTable, $fncField, $fncValue);
    if ($pgInsertColumns != ''):
        $pgInsertColumns .= ', ';
        $pgInsertValues  .= ', ';
    endif;  // $pgInsertColumns != ''
    $pgInsertColumns .= DB_COL_QUOTE . $fncField . DB_COL_QUOTE;
    wtkBuildInsertLog($fncTable, $fncField, $fncValue);
    $pgPDOvalues[$fncField] = $fncValue;
//    $fncValue = wtkEscapeStringForDB($fncValue);
    $pgInsertValues .= ':' . $fncField;
}  // end of wtkBuildInsertSQL

/**
* Execute Insert SQL
*
* Pass in the Table name and this performs the INSERT script that was created by previously calling wtkBuildInsertSQL.
* This is called by Save.php
*
* @param string $fncTable Database Table Name
*/
function wtkExecInsertSQL($fncTable) {
    global $gloDriver1, $pgInsertColumns, $pgInsertValues, $pgPDOvalues, $pgDebugMode;

    wtkTimeTrack('top of wtkExecInsertSQL');
    if ($pgInsertColumns != ''):
        $fncSQL  = 'INSERT INTO ' . DB_COL_QUOTE . $fncTable . DB_COL_QUOTE . ' (' . $pgInsertColumns . ')';
        $fncSQL .= ' VALUES(' . $pgInsertValues . ')';
        if ($pgDebugMode == true):
            wtkTimeTrack('wtkExecInsertSQL: fncSQL = ' . $fncSQL);
            wtkTimeTrack('wtkExecInsertSQL: pgPDOvalues = ' . implode('|',$pgPDOvalues));
        else:
            if (stripos($gloDriver1, 'ostgre') === false):  // if not PostgreSQL
                wtkTimeTrack('wtkExecInsertSQL: ' . $fncSQL );
                wtkSqlExec($fncSQL, $pgPDOvalues);
            else:   // Not stripos($gloDriver1, 'ostgre') === false
                if (wtkGetParam('UID') == ''):
                    $fncColName = 'GUID';
                else:   // Not UID == ''
                    $fncColName = wtkDecode(wtkGetParam('UID'));
                endif;  // UID == ''
                $fncSQL .= ' RETURNING "' . $fncColName . '"';  // PostgreSQL feature
                wtkTimeTrack('wtkExecInsertSQL: ' . $fncSQL );
                global $gloId;
                $gloId = wtkSqlGetOneResult($fncSQL, $pgPDOvalues);
            endif;  // stripos($gloDriver1, 'ostgre') === false
            wtkSaveUpdateLog($fncTable, $fncSQL);
        endif;  // $pgDebugMode == true
    endif;  // $pgInsertColumns != ''
}  // end of wtkExecInsertSQL

/**
* Build Update SQL
*
* Pass in the table, field, prior value and new value which are added to variables to construct an UPDATE statement
* Checks old and new field values -> Only updates if value has changed.
* This is called by Save.php
*
* @param string $fncTable Table Name
* @param string $fncField Field Name
* @param string $fncOldValue Old Value
* @param string $fncNewValue New Value
* @uses function wtkPrepSQLValue to validate and format value
*/
function wtkBuildUpdateSQL($fncTable, $fncField, $fncOldValue, $fncNewValue) {
    global $pgUpdateStr, $pgPDOvalues;
    // 2FIX  error handling if enter a number longer than field can handle.  For example:
    // Fatal error: mysql error: [1264: Out of range value adjusted for column 'LoginTimeout'
    // at row 1] in EXECUTE("UPDATE `wtkUsers` SET `SecurityLevel` = 50, `LoginTimeout` = 9123456789, `State` = 'Ca' WHERE `UID` = 1")
    wtkTimeTrack('wtkBuildUpdateSQL: ' . $fncTable . '.' . $fncField . ' new value: ' . $fncNewValue);
    $fncNewValue = wtkPrepSQLValue($fncTable, $fncField, $fncNewValue);
    wtkBuildUpdateLog($fncTable, $fncField, $fncOldValue, $fncNewValue );
    $pgPDOvalues[$fncField] = $fncNewValue;

    if ($pgUpdateStr == ''):
        $pgUpdateStr  = ' SET ';
    else:   // Not $pgUpdateStr == ''
        $pgUpdateStr .= ', ';
    endif;  // $pgUpdateStr == ''
    $pgUpdateStr .= DB_COL_QUOTE . $fncField . DB_COL_QUOTE . ' = :' . $fncField;
}  // end of wtkBuildUpdateSQL

/**
* Execute Update SQL
*
* Pass in the Table name and WHERE clause and this performs the UPDATE that was created by previously calling wtkBuildUpdateSQL.
* This is called by Save.php
*
* @param string $fncTable Table Name
* @param string $fncWhere SQL WHERE statement
*/
function wtkExecUpdateSQL($fncTable, $fncWhere) {
    global $pgUpdateStr, $pgPDOvalues;
    wtkTimeTrack('Top of wtkExecUpdateSQL');
    if ($pgUpdateStr != ''):
        $fncSQL  = 'UPDATE ' . DB_COL_QUOTE . $fncTable . DB_COL_QUOTE . $pgUpdateStr . ' ' . $fncWhere ;
        wtkTimeTrack('wtkExecUpdateSQL: ' . $fncSQL );
        wtkSqlExec($fncSQL, $pgPDOvalues);
        wtkSaveUpdateLog($fncTable, $fncSQL);
    endif;  // $pgUpdateStr != ''
}  // end of wtkExecUpdateSQL

/**
* Build SQL to Insert into wtkUpdateLog table
*
* Pass in the table, field and value parameters which are added to variables to construct an INSERT statement.
* This is called by wtkBuildInsertSQL to show log file of who INSERTed what and when.
*
* @param string $fncTable Table Name
* @param string $fncField Field Name
* @param string $fncNewValue New Value
*/
function wtkBuildInsertLog($fncTable, $fncField, $fncNewValue) {
    global $pgUpdateLog;
    $pgUpdateLog .= $fncField . ' = "' . $fncNewValue . '"';
    $pgUpdateLog .= '<br>' . "\n";
}  // end of wtkBuildInsertLog

/**
* Build SQL to Insert into wtkUpdateLog table
*
* Pass in the table, field and value parameters which are added to variables to construct an INSERT statement
* This is called by wtkBuildUpdateSQL to show log file of who UPDATEd what and when
*
* @param string $fncTable Table Name
* @param string $fncField Field Name
* @param string $fncOldValue Old Value
* @param string $fncNewValue New Value
*/
function wtkBuildUpdateLog($fncTable, $fncField, $fncOldValue, $fncNewValue) {
    global $pgUpdateLog;
    if ($fncOldValue == 'wtkunknown'): // special passed value if don't know what current value is
        $pgUpdateLog .= $fncField . ' changed to "' . $fncNewValue . '"';
    else:   // Not $fncOldValue == 'wtkunknown'
        $pgUpdateLog .= $fncField . ' changed from "' . $fncOldValue . '" to "' . $fncNewValue . '"';
    endif;  // $fncOldValue == 'wtkunknown'
    $pgUpdateLog .= '<br>' . "\n";
}  // end of wtkBuildUpdateLog

/**
* Save database changes to wtkUpdateLog
*
* @param string $fncTable Table Name
* @param string $fncSQL SQL Query
*/
function wtkSaveUpdateLog($fncTable, $fncSQL) {
    global $gloId,  // This is set in Save.php ; save to OtherUID if exists which it should for all except Adds
           $gloUserUID, $pgUpdateLog;

    $fncInsSQL  = 'INSERT INTO ' . DB_COL_QUOTE . 'wtkUpdateLog' . DB_COL_QUOTE . ' ('
                    . DB_COL_QUOTE . 'UserUID' . DB_COL_QUOTE . ', '
                    . DB_COL_QUOTE . 'TableName' . DB_COL_QUOTE . ', '
                    . DB_COL_QUOTE . 'FullSQL' . DB_COL_QUOTE . ', '
                    . DB_COL_QUOTE . 'ChangeInfo' . DB_COL_QUOTE . ', '
                    . DB_COL_QUOTE . 'OtherUID' . DB_COL_QUOTE . ')';
    if (!is_int($gloId) || ($gloId == '') || ($gloId == 'ADD')):
        $gloId = 'NULL';
    endif;  // $gloId != ''
    if (is_int($gloUserUID) && ($gloUserUID != '')):
        $fncUserUID = $gloUserUID;
    else:
        $fncUserUID = 'NULL';
    endif;
    $fncInsSQL .= " VALUES (:UserUID, :TableName, :FullSQL, :ChangeInfo, :OtherUID)";
    $pgUpdateLog = substr($pgUpdateLog,0,1405);
    $fncSqlFilter = array (
        'UserUID' => $fncUserUID,
        'TableName' => $fncTable,
        'OtherUID' => $gloId,
        'FullSQL' => $fncSQL,
        'ChangeInfo' => $pgUpdateLog
    );
    wtkTimeTrack('wtkSaveUpdateLog: ' . $fncInsSQL);
    wtkSqlExec($fncInsSQL, $fncSqlFilter);
}  // end of wtkSaveUpdateLog

/**
* Generate new file name.  This is mostly used for creating a file name for image uploads.
* Pass in the Table Name and File Extension.  The TableName is inserted into wtkGUID and
* the auto-generated GUID is used as part of the file name.
*
* @param string $fncTableName name of table that is asociated with new file
* @param string $fncFileExt the file extension
* @returns string Returns new file name as 'w' . File GUID . '.' . $fncFileExt
*/
function wtkGenerateFileName($fncTableName, $fncFileExt){
    $fncFileExt = strtolower($fncFileExt);
    if ($fncFileExt == 'jpeg'):
        $fncFileExt = 'jpg';
    endif;

    $fncTmpName = $fncTableName . RAND(1, 9999);
    $fncGUIDsql = "INSERT INTO `wtkGUID` (`TableName`) VALUES (:tableName )";
    $fncGUIDsql = wtkSqlPrep($fncGUIDsql);
    $fncSqlFilter = array (
        'tableName' => $fncTmpName
    );
    wtkSqlExec($fncGUIDsql, $fncSqlFilter);
    $fncGUIDsql = "SELECT `GUID` FROM `wtkGUID` WHERE `TableName` = :tableName ORDER BY `GUID` DESC LIMIT 1";
    $fncGUIDsql = wtkSqlPrep($fncGUIDsql);
    $fncFileGUID = wtkSqlGetOneResult($fncGUIDsql, $fncSqlFilter);
    $fncGUIDsql = "UPDATE `wtkGUID` SET `TableName` = :tableName WHERE `GUID` = :GUID ";
    $fncGUIDsql = wtkSqlPrep($fncGUIDsql);
    $fncSqlFilter = array (
        'tableName' => $fncTableName,
        'GUID' => $fncFileGUID
    );
    wtkSqlExec($fncGUIDsql, $fncSqlFilter);
    $fncNewFileName = 'w' . $fncFileGUID . '.' . $fncFileExt ;
    return $fncNewFileName;
} // wtkGenerateFileName

wtkTimeTrack('End of Data.php');
?>
