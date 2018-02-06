<?php

/*
* PHP code generated with wxFormBuilder (version Jan 23 2018)
* http://www.wxformbuilder.org/
*
* PLEASE DO *NOT* EDIT THIS FILE!
*/

/*
 * Class ImageDownFrameBase
 */

class ImageDownFrameBase extends wxFrame {
	
	function __construct( $parent=null ){
		parent::__construct ( $parent, wxID_ANY, _("ImageDown"), wxDefaultPosition, new wxSize( 741,438 ), wxDEFAULT_FRAME_STYLE|wxTAB_TRAVERSAL );
		
		$this->SetSizeHints( wxDefaultSize, wxDefaultSize );
		$this->SetBackgroundColour( wxSystemSettings::GetColour( wxSYS_COLOUR_HIGHLIGHTTEXT ) );
		
		$this->statusBar = $this->CreateStatusBar( 1, wxSTB_SIZEGRIP, wxID_ANY );
		$bSizer5 = new wxBoxSizer( wxVERTICAL );
		
		$bSizerMain = new wxBoxSizer( wxHORIZONTAL );
		
		$fgSizer = new wxFlexGridSizer( 6, 2, 0, 0 );
		$fgSizer->AddGrowableCol( 1 );
		$fgSizer->SetFlexibleDirection( wxBOTH );
		$fgSizer->SetNonFlexibleGrowMode( wxFLEX_GROWMODE_SPECIFIED );
		
		$this->staUrl = new wxStaticText( $this, wxID_ANY, _("Url:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staUrl->Wrap( -1 );
		$fgSizer->Add( $this->staUrl, 0, wxALIGN_CENTER_VERTICAL|wxALIGN_RIGHT|wxALL, 5 );
		
		$this->txtUrl = new wxTextCtrl( $this, wxID_ANY, wxEmptyString, wxDefaultPosition, wxDefaultSize, wxTE_MULTILINE );
		$fgSizer->Add( $this->txtUrl, 0, wxALL|wxEXPAND|wxALIGN_CENTER_VERTICAL, 5 );
		
		
		$fgSizer->Add( 0, 0, 1, wxEXPAND, 5, null );
		
		$this->staUrlDesc = new wxStaticText( $this, wxID_ANY, _("1. 一行一个url; 2. 可用{name:type}表示变量和类型，如 https://wslim.cn/article/{id:int}/{page} 在下面指定替换值."), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staUrlDesc->Wrap( -1 );
		$fgSizer->Add( $this->staUrlDesc, 0, wxALL, 5 );
		
		$this->staUrlReplace = new wxStaticText( $this, wxID_ANY, _("Url替换"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staUrlReplace->Wrap( -1 );
		$fgSizer->Add( $this->staUrlReplace, 0, wxALIGN_CENTER_VERTICAL|wxALIGN_RIGHT|wxALL, 5 );
		
		$this->txtUrlReplace = new wxTextCtrl( $this, wxID_ANY, wxEmptyString, wxDefaultPosition, wxDefaultSize, wxTE_MULTILINE );
		$fgSizer->Add( $this->txtUrlReplace, 0, wxALL|wxEXPAND, 5 );
		
		
		$fgSizer->Add( 0, 0, 1, wxEXPAND, 5, null );
		
		$this->staUrlReplaceDesc = new wxStaticText( $this, wxID_ANY, _("1. 一行一个替换变量; 2. 如url使用了{id:int}/{page} 则这里可以填id=\"1-5\" page=\"1-3\""), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staUrlReplaceDesc->Wrap( -1 );
		$fgSizer->Add( $this->staUrlReplaceDesc, 0, wxALL, 5 );
		
		$this->staOptions = new wxStaticText( $this, wxID_ANY, _("选项:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staOptions->Wrap( -1 );
		$fgSizer->Add( $this->staOptions, 0, wxALIGN_CENTER_VERTICAL|wxALIGN_RIGHT|wxALL, 5 );
		
		$bSizerOptions = new wxBoxSizer( wxHORIZONTAL );
		
		$this->staWidth = new wxStaticText( $this, wxID_ANY, _("width:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staWidth->Wrap( -1 );
		$bSizerOptions->Add( $this->staWidth, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->txtWidth = new wxTextCtrl( $this, wxID_ANY, wxEmptyString, wxDefaultPosition, wxDefaultSize, 0 );
		$this->txtWidth->SetMaxSize( new wxSize( 40,-1 ) );
		
		$bSizerOptions->Add( $this->txtWidth, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->staHeight = new wxStaticText( $this, wxID_ANY, _("height:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staHeight->Wrap( -1 );
		$bSizerOptions->Add( $this->staHeight, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->txtHeight = new wxTextCtrl( $this, wxID_ANY, wxEmptyString, wxDefaultPosition, wxDefaultSize, 0 );
		$this->txtHeight->SetMaxSize( new wxSize( 40,-1 ) );
		
		$bSizerOptions->Add( $this->txtHeight, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->staInclude = new wxStaticText( $this, wxID_ANY, _("包含文本:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staInclude->Wrap( -1 );
		$bSizerOptions->Add( $this->staInclude, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->txtInclude = new wxTextCtrl( $this, wxID_ANY, wxEmptyString, wxDefaultPosition, wxDefaultSize, 0 );
		$bSizerOptions->Add( $this->txtInclude, 1, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->chkIncludeTitle = new wxCheckBox( $this, wxID_ANY, _("自动包含标题"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->chkIncludeTitle->SetValue(True); 
		$bSizerOptions->Add( $this->chkIncludeTitle, 0, wxALIGN_CENTER|wxALL, 5 );
		
		$this->btnTestUrl = new wxButton( $this, wxID_ANY, _("测试Url"), wxDefaultPosition, wxDefaultSize, 0 );
		$bSizerOptions->Add( $this->btnTestUrl, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->btnPreview = new wxButton( $this, wxID_ANY, _("预览5张"), wxDefaultPosition, wxDefaultSize, 0 );
		$bSizerOptions->Add( $this->btnPreview, 0, wxALL, 5 );
		
		$this->btnPreviewAll = new wxButton( $this, wxID_ANY, _("预览全部"), wxDefaultPosition, wxDefaultSize, 0 );
		$bSizerOptions->Add( $this->btnPreviewAll, 0, wxALL, 5 );
		
		
		$fgSizer->Add( $bSizerOptions, 1, wxALL|wxEXPAND, 0 );
		
		$this->staOperator = new wxStaticText( $this, wxID_ANY, _("操作:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staOperator->Wrap( -1 );
		$fgSizer->Add( $this->staOperator, 0, wxALIGN_CENTER_VERTICAL|wxALIGN_RIGHT|wxALL, 5 );
		
		$bSizerOperator = new wxBoxSizer( wxHORIZONTAL );
		
		$this->staSaveDir = new wxStaticText( $this, wxID_ANY, _("目录:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staSaveDir->Wrap( -1 );
		$bSizerOperator->Add( $this->staSaveDir, 0, wxALIGN_CENTER|wxALL, 5 );
		
		$this->pickerSaveDir = new wxDirPickerCtrl( $this, wxID_ANY, wxEmptyString, _("Select a folder"), wxDefaultPosition, wxDefaultSize, wxDIRP_DEFAULT_STYLE );
		$bSizerOperator->Add( $this->pickerSaveDir, 1, wxALL, 5 );
		
		$this->staSaveFile = new wxStaticText( $this, wxID_ANY, _("文件名:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staSaveFile->Wrap( -1 );
		$bSizerOperator->Add( $this->staSaveFile, 0, wxALIGN_CENTER|wxALL, 5 );
		
		$sleSaveFileChoices = array( _("filename"), _("title+filename") );
		$this->sleSaveFile = new wxChoice( $this, wxID_ANY, wxDefaultPosition, wxDefaultSize, $sleSaveFileChoices, 0 );
		$this->sleSaveFile->SetSelection( 0 );
		$bSizerOperator->Add( $this->sleSaveFile, 0, wxALIGN_CENTER|wxALL, 5 );
		
		$this->staStartId = new wxStaticText( $this, wxID_ANY, _("序号:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staStartId->Wrap( -1 );
		$bSizerOperator->Add( $this->staStartId, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->txtStartId = new wxTextCtrl( $this, wxID_ANY, _("1"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->txtStartId->SetMaxSize( new wxSize( 30,-1 ) );
		
		$bSizerOperator->Add( $this->txtStartId, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->staEndId = new wxStaticText( $this, wxID_ANY, _("到:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staEndId->Wrap( -1 );
		$bSizerOperator->Add( $this->staEndId, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->txtEndId = new wxTextCtrl( $this, wxID_ANY, wxEmptyString, wxDefaultPosition, wxDefaultSize, 0 );
		$this->txtEndId->SetMaxSize( new wxSize( 30,-1 ) );
		
		$bSizerOperator->Add( $this->txtEndId, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->staPerCount = new wxStaticText( $this, wxID_ANY, _("每次:"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->staPerCount->Wrap( -1 );
		$bSizerOperator->Add( $this->staPerCount, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->txtPerCount = new wxTextCtrl( $this, wxID_ANY, _("3"), wxDefaultPosition, wxDefaultSize, 0 );
		$this->txtPerCount->SetMaxSize( new wxSize( 20,-1 ) );
		
		$bSizerOperator->Add( $this->txtPerCount, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		$this->btnSave = new wxButton( $this, wxID_ANY, _("保存"), wxDefaultPosition, wxDefaultSize, 0 );
		$bSizerOperator->Add( $this->btnSave, 0, wxALIGN_CENTER_VERTICAL|wxALL, 5 );
		
		
		$fgSizer->Add( $bSizerOperator, 1, wxEXPAND, 0 );
		
		
		$bSizerMain->Add( $fgSizer, 1, wxALL|wxEXPAND, 5 );
		
		
		$bSizer5->Add( $bSizerMain, 1, wxEXPAND, 5 );
		
		$bSizerPreview = new wxBoxSizer( wxHORIZONTAL );
		
		$this->nbookResult = new wxNotebook( $this, wxID_ANY, wxDefaultPosition, wxDefaultSize, 0 );
		
		$bSizerPreview->Add( $this->nbookResult, 1, wxEXPAND | wxALL, 5 );
		
		
		$bSizer5->Add( $bSizerPreview, 10, wxALIGN_TOP|wxEXPAND, 5 );
		
		
		$this->SetSizer( $bSizer5 );
		$this->Layout();
		
		$this->Centre( wxBOTH );
	}
	
	
	function __destruct( ){
	}
	
}

?>
