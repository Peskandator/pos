<?php

namespace App\Console\Command;

use App\Utils\SrcDir;
use KubAT\PhpSimple\HtmlDomParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use thiagoalessio\TesseractOCR\TesseractOCR;


/**
 * Check all orders and generate invoice if applicable.
 */
class SearchDocumentsCommand extends Command
{
    protected static $defaultName = 'search:documents';
    protected SrcDir $srcDir;


    public function __construct(SrcDir $srcDir)
    {
        $this->srcDir = $srcDir;
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName(self::$defaultName);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
//        $needles = ['50' => ['Hana Truhlářová', 'Václav Dvořák']];
        $needles = ['50' => ['k neprodlenému odstranění důvodu', 'Václav Dvořák']];

        $urlParam = "https://www.c-budejovice.cz/uredni-deska";
        $detailCommonUrl = "https://www.c-budejovice.cz";
        $boardUrl = $urlParam;
        if ($stream = fopen($boardUrl, 'r')) {
            $pageData = stream_get_contents($stream);
            $tableBody = $this->getTableBody($pageData);
            $rows = $this->contentBetweenTags($tableBody, 'tr');
            $announcements = $this->processRows($detailCommonUrl, $rows);
            $matches = [];

            $count = 0;
            foreach ($announcements as $announcement) {
                $detailUrl = $announcement['url'];
//                if ($detailUrl === 'https://www.c-budejovice.cz/144642024-stanoveni-prechodne-upravy-provozu-l-m-parizka-v-ceskych-budejovicich-vyhrazene-parkovani') {

                var_dump($detailUrl);
                if ($count < 5) {
                    $announcement['documents'] = $this->scanDetailForDocuments($detailUrl);
                    $matches = $this->scanDocuments($announcement, $needles, $matches);
                }
                $count++;

//                }
            }
            var_dump($matches);

            fclose($stream);
        }
        return 0;
    }

    protected function getTableBody(string $pageContents): string
    {
        $pattern = '/<tbody>(.*)<\/tbody>/s';
        preg_match_all($pattern, $pageContents, $matches);

        return $matches[0][0] ?? "";
    }

    protected function contentBetweenTags($content, $tagname): array
    {
        $pattern = "#<\s*?$tagname\b[^>]*>(.*?)</$tagname\b[^>]*>#s";
        preg_match_all($pattern, $content, $matches);

        if(empty($matches)) {
            return [];
        }

        return $matches[1] ?? [];
    }

    protected function getLink($content): ?string
    {
        $pattern = '#href="[^"]*"#i';

        preg_match($pattern, $content, $matches);

        if(empty($matches)) {
            return "";
        }
        $match = $matches[0];
        $trimmed = str_replace(['href=', '"'], '', $match);
        return $trimmed;
    }

    protected function processRows(string $boardUrl, array $rows): array
    {
        $annoucements = [];
        foreach ($rows as $row) {
            $announcement = [];
            $cells = $this->contentBetweenTags($row, 'td');


            $titleCell = $cells[0];
            $titleContent = $this->contentBetweenTags($titleCell, 'a');
            $announcement['title'] = $titleContent[0];
            $announcement['url'] = $boardUrl . $this->getLink($cells[0]);

            $dates = $this->contentBetweenTags($cells[1], 'span');
            $announcement['startDate'] = $dates[0];
            $announcement['endDate'] = $dates[1];

            $announcement['category'] = $cells[2];
            $announcement['institution'] = $cells[3];
            $annoucements[] = $announcement;
        }

        return $annoucements;
    }

    protected function scanDetailForDocuments($url): array
    {
        $documents = [];

        if ($stream = fopen($url, 'r')) {
            $pageData = stream_get_contents($stream);
            fclose($stream);
        }

        $dom = HtmlDomParser::str_get_html($pageData);

        $found = $dom->find('.file-list .file-item .file a');

        foreach ($found as $finding) {
            $documentUrl = $finding->href;
            $documents[] = $documentUrl;
        }

        return $documents;
    }

    /**
     * @throws \Spatie\PdfToImage\Exceptions\PdfDoesNotExist
     */
    protected function scanDocuments(array $announcement, array $needles, array $matches): array
    {
//        $parser = new \Smalot\PdfParser\Parser();
//            $pdf = $parser->parseContent($fileContent);
//            $text = $pdf->getText();


        $filesDir = $this->srcDir->getUploadsDir() . '/files';
        $tempFile = $filesDir . '/tempfile.pdf';
        $tempImageDir = $filesDir . '/tempimages';


        $documents = $announcement['documents'];

        foreach ($documents as $document) {
            $fileContent = file_get_contents($document);

            if ($tempFileStream = fopen($tempFile, 'w')) {
                fwrite($tempFileStream, $fileContent);
                fclose($tempFileStream);
            }

            $pdfToImageParser = new \Spatie\PdfToImage\Pdf($tempFile);
            $pageCount = $pdfToImageParser->getNumberOfPages();
            $pdfToImageParser->saveAllPagesAsImages($tempImageDir);

            for ($i = 1; $i <= $pageCount; $i++) {
                $filePath = $tempImageDir . '/' . $i . '.jpg';
                $text = (new TesseractOCR($filePath))
                    ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe')
//                    ->whitelist(range('A', 'Z'))
                    ->lang('ces', 'eng')
                    ->run()
                ;

                foreach ($needles as $userId => $userNeedles) {
//                    var_dump($needles);
                    foreach ($userNeedles as $needle) {
//                        var_dump($needle);
//                        $pattern = "#$needle#";
//                        var_dump($text);
//                        preg_match($pattern, $text, $pesek);
//                        var_dump($pesek);

                        if (str_contains($text, $needle)) {
                            if (!isset($matches[$userId][$announcement['title']]['needles']) || !in_array($needle, $matches[$userId][$announcement['title']]['needles']))
                            {
                                $matches[$userId][$announcement['title']]['needles'][] = $needle;
                            }
                            $matches[$userId][$announcement['title']]['data'] = $announcement;
                        }
                    }
                }
            }
        }

        return $matches;
    }
}
