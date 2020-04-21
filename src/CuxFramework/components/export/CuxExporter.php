<?php

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

class CuxExporter extends CuxBaseObject {

    public function config(array $config) {
        parent::config($config);
    }
    
    public function createWriter(string $fileName, string $type = Type::XLSX, bool $directDownload = true) : WriterMultiSheetsAbstract{
        $writer = WriterFactory::createFromType(Type::XLSX);
        if ($directDownload) {
            $writer->openToBrowser($fileName);
        } else {
            $writer->openToFile($fileName);
        }
        return $writer;
    }

    public function getBorderedStyle(): Style {
        $border = (new BorderBuilder())
                ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->build();

        $style = (new StyleBuilder())
                ->setBorder($border)
                ->setShouldWrapText()
                ->build();

        return $style;
    }

    public function getHeaderStyle(): Style {
        $border = (new BorderBuilder())
                ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->setBorderTop(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->setBorderLeft(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->setBorderRight(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
                ->build();

        $style = (new StyleBuilder())
                ->setBorder($border)
                ->setFontBold()
                ->setShouldWrapText()
                ->build();

        return $style;
    }

    public function createRowFromArray(array $rowData = array(), Style $style = null){
        return WriterEntityFactory::createRowFromArray($rowData, $style);
    }
    
}
