<?php

namespace CuxFramework\components\export;

//require("vendor/spout/src/Spout/Autoloader/autoload.php");

use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\AbstractMultiSheetsWriter;
use Box\Spout\Writer\Style\Style;

use CuxFramework\utils\CuxBaseObject;
use CuxFramework\utils\CuxBase;

class CuxExporter extends CuxBaseObject {

    public function config(array $config) {
        parent::config($config);
    }

    public function createWriter(string $fileName, string $type = Type::XLSX, bool $directDownload = true) : AbstractMultiSheetsWriter{
        $writer = WriterFactory::create(Type::XLSX);
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

}
