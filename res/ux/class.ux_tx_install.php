<?php
/***************************************************************
*  Copyright notice
*
*  (c) Marcus Krause (marcus#exp2009@t3sec.info)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Modifying the tx_install.php class file so the salted password hashes
 * are created for new admin users.
 *
 * $Id$
 *
 * @author	Marcus Krause <marcus#exp2009@t3sec.info>
 */

	// Make sure that we are executed only in TYPO3 context
if (!defined ("TYPO3_MODE")) die ("Access denied.");

require_once (PATH_t3lib.'class.t3lib_div.php');
require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/lib/class.tx_t3secsaltedpw_phpass.php');
require_once t3lib_extMgm::extPath('t3sec_saltedpw', 'res/staticlib/class.tx_t3secsaltedpw_div.php');


/**
 * Plugin for the install tool module.
 *
 * XCLASS that creates salted password hashes.
 *
 * @author	   Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	   Ingmar Schlecht <ingmar@typo3.org>
 * @author     Marcus Krause <marcus#exp2009@t3sec.info>
 * @since      2008-11-23
 * @package    TYPO3
 * @subpackage tx_install
 */
class ux_tx_install extends tx_install	{

	/**
	 * @return	[type]		...
	 */
	function checkTheDatabase()	{
		if (!$this->config_array['mysqlConnect'])	{
			$this->message('Database Analyser','Your database connection failed',"
				Please go to the 'Basic Configuration' section and correct this problem first.
			",2);
			$this->output($this->outputWrapper($this->printAll()));
			return;
		}
		if ($this->config_array['no_database'])	{
			$this->message('Database Analyser','No database selected',"
				Please go to the 'Basic Configuration' section and correct this problem first.
			",2);
			$this->output($this->outputWrapper($this->printAll()));
			return;
		}

			// Getting current tables
		$whichTables=$this->getListOfTables();


			// Getting number of static_template records
		if ($whichTables['static_template'])	{
			$res_static = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)', 'static_template', '');
			list($static_template_count) = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_static);
		}
		$static_template_count=intval($static_template_count);

		$headCode ='Database Analyser';
		$this->message($headCode,'What is it?',"
			In this section you can get an overview of your currently selected database compared to sql-files. You can also import sql-data directly into the database or upgrade tables from earlier versions of TYPO3.
		",0);

		$cInfo='
			Username: <strong>'.TYPO3_db_username.'</strong>
			Password: <strong>'.TYPO3_db_password.'</strong>
			Host: <strong>'.TYPO3_db_host.'</strong>
		';
		$this->message($headCode, 'Connected to SQL database successfully',"
		".trim($cInfo)."
		",-1,1);
		$this->message($headCode, 'Database',"
			<strong>".TYPO3_db.'</strong> is selected as database.
			Has <strong>'.count($whichTables)."</strong> tables.
		",-1,1);


			// Menu
		$this->messageFunc_nl2br = 0;

		$sql_files = array_merge(
			t3lib_div::getFilesInDir(PATH_typo3conf,'sql',1,1),
			array()
		);

		$action_type = $this->INSTALL['database_type'];
		$actionParts = explode('|',$action_type);
		if (count($actionParts)<2)	{
			$action_type='';
		}

		$out='';
		$out.='<tr>
				<td nowrap="nowrap"><strong>'.$this->fw('Update required tables').'</strong></td>
				<td'.($action_type=='cmpFile|CURRENT_TABLES'?' bgcolor="#D9D5C9"':'').'>'.$this->fw('<a href="'.htmlspecialchars($this->action.'&TYPO3_INSTALL[database_type]=cmpFile|CURRENT_TABLES#bottom').'"><strong>COMPARE</strong></a>').'</td>
				<td>'.$this->fw('&nbsp;').'</td>
				<td>'.$this->fw('&nbsp;').'</td>
			</tr>';

		$out.='<tr>
				<td nowrap="nowrap"><strong>'.$this->fw('Dump static data').'</strong></td>
				<td>'.$this->fw('&nbsp;').'</td>
				<td nowrap="nowrap"'.($action_type=='import|CURRENT_STATIC'?' bgcolor="#D9D5C9"':'').'>'.$this->fw('<a href="'.htmlspecialchars($this->action.'&TYPO3_INSTALL[database_type]=import|CURRENT_STATIC#bottom').'"><strong>IMPORT</strong></a>').'</td>
				<td>'.$this->fw('&nbsp;').'</td>
			</tr>';

		$out.='<tr>
				<td colspan="4">&nbsp;</td>
			</tr>';


		reset($sql_files);
		$directJump='';
		while(list($k,$file)=each($sql_files))	{
			if ($this->mode=="123" && !count($whichTables) && strstr($file,'_testsite'))	{
				$directJump = $this->action.'&TYPO3_INSTALL[database_type]=import|'.rawurlencode($file);
			}
			$lf=t3lib_div::testInt($k);
			$fShortName = substr($file,strlen(PATH_site));

			$spec1 = $spec2 = '';

			$out.='<tr>
				<td nowrap="nowrap">'.$this->fw($fShortName.' ('.t3lib_div::formatSize(filesize($file)).')').'</td>
				<td'.($action_type=='cmpFile|'.$file?' bgcolor="#D9D5C9"':'').'>'.$this->fw('<a href="'.htmlspecialchars($this->action.'&TYPO3_INSTALL[database_type]=cmpFile|'.rawurlencode($file).'#bottom').'"><strong>COMPARE</strong></a>').'</td>
				<td nowrap="nowrap"'.($action_type=='import|'.$file?' bgcolor="#D9D5C9"':'').'>'.$this->fw('<a href="'.htmlspecialchars($this->action.'&TYPO3_INSTALL[database_type]=import|'.rawurlencode($file).'#bottom').'"><strong>IMPORT'.$spec1.$spec2.'</strong></a>').'</td>
				<td nowrap="nowrap"'.($action_type=='view|'.$file?' bgcolor="#D9D5C9"':'').'>'.$this->fw('<a href="'.htmlspecialchars($this->action.'&TYPO3_INSTALL[database_type]=view|'.rawurlencode($file).'#bottom').'"><strong>VIEW'.$spec1.$spec2.'</strong></a>').'</td>
			</tr>';
		}
			// TCA
		$out.='<tr>
			<td></td>
			<td colspan="3"'.($action_type=="cmpTCA|"?' bgcolor="#D9D5C9"':'').'><strong>'.$this->fw('<a href="'.htmlspecialchars($this->action.'&TYPO3_INSTALL[database_type]=cmpTCA|#bottom').'">Compare with $TCA</a>').'</strong></td>
		</tr>';
		$out.='<tr>
			<td></td>
			<td colspan="3"'.($action_type=="adminUser|"?' bgcolor="#D9D5C9"':'').'><strong>'.$this->fw('<a href="'.htmlspecialchars($this->action.'&TYPO3_INSTALL[database_type]=adminUser|#bottom').'">Create "admin" user</a>').'</strong></td>
		</tr>';
		$out.='<tr>
			<td></td>
			<td colspan="3"'.($action_type=="UC|"?' bgcolor="#D9D5C9"':'').'><strong>'.$this->fw('<a href="'.htmlspecialchars($this->action.'&TYPO3_INSTALL[database_type]=UC|#bottom').'">Reset user preferences</a>').'</strong></td>
		</tr>';
		$out.='<tr>
			<td></td>
			<td colspan="3"'.($action_type=="cache|"?' bgcolor="#D9D5C9"':'').'><strong>'.$this->fw('<a href="'.htmlspecialchars($this->action.'&TYPO3_INSTALL[database_type]=cache|#bottom').'">Clear tables</a>').'</strong></td>
		</tr>';

		$theForm='<table border="0" cellpadding="2" cellspacing="2">'.$out.'</table>';
		$theForm.='<a name="bottom"></a>';

		if ($directJump)	{
			if (!$action_type)	{
				$this->message($headCode, 'Menu','
				<script language="javascript" type="text/javascript">
				window.location.href = "'.$directJump.'";
				</script>',0,1);
			}
		} else {
			$this->message($headCode, 'Menu',"
			From this menu you can select which of the available SQL files you want to either compare or import/merge with the existing database.<br /><br />
			<strong>COMPARE:</strong> Compares the tables and fields of the current database and the selected file. It also offers to 'update' the difference found.<br />
			<strong>IMPORT:</strong> Imports the SQL-dump file into the current database. You can either dump the raw file or choose which tables to import. In any case, you'll see a new screen where you must confirm the operation.<br />
			<strong>VIEW:</strong> Shows the content of the SQL-file, limiting characters on a single line to a reader-friendly amount.<br /><br />
			The SQL-files are selected from typo3conf/ (here you can put your own) and t3lib/stddb/ (TYPO3 distribution). The SQL-files should be made by the <em>mysqldump</em> tool or at least be formatted like that tool would do.
	<br />
			<br />
			".$theForm."
			",0,1);
		}


		if ($action_type)	{
			switch($actionParts[0])	{
				case 'cmpFile':
					$tblFileContent='';
					if (!strcmp($actionParts[1],'CURRENT_TABLES')) {
						$tblFileContent = t3lib_div::getUrl(PATH_t3lib.'stddb/tables.sql');

						foreach ($GLOBALS['TYPO3_LOADED_EXT'] as $loadedExtConf) {
							if (is_array($loadedExtConf) && $loadedExtConf['ext_tables.sql'])	{
								$tblFileContent.= chr(10).chr(10).chr(10).chr(10).t3lib_div::getUrl($loadedExtConf['ext_tables.sql']);
							}
						}
					} elseif (@is_file($actionParts[1]))	{
						$tblFileContent = t3lib_div::getUrl($actionParts[1]);
					}
					if ($tblFileContent)	{
						$fileContent = implode(
							chr(10),
							$this->getStatementArray($tblFileContent,1,'^CREATE TABLE ')
						);
						$FDfile = $this->getFieldDefinitions_fileContent($fileContent);
						if (!count($FDfile))	{
							die ("Error: There were no 'CREATE TABLE' definitions in the provided file");
						}

							// Updating database...
						if (is_array($this->INSTALL['database_update']))	{
							$FDdb = $this->getFieldDefinitions_database();
							$diff = $this->getDatabaseExtra($FDfile, $FDdb);
							$update_statements = $this->getUpdateSuggestions($diff);
							$diff = $this->getDatabaseExtra($FDdb, $FDfile);
							$remove_statements = $this->getUpdateSuggestions($diff,'remove');

							$this->performUpdateQueries($update_statements['add'],$this->INSTALL['database_update']);
							$this->performUpdateQueries($update_statements['change'],$this->INSTALL['database_update']);
							$this->performUpdateQueries($remove_statements['change'],$this->INSTALL['database_update']);
							$this->performUpdateQueries($remove_statements['drop'],$this->INSTALL['database_update']);

							$this->performUpdateQueries($update_statements['create_table'],$this->INSTALL['database_update']);
							$this->performUpdateQueries($remove_statements['change_table'],$this->INSTALL['database_update']);
							$this->performUpdateQueries($remove_statements['drop_table'],$this->INSTALL['database_update']);
						}

							// Init again / first time depending...
						$FDdb = $this->getFieldDefinitions_database();

						$diff = $this->getDatabaseExtra($FDfile, $FDdb);
						$update_statements = $this->getUpdateSuggestions($diff);

						$diff = $this->getDatabaseExtra($FDdb, $FDfile);
						$remove_statements = $this->getUpdateSuggestions($diff,'remove');

						$tLabel = 'Update database tables and fields';

						if ($remove_statements || $update_statements)	{
							$formContent = $this->generateUpdateDatabaseForm('get_form',$update_statements,$remove_statements,$action_type);
							$this->message($tLabel,'Table and field definitions should be updated',"
							There seems to be a number of differencies between the database and the selected SQL-file.
							Please select which statements you want to execute in order to update your database:<br /><br />
							".$formContent."
							",2);
						} else {
							$formContent = $this->generateUpdateDatabaseForm('get_form',$update_statements,$remove_statements,$action_type);
							$this->message($tLabel,'Table and field definitions are OK.',"
							The tables and fields in the current database corresponds perfectly to the database in the selected SQL-file.
							",-1);
						}
					}
				break;
				case 'cmpTCA':
					$this->includeTCA();
					$FDdb = $this->getFieldDefinitions_database();

						// Displaying configured fields which are not in the database
					$tLabel='Tables and fields in $TCA, but not in database';
					$cmpTCA_DB = $this->compareTCAandDatabase($GLOBALS['TCA'],$FDdb);
					if (!count($cmpTCA_DB['extra']))	{
						$this->message($tLabel,'Table and field definitions OK','
						All fields and tables configured in $TCA appeared to exist in the database as well
						',-1);
					} else {
						$this->message($tLabel,'Invalid table and field definitions in $TCA!','
						There are some tables and/or fields configured in the \$TCA array which does not exist in the database!
						This will most likely cause you trouble with the TYPO3 backend interface!
						',3);
						while(list($tableName, $conf)=each($cmpTCA_DB['extra']))	{
							$this->message($tLabel, $tableName,$this->displayFields($conf['fields'],0,'Suggested database field:'),2);
						}
					}

						// Displaying tables that are not setup in
					$cmpDB_TCA = $this->compareDatabaseAndTCA($FDdb,$GLOBALS['TCA']);
					$excludeTables='be_sessions,fe_session_data,fe_sessions';
					if (TYPO3_OS=='WIN')	{$excludeTables = strtolower($excludeTables);}
					$excludeFields = array(
						'be_users' => 'uc,lastlogin,usergroup_cached_list',
						'fe_users' => 'uc,lastlogin,fe_cruser_id',
						'pages' => 'SYS_LASTCHANGED',
						'sys_dmail' => 'mailContent',
						'tt_board' => 'doublePostCheck',
						'tt_guest' => 'doublePostCheck',
						'tt_products' => 'ordered'
					);
					$tCount=0;
					$fCount=0;
					$tLabel="Tables from database, but not in \$TCA";
					$fLabel="Fields from database, but not in \$TCA";
					$this->message($tLabel);
					if (is_array($cmpDB_TCA['extra']))	{
						while(list($tableName, $conf)=each($cmpDB_TCA['extra']))	{
							if (!t3lib_div::inList($excludeTables,$tableName)
									&& substr($tableName,0,4)!="sys_"
									&& substr($tableName,-3)!="_mm"
									&& substr($tableName,0,6)!="index_"
									&& substr($tableName,0,6)!='cache_')	{
								if ($conf['whole_table'])	{
									$this->message($tLabel, $tableName,$this->displayFields($conf['fields']),1);
									$tCount++;
								} else {
									list($theContent, $fC)	= $this->displaySuggestions($conf['fields'],$excludeFields[$tableName]);
									$fCount+=$fC;
									if ($fC)	$this->message($fLabel, $tableName,$theContent,1);
								}
							}
						}
					}
					if (!$tCount)	{
						$this->message($tLabel,'Correct number of tables in the database',"
						There are no extra tables in the database compared to the configured tables in the \$TCA array.
						",-1);
					} else {
						$this->message($tLabel,'Extra tables in the database',"
						There are some tables in the database which are not configured in the \$TCA array.
						You should probably not worry about this, but please make sure that you know what these tables are about and why they are not configured in \$TCA.
						",2);
					}

					if (!$fCount)	{
						$this->message($fLabel,'Correct number of fields in the database',"
						There are no additional fields in the database tables compared to the configured fields in the \$TCA array.
						",-1);
					} else {
						$this->message($fLabel,'Extra fields in the database',"
						There are some additional fields the database tables which are not configured in the \$TCA array.
						You should probably not worry about this, but please make sure that you know what these fields are about and why they are not configured in \$TCA.
						",2);
					}

						// Displaying actual and suggested field database defitions
					if (is_array($cmpTCA_DB['matching']))	{
						$tLabel="Comparison between database and \$TCA";

						$this->message($tLabel,'Actual and suggested field definitions',"
						This table shows you the suggested field definitions which are calculated based on the configuration in \$TCA.
						If the suggested value differs from the actual current database value, you should not panic, but simply check if the datatype of that field is sufficient compared to the data, you want TYPO3 to put there.
						",0);
						while(list($tableName, $conf)=each($cmpTCA_DB['matching']))	{
							$this->message($tLabel, $tableName,$this->displayFieldComp($conf['fields'], $FDdb[$tableName]['fields']),1);
						}
					}
				break;
				case 'import':
					$mode123Imported=0;
					$tblFileContent='';
					if (preg_match('/^CURRENT_/', $actionParts[1]))	{
						if (!strcmp($actionParts[1],'CURRENT_TABLES') || !strcmp($actionParts[1],'CURRENT_TABLES+STATIC'))	{
							$tblFileContent = t3lib_div::getUrl(PATH_t3lib.'stddb/tables.sql');

							reset($GLOBALS['TYPO3_LOADED_EXT']);
							while(list(,$loadedExtConf)=each($GLOBALS['TYPO3_LOADED_EXT']))	{
								if (is_array($loadedExtConf) && $loadedExtConf['ext_tables.sql'])	{
									$tblFileContent.= chr(10).chr(10).chr(10).chr(10).t3lib_div::getUrl($loadedExtConf['ext_tables.sql']);
								}
							}
						}
						if (!strcmp($actionParts[1],'CURRENT_STATIC') || !strcmp($actionParts[1],'CURRENT_TABLES+STATIC'))	{
							reset($GLOBALS['TYPO3_LOADED_EXT']);
							while(list(,$loadedExtConf)=each($GLOBALS['TYPO3_LOADED_EXT']))	{
								if (is_array($loadedExtConf) && $loadedExtConf['ext_tables_static+adt.sql'])	{
									$tblFileContent.= chr(10).chr(10).chr(10).chr(10).t3lib_div::getUrl($loadedExtConf['ext_tables_static+adt.sql']);
								}
							}
						}
					} elseif (@is_file($actionParts[1]))	{
						$tblFileContent = t3lib_div::getUrl($actionParts[1]);
					}

					if ($tblFileContent)	{
						$tLabel='Import SQL dump';
							// Getting statement array from
						$statements = $this->getStatementArray($tblFileContent,1);
						list($statements_table, $insertCount) = $this->getCreateTables($statements,1);

							// Updating database...
						if ($this->INSTALL['database_import_all'])	{
							$r=0;
							foreach ($statements as $k=>$v)	{
								$res = $GLOBALS['TYPO3_DB']->admin_query($v);
								$r++;
							}

								// Make a database comparison because some tables that are defined twice have not been created at this point. This applies to the "pages.*" fields defined in sysext/cms/ext_tables.sql for example.
							$fileContent = implode(
								$this->getStatementArray($tblFileContent,1,'^CREATE TABLE '),
								chr(10)
							);
							$FDfile = $this->getFieldDefinitions_fileContent($fileContent);
							$FDdb = $this->getFieldDefinitions_database();
							$diff = $this->getDatabaseExtra($FDfile, $FDdb);
							$update_statements = $this->getUpdateSuggestions($diff);
							if (is_array($update_statements['add']))	{
								foreach ($update_statements['add'] as $statement)	{
									$res = $GLOBALS['TYPO3_DB']->admin_query($statement);
								}
							}

							if ($this->mode=='123')	{
									// Create default be_user admin/password
								$username = 'admin';
								$pass = 'password';

								$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'be_users', 'username='.$GLOBALS['TYPO3_DB']->fullQuoteStr($username, 'be_users'));
								if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res))	{
									$insertFields = array(
										'username' => $username,
										'password' => md5($pass),
										'admin' => 1,
										'uc' => '',
										'fileoper_perms' => 7,
										'tstamp' => time(),
										'crdate' => time()
									);

									$GLOBALS['TYPO3_DB']->exec_INSERTquery('be_users', $insertFields);
								}
							}

							$this->message($tLabel,'Imported ALL',"
								Queries: ".$r."
							",1,1);
							if (t3lib_div::_GP('goto_step'))	{
								$this->action.='&step='.t3lib_div::_GP('goto_step');
								Header('Location: '.t3lib_div::locationHeaderUrl($this->action));
								exit;
							}
						} elseif (is_array($this->INSTALL['database_import']))	{
								// Traverse the tables
							reset($this->INSTALL['database_import']);
							while(list($table,$md5str)=each($this->INSTALL['database_import']))	{
								if ($md5str==md5($statements_table[$table]))	{
									$res = $GLOBALS['TYPO3_DB']->admin_query('DROP TABLE IF EXISTS '.$table);
									$res = $GLOBALS['TYPO3_DB']->admin_query($statements_table[$table]);

									if ($insertCount[$table])	{
										$statements_insert = $this->getTableInsertStatements($statements, $table);
										reset($statements_insert);
										while(list($k,$v)=each($statements_insert))	{
											$res = $GLOBALS['TYPO3_DB']->admin_query($v);
										}
									}

									$this->message($tLabel,"Imported '".$table."'","
										Rows: ".$insertCount[$table]."
									",1,1);
								}
							}
						}

						$mode123Imported=$this->isBasicComplete($tLabel);

						if (!$mode123Imported)	{
								// Re-Getting current tables - may have been changed during import
							$whichTables=$this->getListOfTables();

							if (count($statements_table))	{
								reset($statements_table);
								$out='';
								while(list($table,$definition)=each($statements_table))	{
									$exist=isset($whichTables[$table]);
									$out.='<tr>
										<td><input type="checkbox" name="TYPO3_INSTALL[database_import]['.$table.']" id="database_import_'.$table.'" value="'.md5($definition).'"></td>
										<td><label for="database_import_'.$table.'"><strong>'.$this->fw($table).'</strong></label></td>
										<td><img src="clear.gif" width="10" height="1"></td>
										<td nowrap="nowrap">'.$this->fw($insertCount[$table]?"Rows: ".$insertCount[$table]:"").'</td>
										<td><img src="clear.gif" width="10" height="1"></td>
										<td nowrap="nowrap">'.($exist?'<img src="'.$this->backPath.'gfx/icon_warning.gif" width="18" height="16" align="top" alt="">'.$this->fw('Table exists!'):'').'</td>
										</tr>';
								}

								$content ='';
								if ($this->mode!='123')	{
									$content.='<table border="0" cellpadding="0" cellspacing="0">'.$out.'</table>
									<hr />
									';
								}
								$content.='<input type="checkbox" name="TYPO3_INSTALL[database_import_all]" id="database_import_all" value="1"'.($this->mode=="123"||t3lib_div::_GP('presetWholeTable')?' checked="checked"':'').'> <label for="database_import_all">'.$this->fw("Import the whole file '".basename($actionParts[1])."' directly (ignores selections above)").'</label><br />

								';
								$form = $this->getUpdateDbFormWrap($action_type, $content);
								$this->message($tLabel,'Select tables to import',"
								This is an overview of the CREATE TABLE definitions in the SQL file.
								Select which tables you want to dump to the database.
								Any table you choose dump to the database is dropped from the database first, so you'll lose all data in existing tables.
								".$form,1,1);
							} else {
								$this->message($tLabel,'No tables',"
								There seems to be no CREATE TABLE definitions in the SQL file.
								This tool is intelligently creating one table at a time and not just dumping the whole content of the file uncritically. That's why there must be defined tables in the SQL file.
								",3,1);
							}
						}
					}
				break;
				case 'view':
					if (@is_file($actionParts[1])) {
						$tLabel = 'Import SQL dump';
							// Getting statement array from
						$fileContent = t3lib_div::getUrl($actionParts[1]);
						$statements = $this->getStatementArray($fileContent, 1);
						$maxL = 1000;
						$strLen = strlen($fileContent);
						$maxlen = 200+($maxL-t3lib_div::intInRange(($strLen-20000)/100,0,$maxL));
						if (count($statements))	{
							$out = '';
							foreach ($statements as $statement) {
								$out.= nl2br(htmlspecialchars(t3lib_div::fixed_lgd($statement,$maxlen)).chr(10).chr(10));
							}
						}
						$this->message($tLabel,'Content of '.basename($actionParts[1]),$out,1);
					}
				break;
				case 'adminUser':	// Create admin user
					if ($whichTables['be_users'])	{
						if (is_array($this->INSTALL['database_adminUser']))	{
							$username = ereg_replace('[^[:alnum:]_-]','',trim($this->INSTALL['database_adminUser']['username']));
							$username = str_replace(' ','_',$username);
							$pass = trim($this->INSTALL['database_adminUser']['password']);
							if ($username && $pass)	{
								$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'be_users', 'username='.$GLOBALS['TYPO3_DB']->fullQuoteStr($username, 'be_users'));
								if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res))	{

									$insertFields = array(
										'username' => $username,
										'password' => md5($pass),
										'admin' => 1,
										'uc' => '',
										'fileoper_perms' => 7,
										'tstamp' => time(),
										'crdate' => time()
									);
										// creates salted password hashes
									if (t3lib_extMgm::isLoaded('t3sec_saltedpw')
											&& tx_t3secsaltedpw_div::isUsageEnabled()) {
										$objPHPass = t3lib_div::makeInstance('tx_t3secsaltedpw_phpass');
										$insertFields['password'] = $objPHPass->getHashedPassword($pass);
									}
									$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery('be_users', $insertFields);

									$this->isBasicComplete($headCode);

									if ($result) {
										$this->message($headCode,'User created','
											Username: <strong>'.htmlspecialchars($username).'</strong><br />
											Password: <strong>'.htmlspecialchars($pass).'</strong><br />',
											1,1);
									} else {
										$this->message($headCode,'User not created','
											Error: <strong>'.htmlspecialchars($GLOBALS['TYPO3_DB']->sql_error()).'</strong><br />',
											3,1);
									}
								} else {
									$this->message($headCode,'Username not unique!','
									The username, <strong>'.htmlspecialchars($username).'</strong>, was not unique.',2,1);
								}
							}
						}
						$content = '
						<input type="text" name="TYPO3_INSTALL[database_adminUser][username]"> username - unique, no space, lowercase<br />
						<input type="text" name="TYPO3_INSTALL[database_adminUser][password]"> password
						';
						$form = $this->getUpdateDbFormWrap($action_type, $content);
						$this->message($headCode,'Create admin user',"
						Enter username and password for a new admin user.<br />
						You should use this function only if there are no admin users in the database, for instance if this is a blank database.<br />
						After you've created the user, log in and add the rest of the user information, like email and real name.<br />
						<br />
						".$form."
						",0,1);
					} else {
						$this->message($headCode,'Required table not in database',"
						'be_users' must be a table in the database!
						",3,1);
					}
				break;
				case 'UC':	// clear uc
					if ($whichTables['be_users'])	{
						if (!strcmp($this->INSTALL['database_UC'],1))	{
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery('be_users', '', array('uc' => ''));
							$this->message($headCode,'Clearing be_users.uc','Done.',1);
						}
						$content = '
						<input type="checkbox" name="TYPO3_INSTALL[database_UC]" id="database_UC" value="1" checked="checked"> <label for="database_UC">Clear be_users preferences ("uc" field)</label>
						';
						$form = $this->getUpdateDbFormWrap($action_type, $content);
						$this->message($headCode,'Clear user preferences',"
						If you press this button all backend users from the tables be_users will have their user preferences cleared (field 'uc' set to an empty string).<br />
						This may come in handy in rare cases where that configuration may be corrupt.<br />
						Clearing this will clear all user settings from the 'Setup' module.<br />
						<br />
						".$form);
					} else {
						$this->message($headCode,'Required table not in database',"
						'be_users' must be a table in the database!
						",3);
					}
				break;
				case 'cache':
					$tableListArr = explode(',','cache_pages,cache_pagesection,cache_hash,cache_imagesizes,--div--,sys_log,sys_history,--div--,be_sessions,fe_sessions,fe_session_data'.
						(t3lib_extMgm::isLoaded('indexed_search') ? ',--div--,index_words,index_rel,index_phash,index_grlist,index_section,index_fulltext' : '').
						(t3lib_extMgm::isLoaded('tt_products') ? ',--div--,sys_products_orders,sys_products_orders_mm_tt_products' : '').
						(t3lib_extMgm::isLoaded('direct_mail') ? ',--div--,sys_dmail_maillog' : '').
						(t3lib_extMgm::isLoaded('sys_stat') ? ',--div--,sys_stat' : '')
					);

					if (is_array($this->INSTALL['database_clearcache']))	{
						$qList=array();
						reset($tableListArr);
						while(list(,$table)=each($tableListArr))	{
							if ($table!='--div--')	{
								$table_c = TYPO3_OS=='WIN' ? strtolower($table) : $table;
								if ($this->INSTALL['database_clearcache'][$table] && $whichTables[$table_c])	{
									$GLOBALS['TYPO3_DB']->exec_DELETEquery($table, '');
									$qList[] = $table;
								}
							}
						}
						if (count($qList))	{
							$this->message($headCode,'Clearing cache','
							The following tables were emptied:<br /><br />
							'.implode($qList,'<br />')
							,1);
						}
					}
						// Count entries and make checkboxes
					$labelArr = array(
						'cache_pages' => 'Pages',
						'cache_pagesection' => 'TS template related information',
						'cache_hash' => 'Multipurpose md5-hash cache',
						'cache_imagesizes' => 'Cached image sizes',
						'sys_log' => 'Backend action logging',
						'sys_stat' => 'Page hit statistics',
						'sys_history' => 'Addendum to the sys_log which tracks ALL changes to content through TCE. May become huge by time. Is used for rollback (undo) and the WorkFlow engine.',
						'be_sessions' => 'Backend User sessions',
						'fe_sessions' => 'Frontend User sessions',
						'fe_session_data' => 'Frontend User sessions data',
						'sys_dmail_maillog' => 'Direct Mail log',
						'sys_products_orders' => 'tt_product orders',
						'sys_products_orders_mm_tt_products' => 'relations between tt_products and sys_products_orders'
					);

					$checkBoxes=array();
					$countEntries=array();
					reset($tableListArr);
					while(list(,$table)=each($tableListArr))	{
						if ($table!='--div--')	{
							$table_c = TYPO3_OS=='WIN' ? strtolower($table) : $table;
							if ($whichTables[$table_c])	{
								$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)', $table, '');
								list($countEntries[$table]) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
									// Checkboxes:
								$checkBoxes[]= '<input type="checkbox" name="TYPO3_INSTALL[database_clearcache]['.$table.']" id="TYPO3_INSTALL[database_clearcache]['.$table.']" value="1"'.($this->INSTALL['database_clearcache'][$table]||$_GET['PRESET']['database_clearcache'][$table]?' checked="checked"':'').'> <label for="TYPO3_INSTALL[database_clearcache]['.$table.']"><strong>'.$table.'</strong> ('.$countEntries[$table].' rows) - '.$labelArr[$table].'</label>';
							}
						} else {
								$checkBoxes[]= 	'<hr />';
						}
					}

					$content = implode('<br />',$checkBoxes).'<br /><br />';

					$form = $this->getUpdateDbFormWrap($action_type, $content);
					$this->message($headCode,'Clear out selected tables','
					Pressing this button will delete all records from the selected tables.<br />
					<br />
					'.$form.'
					');
				break;
			}
		}

		$this->output($this->outputWrapper($this->printAll()));
	}
}
?>