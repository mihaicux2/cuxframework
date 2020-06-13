<?php
/**
 * CuxExporter class file
 * 
 * @package Components
 * @subpackage Export
 * @author Mihail Cuculici <mihai.cuculici@gmail.com>
 * @version 0,9
 * @since 2020-06-13
 */

namespace CuxFramework\components\export;

//require("vendor/box/spout/src/Spout/Autoloader/autoload.php");

use Box\Spout\Common\Type;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Box\Spout\Writer\WriterMultiSheetsAbstract;
use Box\Spout\Common\Entity\Style\Style;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\CuxBase;

/**
 * Class used as a minimum wrapper for the Spout EXCEL document writer
 */
class CuxExporter extends CuxBaseObject {

    /**
     * Excel style for simple bordered data
     * @var Box\Spout\Common\Entity\Style 
     */
    private static $_borderedStyle;
    
    /**
     * Excel style for ERROR data
     * @var Box\Spout\Common\Entity\Style
     */
    private static $_errorStyle;
    
    /**
     * Excel style for HEADER data
     * @var Box\Spout\Common\Entity\Style
     */
    private static $_headerStyle;

    /**
     * Setup object instance properties
     * @param array $config
     */
    public function config(array $config) {
        parent::config($config);
    }

    /**
     * Generate an EXCEL file writer
     * @param string $fileName
     * @param string $type
     * @param bool $directDownload
     * @return WriterMultiSheetsAbstract
     */
    public function createWriter(string $fileName, string $type = Type::XLSX, bool $directDownload = true): WriterMultiSheetsAbstract {
        $writer = WriterFactory::createFromType(Type::XLSX);
        if ($directDownload) {
            $writer->openToBrowser($fileName);
        } else {
            $writer->openToFile($fileName);
        }
        return $writer;
    }

    /**
     * Getter for the $_borderdStyle property
     * @return Style
     */
    public function getBorderedStyle(): Style {
        if (is_null(static::$_borderedStyle)) {
            $border = (new BorderBuilder())
                    ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->build();

            static::$_borderedStyle = (new StyleBuilder())
                    ->setBorder($border)
                    ->setShouldWrapText()
                    ->build();
        }

        return static::$_borderedStyle;
    }

    /**
     * Getter for the $_errorStyle property
     * @return Style
     */
    public function getErrorStyle(): Style {
        if (is_null(static::$_errorStyle)) {
            $border = (new BorderBuilder())
                    ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->build();

            static::$_errorStyle = (new StyleBuilder())
                    ->setBorder($border)
                    ->setFontColor(Color::RED)
                    ->setFontBold()
                    ->setFontItalic()
                    ->setShouldWrapText()
                    ->build();
        }

        return static::$_errorStyle;
    }
    
    /**
     * Getter for the $_headerStyle property
     * @return Style
     */
    public function getHeaderStyle(): Style {
        if (is_null(static::$_headerStyle)){
            $border = (new BorderBuilder())
                    ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                    ->build();

            static::$_headerStyle = (new StyleBuilder())
                    ->setBorder($border)
                    ->setFontBold()
                    ->setShouldWrapText()
                    ->build();
            }

        return static::$_headerStyle;
    }

    /**
     * Create a new EXCEL row, using the provided cell data
     * @param array $rowData
     * @param Style $style
     * @return type
     */
    public function createRowFromArray(array $rowData = array(), Style $style = null) {
        return WriterEntityFactory::createRowFromArray($rowData, $style);
    }

}
