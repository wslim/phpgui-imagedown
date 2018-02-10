<?php
use Wslim\Tool\ImageDownTool;
use Wslim\Util\DataHelper;

class ImageDownFrame extends ImageDownFrameBase
{
    /**
     * img array
     * 
     * @var array
     */
    protected $imgs;

    /**
     * img filter
     * 
     * @var array
     */
    protected $filter = [
        'width' => 0,
        'height' => 0,
        'size' => 0,
        'include' => ''
    ];

    protected $lastSaveDir;

    /**
     *
     * @var wxStatusBar
     */
    protected $statusBar;

    /**
     *
     * @var wxTextCtrl
     */
    protected $txtUrl;

    /**
     *
     * @var wxTextCtrl
     */
    protected $txtUrlReplace;

    /**
     *
     * @var wxTextCtrl
     */
    protected $txtInclude;
    
    /**
     * 
     * @var wxCheckBox
     */
    protected $chkIncludeTitle;

    /**
     *
     * @var wxDirPickerCtrl
     */
    protected $pickerSaveDir;

    /**
     *
     * @var wxButton
     */
    protected $btnTestUrl;

    /**
     *
     * @var wxButton
     */
    protected $btnPreview;

    /**
     *
     * @var wxButton
     */
    protected $btnPreviewAll;

    /**
     *
     * @var wxButton
     */
    protected $btnSave;
    
    /**
     * 
     * @var wxChoice
     */
    protected $sleSaveFile;
    
    /**
     * 
     * @var wxNotebook
     */
    protected $nbookResult;
    
    /**
     *
     * @var wxTextCtrl
     */
    protected $txtUrlResult;
    
    /**
     * 
     * @var wxScrolledWindow
     */
    protected $swinImgResult;
    
    protected $gSizerImgResult;

    public function __construct($parent = null)
    {
        parent::__construct($parent);
        
        // url and urlReplace
        $this->txtUrl->SetValue("https://wslim.cn/article/{id}");
        $this->txtUrlReplace->SetValue("id=5-9");
        
        // savedir
        $this->lastSaveDir = dirname(__DIR__) . '/tmp/';
        // $this->pickerSaveDir->SetInitialDirectory(__DIR__); // 不支持
        $this->pickerSaveDir->SetPath($this->lastSaveDir);
        $this->pickerSaveDir->Connect(wxEVT_DIRPICKER_CHANGED, array(
            $this,
            'onSaveDirChanged'
        ));
        
        $this->btnTestUrl->Connect(wxEVT_COMMAND_BUTTON_CLICKED, array(
            $this,
            'onTestUrlClick'
        ));
        
        $this->btnPreview->Connect(wxEVT_COMMAND_BUTTON_CLICKED, array(
            $this,
            'onPreviewClick'
        ));
        $this->btnPreviewAll->Connect(wxEVT_COMMAND_BUTTON_CLICKED, array(
            $this,
            'onPreviewAllClick'
        ));
        
        $this->btnSave->Connect(wxEVT_COMMAND_BUTTON_CLICKED, array(
            $this,
            'onSaveClick'
        ));
        
        // result
        $this->txtUrlResult = new wxTextCtrl($this->nbookResult, wxID_ANY, wxEmptyString, wxDefaultPosition, wxDefaultSize, wxTE_MULTILINE );
        $this->swinImgResult = new wxScrolledWindow($this->nbookResult, wxID_ANY, wxDefaultPosition, wxDefaultSize, wxHSCROLL|wxVSCROLL);
        $this->swinImgResult->SetScrollRate( 20, 20 );
        $this->gSizerImgResult = new wxGridSizer(6,0,0);
        
        $this->nbookResult->AddPage($this->txtUrlResult, 'url');
        $this->nbookResult->AddPage($this->swinImgResult, 'img');
        
        $this->statusBar->SetStatusText("welcome");
    }

    public function onTestUrlClick()
    {
        $urls = $this->txtUrl->GetValue();
        $replace = $this->txtUrlReplace->GetValue();
        $urls = ImageDownTool::parseUrl($urls, $replace);
        
        $source = implode("\r\n", $urls);
        $this->txtUrlResult->SetValue($source);
        $this->nbookResult->SetSelection(0);
    }

