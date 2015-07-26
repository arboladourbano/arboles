<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg10.php" ?>
<?php include_once "ewmysql10.php" ?>
<?php include_once "phpfn10.php" ?>
<?php include_once "individuosinfo.php" ?>
<?php include_once "usuariosinfo.php" ?>
<?php include_once "userfn10.php" ?>
<?php

//
// Page class
//

$individuos_edit = NULL; // Initialize page object first

class cindividuos_edit extends cindividuos {

	// Page ID
	var $PageID = 'edit';

	// Project ID
	var $ProjectID = "{E8FDA5CE-82C7-4B68-84A5-21FD1A691C43}";

	// Table name
	var $TableName = 'individuos';

	// Page object name
	var $PageObjName = 'individuos_edit';

	// Page name
	function PageName() {
		return ew_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ew_CurrentPage() . "?";
		if ($this->UseTokenInUrl) $PageUrl .= "t=" . $this->TableVar . "&"; // Add page token
		return $PageUrl;
	}

	// Message
	function getMessage() {
		return @$_SESSION[EW_SESSION_MESSAGE];
	}

	function setMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_MESSAGE], $v);
	}

	function getFailureMessage() {
		return @$_SESSION[EW_SESSION_FAILURE_MESSAGE];
	}

	function setFailureMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_FAILURE_MESSAGE], $v);
	}

	function getSuccessMessage() {
		return @$_SESSION[EW_SESSION_SUCCESS_MESSAGE];
	}

	function setSuccessMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_SUCCESS_MESSAGE], $v);
	}

	function getWarningMessage() {
		return @$_SESSION[EW_SESSION_WARNING_MESSAGE];
	}

	function setWarningMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_WARNING_MESSAGE], $v);
	}

	// Show message
	function ShowMessage() {
		$hidden = FALSE;
		$html = "";

		// Message
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage, "");
		if ($sMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sMessage . "</div>";
			$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
		}

		// Warning message
		$sWarningMessage = $this->getWarningMessage();
		$this->Message_Showing($sWarningMessage, "warning");
		if ($sWarningMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sWarningMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sWarningMessage;
			$html .= "<div class=\"alert alert-warning ewWarning\">" . $sWarningMessage . "</div>";
			$_SESSION[EW_SESSION_WARNING_MESSAGE] = ""; // Clear message in Session
		}

		// Success message
		$sSuccessMessage = $this->getSuccessMessage();
		$this->Message_Showing($sSuccessMessage, "success");
		if ($sSuccessMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sSuccessMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sSuccessMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sSuccessMessage . "</div>";
			$_SESSION[EW_SESSION_SUCCESS_MESSAGE] = ""; // Clear message in Session
		}

		// Failure message
		$sErrorMessage = $this->getFailureMessage();
		$this->Message_Showing($sErrorMessage, "failure");
		if ($sErrorMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sErrorMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sErrorMessage;
			$html .= "<div class=\"alert alert-error ewError\">" . $sErrorMessage . "</div>";
			$_SESSION[EW_SESSION_FAILURE_MESSAGE] = ""; // Clear message in Session
		}
		echo "<table class=\"ewStdTable\"><tr><td><div class=\"ewMessageDialog\"" . (($hidden) ? " style=\"display: none;\"" : "") . ">" . $html . "</div></td></tr></table>";
	}
	var $PageHeader;
	var $PageFooter;

	// Show Page Header
	function ShowPageHeader() {
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		if ($sHeader <> "") { // Header exists, display
			echo "<p>" . $sHeader . "</p>";
		}
	}

	// Show Page Footer
	function ShowPageFooter() {
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		if ($sFooter <> "") { // Footer exists, display
			echo "<p>" . $sFooter . "</p>";
		}
	}

	// Validate page request
	function IsPageRequest() {
		global $objForm;
		if ($this->UseTokenInUrl) {
			if ($objForm)
				return ($this->TableVar == $objForm->GetValue("t"));
			if (@$_GET["t"] <> "")
				return ($this->TableVar == $_GET["t"]);
		} else {
			return TRUE;
		}
	}

	//
	// Page class constructor
	//
	function __construct() {
		global $conn, $Language;
		$GLOBALS["Page"] = &$this;

		// Language object
		if (!isset($Language)) $Language = new cLanguage();

		// Parent constuctor
		parent::__construct();

		// Table object (individuos)
		if (!isset($GLOBALS["individuos"])) {
			$GLOBALS["individuos"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["individuos"];
		}

		// Table object (usuarios)
		if (!isset($GLOBALS['usuarios'])) $GLOBALS['usuarios'] = new cusuarios();

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'edit', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'individuos', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();
	}

	// 
	//  Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsExportFile, $UserProfile, $Language, $Security, $objForm;

		// User profile
		$UserProfile = new cUserProfile();
		$UserProfile->LoadProfile(@$_SESSION[EW_SESSION_USER_PROFILE]);

		// Security
		$Security = new cAdvancedSecurity();
		if (!$Security->IsLoggedIn()) $Security->AutoLogin();
		if (!$Security->IsLoggedIn()) {
			$Security->SaveLastUrl();
			$this->Page_Terminate("login.php");
		}
		$Security->TablePermission_Loading();
		$Security->LoadCurrentUserLevel($this->ProjectID . $this->TableName);
		$Security->TablePermission_Loaded();
		if (!$Security->IsLoggedIn()) {
			$Security->SaveLastUrl();
			$this->Page_Terminate("login.php");
		}
		if (!$Security->CanEdit()) {
			$Security->SaveLastUrl();
			$this->setFailureMessage($Language->Phrase("NoPermission")); // Set no permission
			$this->Page_Terminate("individuoslist.php");
		}

		// Update last accessed time
		if ($UserProfile->IsValidUser(session_id())) {
			if (!$Security->IsSysAdmin())
				$UserProfile->SaveProfileToDatabase(CurrentUserName()); // Update last accessed time to user profile
		} else {
			echo $Language->Phrase("UserProfileCorrupted");
		}

		// Create form object
		$objForm = new cFormObj();
		$this->CurrentAction = (@$_GET["a"] <> "") ? $_GET["a"] : @$_POST["a_list"]; // Set up curent action
		$this->id_individuo->Visible = !$this->IsAdd() && !$this->IsCopy() && !$this->IsGridAdd();

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $conn;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();
		$this->Page_Redirecting($url);

		 // Close connection
		$conn->Close();

		// Go to URL if specified
		if ($url <> "") {
			if (!EW_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			header("Location: " . $url);
		}
		exit();
	}
	var $DbMasterFilter;
	var $DbDetailFilter;
	var $DisplayRecs = 1;
	var $StartRec;
	var $StopRec;
	var $TotalRecs = 0;
	var $RecRange = 10;
	var $Pager;
	var $RecCnt;
	var $RecKey = array();
	var $Recordset;

	// 
	// Page main
	//
	function Page_Main() {
		global $objForm, $Language, $gsFormError;

		// Load current record
		$bLoadCurrentRecord = FALSE;
		$sReturnUrl = "";
		$bMatchRecord = FALSE;

		// Load key from QueryString
		if (@$_GET["id_individuo"] <> "") {
			$this->id_individuo->setQueryStringValue($_GET["id_individuo"]);
			$this->RecKey["id_individuo"] = $this->id_individuo->QueryStringValue;
		} else {
			$bLoadCurrentRecord = TRUE;
		}

		// Set up Breadcrumb
		$this->SetupBreadcrumb();

		// Load recordset
		$this->StartRec = 1; // Initialize start position
		if ($this->Recordset = $this->LoadRecordset()) // Load records
			$this->TotalRecs = $this->Recordset->RecordCount(); // Get record count
		if ($this->TotalRecs <= 0) { // No record found
			if ($this->getSuccessMessage() == "" && $this->getFailureMessage() == "")
				$this->setFailureMessage($Language->Phrase("NoRecord")); // Set no record message
			$this->Page_Terminate("individuoslist.php"); // Return to list page
		} elseif ($bLoadCurrentRecord) { // Load current record position
			$this->SetUpStartRec(); // Set up start record position

			// Point to current record
			if (intval($this->StartRec) <= intval($this->TotalRecs)) {
				$bMatchRecord = TRUE;
				$this->Recordset->Move($this->StartRec-1);
			}
		} else { // Match key values
			while (!$this->Recordset->EOF) {
				if (strval($this->id_individuo->CurrentValue) == strval($this->Recordset->fields('id_individuo'))) {
					$this->setStartRecordNumber($this->StartRec); // Save record position
					$bMatchRecord = TRUE;
					break;
				} else {
					$this->StartRec++;
					$this->Recordset->MoveNext();
				}
			}
		}

		// Process form if post back
		if (@$_POST["a_edit"] <> "") {
			$this->CurrentAction = $_POST["a_edit"]; // Get action code
			$this->LoadFormValues(); // Get form values
		} else {
			$this->CurrentAction = "I"; // Default action is display
		}

		// Validate form if post back
		if (@$_POST["a_edit"] <> "") {
			if (!$this->ValidateForm()) {
				$this->CurrentAction = ""; // Form error, reset action
				$this->setFailureMessage($gsFormError);
				$this->EventCancelled = TRUE; // Event cancelled
				$this->RestoreFormValues();
			}
		}
		switch ($this->CurrentAction) {
			case "I": // Get a record to display
				if (!$bMatchRecord) {
					if ($this->getSuccessMessage() == "" && $this->getFailureMessage() == "")
						$this->setFailureMessage($Language->Phrase("NoRecord")); // Set no record message
					$this->Page_Terminate("individuoslist.php"); // Return to list page
				} else {
					$this->LoadRowValues($this->Recordset); // Load row values
				}
				break;
			Case "U": // Update
				$this->SendEmail = TRUE; // Send email on update success
				if ($this->EditRow()) { // Update record based on key
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("UpdateSuccess")); // Update success
					$sReturnUrl = $this->getReturnUrl();
					if (ew_GetPageName($sReturnUrl) == "individuosview.php")
						$sReturnUrl = $this->GetViewUrl(); // View paging, return to View page directly
					$this->Page_Terminate($sReturnUrl); // Return to caller
				} else {
					$this->EventCancelled = TRUE; // Event cancelled
					$this->RestoreFormValues(); // Restore form values if update failed
				}
		}

		// Render the record
		$this->RowType = EW_ROWTYPE_EDIT; // Render as Edit
		$this->ResetAttrs();
		$this->RenderRow();
	}

	// Set up starting record parameters
	function SetUpStartRec() {
		if ($this->DisplayRecs == 0)
			return;
		if ($this->IsPageRequest()) { // Validate request
			if (@$_GET[EW_TABLE_START_REC] <> "") { // Check for "start" parameter
				$this->StartRec = $_GET[EW_TABLE_START_REC];
				$this->setStartRecordNumber($this->StartRec);
			} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
				$PageNo = $_GET[EW_TABLE_PAGE_NO];
				if (is_numeric($PageNo)) {
					$this->StartRec = ($PageNo-1)*$this->DisplayRecs+1;
					if ($this->StartRec <= 0) {
						$this->StartRec = 1;
					} elseif ($this->StartRec >= intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1) {
						$this->StartRec = intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1;
					}
					$this->setStartRecordNumber($this->StartRec);
				}
			}
		}
		$this->StartRec = $this->getStartRecordNumber();

		// Check if correct start record counter
		if (!is_numeric($this->StartRec) || $this->StartRec == "") { // Avoid invalid start record counter
			$this->StartRec = 1; // Reset start record counter
			$this->setStartRecordNumber($this->StartRec);
		} elseif (intval($this->StartRec) > intval($this->TotalRecs)) { // Avoid starting record > total records
			$this->StartRec = intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1; // Point to last page first record
			$this->setStartRecordNumber($this->StartRec);
		} elseif (($this->StartRec-1) % $this->DisplayRecs <> 0) {
			$this->StartRec = intval(($this->StartRec-1)/$this->DisplayRecs)*$this->DisplayRecs+1; // Point to page boundary
			$this->setStartRecordNumber($this->StartRec);
		}
	}

	// Get upload files
	function GetUploadFiles() {
		global $objForm;

		// Get upload data
	}

	// Load form values
	function LoadFormValues() {

		// Load from form
		global $objForm;
		if (!$this->id_individuo->FldIsDetailKey)
			$this->id_individuo->setFormValue($objForm->GetValue("x_id_individuo"));
		if (!$this->id_especie->FldIsDetailKey) {
			$this->id_especie->setFormValue($objForm->GetValue("x_id_especie"));
		}
		if (!$this->calle->FldIsDetailKey) {
			$this->calle->setFormValue($objForm->GetValue("x_calle"));
		}
		if (!$this->alt_ini->FldIsDetailKey) {
			$this->alt_ini->setFormValue($objForm->GetValue("x_alt_ini"));
		}
		if (!$this->ALTURA_TOT->FldIsDetailKey) {
			$this->ALTURA_TOT->setFormValue($objForm->GetValue("x_ALTURA_TOT"));
		}
		if (!$this->DIAMETRO->FldIsDetailKey) {
			$this->DIAMETRO->setFormValue($objForm->GetValue("x_DIAMETRO"));
		}
		if (!$this->INCLINACIO->FldIsDetailKey) {
			$this->INCLINACIO->setFormValue($objForm->GetValue("x_INCLINACIO"));
		}
		if (!$this->lat->FldIsDetailKey) {
			$this->lat->setFormValue($objForm->GetValue("x_lat"));
		}
		if (!$this->lng->FldIsDetailKey) {
			$this->lng->setFormValue($objForm->GetValue("x_lng"));
		}
		if (!$this->espacio_verde->FldIsDetailKey) {
			$this->espacio_verde->setFormValue($objForm->GetValue("x_espacio_verde"));
		}
		if (!$this->id_usuario->FldIsDetailKey) {
			$this->id_usuario->setFormValue($objForm->GetValue("x_id_usuario"));
		}
		if (!$this->fecha_creacion->FldIsDetailKey) {
			$this->fecha_creacion->setFormValue($objForm->GetValue("x_fecha_creacion"));
			$this->fecha_creacion->CurrentValue = ew_UnFormatDateTime($this->fecha_creacion->CurrentValue, 7);
		}
		if (!$this->fecha_modificacion->FldIsDetailKey) {
			$this->fecha_modificacion->setFormValue($objForm->GetValue("x_fecha_modificacion"));
			$this->fecha_modificacion->CurrentValue = ew_UnFormatDateTime($this->fecha_modificacion->CurrentValue, 7);
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadRow();
		$this->id_individuo->CurrentValue = $this->id_individuo->FormValue;
		$this->id_especie->CurrentValue = $this->id_especie->FormValue;
		$this->calle->CurrentValue = $this->calle->FormValue;
		$this->alt_ini->CurrentValue = $this->alt_ini->FormValue;
		$this->ALTURA_TOT->CurrentValue = $this->ALTURA_TOT->FormValue;
		$this->DIAMETRO->CurrentValue = $this->DIAMETRO->FormValue;
		$this->INCLINACIO->CurrentValue = $this->INCLINACIO->FormValue;
		$this->lat->CurrentValue = $this->lat->FormValue;
		$this->lng->CurrentValue = $this->lng->FormValue;
		$this->espacio_verde->CurrentValue = $this->espacio_verde->FormValue;
		$this->id_usuario->CurrentValue = $this->id_usuario->FormValue;
		$this->fecha_creacion->CurrentValue = $this->fecha_creacion->FormValue;
		$this->fecha_creacion->CurrentValue = ew_UnFormatDateTime($this->fecha_creacion->CurrentValue, 7);
		$this->fecha_modificacion->CurrentValue = $this->fecha_modificacion->FormValue;
		$this->fecha_modificacion->CurrentValue = ew_UnFormatDateTime($this->fecha_modificacion->CurrentValue, 7);
	}

	// Load recordset
	function LoadRecordset($offset = -1, $rowcnt = -1) {
		global $conn;

		// Call Recordset Selecting event
		$this->Recordset_Selecting($this->CurrentFilter);

		// Load List page SQL
		$sSql = $this->SelectSQL();
		if ($offset > -1 && $rowcnt > -1)
			$sSql .= " LIMIT $rowcnt OFFSET $offset";

		// Load recordset
		$rs = ew_LoadRecordset($sSql);

		// Call Recordset Selected event
		$this->Recordset_Selected($rs);
		return $rs;
	}

	// Load row based on key values
	function LoadRow() {
		global $conn, $Security, $Language;
		$sFilter = $this->KeyFilter();

		// Call Row Selecting event
		$this->Row_Selecting($sFilter);

		// Load SQL based on filter
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$res = FALSE;
		$rs = ew_LoadRecordset($sSql);
		if ($rs && !$rs->EOF) {
			$res = TRUE;
			$this->LoadRowValues($rs); // Load row values
			$rs->Close();
		}
		return $res;
	}

	// Load row values from recordset
	function LoadRowValues(&$rs) {
		global $conn;
		if (!$rs || $rs->EOF) return;

		// Call Row Selected event
		$row = &$rs->fields;
		$this->Row_Selected($row);
		$this->id_individuo->setDbValue($rs->fields('id_individuo'));
		$this->id_especie->setDbValue($rs->fields('id_especie'));
		if (array_key_exists('EV__id_especie', $rs->fields)) {
			$this->id_especie->VirtualValue = $rs->fields('EV__id_especie'); // Set up virtual field value
		} else {
			$this->id_especie->VirtualValue = ""; // Clear value
		}
		$this->calle->setDbValue($rs->fields('calle'));
		$this->alt_ini->setDbValue($rs->fields('alt_ini'));
		$this->ALTURA_TOT->setDbValue($rs->fields('ALTURA_TOT'));
		$this->DIAMETRO->setDbValue($rs->fields('DIAMETRO'));
		$this->INCLINACIO->setDbValue($rs->fields('INCLINACIO'));
		$this->lat->setDbValue($rs->fields('lat'));
		$this->lng->setDbValue($rs->fields('lng'));
		$this->espacio_verde->setDbValue($rs->fields('espacio_verde'));
		$this->id_usuario->setDbValue($rs->fields('id_usuario'));
		if (array_key_exists('EV__id_usuario', $rs->fields)) {
			$this->id_usuario->VirtualValue = $rs->fields('EV__id_usuario'); // Set up virtual field value
		} else {
			$this->id_usuario->VirtualValue = ""; // Clear value
		}
		$this->fecha_creacion->setDbValue($rs->fields('fecha_creacion'));
		$this->fecha_modificacion->setDbValue($rs->fields('fecha_modificacion'));
	}

	// Load DbValue from recordset
	function LoadDbValues(&$rs) {
		if (!$rs || !is_array($rs) && $rs->EOF) return;
		$row = is_array($rs) ? $rs : $rs->fields;
		$this->id_individuo->DbValue = $row['id_individuo'];
		$this->id_especie->DbValue = $row['id_especie'];
		$this->calle->DbValue = $row['calle'];
		$this->alt_ini->DbValue = $row['alt_ini'];
		$this->ALTURA_TOT->DbValue = $row['ALTURA_TOT'];
		$this->DIAMETRO->DbValue = $row['DIAMETRO'];
		$this->INCLINACIO->DbValue = $row['INCLINACIO'];
		$this->lat->DbValue = $row['lat'];
		$this->lng->DbValue = $row['lng'];
		$this->espacio_verde->DbValue = $row['espacio_verde'];
		$this->id_usuario->DbValue = $row['id_usuario'];
		$this->fecha_creacion->DbValue = $row['fecha_creacion'];
		$this->fecha_modificacion->DbValue = $row['fecha_modificacion'];
	}

	// Render row values based on field settings
	function RenderRow() {
		global $conn, $Security, $Language;
		global $gsLanguage;

		// Initialize URLs
		// Convert decimal values if posted back

		if ($this->lat->FormValue == $this->lat->CurrentValue && is_numeric(ew_StrToFloat($this->lat->CurrentValue)))
			$this->lat->CurrentValue = ew_StrToFloat($this->lat->CurrentValue);

		// Convert decimal values if posted back
		if ($this->lng->FormValue == $this->lng->CurrentValue && is_numeric(ew_StrToFloat($this->lng->CurrentValue)))
			$this->lng->CurrentValue = ew_StrToFloat($this->lng->CurrentValue);

		// Call Row_Rendering event
		$this->Row_Rendering();

		// Common render codes for all row types
		// id_individuo
		// id_especie
		// calle
		// alt_ini
		// ALTURA_TOT
		// DIAMETRO
		// INCLINACIO
		// lat
		// lng
		// espacio_verde
		// id_usuario
		// fecha_creacion
		// fecha_modificacion

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// id_individuo
			$this->id_individuo->ViewValue = $this->id_individuo->CurrentValue;
			$this->id_individuo->ViewCustomAttributes = "";

			// id_especie
			if ($this->id_especie->VirtualValue <> "") {
				$this->id_especie->ViewValue = $this->id_especie->VirtualValue;
			} else {
				$this->id_especie->ViewValue = $this->id_especie->CurrentValue;
			if (strval($this->id_especie->CurrentValue) <> "") {
				$sFilterWrk = "`id_especie`" . ew_SearchString("=", $this->id_especie->CurrentValue, EW_DATATYPE_NUMBER);
			$sSqlWrk = "SELECT `id_especie`, `NOMBRE_CIE` AS `DispFld`, `NOMBRE_COM` AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld` FROM `especies`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}

			// Call Lookup selecting
			$this->Lookup_Selecting($this->id_especie, $sWhereWrk);
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
			$sSqlWrk .= " ORDER BY `NOMBRE_CIE`";
				$rswrk = $conn->Execute($sSqlWrk);
				if ($rswrk && !$rswrk->EOF) { // Lookup values found
					$this->id_especie->ViewValue = $rswrk->fields('DispFld');
					$this->id_especie->ViewValue .= ew_ValueSeparator(1,$this->id_especie) . $rswrk->fields('Disp2Fld');
					$rswrk->Close();
				} else {
					$this->id_especie->ViewValue = $this->id_especie->CurrentValue;
				}
			} else {
				$this->id_especie->ViewValue = NULL;
			}
			}
			$this->id_especie->ViewCustomAttributes = "";

			// calle
			$this->calle->ViewValue = $this->calle->CurrentValue;
			$this->calle->ViewCustomAttributes = "";

			// alt_ini
			$this->alt_ini->ViewValue = $this->alt_ini->CurrentValue;
			$this->alt_ini->ViewCustomAttributes = "";

			// ALTURA_TOT
			$this->ALTURA_TOT->ViewValue = $this->ALTURA_TOT->CurrentValue;
			$this->ALTURA_TOT->ViewCustomAttributes = "";

			// DIAMETRO
			$this->DIAMETRO->ViewValue = $this->DIAMETRO->CurrentValue;
			$this->DIAMETRO->ViewCustomAttributes = "";

			// INCLINACIO
			$this->INCLINACIO->ViewValue = $this->INCLINACIO->CurrentValue;
			$this->INCLINACIO->ViewCustomAttributes = "";

			// lat
			$this->lat->ViewValue = $this->lat->CurrentValue;
			$this->lat->ViewCustomAttributes = "";

			// lng
			$this->lng->ViewValue = $this->lng->CurrentValue;
			$this->lng->ViewCustomAttributes = "";

			// espacio_verde
			$this->espacio_verde->ViewValue = $this->espacio_verde->CurrentValue;
			$this->espacio_verde->ViewCustomAttributes = "";

			// id_usuario
			if ($this->id_usuario->VirtualValue <> "") {
				$this->id_usuario->ViewValue = $this->id_usuario->VirtualValue;
			} else {
			if (strval($this->id_usuario->CurrentValue) <> "") {
				$sFilterWrk = "`id`" . ew_SearchString("=", $this->id_usuario->CurrentValue, EW_DATATYPE_NUMBER);
			$sSqlWrk = "SELECT `id`, `nombre_completo` AS `DispFld`, '' AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld` FROM `usuarios`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}

			// Call Lookup selecting
			$this->Lookup_Selecting($this->id_usuario, $sWhereWrk);
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
			$sSqlWrk .= " ORDER BY `nombre_completo`";
				$rswrk = $conn->Execute($sSqlWrk);
				if ($rswrk && !$rswrk->EOF) { // Lookup values found
					$this->id_usuario->ViewValue = $rswrk->fields('DispFld');
					$rswrk->Close();
				} else {
					$this->id_usuario->ViewValue = $this->id_usuario->CurrentValue;
				}
			} else {
				$this->id_usuario->ViewValue = NULL;
			}
			}
			$this->id_usuario->ViewCustomAttributes = "";

			// fecha_creacion
			$this->fecha_creacion->ViewValue = $this->fecha_creacion->CurrentValue;
			$this->fecha_creacion->ViewValue = ew_FormatDateTime($this->fecha_creacion->ViewValue, 7);
			$this->fecha_creacion->ViewCustomAttributes = "";

			// fecha_modificacion
			$this->fecha_modificacion->ViewValue = $this->fecha_modificacion->CurrentValue;
			$this->fecha_modificacion->ViewValue = ew_FormatDateTime($this->fecha_modificacion->ViewValue, 7);
			$this->fecha_modificacion->ViewCustomAttributes = "";

			// id_individuo
			$this->id_individuo->LinkCustomAttributes = "";
			$this->id_individuo->HrefValue = "";
			$this->id_individuo->TooltipValue = "";

			// id_especie
			$this->id_especie->LinkCustomAttributes = "";
			$this->id_especie->HrefValue = "";
			$this->id_especie->TooltipValue = "";

			// calle
			$this->calle->LinkCustomAttributes = "";
			$this->calle->HrefValue = "";
			$this->calle->TooltipValue = "";

			// alt_ini
			$this->alt_ini->LinkCustomAttributes = "";
			$this->alt_ini->HrefValue = "";
			$this->alt_ini->TooltipValue = "";

			// ALTURA_TOT
			$this->ALTURA_TOT->LinkCustomAttributes = "";
			$this->ALTURA_TOT->HrefValue = "";
			$this->ALTURA_TOT->TooltipValue = "";

			// DIAMETRO
			$this->DIAMETRO->LinkCustomAttributes = "";
			$this->DIAMETRO->HrefValue = "";
			$this->DIAMETRO->TooltipValue = "";

			// INCLINACIO
			$this->INCLINACIO->LinkCustomAttributes = "";
			$this->INCLINACIO->HrefValue = "";
			$this->INCLINACIO->TooltipValue = "";

			// lat
			$this->lat->LinkCustomAttributes = "";
			$this->lat->HrefValue = "";
			$this->lat->TooltipValue = "";

			// lng
			$this->lng->LinkCustomAttributes = "";
			$this->lng->HrefValue = "";
			$this->lng->TooltipValue = "";

			// espacio_verde
			$this->espacio_verde->LinkCustomAttributes = "";
			$this->espacio_verde->HrefValue = "";
			$this->espacio_verde->TooltipValue = "";

			// id_usuario
			$this->id_usuario->LinkCustomAttributes = "";
			$this->id_usuario->HrefValue = "";
			$this->id_usuario->TooltipValue = "";

			// fecha_creacion
			$this->fecha_creacion->LinkCustomAttributes = "";
			$this->fecha_creacion->HrefValue = "";
			$this->fecha_creacion->TooltipValue = "";

			// fecha_modificacion
			$this->fecha_modificacion->LinkCustomAttributes = "";
			$this->fecha_modificacion->HrefValue = "";
			$this->fecha_modificacion->TooltipValue = "";
		} elseif ($this->RowType == EW_ROWTYPE_EDIT) { // Edit row

			// id_individuo
			$this->id_individuo->EditCustomAttributes = "";
			$this->id_individuo->EditValue = $this->id_individuo->CurrentValue;
			$this->id_individuo->ViewCustomAttributes = "";

			// id_especie
			$this->id_especie->EditCustomAttributes = "";
			$this->id_especie->EditValue = ew_HtmlEncode($this->id_especie->CurrentValue);
			if (strval($this->id_especie->CurrentValue) <> "") {
				$sFilterWrk = "`id_especie`" . ew_SearchString("=", $this->id_especie->CurrentValue, EW_DATATYPE_NUMBER);
			$sSqlWrk = "SELECT `id_especie`, `NOMBRE_CIE` AS `DispFld`, `NOMBRE_COM` AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld` FROM `especies`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}

			// Call Lookup selecting
			$this->Lookup_Selecting($this->id_especie, $sWhereWrk);
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
			$sSqlWrk .= " ORDER BY `NOMBRE_CIE`";
				$rswrk = $conn->Execute($sSqlWrk);
				if ($rswrk && !$rswrk->EOF) { // Lookup values found
					$this->id_especie->EditValue = $rswrk->fields('DispFld');
					$this->id_especie->EditValue .= ew_ValueSeparator(1,$this->id_especie) . $rswrk->fields('Disp2Fld');
					$rswrk->Close();
				} else {
					$this->id_especie->EditValue = $this->id_especie->CurrentValue;
				}
			} else {
				$this->id_especie->EditValue = NULL;
			}
			$this->id_especie->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->id_especie->FldCaption()));

			// calle
			$this->calle->EditCustomAttributes = "";
			$this->calle->EditValue = ew_HtmlEncode($this->calle->CurrentValue);
			$this->calle->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->calle->FldCaption()));

			// alt_ini
			$this->alt_ini->EditCustomAttributes = "";
			$this->alt_ini->EditValue = ew_HtmlEncode($this->alt_ini->CurrentValue);
			$this->alt_ini->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->alt_ini->FldCaption()));

			// ALTURA_TOT
			$this->ALTURA_TOT->EditCustomAttributes = "";
			$this->ALTURA_TOT->EditValue = ew_HtmlEncode($this->ALTURA_TOT->CurrentValue);
			$this->ALTURA_TOT->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->ALTURA_TOT->FldCaption()));

			// DIAMETRO
			$this->DIAMETRO->EditCustomAttributes = "";
			$this->DIAMETRO->EditValue = ew_HtmlEncode($this->DIAMETRO->CurrentValue);
			$this->DIAMETRO->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->DIAMETRO->FldCaption()));

			// INCLINACIO
			$this->INCLINACIO->EditCustomAttributes = "";
			$this->INCLINACIO->EditValue = ew_HtmlEncode($this->INCLINACIO->CurrentValue);
			$this->INCLINACIO->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->INCLINACIO->FldCaption()));

			// lat
			$this->lat->EditCustomAttributes = "";
			$this->lat->EditValue = ew_HtmlEncode($this->lat->CurrentValue);
			$this->lat->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->lat->FldCaption()));
			if (strval($this->lat->EditValue) <> "" && is_numeric($this->lat->EditValue)) $this->lat->EditValue = ew_FormatNumber($this->lat->EditValue, -2, -1, -2, 0);

			// lng
			$this->lng->EditCustomAttributes = "";
			$this->lng->EditValue = ew_HtmlEncode($this->lng->CurrentValue);
			$this->lng->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->lng->FldCaption()));
			if (strval($this->lng->EditValue) <> "" && is_numeric($this->lng->EditValue)) $this->lng->EditValue = ew_FormatNumber($this->lng->EditValue, -2, -1, -2, 0);

			// espacio_verde
			$this->espacio_verde->EditCustomAttributes = "";
			$this->espacio_verde->EditValue = ew_HtmlEncode($this->espacio_verde->CurrentValue);
			$this->espacio_verde->PlaceHolder = ew_HtmlEncode(ew_RemoveHtml($this->espacio_verde->FldCaption()));

			// id_usuario
			$this->id_usuario->EditCustomAttributes = "";
			$sFilterWrk = "";
			$sSqlWrk = "SELECT `id`, `nombre_completo` AS `DispFld`, '' AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld`, '' AS `SelectFilterFld`, '' AS `SelectFilterFld2`, '' AS `SelectFilterFld3`, '' AS `SelectFilterFld4` FROM `usuarios`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}

			// Call Lookup selecting
			$this->Lookup_Selecting($this->id_usuario, $sWhereWrk);
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
			$sSqlWrk .= " ORDER BY `nombre_completo`";
			$rswrk = $conn->Execute($sSqlWrk);
			$arwrk = ($rswrk) ? $rswrk->GetRows() : array();
			if ($rswrk) $rswrk->Close();
			array_unshift($arwrk, array("", $Language->Phrase("PleaseSelect"), "", "", "", "", "", "", ""));
			$this->id_usuario->EditValue = $arwrk;

			// fecha_creacion
			// fecha_modificacion
			// Edit refer script
			// id_individuo

			$this->id_individuo->HrefValue = "";

			// id_especie
			$this->id_especie->HrefValue = "";

			// calle
			$this->calle->HrefValue = "";

			// alt_ini
			$this->alt_ini->HrefValue = "";

			// ALTURA_TOT
			$this->ALTURA_TOT->HrefValue = "";

			// DIAMETRO
			$this->DIAMETRO->HrefValue = "";

			// INCLINACIO
			$this->INCLINACIO->HrefValue = "";

			// lat
			$this->lat->HrefValue = "";

			// lng
			$this->lng->HrefValue = "";

			// espacio_verde
			$this->espacio_verde->HrefValue = "";

			// id_usuario
			$this->id_usuario->HrefValue = "";

			// fecha_creacion
			$this->fecha_creacion->HrefValue = "";

			// fecha_modificacion
			$this->fecha_modificacion->HrefValue = "";
		}
		if ($this->RowType == EW_ROWTYPE_ADD ||
			$this->RowType == EW_ROWTYPE_EDIT ||
			$this->RowType == EW_ROWTYPE_SEARCH) { // Add / Edit / Search row
			$this->SetupFieldTitles();
		}

		// Call Row Rendered event
		if ($this->RowType <> EW_ROWTYPE_AGGREGATEINIT)
			$this->Row_Rendered();
	}

	// Validate form
	function ValidateForm() {
		global $Language, $gsFormError;

		// Initialize form error message
		$gsFormError = "";

		// Check if validation required
		if (!EW_SERVER_VALIDATE)
			return ($gsFormError == "");
		if (!$this->id_especie->FldIsDetailKey && !is_null($this->id_especie->FormValue) && $this->id_especie->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->id_especie->FldCaption());
		}
		if (!ew_CheckInteger($this->alt_ini->FormValue)) {
			ew_AddMessage($gsFormError, $this->alt_ini->FldErrMsg());
		}
		if (!ew_CheckInteger($this->ALTURA_TOT->FormValue)) {
			ew_AddMessage($gsFormError, $this->ALTURA_TOT->FldErrMsg());
		}
		if (!ew_CheckInteger($this->DIAMETRO->FormValue)) {
			ew_AddMessage($gsFormError, $this->DIAMETRO->FldErrMsg());
		}
		if (!ew_CheckInteger($this->INCLINACIO->FormValue)) {
			ew_AddMessage($gsFormError, $this->INCLINACIO->FldErrMsg());
		}
		if (!$this->lat->FldIsDetailKey && !is_null($this->lat->FormValue) && $this->lat->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->lat->FldCaption());
		}
		if (!ew_CheckNumber($this->lat->FormValue)) {
			ew_AddMessage($gsFormError, $this->lat->FldErrMsg());
		}
		if (!$this->lng->FldIsDetailKey && !is_null($this->lng->FormValue) && $this->lng->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->lng->FldCaption());
		}
		if (!ew_CheckNumber($this->lng->FormValue)) {
			ew_AddMessage($gsFormError, $this->lng->FldErrMsg());
		}
		if (!$this->id_usuario->FldIsDetailKey && !is_null($this->id_usuario->FormValue) && $this->id_usuario->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->id_usuario->FldCaption());
		}

		// Return validate result
		$ValidateForm = ($gsFormError == "");

		// Call Form_CustomValidate event
		$sFormCustomError = "";
		$ValidateForm = $ValidateForm && $this->Form_CustomValidate($sFormCustomError);
		if ($sFormCustomError <> "") {
			ew_AddMessage($gsFormError, $sFormCustomError);
		}
		return $ValidateForm;
	}

	// Update record based on key values
	function EditRow() {
		global $conn, $Security, $Language;
		$sFilter = $this->KeyFilter();
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$rs = $conn->Execute($sSql);
		$conn->raiseErrorFn = '';
		if ($rs === FALSE)
			return FALSE;
		if ($rs->EOF) {
			$EditRow = FALSE; // Update Failed
		} else {

			// Save old values
			$rsold = &$rs->fields;
			$this->LoadDbValues($rsold);
			$rsnew = array();

			// id_especie
			$this->id_especie->SetDbValueDef($rsnew, $this->id_especie->CurrentValue, 0, $this->id_especie->ReadOnly);

			// calle
			$this->calle->SetDbValueDef($rsnew, $this->calle->CurrentValue, NULL, $this->calle->ReadOnly);

			// alt_ini
			$this->alt_ini->SetDbValueDef($rsnew, $this->alt_ini->CurrentValue, NULL, $this->alt_ini->ReadOnly);

			// ALTURA_TOT
			$this->ALTURA_TOT->SetDbValueDef($rsnew, $this->ALTURA_TOT->CurrentValue, NULL, $this->ALTURA_TOT->ReadOnly);

			// DIAMETRO
			$this->DIAMETRO->SetDbValueDef($rsnew, $this->DIAMETRO->CurrentValue, NULL, $this->DIAMETRO->ReadOnly);

			// INCLINACIO
			$this->INCLINACIO->SetDbValueDef($rsnew, $this->INCLINACIO->CurrentValue, NULL, $this->INCLINACIO->ReadOnly);

			// lat
			$this->lat->SetDbValueDef($rsnew, $this->lat->CurrentValue, 0, $this->lat->ReadOnly);

			// lng
			$this->lng->SetDbValueDef($rsnew, $this->lng->CurrentValue, 0, $this->lng->ReadOnly);

			// espacio_verde
			$this->espacio_verde->SetDbValueDef($rsnew, $this->espacio_verde->CurrentValue, NULL, $this->espacio_verde->ReadOnly);

			// id_usuario
			$this->id_usuario->SetDbValueDef($rsnew, $this->id_usuario->CurrentValue, 0, $this->id_usuario->ReadOnly);

			// fecha_creacion
			$this->fecha_creacion->SetDbValueDef($rsnew, ew_CurrentDateTime(), ew_CurrentDate());
			$rsnew['fecha_creacion'] = &$this->fecha_creacion->DbValue;

			// fecha_modificacion
			$this->fecha_modificacion->SetDbValueDef($rsnew, ew_CurrentDateTime(), NULL);
			$rsnew['fecha_modificacion'] = &$this->fecha_modificacion->DbValue;

			// Call Row Updating event
			$bUpdateRow = $this->Row_Updating($rsold, $rsnew);
			if ($bUpdateRow) {
				$conn->raiseErrorFn = 'ew_ErrorFn';
				if (count($rsnew) > 0)
					$EditRow = $this->Update($rsnew, "", $rsold);
				else
					$EditRow = TRUE; // No field to update
				$conn->raiseErrorFn = '';
				if ($EditRow) {
				}
			} else {
				if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

					// Use the message, do nothing
				} elseif ($this->CancelMessage <> "") {
					$this->setFailureMessage($this->CancelMessage);
					$this->CancelMessage = "";
				} else {
					$this->setFailureMessage($Language->Phrase("UpdateCancelled"));
				}
				$EditRow = FALSE;
			}
		}

		// Call Row_Updated event
		if ($EditRow)
			$this->Row_Updated($rsold, $rsnew);
		$rs->Close();
		return $EditRow;
	}

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $Breadcrumb, $Language;
		$Breadcrumb = new cBreadcrumb();
		$PageCaption = $this->TableCaption();
		$Breadcrumb->Add("list", "<span id=\"ewPageCaption\">" . $PageCaption . "</span>", "individuoslist.php", $this->TableVar);
		$PageCaption = $Language->Phrase("edit");
		$Breadcrumb->Add("edit", "<span id=\"ewPageCaption\">" . $PageCaption . "</span>", ew_CurrentUrl(), $this->TableVar);
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Page Redirecting event
	function Page_Redirecting(&$url) {

		// Example:
		//$url = "your URL";

	}

	// Message Showing event
	// $type = ''|'success'|'failure'|'warning'
	function Message_Showing(&$msg, $type) {
		if ($type == 'success') {

			//$msg = "your success message";
		} elseif ($type == 'failure') {

			//$msg = "your failure message";
		} elseif ($type == 'warning') {

			//$msg = "your warning message";
		} else {

			//$msg = "your message";
		}
	}

	// Page Render event
	function Page_Render() {

		//echo "Page Render";
	}

	// Page Data Rendering event
	function Page_DataRendering(&$header) {

		// Example:
		//$header = "your header";

	}

	// Page Data Rendered event
	function Page_DataRendered(&$footer) {

		// Example:
		//$footer = "your footer";

	}

	// Form Custom Validate event
	function Form_CustomValidate(&$CustomError) {

		// Return error message in CustomError
		return TRUE;
	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($individuos_edit)) $individuos_edit = new cindividuos_edit();

