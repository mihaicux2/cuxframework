<?php

namespace CuxFramework\components\pdf;

use CuxFramework\utils\CuxBaseObject;

class CuxPdf extends CuxBaseObject {
    
    // mode
    const MODE_BLANK = '';
    const MODE_CORE = 'c';
    const MODE_UTF8 = 'utf-8';

    // format
    const FORMAT_A3 = 'A3';
    const FORMAT_A4 = 'A4';
    const FORMAT_LETTER = 'Letter';
    const FORMAT_LEGAL = 'Legal';
    const FORMAT_FOLIO = 'Folio';
    const FORMAT_LEDGER = 'Ledger-L';
    const FORMAT_TABLOID = 'Tabloid';

    // orientation
    const ORIENT_PORTRAIT = 'P';
    const ORIENT_LANDSCAPE = 'L';

    // output destination
    const DEST_BROWSER = 'I';
    const DEST_DOWNLOAD = 'D';
    const DEST_FILE = 'F';
    const DEST_STRING = 'S';

    /**
     * @var string specifies the mode of the new document. If the mode is set by passing a country/language string,
     * this may also set: available fonts, text justification, and directionality RTL.
     */
    public $mode = self::MODE_CORE;

    /**
     * @var string|array, the format can be specified either as a pre-defined page size,
     * or as an array of width and height in millimetres.
     */
    public $format = self::FORMAT_A4;

    /**
     * @var int sets the default document font size in points (pt)
     */
    public $defaultFontSize = 0;

    /**
     * @var string sets the default font-family for the new document. Uses default value set in defaultCSS
     * unless codepage has been set to "win-1252". If codepage="win-1252", the appropriate core Adobe font
     * will be set i.e. Helvetica, Times, or Courier.
     */
    public $defaultFont = '';

    /**
     * @var float sets the page left margin for the new document. All values should be specified as LENGTH in millimetres.
     * If you are creating a DOUBLE-SIDED document, the margin values specified will be used for ODD pages; left and right margins
     * will be mirrored for EVEN pages.
     */
    public $marginLeft = 15;

    /**
     * @var float sets the page right margin for the new document (in millimetres).
     */
    public $marginRight = 15;

    /**
     * @var float sets the page top margin for the new document (in millimetres).
     */
    public $marginTop = 16;

    /**
     * @var float sets the page bottom margin for the new document (in millimetres).
     */
    public $marginBottom = 16;

    /**
     * @var float sets the page header margin for the new document (in millimetres).
     */
    public $marginHeader = 9;

    /**
     * @var float sets the page footer margin for the new document (in millimetres).
     */
    public $marginFooter = 9;

    /**
     * @var string specifies the default page orientation of the new document.
     */
    public $orientation = self::ORIENT_PORTRAIT;

    /**
     * @var string css file to prepend to the PDF
     */
    public $cssFile = 'css/mpdf-bootstrap.css';

    /**
     * @var string additional inline css to append after the cssFile
     */
    public $cssInline = '';

    /**
     * @var string the output filename
     */
    public $filename = '';

    /**
     * @var string the output destination
     */
    public $destination = self::DEST_BROWSER;

    /**
     * @var array the mPDF methods that will called in the sequence listed before
     * rendering the content. Should be an associative array of $method => $params
     * format, where:
     * - `$method`: string, is the mPDF method / function name
     * - `$param`: mixed, are the mPDF method parameters
     */
    public $methods = '';

    /**
     * @var string the mPDF configuration options entered as a `$key => value`
     * associative array, where:
     * - `$key`: string is the mPDF configuration property name
     * - `$value`: mixed is the mPDF configured property value
     */
    public $options = [];

    /**
     * @var mPDF api instance
     */
    private $_mpdf;
    
    public function config(array $config) {
        parent::config($config);
        
        $this->_mpdf = new \Mpdf\Mpdf(array(
            'mode' => $this->mode,
            'format' => $this->format,
            'default_font_size' => $this->defaultFontSize,
            'default_font' => $this->defaultFont,
            'margin_left' => $this->marginLeft,
            'margin_right' => $this->marginRight,
            'margin_top' => $this->marginTop,
            'margin_bottom' => $this->marginBottom,
            'margin_header' => $this->marginHeader,
            'margin_footer' => $this->marginFooter,
            'orientation' => $this->orientation
        ));
    }
    
    public function output(string $content = '', string $file = '', $dest = self::DEST_BROWSER){
        
        if (!empty($this->cssFile) || !empty($this->cssInline)) {
            if (!empty($this->cssFile) && file_exists($this->cssFile) && is_readable($this->cssFile)){
                $stylesheet = file_get_contents($this->cssFile);
                $this->_mpdf->WriteHTML($stylesheet, 1);
            }
            if (!empty($this->cssInline)){
                $this->_mpdf->WriteHTML($this->cssInline, 1);
            }
            $this->_mpdf->WriteHTML($content, 2);
        } else {
            $this->_mpdf->WriteHTML($content);
        }
        
        return $this->_mpdf->Output($file, $dest);
        
    }
    
}
