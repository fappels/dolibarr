<?php
/* Copyright (C) 2015		ATM Consulting					<support@atm-consulting.fr>
 * Copyright (C) 2019-2020	Open-DSI						<support@open-dsi.fr>
 * Copyright (C) 2020-2025	Frédéric France					<frederic.france@free.fr>
 * Copyright (C) 2024-2025	MDW								<mdeweerd@users.noreply.github.com>
 * Copyright (C) 2024		Francis Appels					<francis.appels@z-application.com>
 * Copyright (C) 2025		Alexandre Spangaro				<alexandre@inovea-conseil.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *    \file       htdocs/intracommreport/class/intracommreport.class.php
 *    \ingroup    Intracomm report
 *    \brief      File of class to manage intracomm report
 */


require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';


/**
 * Class to manage intracomm report
 */
class IntracommReport extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'intracommreport';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element = 'intracommreport';

	/**
	 * @var string Field with ID of parent key if this field has a parent
	 */
	public $fk_element = 'fk_intracommreport';

	/**
	 * @var string 	String with name of icon for intracommreport. Must be a 'fa-xxx' fontawesome code (or 'fa-xxx_fa_color_size') or 'intracommreport@monmodule' if picto is file 'img/object_intracommreport.png'.
	 */
	public $picto = 'intracommreport';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;

	/**
	 *  'type' field format:
	 *  	'integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]',
	 *  	'select' (list of values are in 'options'. for integer list of values are in 'arrayofkeyval'),
	 *  	'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:CategoryIdType[:CategoryIdList[:SortField]]]]]]',
	 *  	'chkbxlst:...',
	 *  	'varchar(x)',
	 *  	'text', 'text:none', 'html',
	 *   	'double(24,8)', 'real', 'price', 'stock',
	 *  	'date', 'datetime', 'timestamp', 'duration',
	 *  	'boolean', 'checkbox', 'radio', 'array',
	 *  	'mail', 'phone', 'url', 'password', 'ip'
	 *		Note: Filter must be a Dolibarr Universal Filter syntax string. Example: "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.status:!=:0) or (t.nature:is:NULL)"
	 *  'length' the length of field. Example: 255, '24,8'
	 *  'label' the translation key.
	 *  'alias' the alias used into some old hard coded SQL requests
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or 'getDolGlobalInt("MY_SETUP_PARAM")' or 'isModEnabled("multicurrency")' ...)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'alwayseditable' says if field can be modified also when status is not draft ('1' or '0')
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommended to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'help' and 'helplist' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if you need to validate the field with $this->validateField(). Need MAIN_ACTIVATE_VALIDATION_RESULT.
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array<string,array{type:string,label:string,enabled:int<0,2>|string,position:int,notnull?:int,visible:int<-6,6>|string,alwayseditable?:int<0,1>,noteditable?:int<0,1>,default?:string,index?:int,foreignkey?:string,searchall?:int<0,1>,isameasure?:int<0,1>,css?:string,csslist?:string,help?:string,showoncombobox?:int<0,4>,disabled?:int<0,1>,arrayofkeyval?:array<int|string,string>,autofocusoncreate?:int<0,1>,comment?:string,copytoclipboard?:int<1,2>,validate?:int<0,1>,showonheader?:int<0,1>}>	Array of properties of field to show
	 */
	public $fields = array(
		"rowid" => array("type"=>"integer", "label"=>"TechnicalID", "enabled"=>"1", 'position'=>1, 'notnull'=>1, "visible"=>"0", "noteditable"=>"1", "index"=>"1", "css"=>"left", "comment"=>"Id"),
		"ref" => array("type"=>"varchar(128)", "label"=>"Ref", "enabled"=>"1", 'position'=>20, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', "index"=>"1", "searchall"=>"1", "showoncombobox"=>"1", "validate"=>"1", "comment"=>"Reference of object"),
		"label" => array("type"=>"varchar(255)", "label"=>"Label", "enabled"=>"1", 'position'=>30, 'notnull'=>0, "visible"=>"1", "alwayseditable"=>"1", "searchall"=>"1", "css"=>"minwidth300", "cssview"=>"wordbreak", "help"=>"Help text", "showoncombobox"=>"2", "validate"=>"1",),
		"exporttype" => array("type"=>"varchar(64)", "label"=>"ExportType", "enabled"=>"1", 'position'=>32, 'notnull'=>1, "arrayofkeyval"=>array("deb" => "DEB", "des" => "DES"),"visible"=>"1",),
		"type_declaration" => array("type"=>"varchar(64)", "label"=>"TypeOfDeclaration", "enabled"=>"1", 'position'=>34, 'notnull'=>1, "visible"=>"1", "arrayofkeyval"=>array("introduction" => "Introduction", "expedition" => "Expedition"), "default"=>"expedition",),
		"period_month" => array("type"=>"integer", "label"=>"AnalysisPeriodMonth", "enabled"=>"1", 'position'=>36, 'notnull'=>0, "visible"=>"2",),
		"period_year" => array("type"=>"integer", "label"=>"AnalysisPeriodYear", "enabled"=>"1", 'position'=>38, 'notnull'=>0, "visible"=>"2",),
		"amount" => array("type"=>"price", "label"=>"Amount", "enabled"=>"1", 'position'=>40, 'notnull'=>0, "visible"=>"5", "default"=>"null", "isameasure"=>"1", "help"=>"TotalInvoiced", "validate"=>"1", 'noteditable'=>'1',),
		"description" => array("type"=>"text", "label"=>"Description", "enabled"=>"1", 'position'=>60, 'notnull'=>0, "visible"=>"3", "validate"=>"1",),
		"note_public" => array("type"=>"html", "label"=>"NotePublic", "enabled"=>"1", 'position'=>61, 'notnull'=>0, "visible"=>"0", "cssview"=>"wordbreak", "validate"=>"1",),
		"note_private" => array("type"=>"html", "label"=>"NotePrivate", "enabled"=>"1", 'position'=>62, 'notnull'=>0, "visible"=>"0", "cssview"=>"wordbreak", "validate"=>"1",),
		"date_creation" => array("type"=>"datetime", "label"=>"DateCreation", "enabled"=>"1", 'position'=>500, 'notnull'=>1, "visible"=>"-2",),
		"tms" => array("type"=>"timestamp", "label"=>"DateModification", "enabled"=>"1", 'position'=>501, 'notnull'=>0, "visible"=>"-2",),
		"fk_user_creat" => array("type"=>"integer:User:user/class/user.class.php", "label"=>"UserAuthor", "picto"=>"user", "enabled"=>"1", 'position'=>510, 'notnull'=>1, "visible"=>"-2", "csslist"=>"tdoverflowmax150",),
		"fk_user_modif" => array("type"=>"integer:User:user/class/user.class.php", "label"=>"UserModif", "picto"=>"user", "enabled"=>"1", 'position'=>511, 'notnull'=>-1, "visible"=>"-2", "csslist"=>"tdoverflowmax150",),
		"last_main_doc" => array("type"=>"varchar(255)", "label"=>"LastMainDoc", "enabled"=>"1", 'position'=>600, 'notnull'=>0, "visible"=>"0",),
		"import_key" => array("type"=>"varchar(14)", "label"=>"ImportId", "enabled"=>"1", 'position'=>1000, 'notnull'=>-1, "visible"=>"-2",),
		"model_pdf" => array("type"=>"varchar(255)", "label"=>"Model pdf", "enabled"=>"1", 'position'=>1010, 'notnull'=>-1, "visible"=>"0",),
		"status" => array("type"=>"integer", "label"=>"Status", "enabled"=>"1", 'position'=>2000, 'notnull'=>1, "visible"=>"5", "index"=>"1", "arrayofkeyval"=>array("0" => "Draft", "1" => "Validated", "9" => "Canceled"), "validate"=>"1",),
		'entity' => array('type'=>'sellist:entity:label:rowid', 'label'=>'Entity', 'enabled'=>1, 'visible'=>-2, 'notnull'=> 1, 'default'=>1, 'index'=>1, 'position'=>502),
	);
	/**
	 * @var int
	 */
	public $rowid;
	/**
	 * @var string
	 */
	public $ref;
	/**
	 * @var string
	 */
	public $type_declaration;
	/**
	 * @var string
	 */
	public $content_xml;
	/**
	 * @var 'deb'|'des'
	 */
	public $type_export;
	/**
	 * @var int|string
	 */
	public $datec;
	/**
	 * @var int
	 */
	public $tms;

	/**
	 * @var string ref ???
	 */
	public $label;

	/**
	 * @var int
	 */
	public $period_year;

	/**
	 * @var int
	 */
	public $period_month;

	/**
	 * @var string
	 */
	public $declaration;

	/**
	 * @var string declaration number
	 */
	public $declaration_number;

	/**
	 * @var string
	 */
	public $numero_declaration;


	/**
	 * DEB - Product
	 */
	const TYPE_DEB = 0;

	/**
	 * DES - Service
	 */
	const TYPE_DES = 1;

	/**
	  * @var string   Helper class to display report lines preview (lines model)
	  */
	public $class_element_line = 'IntracomreportDebLine';

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $langs;

		$this->db = $db;
		$this->ismultientitymanaged = 1;
		$this->isextrafieldmanaged = 1;

		// @phan-suppress-next-line PhanTypeMismatchProperty
		if (!getDolGlobalInt('MAIN_SHOW_TECHNICAL_ID') && isset($this->fields['rowid']) && !empty($this->fields['ref'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}

		$this->type_export = 'deb';
	}

	/**
	 * Create object into database
	 *
	 * @param 	User		$user 		User
	 * @param 	int<0,1> 	$notrigger 	notrigger
	 * @return 	int
	 */
	public function create(User $user, $notrigger = 0)
	{
		$resultcreate = $this->createCommon($user, $notrigger);

		//$resultvalidate = $this->validate($user, $notrigger);

		return $resultcreate;
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param 	int    	$id   			Id object
	 * @param 	string 	$ref  			Ref
	 * @param	int		$noextrafields	0=Default to load extrafields, 1=No extrafields
	 * @param	int		$nolines		0=Default to load extrafields, 1=No extrafields
	 * @return 	int     				Return integer <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null, $noextrafields = 0, $nolines = 0)
	{
		$result = $this->fetchCommon($id, $ref, '', $noextrafields);
		return $result;
	}

	/**
	 * Load list of objects in memory from the database.
	 * Using a fetchAll() with limit = 0 is a very bad practice. Instead try to forge yourself an optimized SQL request with
	 * your own loop with start and stop pagination.
	 *
	 * @param  string      	$sortorder    	Sort Order
	 * @param  string      	$sortfield    	Sort field
	 * @param  int         	$limit        	Limit the number of lines returned
	 * @param  int         	$offset       	Offset
	 * @param  string		$filter       	Filter as an Universal Search string.
	 * 										Example: '((client:=:1) OR ((client:>=:2) AND (client:<=:3))) AND (client:!=:8) AND (nom:like:'a%')'
	 * @param  string      	$filtermode   	No more used
	 * @return array|int                 	int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 1000, $offset = 0, string $filter = '', $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".$this->db->prefix().$this->table_element." as t";
		if (isset($this->isextrafieldmanaged) && $this->isextrafieldmanaged == 1) {
			$sql .= " LEFT JOIN ".$this->db->prefix().$this->table_element."_extrafields as te ON te.fk_object = t.rowid";
		}
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}

		// Manage filter
		$errormessage = '';
		$sql .= forgeSQLFromUniversalSearchCriteria($filter, $errormessage);
		if ($errormessage) {
			$this->errors[] = $errormessage;
			dol_syslog(__METHOD__.' '.implode(',', $this->errors), LOG_ERR);
			return -1;
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				if (!empty($record->isextrafieldmanaged)) {
					$record->fetch_optionals();
				}

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.implode(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  int 	$notrigger 0=launch triggers after, 1=disable triggers
	 * @return int             Return integer <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = 0)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Function delete
	 *
	 * @param 	User 	$user 		User
	 * @param 	int 	$notrigger 	notrigger
	 * @return 	int
	 */
	public function delete($user, $notrigger = 0)
	{
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						Return integer <=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED) {
			dol_syslog(get_class($this)."::validate action abandoned: already validated", LOG_WARNING);
			return 0;
		}

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) { // empty should not happened, but when it occurs, the test save life
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ";
			if (!empty($this->fields['ref'])) {
				$sql .= " ref = '".$this->db->escape($num)."',";
			}
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) {
				$sql .= ", date_validation = '".$this->db->idate($now)."'";
			}
			if (!empty($this->fields['fk_user_valid'])) {
				$sql .= ", fk_user_valid = ".((int) $user->id);
			}
			$sql .= " WHERE rowid = ".((int) $this->id);

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger) {
				// Call trigger
				$result = $this->call_trigger('MYOBJECT_VALIDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		if (!$error) {
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref)) {
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'intracommreport/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'intracommreport/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++;
					$this->error = $this->db->lasterror();
				}
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filepath = 'intracommreport/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filepath = 'intracommreport/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++;
					$this->error = $this->db->lasterror();
				}

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->intracommreport->dir_output.'/intracommreport/'.$oldref;
				$dirdest = $conf->intracommreport->dir_output.'/intracommreport/'.$newref;
				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->intracommreport->dir_output.'/intracommreport/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry) {
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		if (!$error) {
			// set total amount
			$this->setValueFrom('amount', $this->amount);
			// generate xml
			$dirdest = $conf->intracommreport->dir_output.'/intracommreport/'.$this->newref;
			$filename = $dirdest.'/'.$this->exporttype.'_'.$this->newref.'.xml';
			$this->numero_declaration = $this->newref;
			if ($this->exporttype == 'deb') {
				$content_xml = $this->getXML('O', $this->type_declaration, sprintf("%04d", $this->period_year).'-'.sprintf("%02d", $this->period_month));
			} elseif ($this->exporttype == 'des') {
				$content_xml = $this->getXMLDes($this->period_year, $this->period_month, $this->type_declaration);
			}
			if (is_string($content_xml)) {
				if (!dol_is_dir($dirdest)) {
					dol_mkdir($dirdest);
				}
				$file = fopen($filename, "w");
				fwrite($file, $content_xml);
				fclose($file);
				dolChmod($filename);
			} else {
				$error++;
			}
		}

		// Set new ref and current status
		if (!$error) {
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$this->amount = 0;

		$sql = $this->getSQLFactLines($this->type_declaration, sprintf("%04d", $this->period_year).'-'.sprintf("%02d", $this->period_month), $this->exporttype);

		$resql = $this->db->query($sql);

		if ($resql && $this->db->num_rows($resql) > 0) {
			$i = 1;

			while ($res = $this->db->fetch_object($resql)) {
				if ($this->exporttype == 'des') {
					// TODO
				} else {
					$objectline = new IntracommreportDebLine($this->db);
					$objectline->id = $i;
					$objectline->fk_facture = $res->fk_facture;
					$objectline->fk_product = $res->fk_product;
					$objectline->customcode = $res->customcode;
					$objectline->code = $res->code;
					$objectline->product_code = $res->product_code;
					$objectline->weight = round($res->weight * $res->qty);
					$objectline->qty = $res->qty;
					$objectline->amount = abs($res->total_ht);
					$objectline->procedure_code = ($res->total_ht >= 0 ? '21' : '25');
					$objectline->fk_soc = $res->id_client;
					$objectline->tva_intra = $res->tva_intra;
					$objectline->mode_transport = $res->mode_transport;
					$objectline->region_code = substr($res->zip, 0, 2);

					$this->lines[] = $objectline;

					$this->amount += $objectline->amount;
				}

				$i++;
			}
		}

		return $this->lines;
	}

	/**
	 * Generate XML file
	 *
	 * @param string		$mode 				'O' for create, R for regenerate (Look always 0 meant toujours 0 within the framework of XML exchanges according to documentation)
	 * @param string		$type 				Declaration type by default - introduction or expedition (always 'expedition' for Des)
	 * @param string		$period_reference	Period of reference
	 * @return string|false						Return a well-formed XML string based on SimpleXML element, false or 0 if error
	 */
	public function getXML($mode = 'O', $type = 'introduction', $period_reference = '')
	{
		global $conf, $mysoc;

		/**************Construction de quelques variables********************/
		$party_id = substr(strtr($mysoc->tva_intra ?? '', array(' ' => '')), 0, 4).$mysoc->idprof2;
		$declarant = substr($mysoc->managers, 0, 14);
		$id_declaration = self::getDeclarationNumber($this->numero_declaration);
		/********************************************************************/

		/**************Construction du fichier XML***************************/
		$e = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" standalone="yes"?><INSTAT></INSTAT>');

		$envelope = $e->addChild('Envelope');
		$envelope->addChild('envelopeId', $conf->global->INTRACOMMREPORT_NUM_AGREMENT);
		$date_time = $envelope->addChild('DateTime');
		$date_time->addChild('date', date('Y-m-d'));
		$date_time->addChild('time', date('H:i:s'));
		$party = $envelope->addChild('Party');
		$party->addAttribute('partyType', $conf->global->INTRACOMMREPORT_TYPE_ACTEUR);
		$party->addAttribute('partyRole', $conf->global->INTRACOMMREPORT_ROLE_ACTEUR);
		$party->addChild('partyId', $party_id);
		$party->addChild('partyName', $declarant);
		$envelope->addChild('softwareUsed', 'Dolibarr');
		$declaration = $envelope->addChild('Declaration');
		$declaration->addChild('declarationId', $id_declaration);
		$declaration->addChild('referencePeriod', $period_reference);
		if (getDolGlobalString('INTRACOMMREPORT_TYPE_ACTEUR') === 'PSI') {
			$psiId = $party_id;
		} else {
			$psiId = 'NA';
		}
		$declaration->addChild('PSIId', $psiId);
		$function = $declaration->addChild('Function');
		$function->addChild('functionCode', $mode);
		$declaration->addChild('declarationTypeCode', getDolGlobalString('INTRACOMMREPORT_NIV_OBLIGATION_'.strtoupper($type)));
		$declaration->addChild('flowCode', ($type == 'introduction' ? 'A' : 'D'));
		$declaration->addChild('currencyCode', $conf->global->MAIN_MONNAIE);
		/********************************************************************/

		/**************Ajout des lignes de factures**************************/
		$res = $this->addItemsFact($declaration, $type, $period_reference);
		/********************************************************************/

		$this->errors = array_unique($this->errors);

		if (!empty($res)) {
			$dom = dom_import_simplexml($e)->ownerDocument;
			$dom->formatOutput = true;
			return $dom->saveXML();
		} else {
			return false;
		}
	}

	/**
	 * Generate XMLDes file
	 *
	 * @param int		$period_year		Year of declaration
	 * @param int<1,12>	$period_month		Month of declaration
	 * @param 'introduction'|'expedition'	$type_declaration	Declaration type by default - 'introduction' or 'expedition' (always 'expedition' for Des)
	 * @return string|false					Return a well-formed XML string based on SimpleXML element, false or 0 if error
	 */
	public function getXMLDes($period_year, $period_month, $type_declaration = 'expedition')
	{
		global $mysoc;

		$e = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><fichier_des></fichier_des>');

		$declaration_des = $e->addChild('declaration_des');
		$declaration_des->addChild('num_des', self::getDeclarationNumber($this->numero_declaration));
		$declaration_des->addChild('num_tvaFr', $mysoc->tva_intra ?? ''); // /^FR[a-Z0-9]{2}[0-9]{9}$/  // Doit faire 13 caractères
		$declaration_des->addChild('mois_des', (string) $period_month);
		$declaration_des->addChild('an_des', (string) $period_year);

		// Add invoice lines
		$res = $this->addItemsFact($declaration_des, $type_declaration, $period_year.'-'.$period_month, 'des');

		$this->errors = array_unique($this->errors);

		if (!empty($res)) {
			return $e->asXML();
		} else {
			return false;
		}
	}

	/**
	 *  Add line from invoice
	 *
	 *  @param	SimpleXMLElement	$declaration		Reference declaration
	 *  @param	string				$type				Declaration type by default - 'introduction' or 'expedition' (always 'expedition' for Des)
	 *  @param	string				$period_reference	Reference period ("YYYY-MM")
	 *  @param	'deb'|'des'			$exporttype	    	'deb' for DEB, 'des' for DES
	 *  @return	int       			  					Return integer <0 if KO, >0 if OK
	 */
	public function addItemsFact(&$declaration, $type, $period_reference, $exporttype = 'deb')
	{
		require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

		$sql = $this->getSQLFactLines($type, $period_reference, $exporttype);

		$resql = $this->db->query($sql);

		if ($resql) {
			$i = 1;

			if ($this->db->num_rows($resql) <= 0) {
				$this->errors[] = 'No data for this period';
				return 0;
			}

			$categ_fraisdeport = null;
			if ($exporttype == 'deb' && getDolGlobalInt('INTRACOMMREPORT_CATEG_FRAISDEPORT') > 0) {
				$categ_fraisdeport = new Categorie($this->db);
				$categ_fraisdeport->fetch(getDolGlobalInt('INTRACOMMREPORT_CATEG_FRAISDEPORT'));
				$TLinesFraisDePort = array();
			}

			while ($res = $this->db->fetch_object($resql)) {
				if ($exporttype == 'des') {
					$this->addItemXMlDes($declaration, $res, $i);
				} else {
					if (empty($res->fk_pays)) {
						// We don't stop the loop because we want to know all the third parties who don't have an informed country
						$this->errors[] = 'Country not filled in for the third party <a href="'.dol_buildpath('/societe/soc.php', 1).'?socid='.$res->id_client.'">'.$res->nom.'</a>';
					} else {
						if (getDolGlobalInt('INTRACOMMREPORT_CATEG_FRAISDEPORT') > 0 && $categ_fraisdeport->containsObject('product', $res->id_prod)) {
							$TLinesFraisDePort[] = $res;
						} else {
							$this->addItemXMl($declaration, $res, $i, '');
						}
					}
				}

				$i++;
			}

			if (!empty($TLinesFraisDePort) && $categ_fraisdeport !== null) {
				$this->addItemFraisDePort($declaration, $TLinesFraisDePort, $type, $categ_fraisdeport, $i);
			}

			if (count($this->errors) > 0) {
				return 0;
			}
		}

		return 1;
	}

	/**
	 *  Add invoice line
	 *
	 *  @param      string	$type				Declaration type by default - introduction or expedition (always 'expedition' for Des)
	 *  @param      string	$period_reference	Reference declaration
	 *  @param      string	$exporttype	    	deb=DEB, des=DES
	 *  @return     string       			  	Return integer <0 if KO, >0 if OK
	 */
	public function getSQLFactLines($type, $period_reference, $exporttype = 'deb')
	{
		global $mysoc, $conf;

		if ($type == 'expedition' || $exporttype == 'des') {
			$sql = "SELECT f.ref as refinvoice, f.rowid as fk_facture, l.total_ht";
			$table = 'facture';
			$table_extraf = 'facture_extrafields';
			$tabledet = 'facturedet';
			$field_link = 'fk_facture';
		} else { // Introduction
			$sql = "SELECT f.ref_supplier as refinvoice, f.rowid as fk_facture, l.total_ht";
			$table = 'facture_fourn';
			$table_extraf = 'facture_fourn_extrafields';
			$tabledet = 'facture_fourn_det';
			$field_link = 'fk_facture_fourn';
		}
		list($year, $month) = explode('-', $period_reference);
		$period_end_of_month_day = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $year);

		$sql .= ", l.fk_product, l.qty
				, p.weight, p.rowid as id_prod, p.customcode
				, s.rowid as id_client, s.nom, s.zip, s.fk_pays, s.tva_intra
				, c.code, cp.code as product_code
				, ext.mode_transport
				FROM ".MAIN_DB_PREFIX.$tabledet." l
				INNER JOIN ".MAIN_DB_PREFIX.$table." f ON (f.rowid = l.".$this->db->escape($field_link).")
				LEFT JOIN ".MAIN_DB_PREFIX.$table_extraf." ext ON (ext.fk_object = f.rowid)
				INNER JOIN ".MAIN_DB_PREFIX."product p ON (p.rowid = l.fk_product)
				INNER JOIN ".MAIN_DB_PREFIX."societe s ON (s.rowid = f.fk_soc)
				LEFT JOIN ".MAIN_DB_PREFIX."c_country c ON (c.rowid = s.fk_pays)
				LEFT JOIN ".MAIN_DB_PREFIX."c_country cp ON (cp.rowid = p.fk_country)
				WHERE f.fk_statut > 0
				AND l.product_type = ".($exporttype == "des" ? 1 : 0)."
				AND f.entity = ".((int) $conf->entity)."
				AND (s.fk_pays <> ".((int) $mysoc->country_id)." OR s.fk_pays IS NULL)
				AND c.eec = 1
				AND l.total_ht <> 0
				AND f.datef BETWEEN '".$this->db->escape((string) $period_reference)."-01' AND '".$this->db->escape((string) $period_reference)."-".((int) $period_end_of_month_day)."'";

		return $sql;
	}

	/**
	 *	Add item for DEB
	 *
	 * 	@param	SimpleXMLElement	$declaration		Reference declaration
	 * 	@param	stdClass			$res				Result of request SQL
	 *  @param	int					$i					Line Id
	 * 	@param	string				$code_douane_spe	Specific customs authorities code
	 *  @return	void
	 */
	public function addItemXMl(&$declaration, &$res, $i, $code_douane_spe = '')
	{
		$item = $declaration->addChild('Item');
		$item->addChild('itemNumber', (string) $i);
		$cn8 = $item->addChild('CN8');
		if (empty($code_douane_spe)) {
			$code_douane = $res->customcode;
		} else {
			$code_douane = $code_douane_spe;
		}
		$cn8->addChild('CN8Code', $code_douane);
		$item->addChild('MSConsDestCode', $res->code); // code iso pays client
		$item->addChild('countryOfOriginCode', $res->product_code); // code iso pays d'origine produit
		$item->addChild('netMass', (string) round($res->weight * $res->qty)); // Poids du produit
		$item->addChild('quantityInSU', (string) $res->qty); // Quantité de produit dans la ligne
		$item->addChild('invoicedAmount', (string) abs(round($res->total_ht))); // Montant total ht de la facture (entier attendu)
		// $item->addChild('invoicedNumber', $res->refinvoice); // Numéro facture
		if (!empty($res->tva_intra)) {
			$item->addChild('partnerId', $res->tva_intra);
		}
		$item->addChild('statisticalProcedureCode', ($res->total_ht >= 0 ? '21' : '25')); // TODO make this configurable
		$nature_of_transaction = $item->addChild('NatureOfTransaction');
		$nature_of_transaction->addChild('natureOfTransactionACode', '1');
		$nature_of_transaction->addChild('natureOfTransactionBCode', '1');
		$item->addChild('modeOfTransportCode', $res->mode_transport);
		$item->addChild('regionCode', substr($res->zip, 0, 2));
	}

	/**
	 *	Add item for DES
	 *
	 * 	@param	SimpleXMLElement	$declaration		Reference declaration
	 * 	@param	stdClass			$res				Result of request SQL
	 *  @param	int					$i					Line Id
	 *  @return	void
	 */
	public function addItemXMlDes($declaration, &$res, $i)
	{
		$item = $declaration->addChild('ligne_des');
		$item->addChild('numlin_des', (string) $i);
		$item->addChild('valeur', (string) round($res->total_ht)); // Total amount excl. tax of the invoice (whole amount expected)
		$item->addChild('partner_des', $res->tva_intra); // Represents the foreign customer's VAT number
	}

	/**
	 *	This function adds an item by retrieving the customs code of the product with the highest amount in the invoice
	 *
	 * 	@param	SimpleXMLElement	$declaration		Reference declaration
	 * 	@param	Object[]			$TLinesFraisDePort	Data of shipping costs line
	 *  @param	string	    		$type				Declaration type by default - introduction or expedition (always 'expedition' for Des)
	 *  @param	Categorie			$categ_fraisdeport	category of shipping costs
	 *  @param	int		    		$i					Line Id
	 *  @return	void
	 */
	public function addItemFraisDePort(&$declaration, &$TLinesFraisDePort, $type, &$categ_fraisdeport, $i)
	{
		global $conf;

		if ($type == 'expedition') {
			$table = 'facture';
			$tabledet = 'facturedet';
			$field_link = 'fk_facture';
			$more_sql = 'f.ref';
		} else { // Introduction
			$table = 'facture_fourn';
			$tabledet = 'facture_fourn_det';
			$field_link = 'fk_facture_fourn';
			$more_sql = 'f.ref_supplier';
		}

		foreach ($TLinesFraisDePort as $res) {
			$sql = "SELECT p.customcode
					FROM ".MAIN_DB_PREFIX.$tabledet." d
					INNER JOIN ".MAIN_DB_PREFIX.$table." f ON (f.rowid = d.".$this->db->escape($field_link).")
					INNER JOIN ".MAIN_DB_PREFIX."product p ON (p.rowid = d.fk_product)
					WHERE d.fk_product IS NOT NULL
					AND f.entity = ".((int) $conf->entity)."
					AND ".$more_sql." = '".$this->db->escape($res->refinvoice)."'
					AND d.total_ht =
					(
						SELECT MAX(d.total_ht)
						FROM ".MAIN_DB_PREFIX.$tabledet." d
						INNER JOIN ".MAIN_DB_PREFIX.$table." f ON (f.rowid = d.".$this->db->escape($field_link).")
						WHERE d.fk_product IS NOT NULL
						AND ".$more_sql." = '".$this->db->escape($res->refinvoice)."'
						AND d.fk_product NOT IN
						(
							SELECT fk_product
							FROM ".MAIN_DB_PREFIX."categorie_product
							WHERE fk_categorie = ".((int) $categ_fraisdeport->id)."
						)
					)";

			$resql = $this->db->query($sql);
			$ress = $this->db->fetch_object($resql);

			$this->addItemXMl($declaration, $res, $i, $ress->customcode);

			$i++;
		}
	}

	/**
	 *	Return next reference of declaration not already used (or last reference)
	 *
	 *	@return    string					free ref or last ref
	 */
	public function getNextDeclarationNumber()
	{
		$sql = "SELECT MAX(numero_declaration) as max_declaration_number";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
		$sql .= " WHERE exporttype = '".$this->db->escape($this->type_export)."'";
		$resql = $this->db->query($sql);
		$res = null;
		if ($resql) {
			$res = $this->db->fetch_object($resql);
		}

		return (string) ($res !== null ? ($res->max_declaration_number + 1) : '');
	}

	/**
	 *	Verify declaration number. Positive integer of a maximum of 6 characters recommended by the documentation
	 *
	 *	@param     	string		$number		Number to verify / convert
	 *	@return		string 				Number
	 */
	public static function getDeclarationNumber($number)
	{
		return str_pad($number, 6, '0', STR_PAD_LEFT);
	}

	/**
	 *	Generate XML file
	 *
	 *  @param		string		$content_xml	Content
	 *	@return		void
	 */
	public function generateXMLFile($content_xml)
	{
		$name = $this->period.'.xml';

		// TODO Must be stored into a dolibarr temp directory
		$fname = sys_get_temp_dir().'/'.$name;

		$f = fopen($fname, 'w+');
		fwrite($f, $content_xml);
		fclose($f);

		header('Content-Description: File Transfer');
		header('Content-Type: application/xml');
		header('Content-Disposition: attachment; filename="'.$name.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: '.filesize($fname));

		readfile($fname);
		exit;
	}


	/**
	 *  Return a link to the object card (with optionally the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';
		$params = [
			'id' => $this->id,
			'objecttype' => $this->element.($this->module ? '@'.$this->module : ''),
			'option' => $option,
		];
		$classfortooltip = 'classfortooltip';
		$dataparams = '';
		if (getDolGlobalInt('MAIN_ENABLE_AJAX_TOOLTIP')) {
			$classfortooltip = 'classforajaxtooltip';
			$dataparams = ' data-params="'.dol_escape_htmltag(json_encode($params)).'"';
			$label = '';
		} else {
			$label = implode($this->getTooltipContentArray($params));
		}

		$url = dol_buildpath('/intracommreport/card.php', 1).'?id='.$this->id;

		if ($option !== 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && isset($_SERVER["PHP_SELF"]) && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($url && $add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (getDolGlobalInt('MAIN_OPTIMIZEFORTEXTBROWSER')) {
				$label = $langs->trans("ShowMyObject");
				$linkclose .= ' alt="'.dolPrintHTMLForAttribute($label).'"';
			}
			$linkclose .= ($label ? ' title="'.dolPrintHTMLForAttribute($label).'"' : ' title="tocomplete"');
			$linkclose .= $dataparams.' class="'.$classfortooltip.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), (($withpicto != 2) ? 'class="paddingright"' : ''), 0, 0, $notooltip ? 0 : 1);
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (!getDolGlobalString(strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS')) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array($this->element.'dao'));
		$parameters = array('id' => $this->id, 'getnomurl' => &$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *	Return a thumb for kanban views
	 *
	 *	@param      string	    			$option                 Where point the link (0=> main card, 1,2 => shipment, 'nolink'=>No link)
	 *  @param		?array<string,mixed>	$arraydata				Array of data
	 *  @return		string											HTML Code for Kanban thumb.
	 */
	public function getKanbanView($option = '', $arraydata = null)
	{
		$selected = (empty($arraydata['selected']) ? 0 : $arraydata['selected']);

		$return = '<div class="box-flex-item box-flex-grow-zero">';
		$return .= '<div class="info-box info-box-sm">';
		$return .= '<span class="info-box-icon bg-infobox-action">';
		$return .= img_picto('', $this->picto);
		$return .= '</span>';
		$return .= '<div class="info-box-content">';
		$return .= '<span class="info-box-ref inline-block tdoverflowmax150 valignmiddle">'.(method_exists($this, 'getNomUrl') ? $this->getNomUrl() : $this->ref).'</span>';
		if ($selected >= 0) {
			$return .= '<input id="cb'.$this->id.'" class="flat checkforselect fright" type="checkbox" name="toselect[]" value="'.$this->id.'"'.($selected ? ' checked="checked"' : '').'>';
		}
		if (property_exists($this, 'label')) {
			$return .= ' <div class="inline-block opacitymedium valignmiddle tdoverflowmax100">'.$this->label.'</div>';
		}
		if (property_exists($this, 'thirdparty') && is_object($this->thirdparty)) {
			$return .= '<br><div class="info-box-ref tdoverflowmax150">'.$this->thirdparty->getNomUrl(1).'</div>';
		}
		if (method_exists($this, 'getLibStatut')) {
			$return .= '<br><div class="info-box-status">'.$this->getLibStatut(3).'</div>';
		}
		$return .= '</div>';
		$return .= '</div>';
		$return .= '</div>';

		return $return;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLabelStatus($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the label of a given status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (is_null($status)) {
			return '';
		}

		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("intracommreport");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) {
			$statusType = 'status6';
		}

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}
}

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class IntracommreportLine. You can also remove this and generate a CRUD class for lines objects.
 */
