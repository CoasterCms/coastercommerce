<?php namespace CoasterCommerce\Core\PDF;

use CoasterCommerce\Core\Model\PDFSetting;
use FPDF;
use Illuminate\Http\Response;

class PDF extends FPDF
{

    /**
     * @var array
     */
    protected static $settings;

    /**
     * @var array
     */
    protected $_settings;

    /**
     * @var float
     */
    protected $_fontCellHeight;

    /**
     * @var int
     */
    protected $_writeOutput;

    /**
     * @var int
     */
    protected $_htmlEntitiesToWin1252;

    /**
    protected $_settings;

    /**
     * PDF constructor.
     * @param string $orientation
     * @param string $unit
     * @param string $size
     */
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        $this->_writeOutput = 1;
        $this->_htmlEntitiesToWin1252 = 1;
        $this->_loadSettings();
        parent::__construct($orientation, $unit, $size);
        $this->AddPage();
        $this->SetDefaultFont();
    }

    /**
     * @return int
     */
    public function disableWrite()
    {
        $tmp = $this->_writeOutput;
        $this->_writeOutput = 0;
        return $tmp; // return prev output
    }

    /**
     * @param int $enable
     */
    public function enableWrite($enable = 1)
    {
        $this->_writeOutput = $enable;
    }

    /**
     *
     */
    public function SetHeaderFont()
    {
        $this->SetFont('Arial','B',14);
        $this->_fontCellHeight = 7;
    }

    /**
     *
     */
    public function SetHeader2Font()
    {
        $this->SetFont('Arial',null,14);
        $this->_fontCellHeight = 7;
    }

    /**
     *
     */
    public function SetDefaultFont()
    {
        $this->SetFont('Arial',null,10);
        $this->_fontCellHeight = 5;
    }

    /**
     *
     */
    public function SetBoldFont()
    {
        $this->SetFont('Arial','B',10);
        $this->_fontCellHeight = 5;
    }


    /**
     * @param int $w
     * @param int $h
     * @param string $txt
     * @param int|string $border
     * @param int $ln
     * @param string $align
     * @param bool $fill
     * @param string $link
     */
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        if (is_null($h)) $h = $this->_fontCellHeight;
        $txt = $this->_htmlEntitiesToWin1252 ? iconv('UTF-8', 'windows-1252', html_entity_decode($txt)) : $txt;
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    /**
     * @param int $w
     * @param int $h
     * @param string $txt
     * @param int $border
     * @param string $align
     * @param bool $fill
     * @param int|int[] $padding
     */
    public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false, $padding = null)
    {
        $normPadding = $this->_normalizePadding($padding);
        // create inner content if using padding
        if (array_filter($normPadding)) {
            $x = $this->GetX();
            $y = $this->GetY();
            $this->SetXY($x + $normPadding[0], $y + $normPadding[2]);
            $tmp = $fill ? $this->disableWrite() : $this->_writeOutput; // dont actually write output as won't be seen under fill, just pretend to find $h of content
            parent::MultiCell($w - ($normPadding[0] + $normPadding[1]), null, $txt, 0, $align, 0);
            $this->enableWrite($tmp);
            $h = $this->GetY() - $y + $normPadding[3]; // get height required for outer content
            $this->SetXY($x, $y);
            $txtOverFill = $fill ? $txt : null;
            $txt = '';
        }
        // generate content or just outer box if using padding
        parent::MultiCell($w, $h, $txt, $border, $align, $fill);
        // create inner content if using padding (needs to be last if using fill)
        if (isset($txtOverFill)) {
            $this->SetXY($x + $normPadding[0], $y + $normPadding[2]);
            parent::MultiCell($w - ($normPadding[0] + $normPadding[1]), null, $txtOverFill, 0, $align, 0);
            $this->SetY($this->GetY() + $normPadding[3], false);
        }
    }

    /**
     * @param int $w
     * @param int $h
     * @param string $txt
     * @param int $border
     * @param string $align
     * @param bool $fill
     * @param int|int[] $padding
     */
    public function MultiLineCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false, $padding = null)
    {
        $x = $this->GetX(); // get current x point
        $txtLines = explode("\n", $txt);
        $outerBorder = $border === 'O';
        $lastIndex = count($txtLines) - 1;
        $normPadding = $this->_normalizePadding($padding);
        foreach ($txtLines as $i => $txtLine) {
            $padding = $normPadding;
            $padding[2] = ($i === 0) ? $normPadding[2] : 0;
            $padding[3] = ($i === $lastIndex) ? $normPadding[3] : 0;
            if ($outerBorder) {
                $border = 'LR' . ($i === 0 ? 'T' : '') . ($i === $lastIndex ? 'B' : '');
            }
            $this->MultiCell($w, $h, $txtLine, $border, $align, $fill, $padding);
            $this->SetX($x); // always use same x point
        }
    }

    /**
     * padding
     * x y
     * left right top bottom
     * @param array $padding
     * @return array
     */
    protected function _normalizePadding($padding)
    {
        $padding = is_array($padding) ? $padding : [(int) $padding];
        $arraySize = count($padding);
        switch ($arraySize) {
            case 2:
                $normalizedPadding = [$padding[0], $padding[0], $padding[1], $padding[1]];
                break;
            case 4:
                $normalizedPadding = $padding;
                break;
            default:
                $normalizedPadding = array_fill(0, 4, $padding[0]);
        }
        return $normalizedPadding;
    }

    protected function _out($s)
    {
        if ($this->_writeOutput) {
            parent::_out($s);
        }
    }

    /**
     * Update XY and Font
     * @param null $y
     */
    public function resetCursor($y = null)
    {
        $this->SetDefaultFont(); // reset to default font
        $this->SetXStart(0,is_null($y) ? $this->GetY() : $y); // change Y and reset X to left margin
    }

    /**
     * @return float
     */
    public function GetXStart()
    {
        return $this->lMargin;
    }

    /**
     * @param float $offset
     * @param float $y
     */
    public function SetXStart($offset = 0.0, $y = null)
    {
        $this->x = $this->getXStart() + $offset;
        $this->y = is_null($y) ? $this->y : $y;
    }

    /**
     * @return float
     */
    public function GetXEnd()
    {
        return $this->w - $this->rMargin;
    }

    /**
     * @param float $offset
     * @param float $y
     */
    public function SetXEnd($offset = 0.0, $y = null)
    {
        $this->x = $this->getXEnd() + $offset;
        $this->y = is_null($y) ? $this->y : $y;
    }

    /**
     * @return float
     */
    public function GetYStart()
    {
        return $this->tMargin;
    }

    /**
     * @param float $offset
     */
    public function SetYStart($offset = 0.0)
    {
        $this->y = $this->getYStart() + $offset;
    }

    /**
     * @return float
     */
    public function GetYEnd()
    {
        return $this->y - $this->bMargin;
    }

    /**
     * @param float $offset
     */
    public function SetYEnd($offset = 0.0)
    {
        $this->y = $this->getYEnd() + $offset;
    }

    /**
     * @return string
     */
    public function GetWritableWidth()
    {
        return $this->w - ($this->lMargin + $this->rMargin);
    }

    /**
     * @param string $file
     * @return float
     */
    public function GetImageWidth($file)
    {
        if (array_key_exists($file, $this->images)) {
            $info = $this->images[$file];
            $w = 96; // dpi used in Image()
            return $info['w']*72/$w/$this->k;
        }
        return 0;
    }

    /**
     * @param string $file
     * @return float
     */
    public function GetImageHeight($file)
    {
        if (array_key_exists($file, $this->images)) {
            $info = $this->images[$file];
            $h = 96; // dpi used in Image()
            return $info['h']*72/$h/$this->k;
        }
        return 0;
    }

    /**
     * @param string $fileName
     * @param bool $isDownload
     * @return array
     */
    protected function _responseHeaders($fileName, $isDownload)
    {
        $headers = [
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public'
        ];
        if ($isDownload) {
            $headers['Content-Type'] = 'application/x-download';
            $headers['Content-Disposition'] = 'attachment; filename="' . $fileName . '"';
        } else {
            $headers['Content-Type'] = 'application/pdf';
            $headers['Content-Disposition'] = 'inline; filename="' . $fileName . '"';
        }
        return $headers;
    }

    /**
     * Load settings from db
     */
    protected function _loadSettings()
    {
        if (is_null(static::$settings)) {
            static::$settings = PDFSetting::all()->pluck('value', 'setting')->toArray();
        }
        $this->_settings = static::$settings;
    }

    /**
     * @param string $setting
     * @return mixed
     */
    public function getSetting($setting)
    {
        return array_key_exists($setting, $this->_settings) ? $this->_settings[$setting] : null;
    }

    /**
     * @param string $setting
     * @param mixed $value
     */
    public function setSetting($setting, $value)
    {
        $this->_settings[$setting] = $value;
    }

    /**
     * @param string $fileName
     * @param bool $isDownload
     * @return Response
     */
    public function GenerateResponse($fileName = 'doc.pdf', $isDownload = false)
    {
        $this->Close();
        $this->_checkoutput();
        return new Response($this->buffer, 200, $this->_responseHeaders($fileName, $isDownload));
    }

}