    public function onPreviewClick()
    {
        static::previewImages(5);
    }
    
    public function onPreviewAllClick()
    {
        static::previewImages(1000);
    }
    
    public function previewImages($count=5)
    {
        $this->swinImgResult->DestroyChildren();
        $this->gSizerImgResult->Clear();
        
        $imgs = static::getFilterImgs();
        if (!$imgs) {
            $this->statusBar->SetStatusText("images after filer: 0");
            return;
        }
        
        // get $count 
        $preImgs = array_slice($imgs, 0, $count);
        
        // save image to temp
        //$save_dir = wxGetUserHome() . '/tmp';
        $save_dir = $this->pickerSaveDir->GetPath();
        $res = ImageDownTool::saveImages($preImgs, $save_dir);
        if ($res['errcode'] || !$res['imgs']) {
            $this->statusBar->SetStatusText("save image failure");
            return;
        }
        
        $panels = [];
        $width  = floor($this->nbookResult->GetSize()->GetWidth()/6) - 20;
        if ($width < 50) $width = 120;
        foreach ($res['imgs'] as $k => $item) {
            if (!isset($item['save_path'])) continue;
            
            $wximg = new wxImage($item['save_path']);
            $height = floor($wximg->GetHeight()/$wximg->GetWidth() * $width);
            $wximg = $wximg->Rescale($width, $height);
            
            $panels[$k] = new ImagePanel($this->swinImgResult);
            $panels[$k]->SetId(wxID_ANY);
            //$panels[$k]->SetSize($width, $height + 60);
            $panels[$k]->SetMinSize(new wxSize($width-10, $height+60));
            
            $panels[$k]->staBmpImg->SetBitmap(new wxBitmap($wximg));
            $panels[$k]->staTxtTitle->SetLabelText($item['title']);
            $panels[$k]->staTxtFilename->SetLabelText($item['filename']);
            $panels[$k]->chkBox->SetValue(true);
            
            $this->gSizerImgResult->Add($panels[$k], 1, wxALL|wxEXPAND|wxALIGN_TOP, 10);
        }
        //$this->swinImgResult->SetScrollbar(1, 1, 1, 1);
        
        $this->swinImgResult->SetSizer( $this->gSizerImgResult);
        $this->swinImgResult->Layout();
        $this->gSizerImgResult->Fit($this->swinImgResult);
        
        $this->nbookResult->SetSelection(1);
    }
    
    public function onSaveDirChanged()
    {
        $save_dir = $this->pickerSaveDir->GetPath();
        $this->lastSaveDir = $save_dir;
        //$this->pickerSaveDir->SetPath($save_dir);
    }
    
    public function onSaveClick()
    {
        $imgs = static::getFilterImgs();
        if (!$imgs) return;
        
        $save_dir = $this->pickerSaveDir->GetPath(); 
        $save_dir = DataHelper::fromLocalePath($save_dir);
        $save_type = $this->sleSaveFile->GetString($this->sleSaveFile->GetSelection());
        $options = [
            'save_type' => $save_type
        ];
        
        $res = ImageDownTool::saveImages($imgs, $save_dir, $options);
        
        $this->statusBar->SetStatusText($res['errmsg']);
    }
    
    private function getFilterImgs()
    {
        $urls = $this->txtUrl->GetValue();
        $replace = $this->txtUrlReplace->GetValue();
        $includeTitle = $this->chkIncludeTitle->GetValue();
        
        $this->filter['include'] = DataHelper::fromLocalePath($this->txtInclude->GetValue());
        $this->imgs = ImageDownTool::getImages($urls, [
            'replace' => $replace
        ]);
        
        if ($includeTitle && $this->imgs && !$this->filter['include']) {
            $this->filter['include'] = ImageDownTool::getPageTitle($this->imgs);
            $this->txtInclude->SetValue($this->filter['include']);
        }
        
        if (!$this->imgs) {
            wxMessageBox("get images failure or no images");
            $this->statusBar->SetStatusText("get images failure");
            return;
        }
        
        $imgs = ImageDownTool::filterImages($this->imgs, $this->filter);
        if (!$imgs) {
            $this->statusBar->SetStatusText("images after filer: 0");
        }
        
        return $imgs;
    }
}