class IntracommreportDebLine extends CommonObjectLine
{
	// TODO move to separate class and also make one for DES
	// We should have a field rowid, fk_intracommreport and position
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'id' => array('type'=>'integer', 'label'=>'ItemNumber', 'enabled'=>1, 'visible'=>1, 'noteditable'=>'1'),
		'fk_facture' => array('type'=>'integer:Facture:compta/facture/class/facture.class.php', 'label'=>'Facture', 'enabled'=>1, 'visible'=>1, 'noteditable'=>'1', "css"=>"minwidth150"),
		'fk_product' => array('type'=>'integer:Product:product/class/product.class.php', 'label'=>'Product', 'enabled'=>'1', 'visible'=>1, 'noteditable'=>'1'),
		'customcode' => array('type'=>'varchar(32)', 'label'=>'CN8Code', 'enabled'=>'1', 'visible'=>1, 'noteditable'=>'1'),
		'code' => array('type'=>'varchar(32)', 'label'=>'MSConsDestCode', 'enabled'=>'1', 'visible'=>1, 'noteditable'=>'1'),
		'product_code' => array('type'=>'varchar(32)', 'label'=>'countryOfOriginCode', 'enabled'=>'1', 'visible'=>1, 'noteditable'=>'1'),
		'weight' => array('type'=>'real', 'label'=>'netMass', 'enabled'=>'1', 'visible'=>1, 'isameasure'=>'1', 'noteditable'=>'1'),
		'qty' => array('type'=>'real', 'label'=>'Quantity', 'enabled'=>'1', 'visible'=>1, 'isameasure'=>'1', 'noteditable'=>'1'),
		'amount' => array('type'=>'price', "label"=>"Amount", "enabled"=>'1', 'visible'=>'1', 'isameasure'=>'1', 'noteditable'=>'1', "css"=>"minwidth100"),
		'procedure_code' => array('type'=>'varchar(32)', 'label'=>'procedureCode', 'enabled'=>'1', 'visible'=>1, 'noteditable'=>'1'),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php:1:(status:=:1)', 'label'=>'ThirdParty', 'enabled'=>'1', 'visible'=>1, 'noteditable'=>'1'),
		'tva_intra' => array('type'=>'varchar(64)', 'label'=>'partnerId', 'enabled'=>'1', 'visible'=>1, 'noteditable'=>'1'),
		//'mode_transport' =>array('type'=>'sellist:c_intracommreport_mode_transport:label:code::1:', 'label'=>'ModeTransport', 'enabled'=>1, 'visible'=>1, 'noteditable'=>'1'),
		'region_code' => array('type'=>'varchar(32)', 'label'=>'regionCode', 'enabled'=>'1', 'visible'=>1, 'noteditable'=>'1'),
	);

	public $id;
	public $fk_facture;
	public $fk_product;
	public $customcode;
	public $code;
	public $product_code;
	public $weight;
	public $qty;
	public $amount;
	public $procedure_code;
	public $fk_soc;
	public $tva_intra;
	public $mode_transport;
	public $region_code;

	/**
	 * To overload
	 * @see CommonObjectLine
	 */
	public $parent_element = 'intracommreport';

	/**
	 * To overload
	 * @see CommonObjectLine
	 */
	public $fk_parent_attribute = '';

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $langs;
		$this->db = $db;

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (isset($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2]=$langs->trans($val2);
					}
				}
			}
		}
	}
}
