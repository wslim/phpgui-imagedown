<?php

/*
* PHP code generated with wxFormBuilder (version Jan 23 2018)
* http://www.wxformbuilder.org/
*
* PLEASE DO *NOT* EDIT THIS FILE!
*/

/*
 * Class ImageBasePanel
 */

class ImagePanelBase extends wxPanel {
	
	function __construct( $parent=null ){
		parent::__construct( $parent, wxID_ANY, wxDefaultPosition, new wxSize( 120,170 ), wxDOUBLE_BORDER|wxTAB_TRAVERSAL );
		
		$bSizer6 = new wxBoxSizer( wxVERTICAL );
		
		$this->staBmpImg = new wxStaticBitmap( $this, wxID_ANY, wxNullBitmap, wxDefaultPosition, wxDefaultSize, 0 );
		$bSizer6->Add( $this->staBmpImg, 1, wxALIGN_TOP|wxALL|wxEXPAND, 5 );
		
		$this->staTxtTitle = new wxStaticText( $this, wxID_ANY, "MyLabel", wxDefaultPosition, wxDefaultSize, wxALIGN_CENTRE );
		$this->staTxtTitle->Wrap( -1 );
		$bSizer6->Add( $this->staTxtTitle, 0, wxALIGN_BOTTOM|wxALL|wxEXPAND, 5 );
		
		$bSizer7 = new wxBoxSizer( wxHORIZONTAL );
		
		$this->chkBox = new wxCheckBox( $this, wxID_ANY, wxEmptyString, wxDefaultPosition, wxDefaultSize, 0 );
		$this->chkBox->SetValue(True); 
		$bSizer7->Add( $this->chkBox, 0, wxALL, 5 );
		
		$this->staTxtFilename = new wxStaticText( $this, wxID_ANY, "MyLabel", wxDefaultPosition, wxDefaultSize, 0 );
		$this->staTxtFilename->Wrap( -1 );
		$bSizer7->Add( $this->staTxtFilename, 0, wxALL, 5 );
		
		
		$bSizer6->Add( $bSizer7, 0, wxEXPAND, 5 );
		
		
		$this->SetSizer( $bSizer6 );
		$this->Layout();
	}
	
	
	function __destruct( ){
	}
	
}

?>