// Page init
$individuos_edit->Page_Init();

// Page main
$individuos_edit->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$individuos_edit->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var individuos_edit = new ew_Page("individuos_edit");
individuos_edit.PageID = "edit"; // Page ID
var EW_PAGE_ID = individuos_edit.PageID; // For backward compatibility

// Form object
var findividuosedit = new ew_Form("findividuosedit");

// Validate form
findividuosedit.Validate = function() {
	if (!this.ValidateRequired)
		return true; // Ignore validation
	var $ = jQuery, fobj = this.GetForm(), $fobj = $(fobj);
	this.PostAutoSuggest();
	if ($fobj.find("#a_confirm").val() == "F")
		return true;
	var elm, felm, uelm, addcnt = 0;
	var $k = $fobj.find("#" + this.FormKeyCountName); // Get key_count
	var rowcnt = ($k[0]) ? parseInt($k.val(), 10) : 1;
	var startcnt = (rowcnt == 0) ? 0 : 1; // Check rowcnt == 0 => Inline-Add
	var gridinsert = $fobj.find("#a_list").val() == "gridinsert";
	for (var i = startcnt; i <= rowcnt; i++) {
		var infix = ($k[0]) ? String(i) : "";
		$fobj.data("rowindex", infix);
			elm = this.GetElements("x" + infix + "_id_especie");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($individuos->id_especie->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_alt_ini");
			if (elm && !ew_CheckInteger(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($individuos->alt_ini->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_ALTURA_TOT");
			if (elm && !ew_CheckInteger(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($individuos->ALTURA_TOT->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_DIAMETRO");
			if (elm && !ew_CheckInteger(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($individuos->DIAMETRO->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_INCLINACIO");
			if (elm && !ew_CheckInteger(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($individuos->INCLINACIO->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_lat");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($individuos->lat->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_lat");
			if (elm && !ew_CheckNumber(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($individuos->lat->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_lng");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($individuos->lng->FldCaption()) ?>");
			elm = this.GetElements("x" + infix + "_lng");
			if (elm && !ew_CheckNumber(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($individuos->lng->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_id_usuario");
			if (elm && !ew_HasValue(elm))
				return this.OnError(elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($individuos->id_usuario->FldCaption()) ?>");

			// Set up row object
			ew_ElementsToRow(fobj);

			// Fire Form_CustomValidate event
			if (!this.Form_CustomValidate(fobj))
				return false;
	}

	// Process detail forms
	var dfs = $fobj.find("input[name='detailpage']").get();
	for (var i = 0; i < dfs.length; i++) {
		var df = dfs[i], val = df.value;
		if (val && ewForms[val])
			if (!ewForms[val].Validate())
				return false;
	}
	return true;
}

// Form_CustomValidate event
findividuosedit.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
findividuosedit.ValidateRequired = true;
<?php } else { ?>
findividuosedit.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
findividuosedit.Lists["x_id_especie"] = {"LinkField":"x_id_especie","Ajax":true,"AutoFill":false,"DisplayFields":["x_NOMBRE_CIE","x_NOMBRE_COM","",""],"ParentFields":[],"FilterFields":[],"Options":[]};
findividuosedit.Lists["x_id_usuario"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_nombre_completo","","",""],"ParentFields":[],"FilterFields":[],"Options":[]};

// Form object for search
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php $Breadcrumb->Render(); ?>
<?php $individuos_edit->ShowPageHeader(); ?>
<?php
$individuos_edit->ShowMessage();
?>
<form name="ewPagerForm" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>">
<table class="ewPager">
<tr><td>
<?php if (!isset($individuos_edit->Pager)) $individuos_edit->Pager = new cPrevNextPager($individuos_edit->StartRec, $individuos_edit->DisplayRecs, $individuos_edit->TotalRecs) ?>
<?php if ($individuos_edit->Pager->RecordCount > 0) { ?>
<table cellspacing="0" class="ewStdTable"><tbody><tr><td>
	<?php echo $Language->Phrase("Page") ?>&nbsp;
<div class="input-prepend input-append">
<!--first page button-->
	<?php if ($individuos_edit->Pager->FirstButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $individuos_edit->PageUrl() ?>start=<?php echo $individuos_edit->Pager->FirstButton->Start ?>"><i class="icon-step-backward"></i></a>
	<?php } else { ?>
	<a class="btn btn-small" disabled="disabled"><i class="icon-step-backward"></i></a>
	<?php } ?>
<!--previous page button-->
	<?php if ($individuos_edit->Pager->PrevButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $individuos_edit->PageUrl() ?>start=<?php echo $individuos_edit->Pager->PrevButton->Start ?>"><i class="icon-prev"></i></a>
	<?php } else { ?>
	<a class="btn btn-small" disabled="disabled"><i class="icon-prev"></i></a>
	<?php } ?>
<!--current page number-->
	<input class="input-mini" type="text" name="<?php echo EW_TABLE_PAGE_NO ?>" value="<?php echo $individuos_edit->Pager->CurrentPage ?>">
<!--next page button-->
	<?php if ($individuos_edit->Pager->NextButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $individuos_edit->PageUrl() ?>start=<?php echo $individuos_edit->Pager->NextButton->Start ?>"><i class="icon-play"></i></a>
	<?php } else { ?>
	<a class="btn btn-small" disabled="disabled"><i class="icon-play"></i></a>
	<?php } ?>
<!--last page button-->
	<?php if ($individuos_edit->Pager->LastButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $individuos_edit->PageUrl() ?>start=<?php echo $individuos_edit->Pager->LastButton->Start ?>"><i class="icon-step-forward"></i></a>
	<?php } else { ?>
	<a class="btn btn-small" disabled="disabled"><i class="icon-step-forward"></i></a>
	<?php } ?>
</div>
	&nbsp;<?php echo $Language->Phrase("of") ?>&nbsp;<?php echo $individuos_edit->Pager->PageCount ?>
</td>
</tr></tbody></table>
<?php } else { ?>
	<p><?php echo $Language->Phrase("NoRecord") ?></p>
<?php } ?>
</td>
</tr></table>
</form>
<form name="findividuosedit" id="findividuosedit" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="individuos">
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table cellspacing="0" class="ewGrid"><tr><td>
<table id="tbl_individuosedit" class="table table-bordered table-striped">
<?php if ($individuos->id_individuo->Visible) { // id_individuo ?>
	<tr id="r_id_individuo">
		<td><span id="elh_individuos_id_individuo"><?php echo $individuos->id_individuo->FldCaption() ?></span></td>
		<td<?php echo $individuos->id_individuo->CellAttributes() ?>>
<span id="el_individuos_id_individuo" class="control-group">
<span<?php echo $individuos->id_individuo->ViewAttributes() ?>>
<?php echo $individuos->id_individuo->EditValue ?></span>
</span>
<input type="hidden" data-field="x_id_individuo" name="x_id_individuo" id="x_id_individuo" value="<?php echo ew_HtmlEncode($individuos->id_individuo->CurrentValue) ?>">
<?php echo $individuos->id_individuo->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($individuos->id_especie->Visible) { // id_especie ?>
	<tr id="r_id_especie">
		<td><span id="elh_individuos_id_especie"><?php echo $individuos->id_especie->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $individuos->id_especie->CellAttributes() ?>>
<span id="el_individuos_id_especie" class="control-group">
<?php
	$wrkonchange = trim(" " . @$individuos->id_especie->EditAttrs["onchange"]);
	if ($wrkonchange <> "") $wrkonchange = " onchange=\"" . ew_JsEncode2($wrkonchange) . "\"";
	$individuos->id_especie->EditAttrs["onchange"] = "";
?>
<span id="as_x_id_especie" style="white-space: nowrap; z-index: 8980">
	<input type="text" name="sv_x_id_especie" id="sv_x_id_especie" value="<?php echo $individuos->id_especie->EditValue ?>" size="30" placeholder="<?php echo $individuos->id_especie->PlaceHolder ?>"<?php echo $individuos->id_especie->EditAttributes() ?>>&nbsp;<span id="em_x_id_especie" class="ewMessage" style="display: none"><?php echo str_replace("%f", "phpimages/", $Language->Phrase("UnmatchedValue")) ?></span>
	<div id="sc_x_id_especie" style="display: inline; z-index: 8980"></div>
</span>
<input type="hidden" data-field="x_id_especie" name="x_id_especie" id="x_id_especie" value="<?php echo $individuos->id_especie->CurrentValue ?>"<?php echo $wrkonchange ?>>
<?php
$sSqlWrk = "SELECT `id_especie`, `NOMBRE_CIE` AS `DispFld`, `NOMBRE_COM` AS `Disp2Fld` FROM `especies`";
$sWhereWrk = "`NOMBRE_CIE` LIKE '{query_value}%' OR CONCAT(`NOMBRE_CIE`,'" . ew_ValueSeparator(1, $Page->id_especie) . "',`NOMBRE_COM`) LIKE '{query_value}%'";

// Call Lookup selecting
$individuos->Lookup_Selecting($individuos->id_especie, $sWhereWrk);
if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
$sSqlWrk .= " ORDER BY `NOMBRE_CIE`";
$sSqlWrk .= " LIMIT " . EW_AUTO_SUGGEST_MAX_ENTRIES;
?>
<input type="hidden" name="q_x_id_especie" id="q_x_id_especie" value="s=<?php echo ew_Encrypt($sSqlWrk) ?>">
<script type="text/javascript">
var oas = new ew_AutoSuggest("x_id_especie", findividuosedit, true, EW_AUTO_SUGGEST_MAX_ENTRIES);
oas.formatResult = function(ar) {
	var dv = ar[1];
	for (var i = 2; i <= 4; i++)
		dv += (ar[i]) ? ew_ValueSeparator(i - 1, "x_id_especie") + ar[i] : "";
	return dv;
}
findividuosedit.AutoSuggests["x_id_especie"] = oas;
</script>
</span>
<?php echo $individuos->id_especie->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($individuos->calle->Visible) { // calle ?>
	<tr id="r_calle">
		<td><span id="elh_individuos_calle"><?php echo $individuos->calle->FldCaption() ?></span></td>
		<td<?php echo $individuos->calle->CellAttributes() ?>>
<span id="el_individuos_calle" class="control-group">
<input type="text" data-field="x_calle" name="x_calle" id="x_calle" size="30" maxlength="255" placeholder="<?php echo $individuos->calle->PlaceHolder ?>" value="<?php echo $individuos->calle->EditValue ?>"<?php echo $individuos->calle->EditAttributes() ?>>
</span>
<?php echo $individuos->calle->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($individuos->alt_ini->Visible) { // alt_ini ?>
	<tr id="r_alt_ini">
		<td><span id="elh_individuos_alt_ini"><?php echo $individuos->alt_ini->FldCaption() ?></span></td>
		<td<?php echo $individuos->alt_ini->CellAttributes() ?>>
<span id="el_individuos_alt_ini" class="control-group">
<input type="text" data-field="x_alt_ini" name="x_alt_ini" id="x_alt_ini" size="30" placeholder="<?php echo $individuos->alt_ini->PlaceHolder ?>" value="<?php echo $individuos->alt_ini->EditValue ?>"<?php echo $individuos->alt_ini->EditAttributes() ?>>
</span>
<?php echo $individuos->alt_ini->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($individuos->ALTURA_TOT->Visible) { // ALTURA_TOT ?>
	<tr id="r_ALTURA_TOT">
		<td><span id="elh_individuos_ALTURA_TOT"><?php echo $individuos->ALTURA_TOT->FldCaption() ?></span></td>
		<td<?php echo $individuos->ALTURA_TOT->CellAttributes() ?>>
<span id="el_individuos_ALTURA_TOT" class="control-group">
<input type="text" data-field="x_ALTURA_TOT" name="x_ALTURA_TOT" id="x_ALTURA_TOT" size="30" placeholder="<?php echo $individuos->ALTURA_TOT->PlaceHolder ?>" value="<?php echo $individuos->ALTURA_TOT->EditValue ?>"<?php echo $individuos->ALTURA_TOT->EditAttributes() ?>>
</span>
<?php echo $individuos->ALTURA_TOT->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($individuos->DIAMETRO->Visible) { // DIAMETRO ?>
	<tr id="r_DIAMETRO">
		<td><span id="elh_individuos_DIAMETRO"><?php echo $individuos->DIAMETRO->FldCaption() ?></span></td>
		<td<?php echo $individuos->DIAMETRO->CellAttributes() ?>>
<span id="el_individuos_DIAMETRO" class="control-group">
<input type="text" data-field="x_DIAMETRO" name="x_DIAMETRO" id="x_DIAMETRO" size="30" placeholder="<?php echo $individuos->DIAMETRO->PlaceHolder ?>" value="<?php echo $individuos->DIAMETRO->EditValue ?>"<?php echo $individuos->DIAMETRO->EditAttributes() ?>>
</span>
<?php echo $individuos->DIAMETRO->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($individuos->INCLINACIO->Visible) { // INCLINACIO ?>
	<tr id="r_INCLINACIO">
		<td><span id="elh_individuos_INCLINACIO"><?php echo $individuos->INCLINACIO->FldCaption() ?></span></td>
		<td<?php echo $individuos->INCLINACIO->CellAttributes() ?>>
<span id="el_individuos_INCLINACIO" class="control-group">
<input type="text" data-field="x_INCLINACIO" name="x_INCLINACIO" id="x_INCLINACIO" size="30" placeholder="<?php echo $individuos->INCLINACIO->PlaceHolder ?>" value="<?php echo $individuos->INCLINACIO->EditValue ?>"<?php echo $individuos->INCLINACIO->EditAttributes() ?>>
</span>
<?php echo $individuos->INCLINACIO->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($individuos->lat->Visible) { // lat ?>
	<tr id="r_lat">
		<td><span id="elh_individuos_lat"><?php echo $individuos->lat->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $individuos->lat->CellAttributes() ?>>
<span id="el_individuos_lat" class="control-group">
<input type="text" data-field="x_lat" name="x_lat" id="x_lat" size="30" placeholder="<?php echo $individuos->lat->PlaceHolder ?>" value="<?php echo $individuos->lat->EditValue ?>"<?php echo $individuos->lat->EditAttributes() ?>>
</span>
<?php echo $individuos->lat->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($individuos->lng->Visible) { // lng ?>
	<tr id="r_lng">
		<td><span id="elh_individuos_lng"><?php echo $individuos->lng->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $individuos->lng->CellAttributes() ?>>
<span id="el_individuos_lng" class="control-group">
<input type="text" data-field="x_lng" name="x_lng" id="x_lng" size="30" placeholder="<?php echo $individuos->lng->PlaceHolder ?>" value="<?php echo $individuos->lng->EditValue ?>"<?php echo $individuos->lng->EditAttributes() ?>>
</span>
<?php echo $individuos->lng->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($individuos->espacio_verde->Visible) { // espacio_verde ?>
	<tr id="r_espacio_verde">
		<td><span id="elh_individuos_espacio_verde"><?php echo $individuos->espacio_verde->FldCaption() ?></span></td>
		<td<?php echo $individuos->espacio_verde->CellAttributes() ?>>
<span id="el_individuos_espacio_verde" class="control-group">
<input type="text" data-field="x_espacio_verde" name="x_espacio_verde" id="x_espacio_verde" size="30" maxlength="255" placeholder="<?php echo $individuos->espacio_verde->PlaceHolder ?>" value="<?php echo $individuos->espacio_verde->EditValue ?>"<?php echo $individuos->espacio_verde->EditAttributes() ?>>
</span>
<?php echo $individuos->espacio_verde->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($individuos->id_usuario->Visible) { // id_usuario ?>
	<tr id="r_id_usuario">
		<td><span id="elh_individuos_id_usuario"><?php echo $individuos->id_usuario->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></span></td>
		<td<?php echo $individuos->id_usuario->CellAttributes() ?>>
<span id="el_individuos_id_usuario" class="control-group">
<select data-field="x_id_usuario" id="x_id_usuario" name="x_id_usuario"<?php echo $individuos->id_usuario->EditAttributes() ?>>
<?php
if (is_array($individuos->id_usuario->EditValue)) {
	$arwrk = $individuos->id_usuario->EditValue;
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($individuos->id_usuario->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected=\"selected\"" : "";
		if ($selwrk <> "") $emptywrk = FALSE;
?>
<option value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
</option>
<?php
	}
}
?>
</select>
<script type="text/javascript">
findividuosedit.Lists["x_id_usuario"].Options = <?php echo (is_array($individuos->id_usuario->EditValue)) ? ew_ArrayToJson($individuos->id_usuario->EditValue, 1) : "[]" ?>;
</script>
</span>
<?php echo $individuos->id_usuario->CustomMsg ?></td>
	</tr>
<?php } ?>
</table>
</td></tr></table>
<table class="ewPager">
<tr><td>
<?php if (!isset($individuos_edit->Pager)) $individuos_edit->Pager = new cPrevNextPager($individuos_edit->StartRec, $individuos_edit->DisplayRecs, $individuos_edit->TotalRecs) ?>
<?php if ($individuos_edit->Pager->RecordCount > 0) { ?>
<table cellspacing="0" class="ewStdTable"><tbody><tr><td>
	<?php echo $Language->Phrase("Page") ?>&nbsp;
<div class="input-prepend input-append">
<!--first page button-->
	<?php if ($individuos_edit->Pager->FirstButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $individuos_edit->PageUrl() ?>start=<?php echo $individuos_edit->Pager->FirstButton->Start ?>"><i class="icon-step-backward"></i></a>
	<?php } else { ?>
	<a class="btn btn-small" disabled="disabled"><i class="icon-step-backward"></i></a>
	<?php } ?>
<!--previous page button-->
	<?php if ($individuos_edit->Pager->PrevButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $individuos_edit->PageUrl() ?>start=<?php echo $individuos_edit->Pager->PrevButton->Start ?>"><i class="icon-prev"></i></a>
	<?php } else { ?>
	<a class="btn btn-small" disabled="disabled"><i class="icon-prev"></i></a>
	<?php } ?>
<!--current page number-->
	<input class="input-mini" type="text" name="<?php echo EW_TABLE_PAGE_NO ?>" value="<?php echo $individuos_edit->Pager->CurrentPage ?>">
<!--next page button-->
	<?php if ($individuos_edit->Pager->NextButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $individuos_edit->PageUrl() ?>start=<?php echo $individuos_edit->Pager->NextButton->Start ?>"><i class="icon-play"></i></a>
	<?php } else { ?>
	<a class="btn btn-small" disabled="disabled"><i class="icon-play"></i></a>
	<?php } ?>
<!--last page button-->
	<?php if ($individuos_edit->Pager->LastButton->Enabled) { ?>
	<a class="btn btn-small" href="<?php echo $individuos_edit->PageUrl() ?>start=<?php echo $individuos_edit->Pager->LastButton->Start ?>"><i class="icon-step-forward"></i></a>
	<?php } else { ?>
	<a class="btn btn-small" disabled="disabled"><i class="icon-step-forward"></i></a>
	<?php } ?>
</div>
	&nbsp;<?php echo $Language->Phrase("of") ?>&nbsp;<?php echo $individuos_edit->Pager->PageCount ?>
</td>
</tr></tbody></table>
<?php } else { ?>
	<p><?php echo $Language->Phrase("NoRecord") ?></p>
<?php } ?>
</td>
</tr></table>
<button class="btn btn-primary ewButton" name="btnAction" id="btnAction" type="submit"><?php echo $Language->Phrase("EditBtn") ?></button>
</form>
<script type="text/javascript">
findividuosedit.Init();
<?php if (EW_MOBILE_REFLOW && ew_IsMobile()) { ?>
ew_Reflow();
<?php } ?>
</script>
<?php
$individuos_edit->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$individuos_edit->Page_Terminate();
?>
