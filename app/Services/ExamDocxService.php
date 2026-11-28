<?php

namespace App\Services;

use App\Models\ExamPackage;
use App\Models\ExamQuestion;
use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ExamDocxService
{
    private const MAX_DOCX_UNCOMPRESSED_BYTES = 26214400; // 25 MB zip-bomb guard
    private const ALLOWED_IMAGE_EXTS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    private const SECTION_TITLES = [
        'pg' => ['roman' => 'I', 'label' => 'PILIHAN GANDA', 'tag' => '[PG]', 'desc' => 'Pilihlah satu jawaban yang paling tepat!'],
        'pg_complex' => ['roman' => 'II', 'label' => 'PILIHAN GANDA KOMPLEKS', 'tag' => '[PGK]', 'desc' => 'Pilih lebih dari satu jawaban yang benar!'],
        'true_false' => ['roman' => 'III', 'label' => 'BENAR / SALAH', 'tag' => '[BENAR_SALAH]', 'desc' => 'Tentukan Benar (B) atau Salah (S)!'],
        'matching' => ['roman' => 'IV', 'label' => 'MENJODOHKAN', 'tag' => '[MENJODOHKAN]', 'desc' => 'Pasangkan pernyataan dengan jawaban yang sesuai!'],
        'short_answer' => ['roman' => 'V', 'label' => 'ISIAN SINGKAT', 'tag' => '[ISIAN]', 'desc' => 'Isilah titik-titik dengan jawaban yang singkat dan tepat!'],
        'essay' => ['roman' => 'VI', 'label' => 'URAIAN / ESSAY', 'tag' => '[ESSAY]', 'desc' => 'Jawablah pertanyaan berikut dengan jelas dan lengkap!'],
    ];

    private const DEFAULT_WEIGHTS = [
        'pg' => 1.00,
        'pg_complex' => 3.00,
        'true_false' => 2.00,
        'matching' => 3.00,
        'short_answer' => 2.00,
        'essay' => 5.00,
    ];

    private const ARABIC_OPTION_MAP = [
        'أ' => 'A',
        'ا' => 'A',
        'إ' => 'A',
        'آ' => 'A',
        'ب' => 'B',
        'ج' => 'C',
        'ت' => 'C',
        'د' => 'D',
        'ث' => 'D',
        'هـ' => 'E',
        'ه' => 'E',
    ];

    /**
     * Generate a .docx binary file path (temp file) either as blank Template Naskah Soal
     * or exported from an existing ExamPackage.
     */
    public function createDocxFile(?ExamPackage $package = null, bool $isTemplate = false): string
    {
        $mediaFiles = []; // [['relId' => 'rId100', 'zipPath' => 'word/media/img_1.png', 'srcPath' => '...', 'ext' => 'png', 'widthEmu' => ..., 'heightEmu' => ...]]
        $nextRelNum = 100;

        $bodyXml = '';

        if ($isTemplate || !$package) {
            $bodyXml .= $this->buildHeaderBannerXml(
                'TEMPLATE NASKAH SOAL & KISI-KISI (.DOCX)',
                'MTs AL-HASANAH — FORMAT SIAP IMPORT KE BANK SOAL'
            );
            $bodyXml .= $this->buildInstructionBoxXml([
                'PETUNJUK PENGISIAN TEMPLATE:',
                '1. Tulis soal di bawah penanda bagian tipe soal ([PG], [PGK], [BENAR_SALAH], [MENJODOHKAN], [ISIAN], atau [ESSAY]).',
                '2. Nomor soal ditulis berurutan dengan format "1. Teks soal..." lalu pilihan "A. ...", "B. ...", "C. ...", "D. ...".',
                '3. Baris [Kunci: ...], [Materi: ...], [Indikator: ...], dan [Level: L1 | Kesukaran: mudah | Bobot: 1] bersifat opsional (jika belum diisi, tetap bisa diimpor lalu dilengkapi di aplikasi).',
                '4. Mendukung tulisan Arab (UTF-8), cetak tebal/miring/garis bawah, pangkat/indeks, serta gambar yang ditempel langsung pada soal.',
            ]);

            $bodyXml .= $this->buildSampleTemplateBodyXml();
        } else {
            $school = SchoolSetting::getSetting();
            $schoolName = strtoupper($school->school_name ?? 'MTs AL-HASANAH');
            $subjectName = $package->subject?->name ?? 'Mata Pelajaran';
            $classLabel = $package->target_grade
                ? ('Kelas ' . $package->target_grade . ' (Global)')
                : ('Kelas ' . ($package->classRoom?->name ?? '-'));
            $examTypeLabel = strtoupper($package->exam_type ?? 'UJIAN');
            $semLabel = ucfirst($package->semester ?? 'Ganjil');

            $bodyXml .= $this->buildHeaderBannerXml(
                "{$examTypeLabel} — {$package->title}",
                "{$schoolName} | {$subjectName} | {$classLabel} | Semester {$semLabel}"
            );

            $grouped = $package->questions()
                ->orderBy('question_number')
                ->get()
                ->groupBy(fn($q) => $q->question_type ?: 'pg');

            foreach (self::SECTION_TITLES as $typeKey => $meta) {
                $qs = $grouped->get($typeKey);
                if (!$qs || $qs->isEmpty()) {
                    continue;
                }

                $bodyXml .= $this->buildSectionBlockXml($typeKey, $qs, $meta, $mediaFiles, $nextRelNum);
            }
        }

        $bodyXml .= '<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1080" w:right="1080" w:bottom="1080" w:left="1080"/></w:sectPr>';

        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
            . 'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
            . 'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
            . 'xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<w:body>' . $bodyXml . '</w:body>'
            . '</w:document>';

        $tmpPath = tempnam(sys_get_temp_dir(), 'exam_docx_');
        if ($tmpPath === false) {
            throw new \RuntimeException('Gagal membuat file sementara untuk export .docx.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal menginisialisasi arsip .docx.');
        }

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXml($mediaFiles));
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXml());
        $zip->addFromString('word/_rels/document.xml.rels', $this->buildDocumentRelsXml($mediaFiles));
        $zip->addFromString('word/styles.xml', $this->buildStylesXml());
        $zip->addFromString('word/document.xml', $documentXml);

        foreach ($mediaFiles as $m) {
            if (is_file($m['srcPath'])) {
                $zip->addFile($m['srcPath'], $m['zipPath']);
            }
        }

        $zip->close();

        return $tmpPath;
    }

    /**
     * Parse a .docx file into structured ExamQuestion items ready for preview & batch commit.
     */
    public function parseDocx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \InvalidArgumentException('File .docx tidak valid atau rusak.');
        }

        $docXml = $zip->getFromName('word/document.xml');
        if ($docXml === false) {
            $zip->close();
            throw new \InvalidArgumentException('Struktur word/document.xml tidak ditemukan di dalam file .docx.');
        }

        // Zip bomb & XML size check
        $totalUncompressed = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $totalUncompressed += ($stat['size'] ?? 0);
            if ($totalUncompressed > self::MAX_DOCX_UNCOMPRESSED_BYTES) {
                $zip->close();
                throw new \InvalidArgumentException('Ukuran konten .docx melebihi batas aman 25 MB.');
            }
        }

        $relsMap = $this->parseRelsMap($zip);
        $numberingDefs = $this->parseNumberingDefs($zip);
        $extractedImagesCache = [];

        $dom = new \DOMDocument();
        $prevUseErrors = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($docXml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prevUseErrors);

        if (!$loaded) {
            $zip->close();
            throw new \InvalidArgumentException('Gagal membaca XML isi dokumen .docx.');
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');

        $numCounters = []; // [numId => [ilvl => currentCount]]
        $blocks = [];

        // Extract top-level body nodes in order so Word tables (<w:tbl>) are parsed by row & cell
        $bodyChildren = $xpath->query('/w:document/w:body/*');
        foreach ($bodyChildren as $node) {
            if ($node->nodeName === 'w:p') {
                $blocks[] = $this->extractSingleParagraphBlock($xpath, $node, $zip, $relsMap, $numberingDefs, $numCounters, $extractedImagesCache);
            } elseif ($node->nodeName === 'w:tbl') {
                $tableBlocks = $this->extractTableBlocks($xpath, $node, $zip, $relsMap, $numberingDefs, $numCounters, $extractedImagesCache);
                foreach ($tableBlocks as $tb) {
                    $blocks[] = $tb;
                }
            }
        }

        $zip->close();

        return $this->parseParagraphBlocks($blocks);
    }

    /**
     * Parse raw pasted multi-question text (e.g. copied from Google Docs or Microsoft Word)
     * into structured ExamQuestion items.
     */
    public function parsePlainText(string $rawText): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $rawText);
        $lines = explode("\n", $normalized);
        $blocks = [];

        foreach ($lines as $line) {
            $clean = trim(str_replace("\u{00A0}", ' ', $line));
            $blocks[] = [
                'text' => $clean,
                'html' => $clean !== '' ? e($clean) : '',
                'image' => null,
            ];
        }

        return $this->parseParagraphBlocks($blocks);
    }

    /**
     * Shared state-machine parser converting ordered text/html blocks into ExamQuestion payloads.
     */
    public function parseParagraphBlocks(array $blocks): array
    {
        $questions = [];
        $current = null;
        $lastTarget = null; // 'question' | 'option:A'..'option:E'
        $currentType = 'pg';
        $inQuestionsArea = false;
        $inAnswerKeySection = false;
        $bottomKeys = []; // [qNum => answer]
        $warnings = [];

        $flushCurrent = function () use (&$current, &$questions) {
            if (!$current) {
                return;
            }
            $current = $this->finalizeQuestionItem($current);
            $questions[] = $current;
            $current = null;
        };

        $blocks = array_values($blocks);
        for ($idx = 0; $idx < count($blocks); $idx++) {
            $block = $blocks[$idx];
            if (($block['kind'] ?? '') === 'structured_table') {
                $pendingStimulus = null;
                if ($current && !empty($current['is_unnumbered_preamble'])) {
                    $pendingStimulus = trim((string) ($current['question_text'] ?? ''));
                    $current = null;
                } else {
                    $flushCurrent();
                }

                $inQuestionsArea = true;
                $currentType = $block['table_type'] ?? $currentType;
                $lastTarget = null;
                $tblItems = $block['items'] ?? [];
                if ($pendingStimulus !== null && $pendingStimulus !== '' && !empty($tblItems)) {
                    $tblItems[0]['stimulus'] = $pendingStimulus;
                }
                foreach ($tblItems as $tblItem) {
                    $questions[] = $this->finalizeQuestionItem($tblItem);
                }
                continue;
            }

            if (($block['kind'] ?? '') === 'table_rows') {
                $rawRows = $block['rows'] ?? [];
                if (in_array($currentType, ['short_answer', 'essay'], true) || $this->looksLikeArabicVocabularyTable($rawRows)) {
                    $flushCurrent();
                    $inQuestionsArea = true;
                    $targetTblType = in_array($currentType, ['short_answer', 'essay'], true) ? $currentType : 'short_answer';
                    $currentType = $targetTblType;
                    $lastTarget = null;
                    $nextNum = !empty($questions) ? (intval(end($questions)['question_number'] ?? 0) + 1) : 1;
                    $rowQuestions = $this->parseTableRowsAsQuestions($rawRows, $targetTblType, $nextNum);
                    foreach ($rowQuestions as $rq) {
                        $questions[] = $this->finalizeQuestionItem($rq);
                    }
                    continue;
                }

                // Otherwise splice flattened cell paragraphs back into the active block stream
                $flatBlocks = $this->flattenTableRowsToParagraphs($rawRows);
                if (!empty($flatBlocks)) {
                    array_splice($blocks, $idx + 1, 0, $flatBlocks);
                }
                continue;
            }

            // Split Word soft-breaks (Shift+Enter / <w:br/>) inside a single paragraph into individual line blocks
            $rawBlockText = (string) ($block['text'] ?? '');
            if (($block['kind'] ?? '') === 'paragraph' && str_contains($rawBlockText, "\n")) {
                $subBlocks = $this->splitSoftBreakParagraphBlock($block);
                if (count($subBlocks) > 1) {
                    array_splice($blocks, $idx + 1, 0, $subBlocks);
                    continue;
                }
            }

            $rawLine = trim(preg_replace('/[\x{00A0}\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]/u', ' ', $rawBlockText));
            $line = $this->normalizeLeadingArabicDigits($rawLine);
            $html = trim((string) ($block['html'] ?? ''));
            $image = $block['image'] ?? null;

            if ($line === '' && !$image) {
                continue;
            }

            // Attach standalone image line to active question
            if ($line === '' && $image) {
                if ($current && empty($current['question_image'])) {
                    $current['question_image'] = $image;
                }
                continue;
            }

            // Skip banner/template guide lines and closing footer instructions
            if ($this->isTemplateNoiseLine($line) || $this->isFooterClosingLine($line)) {
                continue;
            }

            // Detect KUNCI JAWABAN section at bottom
            if (preg_match('/^(?:\[\s*)?kunci\s+jawaban(?:\s*\])?\s*:?$/iu', $line)) {
                $flushCurrent();
                $inAnswerKeySection = true;
                continue;
            }

            if ($inAnswerKeySection) {
                $this->parseBottomAnswerKeysLine($line, $bottomKeys);
                continue;
            }

            // Detect Section Type Header (e.g. "[PG]", "I. PILIHAN GANDA", "II. URAIAN / ESSAY")
            $detectedSectionType = $this->detectSectionHeaderType($line);
            if ($detectedSectionType !== null) {
                $flushCurrent();
                $currentType = $detectedSectionType;
                $inQuestionsArea = true;
                $lastTarget = null;
                continue;
            }

            // Skip cover instruction sentences before questions start
            if (!$inQuestionsArea && empty($questions) && !$current && $this->isCoverInstructionLine($line)) {
                continue;
            }

            // Detect post-table per-number metadata line: [No. 4 | Materi: ... | Indikator: ...]
            if (!$current && !empty($questions) && preg_match('/^\[\s*No\.?\s*(\d+)\s*\|(.+)\]$/iu', $line, $metaNoMatch)) {
                $targetNo = intval($metaNoMatch[1]);
                for ($qi = count($questions) - 1; $qi >= 0; $qi--) {
                    if (intval($questions[$qi]['question_number']) === $targetNo) {
                        $this->tryApplyMetadataLine('[' . $metaNoMatch[2] . ']', $questions[$qi]);
                        break;
                    }
                }
                continue;
            }

            // Detect metadata tags ([Kunci: ...], [Materi: ...], [Indikator: ...], [Level: ...])
            if ($current && $this->tryApplyMetadataLine($line, $current)) {
                continue;
            }

            // Detect question start: e.g. "1. Alquran...", "No. 1. ..."
            if ($this->isQuestionStartLine($line, $current, $questions)) {
                preg_match('/^(?:soal\s*no\.?\s*|no\.?\s*)?(\d{1,3})\s*[\.\)]\s*(.*)$/iu', $line, $m);
                $qNum = intval($m[1]);
                $qRestPlain = trim($m[2] ?? '');

                $preambleHtml = '';
                $preambleImg = null;
                if ($current && !empty($current['is_unnumbered_preamble']) && empty(array_filter($current['options'] ?? [], fn($v) => trim((string) $v) !== ''))) {
                    $preambleHtml = trim((string) ($current['question_text'] ?? ''));
                    $preambleImg = $current['question_image'] ?? null;
                    $current = null;
                } else {
                    $flushCurrent();
                }
                $inQuestionsArea = true;

                // Strip prefix from HTML too
                $qRestHtml = $this->stripNumberPrefixFromHtml($html, $qRestPlain);

                $current = $this->initEmptyQuestion($qNum, $currentType, $qRestPlain, $qRestHtml, $image ?: $preambleImg);
                if ($preambleHtml !== '') {
                    $current['question_text'] = $preambleHtml . ($current['question_text'] ?? '');
                }
                $lastTarget = 'question';

                // Check if options A/B/C/D (or Arabic أ/ب/ج/د) happened to be on the exact same line after question text
                if (in_array($currentType, ['pg', 'pg_complex'], true) && preg_match('/\s+(?:A|أ|ا|إ|آ)[\.\)]\s+/u', $qRestPlain)) {
                    $this->splitInlineOptionsIntoQuestion($current, $qRestPlain);
                    $lastTarget = 'option:D';
                }
                continue;
            }

            // Detect option lines inside PG / PGK (supports single "A. ..." or multi "A. ...   B. ...")
            if ($current && in_array($current['question_type'], ['pg', 'pg_complex'], true)) {
                $parsedOpts = $this->tryParseOptionLine($line, $html);
                if (!empty($parsedOpts)) {
                    foreach ($parsedOpts as $letter => $optVal) {
                        $current['options'][$letter] = $optVal;
                        $lastTarget = 'option:' . $letter;
                    }
                    continue;
                }
            }

            // If we reach text before any question started: skip section sub-instructions (e.g. "Pilihlah satu jawaban...")
            if (!$current) {
                if ($this->isSectionSubInstructionLine($line)) {
                    continue;
                }
                $inQuestionsArea = true;
                $nextNum = count($questions) + 1;
                $current = $this->initEmptyQuestion($nextNum, $currentType, $line, $html ?: e($line), $image);
                $current['is_unnumbered_preamble'] = true;
                $lastTarget = 'question';
                continue;
            }

            // Append continuation line to previous option or question body
            if ($lastTarget && str_starts_with($lastTarget, 'option:')) {
                $optLetter = substr($lastTarget, 7);
                $existing = $current['options'][$optLetter] ?? '';
                $mergedText = trim($existing . ' ' . $line);
                $splitMap = $this->splitSequentialInlineOptions($optLetter, $mergedText);
                foreach ($splitMap as $lKey => $lVal) {
                    $current['options'][$lKey] = $lVal;
                    $lastTarget = 'option:' . $lKey;
                }
            } else {
                // Skip pure instruction sub-heading inside section like "Pilihlah salah satu jawaban..."
                if ($this->isSectionSubInstructionLine($line) && trim(strip_tags($current['question_text'])) === '') {
                    continue;
                }
                $current['question_text'] = $this->appendHtmlParagraph($current['question_text'], $html ?: e($line));
                if ($image && empty($current['question_image'])) {
                    $current['question_image'] = $image;
                }
            }
        }

        $flushCurrent();

        // Apply bottom answer key section if present & guarantee unique sequential numbers across sections
        $usedNumbers = [];
        $nextAutoNum = 1;
        foreach ($questions as &$q) {
            $num = intval($q['question_number'] ?? 0);
            if ($num < 1 || isset($usedNumbers[$num])) {
                while (isset($usedNumbers[$nextAutoNum])) {
                    $nextAutoNum++;
                }
                $num = $nextAutoNum;
                $q['question_number'] = $num;
            }
            $usedNumbers[$num] = true;
            if ($num >= $nextAutoNum) {
                $nextAutoNum = $num + 1;
            }

            if (!empty($bottomKeys) && isset($bottomKeys[$num]) && empty($q['correct_answer'])) {
                $q['correct_answer'] = $this->normalizeAnswerKeyForType($q['question_type'], $bottomKeys[$num]);
                if ($q['question_type'] === 'true_false') {
                    $q['options'] = ['mode' => 'single', 'answer' => $q['correct_answer']];
                } elseif ($q['question_type'] === 'matching' && is_array($q['options'])) {
                    $q['options']['key'] = $q['correct_answer'];
                }
            }
        }
        unset($q);

        // Validation warnings for preview UI
        foreach ($questions as $q) {
            if (in_array($q['question_type'], ['pg', 'pg_complex'], true)) {
                $filledOpts = collect($q['options'] ?? [])->filter(fn($v) => trim((string) $v) !== '')->count();
                if ($filledOpts < 2) {
                    $warnings[] = "No. {$q['question_number']}: Pilihan jawaban kurang dari 2 opsi.";
                }
            }
            if (trim(strip_tags((string) $q['question_text'])) === '' && empty($q['question_image'])) {
                $warnings[] = "No. {$q['question_number']}: Teks soal masih kosong.";
            }
        }

        $typeCounts = [];
        foreach ($questions as $q) {
            $t = $q['question_type'] ?: 'pg';
            $typeCounts[$t] = ($typeCounts[$t] ?? 0) + 1;
        }

        return [
            'questions' => array_values($questions),
            'warnings' => $warnings,
            'total_parsed' => count($questions),
            'type_counts' => $typeCounts,
        ];
    }

    // -------------------------------------------------------------------------
    // DOCX XML Building Helpers
    // -------------------------------------------------------------------------

    private function buildHeaderBannerXml(string $title, string $subtitle): string
    {
        return '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="60"/></w:pPr>'
            . '<w:r><w:rPr><w:b/><w:sz w:val="28"/><w:color w:val="065F46"/></w:rPr>'
            . '<w:t xml:space="preserve">' . $this->xmlEsc($title) . '</w:t></w:r></w:p>'
            . '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="160"/>'
            . '<w:pBdr><w:bottom w:val="single" w:sz="12" w:space="6" w:color="059669"/></w:pBdr></w:pPr>'
            . '<w:r><w:rPr><w:b/><w:sz w:val="20"/><w:color w:val="334155"/></w:rPr>'
            . '<w:t xml:space="preserve">' . $this->xmlEsc($subtitle) . '</w:t></w:r></w:p>';
    }

    private function buildInstructionBoxXml(array $lines): string
    {
        $xml = '';
        foreach ($lines as $idx => $line) {
            $isBold = $idx === 0;
            $xml .= '<w:p><w:pPr><w:spacing w:before="20" w:after="40"/>'
                . '<w:shd w:val="clear" w:color="auto" w:fill="F1F5F9"/></w:pPr>'
                . '<w:r><w:rPr>' . ($isBold ? '<w:b/>' : '<w:i/>') . '<w:sz w:val="18"/><w:color w:val="475569"/></w:rPr>'
                . '<w:t xml:space="preserve">' . $this->xmlEsc($line) . '</w:t></w:r></w:p>';
        }
        $xml .= '<w:p><w:pPr><w:spacing w:after="120"/></w:pPr></w:p>';
        return $xml;
    }

    private function buildSectionHeaderXml(string $roman, string $label, string $tag, string $desc): string
    {
        return '<w:p><w:pPr><w:spacing w:before="200" w:after="40"/></w:pPr>'
            . '<w:r><w:rPr><w:b/><w:sz w:val="24"/><w:color w:val="0F172A"/></w:rPr>'
            . '<w:t xml:space="preserve">' . $this->xmlEsc("{$roman}. {$label} {$tag}") . '</w:t></w:r></w:p>'
            . '<w:p><w:pPr><w:spacing w:after="100"/></w:pPr>'
            . '<w:r><w:rPr><w:i/><w:sz w:val="20"/><w:color w:val="475569"/></w:rPr>'
            . '<w:t xml:space="preserve">' . $this->xmlEsc($desc) . '</w:t></w:r></w:p>';
    }

    private function buildSampleTemplateBodyXml(): string
    {
        $samples = [
            new ExamQuestion([
                'question_number' => 1,
                'question_type' => 'pg',
                'question_text' => '<p>Alquran berasal dari kata قَرَأَ – يَقْرَأُ – قُرْآنًا yang artinya "bacaan". Pengertian Al-Qur\'an yang tepat adalah ....</p>',
                'options' => ['A' => 'Perkataan para sahabat Nabi', 'B' => 'Firman Allah Swt. yang diturunkan kepada Nabi Muhammad saw.', 'C' => 'Kumpulan cerita para nabi', 'D' => 'Kitab yang disusun oleh ulama'],
                'correct_answer' => 'B',
                'score_weight' => 1,
                'material' => 'Pengertian Al-Qur\'an',
                'indicator' => 'Siswa mampu menjelaskan pengertian Al-Qur\'an secara istilah dengan benar.',
                'cognitive_level' => 'L1',
                'difficulty_level' => 'mudah',
            ]),
            new ExamQuestion([
                'question_number' => 2,
                'question_type' => 'pg',
                'question_text' => '<p>Hasil dari operasi hitung 25 + 15 &times; 2 adalah ....</p>',
                'options' => ['A' => '55', 'B' => '80', 'C' => '65', 'D' => '70'],
                'correct_answer' => 'A',
                'score_weight' => 1,
                'material' => 'Operasi Hitung Campuran',
                'indicator' => 'Siswa dapat menghitung hasil operasi campuran bilangan bulat.',
                'cognitive_level' => 'L1',
                'difficulty_level' => 'mudah',
            ]),
            new ExamQuestion([
                'question_number' => 3,
                'question_type' => 'pg_complex',
                'question_text' => '<p>Manakah di antara perilaku berikut yang mencerminkan sifat amanah? (Pilih lebih dari satu jawaban)</p>',
                'options' => ['A' => 'Menjaga rahasia teman dengan baik', 'B' => 'Mengabaikan tugas kelompok', 'C' => 'Menyampaikan pesan guru kepada orang tua apa adanya', 'D' => 'Mengambil barang teman tanpa izin'],
                'correct_answer' => 'A,C',
                'score_weight' => 3,
                'material' => 'Akhlak Terpuji',
                'indicator' => 'Siswa mampu mengidentifikasi contoh perilaku amanah dalam kehidupan sehari-hari.',
                'cognitive_level' => 'L2',
                'difficulty_level' => 'sedang',
            ]),
            new ExamQuestion([
                'question_number' => 4,
                'question_type' => 'true_false',
                'question_text' => '<p>Shalat fardhu lima waktu hukumnya wajib \'ain bagi setiap muslim yang mukallaf. (Benar / Salah)</p>',
                'options' => ['mode' => 'single', 'answer' => 'B'],
                'correct_answer' => 'B',
                'score_weight' => 2,
                'material' => 'Ketentuan Shalat',
                'indicator' => 'Siswa dapat menentukan kebenaran hukum pelaksanaan shalat fardhu.',
                'cognitive_level' => 'L1',
                'difficulty_level' => 'mudah',
            ]),
            new ExamQuestion([
                'question_number' => 5,
                'question_type' => 'matching',
                'question_text' => '<p>Malaikat yang bertugas menyampaikan wahyu kepada para nabi dan rasul.</p>',
                'options' => ['mode' => 'single', 'right_text' => 'Malaikat Jibril', 'key' => 'A'],
                'correct_answer' => 'A',
                'score_weight' => 3,
                'material' => 'Iman kepada Malaikat',
                'indicator' => 'Siswa mampu memasangkan nama malaikat dan tugasnya.',
                'cognitive_level' => 'L2',
                'difficulty_level' => 'sedang',
            ]),
            new ExamQuestion([
                'question_number' => 6,
                'question_type' => 'short_answer',
                'question_text' => '<p>Surah pertama yang diturunkan kepada Nabi Muhammad saw. adalah Surah ....</p>',
                'correct_answer' => 'Al-Alaq',
                'score_weight' => 2,
                'material' => 'Sejarah Turunnya Al-Qur\'an',
                'indicator' => 'Siswa dapat menyebutkan nama surah yang pertama kali turun.',
                'cognitive_level' => 'L1',
                'difficulty_level' => 'mudah',
            ]),
            new ExamQuestion([
                'question_number' => 7,
                'question_type' => 'essay',
                'question_text' => '<p>Jelaskan perbedaan antara rukun shalat dan syarat sah shalat beserta masing-masing 2 contohnya!</p>',
                'correct_answer' => 'Rukun adalah amalan di dalam shalat (seperti takbiratul ihram, al-fatihah), sedangkan syarat sah adalah hal di luar shalat sebelum dimulai (suci dari hadas, menghadap kiblat).',
                'score_weight' => 5,
                'material' => 'Fikih Shalat',
                'indicator' => 'Siswa mampu menjelaskan perbedaan rukun dan syarat sah shalat.',
                'cognitive_level' => 'L3',
                'difficulty_level' => 'sukar',
            ]),
        ];

        $xml = '';
        $dummyMedia = [];
        $dummyNum = 100;
        $byType = collect($samples)->groupBy('question_type');
        foreach (self::SECTION_TITLES as $typeKey => $meta) {
            $list = $byType->get($typeKey);
            if (!$list) {
                continue;
            }
            $xml .= $this->buildSectionBlockXml($typeKey, $list, $meta, $dummyMedia, $dummyNum);
        }
        return $xml;
    }

    private function buildSectionBlockXml(string $typeKey, $questions, array $meta, array &$mediaFiles, int &$nextRelNum): string
    {
        $xml = $this->buildSectionHeaderXml($meta['roman'], $meta['label'], $meta['tag'], $meta['desc']);

        if (in_array($typeKey, ['true_false', 'matching'], true)) {
            foreach ($questions as $qItem) {
                $qOpts = is_array($qItem->options) ? $qItem->options : (json_decode((string) $qItem->options, true) ?: []);
                if (!empty($qOpts['stimulus'])) {
                    foreach ($this->splitHtmlIntoParagraphFragments((string) $qOpts['stimulus']) as $sFrag) {
                        $xml .= '<w:p><w:pPr><w:spacing w:before="40" w:after="60"/></w:pPr>' . $this->htmlFragmentToWordRuns($sFrag) . '</w:p>';
                    }
                    break;
                }
            }
        }

        if ($typeKey === 'true_false') {
            $xml .= $this->buildTrueFalseTableXml($questions, $mediaFiles, $nextRelNum);
            foreach ($questions as $q) {
                $parts = [];
                if (!empty($q->material)) $parts[] = 'Materi: ' . $q->material;
                if (!empty($q->indicator)) $parts[] = 'Indikator: ' . $q->indicator;
                $lvl = $q->cognitive_level ?: 'L1';
                $diff = $q->difficulty_level ?: 'sedang';
                $wt = floatval($q->score_weight ?: 2);
                $parts[] = "Level: {$lvl} | Kesukaran: {$diff} | Bobot: {$wt}";
                $xml .= $this->buildMetadataLineXml("[No. {$q->question_number} | " . implode(' | ', $parts) . ']');
            }
            return $xml;
        }

        if ($typeKey === 'matching') {
            $xml .= $this->buildMatchingTableXml($questions, $mediaFiles, $nextRelNum);
            foreach ($questions as $q) {
                $parts = [];
                if (!empty($q->material)) $parts[] = 'Materi: ' . $q->material;
                if (!empty($q->indicator)) $parts[] = 'Indikator: ' . $q->indicator;
                $lvl = $q->cognitive_level ?: 'L2';
                $diff = $q->difficulty_level ?: 'sedang';
                $wt = floatval($q->score_weight ?: 3);
                $parts[] = "Level: {$lvl} | Kesukaran: {$diff} | Bobot: {$wt}";
                $xml .= $this->buildMetadataLineXml("[No. {$q->question_number} | " . implode(' | ', $parts) . ']');
            }
            return $xml;
        }

        foreach ($questions as $q) {
            $xml .= $this->buildQuestionBlockXml($q, $mediaFiles, $nextRelNum);
        }

        return $xml;
    }

    private function buildBorderedWordTableXml(array $colWidths, array $headerTitles, array $rowsXml): string
    {
        $tblPr = '<w:tblPr><w:tblW w:w="' . array_sum($colWidths) . '" w:type="dxa"/>'
            . '<w:tblBorders>'
            . '<w:top w:val="single" w:sz="6" w:space="0" w:color="334155"/>'
            . '<w:left w:val="single" w:sz="6" w:space="0" w:color="334155"/>'
            . '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="334155"/>'
            . '<w:right w:val="single" w:sz="6" w:space="0" w:color="334155"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="94A3B8"/>'
            . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="94A3B8"/>'
            . '</w:tblBorders>'
            . '<w:tblCellMar><w:top w:w="70" w:type="dxa"/><w:left w:w="100" w:type="dxa"/><w:bottom w:w="70" w:type="dxa"/><w:right w:w="100" w:type="dxa"/></w:tblCellMar>'
            . '</w:tblPr>';

        $grid = '<w:tblGrid>';
        foreach ($colWidths as $w) {
            $grid .= '<w:gridCol w:w="' . $w . '"/>';
        }
        $grid .= '</w:tblGrid>';

        $headerRow = '<w:tr><w:trPr><w:tblHeader/></w:trPr>';
        foreach ($headerTitles as $idx => $title) {
            $w = $colWidths[$idx] ?? 1000;
            $jc = in_array(strtoupper($title), ['NO', 'B', 'S', 'JAWABAN', 'BENAR', 'SALAH'], true) ? 'center' : 'left';
            $headerRow .= '<w:tc><w:tcPr><w:tcW w:w="' . $w . '" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="E2E8F0"/></w:tcPr>'
                . '<w:p><w:pPr><w:jc w:val="' . $jc . '"/><w:spacing w:before="20" w:after="20"/></w:pPr>'
                . '<w:r><w:rPr><w:b/><w:sz w:val="20"/><w:color w:val="0F172A"/></w:rPr>'
                . '<w:t xml:space="preserve">' . $this->xmlEsc($title) . '</w:t></w:r></w:p></w:tc>';
        }
        $headerRow .= '</w:tr>';

        return '<w:tbl>' . $tblPr . $grid . $headerRow . implode('', $rowsXml) . '</w:tbl>'
            . '<w:p><w:pPr><w:spacing w:after="60"/></w:pPr></w:p>';
    }

    private function buildTrueFalseTableXml($questions, array &$mediaFiles, int &$nextRelNum): string
    {
        $colWidths = [800, 7480, 900, 900];
        $rowsXml = [];

        foreach ($questions as $q) {
            $ans = strtoupper(trim((string) ($q->correct_answer ?? 'B')));
            $isB = str_starts_with($ans, 'B');
            $isS = str_starts_with($ans, 'S');
            $pFrags = $this->splitHtmlIntoParagraphFragments($q->question_text ?: 'Pernyataan...');
            $stmtXml = '';
            foreach ($pFrags as $pf) {
                $stmtXml .= '<w:p><w:pPr><w:spacing w:before="20" w:after="20"/></w:pPr>' . $this->htmlFragmentToWordRuns($pf) . '</w:p>';
            }
            if (!empty($q->question_image)) {
                $imgXml = $this->tryBuildQuestionImageXml($q->question_image, $mediaFiles, $nextRelNum);
                if ($imgXml !== '') $stmtXml .= $imgXml;
            }

            $rowsXml[] = '<w:tr>'
                . '<w:tc><w:tcPr><w:tcW w:w="' . $colWidths[0] . '" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="22"/></w:rPr><w:t>' . $this->xmlEsc($q->question_number . '.') . '</w:t></w:r></w:p></w:tc>'
                . '<w:tc><w:tcPr><w:tcW w:w="' . $colWidths[1] . '" w:type="dxa"/></w:tcPr>' . $stmtXml . '</w:tc>'
                . '<w:tc><w:tcPr><w:tcW w:w="' . $colWidths[2] . '" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="22"/></w:rPr><w:t>' . ($isB ? '✓' : '') . '</w:t></w:r></w:p></w:tc>'
                . '<w:tc><w:tcPr><w:tcW w:w="' . $colWidths[3] . '" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="22"/></w:rPr><w:t>' . ($isS ? '✓' : '') . '</w:t></w:r></w:p></w:tc>'
                . '</w:tr>';
        }

        return $this->buildBorderedWordTableXml($colWidths, ['No', 'Pernyataan', 'B', 'S'], $rowsXml);
    }

    private function buildMatchingTableXml($questions, array &$mediaFiles, int &$nextRelNum): string
    {
        $colWidths = [800, 4100, 1180, 4000];
        $rowsXml = [];
        $idx = 0;

        foreach ($questions as $q) {
            $opts = is_array($q->options) ? $q->options : (json_decode((string) $q->options, true) ?: []);
            $rightText = trim((string) ($opts['right_text'] ?? ''));
            $ansKey = strtoupper(trim((string) ($q->correct_answer ?: ($opts['key'] ?? chr(65 + $idx)))));
            $rightLetter = strtoupper(trim((string) ($opts['key'] ?? $ansKey ?: chr(65 + $idx))));

            $pFrags = $this->splitHtmlIntoParagraphFragments($q->question_text ?: 'Pernyataan...');
            $leftXml = '';
            foreach ($pFrags as $pf) {
                $leftXml .= '<w:p><w:pPr><w:spacing w:before="20" w:after="20"/></w:pPr>' . $this->htmlFragmentToWordRuns($pf) . '</w:p>';
            }
            if (!empty($q->question_image)) {
                $imgXml = $this->tryBuildQuestionImageXml($q->question_image, $mediaFiles, $nextRelNum);
                if ($imgXml !== '') $leftXml .= $imgXml;
            }

            $rightRuns = $rightText !== ''
                ? '<w:r><w:rPr><w:b/><w:sz w:val="22"/></w:rPr><w:t xml:space="preserve">' . $this->xmlEsc("{$rightLetter}. ") . '</w:t></w:r>' . $this->htmlFragmentToWordRuns(e($rightText))
                : '<w:r><w:t></w:t></w:r>';

            $rowsXml[] = '<w:tr>'
                . '<w:tc><w:tcPr><w:tcW w:w="' . $colWidths[0] . '" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="22"/></w:rPr><w:t>' . $this->xmlEsc($q->question_number . '.') . '</w:t></w:r></w:p></w:tc>'
                . '<w:tc><w:tcPr><w:tcW w:w="' . $colWidths[1] . '" w:type="dxa"/></w:tcPr>' . $leftXml . '</w:tc>'
                . '<w:tc><w:tcPr><w:tcW w:w="' . $colWidths[2] . '" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="059669"/></w:rPr><w:t>' . $this->xmlEsc($ansKey) . '</w:t></w:r></w:p></w:tc>'
                . '<w:tc><w:tcPr><w:tcW w:w="' . $colWidths[3] . '" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:before="20" w:after="20"/></w:pPr>' . $rightRuns . '</w:p></w:tc>'
                . '</w:tr>';
            $idx++;
        }

        return $this->buildBorderedWordTableXml($colWidths, ['No', 'Kolom A (Pernyataan)', 'Jawaban', 'Kolom B (Pilihan Pasangan)'], $rowsXml);
    }

    private function tryBuildQuestionImageXml(string $questionImage, array &$mediaFiles, int &$nextRelNum): string
    {
        $relPath = ltrim(preg_replace('/^(\/?storage\/)+/', '', $questionImage), '/');
        $fullImgPath = storage_path('app/public/' . $relPath);
        if (!is_file($fullImgPath)) {
            $altPath = storage_path('app/public/exam-questions/' . basename($relPath));
            if (is_file($altPath)) {
                $fullImgPath = $altPath;
            }
        }
        if (!is_file($fullImgPath)) {
            return '';
        }
        $ext = strtolower(pathinfo($fullImgPath, PATHINFO_EXTENSION)) ?: 'png';
        if (!in_array($ext, self::ALLOWED_IMAGE_EXTS, true)) {
            return '';
        }
        $imgInfo = @getimagesize($fullImgPath);
        $pxW = $imgInfo[0] ?? 400;
        $pxH = $imgInfo[1] ?? 240;
        $maxW = 360;
        $scale = $pxW > $maxW ? ($maxW / $pxW) : 1;
        $emuW = intval(round($pxW * $scale * 9525));
        $emuH = intval(round($pxH * $scale * 9525));
        $rId = 'rId' . ($nextRelNum++);
        $zipName = 'word/media/img_' . count($mediaFiles) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $mediaFiles[] = [
            'relId' => $rId,
            'zipPath' => $zipName,
            'srcPath' => $fullImgPath,
            'ext' => $ext === 'jpeg' ? 'jpg' : $ext,
        ];
        return $this->buildInlineImageParagraphXml($rId, $emuW, $emuH, $nextRelNum);
    }

    private function buildQuestionBlockXml(ExamQuestion $q, array &$mediaFiles, int &$nextRelNum): string
    {
        $xml = '';
        $num = $q->question_number;
        $rawHtml = $q->question_text ?: 'Teks soal belum diisi.';

        // Main question paragraph(s): prepend "N. " to first paragraph
        $paragraphs = $this->splitHtmlIntoParagraphFragments($rawHtml);
        foreach ($paragraphs as $pIdx => $pFrag) {
            $prefixRun = $pIdx === 0
                ? '<w:r><w:rPr><w:b/><w:sz w:val="22"/></w:rPr><w:t xml:space="preserve">' . $this->xmlEsc("{$num}. ") . '</w:t></w:r>'
                : '';
            $runsXml = $this->htmlFragmentToWordRuns($pFrag);
            $indent = $pIdx > 0 ? '<w:ind w:left="360"/>' : '<w:ind w:left="360" w:hanging="360"/>';
            $xml .= '<w:p><w:pPr><w:spacing w:before="80" w:after="40"/>' . $indent . '</w:pPr>' . $prefixRun . $runsXml . '</w:p>';
        }

        // Embedded image if question_image exists on public disk
        if (!empty($q->question_image)) {
            $relPath = ltrim(preg_replace('/^(\/?storage\/)+/', '', $q->question_image), '/');
            $fullImgPath = storage_path('app/public/' . $relPath);
            if (!is_file($fullImgPath)) {
                $altPath = storage_path('app/public/exam-questions/' . basename($relPath));
                if (is_file($altPath)) {
                    $fullImgPath = $altPath;
                }
            }
            if (is_file($fullImgPath)) {
                $ext = strtolower(pathinfo($fullImgPath, PATHINFO_EXTENSION)) ?: 'png';
                if (in_array($ext, self::ALLOWED_IMAGE_EXTS, true)) {
                    $imgInfo = @getimagesize($fullImgPath);
                    $pxW = $imgInfo[0] ?? 400;
                    $pxH = $imgInfo[1] ?? 240;
                    $maxW = 450;
                    $scale = $pxW > $maxW ? ($maxW / $pxW) : 1;
                    $emuW = intval(round($pxW * $scale * 9525));
                    $emuH = intval(round($pxH * $scale * 9525));
                    $rId = 'rId' . ($nextRelNum++);
                    $zipName = 'word/media/img_' . count($mediaFiles) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                    $mediaFiles[] = [
                        'relId' => $rId,
                        'zipPath' => $zipName,
                        'srcPath' => $fullImgPath,
                        'ext' => $ext === 'jpeg' ? 'jpg' : $ext,
                    ];
                    $xml .= $this->buildInlineImageParagraphXml($rId, $emuW, $emuH, $nextRelNum);
                }
            }
        }

        // Options for PG & PG Kompleks
        $opts = is_array($q->options) ? $q->options : (json_decode((string) $q->options, true) ?: []);
        if (in_array($q->question_type, ['pg', 'pg_complex'], true)) {
            foreach (['A', 'B', 'C', 'D', 'E'] as $letter) {
                if (!isset($opts[$letter]) || ($letter === 'E' && trim((string) $opts[$letter]) === '')) {
                    continue;
                }
                $optRuns = $this->htmlFragmentToWordRuns(e((string) $opts[$letter]));
                $xml .= '<w:p><w:pPr><w:spacing w:before="10" w:after="20"/><w:ind w:left="540" w:hanging="240"/></w:pPr>'
                    . '<w:r><w:rPr><w:b/><w:sz w:val="22"/></w:rPr><w:t xml:space="preserve">' . $this->xmlEsc("{$letter}. ") . '</w:t></w:r>'
                    . $optRuns
                    . '</w:p>';
            }
        } elseif ($q->question_type === 'matching' && !empty($opts['right_text'])) {
            $xml .= $this->buildMetadataLineXml('[Pasangan: ' . $opts['right_text'] . ']');
        }

        // Key & Kisi-kisi metadata (preserving round-trip fidelity cleanly)
        $metaParts = [];
        if (!empty($q->correct_answer)) {
            $metaParts[] = '[Kunci: ' . $q->correct_answer . ']';
        }
        $lvl = $q->cognitive_level ?: 'L1';
        $diff = $q->difficulty_level ?: 'sedang';
        $wt = floatval($q->score_weight ?: (self::DEFAULT_WEIGHTS[$q->question_type] ?? 1));
        $metaParts[] = "[Level: {$lvl} | Kesukaran: {$diff} | Bobot: {$wt}]";

        $xml .= $this->buildMetadataLineXml(implode(' ', $metaParts));

        if (!empty($q->material)) {
            $xml .= $this->buildMetadataLineXml('[Materi: ' . $q->material . ']');
        }
        if (!empty($q->indicator)) {
            $xml .= $this->buildMetadataLineXml('[Indikator: ' . $q->indicator . ']');
        }

        return $xml;
    }

    private function buildMetadataLineXml(string $text): string
    {
        return '<w:p><w:pPr><w:spacing w:before="10" w:after="30"/><w:ind w:left="360"/></w:pPr>'
            . '<w:r><w:rPr><w:sz w:val="18"/><w:color w:val="64748B"/></w:rPr>'
            . '<w:t xml:space="preserve">' . $this->xmlEsc($text) . '</w:t></w:r></w:p>';
    }

    private function buildInlineImageParagraphXml(string $rId, int $emuW, int $emuH, int $docPrId): string
    {
        return '<w:p><w:pPr><w:spacing w:before="40" w:after="60"/><w:ind w:left="360"/></w:pPr>'
            . '<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0">'
            . '<wp:extent cx="' . $emuW . '" cy="' . $emuH . '"/>'
            . '<wp:docPr id="' . $docPrId . '" name="Image' . $docPrId . '"/>'
            . '<a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<pic:pic><pic:nvPicPr><pic:cNvPr id="' . $docPrId . '" name="Image' . $docPrId . '"/><pic:cNvPicPr/></pic:nvPicPr>'
            . '<pic:blipFill><a:blip r:embed="' . $rId . '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
            . '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $emuW . '" cy="' . $emuH . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
            . '</pic:pic></a:graphicData></a:graphic>'
            . '</wp:inline></w:drawing></w:r></w:p>';
    }

    private function splitHtmlIntoParagraphFragments(string $html): array
    {
        // Convert <p> and <div> blocks to delimited chunks while keeping inline tags
        $clean = str_replace(["\r\n", "\r"], "\n", $html);
        if (!str_contains($clean, '<')) {
            return array_values(array_filter(array_map('trim', explode("\n", e($clean))), fn($l) => $l !== '')) ?: [''];
        }
        $clean = preg_replace('/<\/(?:p|div)>/i', "\n", $clean);
        $clean = preg_replace('/<(?:p|div)[^>]*>/i', '', $clean);
        $clean = preg_replace('/<br\s*\/?>/i', "\n", $clean);
        $parts = array_values(array_filter(array_map('trim', explode("\n", $clean)), fn($p) => $p !== ''));
        return !empty($parts) ? $parts : [''];
    }

    private function htmlFragmentToWordRuns(string $fragment): string
    {
        $decoded = html_entity_decode($fragment, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Parse safe inline HTML tokens
        $tokens = preg_split('/(<\/?(?:b|strong|i|em|u|sup|sub)>)/i', $fragment, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if (!$tokens) {
            return $this->textToWordRuns($decoded);
        }

        $bold = false;
        $italic = false;
        $underline = false;
        $sup = false;
        $sub = false;
        $out = '';

        foreach ($tokens as $tok) {
            $lower = strtolower($tok);
            if ($lower === '<b>' || $lower === '<strong>') {
                $bold = true;
                continue;
            }
            if ($lower === '</b>' || $lower === '</strong>') {
                $bold = false;
                continue;
            }
            if ($lower === '<i>' || $lower === '<em>') {
                $italic = true;
                continue;
            }
            if ($lower === '</i>' || $lower === '</em>') {
                $italic = false;
                continue;
            }
            if ($lower === '<u>') {
                $underline = true;
                continue;
            }
            if ($lower === '</u>') {
                $underline = false;
                continue;
            }
            if ($lower === '<sup>') {
                $sup = true;
                continue;
            }
            if ($lower === '</sup>') {
                $sup = false;
                continue;
            }
            if ($lower === '<sub>') {
                $sub = true;
                continue;
            }
            if ($lower === '</sub>') {
                $sub = false;
                continue;
            }

            $plain = html_entity_decode(strip_tags($tok), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($plain === '') {
                continue;
            }
            $out .= $this->textToWordRuns($plain, $bold, $italic, $underline, $sup, $sub);
        }

        return $out ?: '<w:r><w:t></w:t></w:r>';
    }

    private function textToWordRuns(string $text, bool $bold = false, bool $italic = false, bool $underline = false, bool $sup = false, bool $sub = false): string
    {
        // Segment Arabic script runs vs Latin runs so Word renders RTL/Arabic cleanly
        $segments = preg_split('/([\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\s]+(?=\p{Arabic})|\p{Arabic}[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\s،؛؟\-–]+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if (!$segments) {
            $segments = [$text];
        }

        $xml = '';
        foreach ($segments as $seg) {
            $isArabic = (bool) preg_match('/\p{Arabic}/u', $seg);
            $rPr = '<w:rPr><w:sz w:val="22"/>';
            if ($bold) $rPr .= '<w:b/>';
            if ($italic) $rPr .= '<w:i/>';
            if ($underline) $rPr .= '<w:u w:val="single"/>';
            if ($sup) $rPr .= '<w:vertAlign w:val="superscript"/>';
            elseif ($sub) $rPr .= '<w:vertAlign w:val="subscript"/>';
            if ($isArabic) $rPr .= '<w:rtl/><w:rFonts w:cs="Traditional Arabic" w:ascii="Times New Roman"/>';
            $rPr .= '</w:rPr>';

            $xml .= '<w:r>' . $rPr . '<w:t xml:space="preserve">' . $this->xmlEsc($seg) . '</w:t></w:r>';
        }
        return $xml;
    }

    private function xmlEsc(string $str): string
    {
        return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function buildContentTypesXml(array $mediaFiles): string
    {
        $extDefaults = [
            'rels' => 'application/vnd.openxmlformats-package.relationships+xml',
            'xml' => 'application/xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        $defaultsXml = '';
        foreach ($extDefaults as $ext => $mime) {
            $defaultsXml .= '<Default Extension="' . $ext . '" ContentType="' . $mime . '"/>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . $defaultsXml
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            . '</Types>';
    }

    private function buildRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>';
    }

    private function buildDocumentRelsXml(array $mediaFiles): string
    {
        $rels = '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        foreach ($mediaFiles as $m) {
            $target = str_replace('word/', '', $m['zipPath']);
            $rels .= '<Relationship Id="' . $m['relId'] . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="' . $this->xmlEsc($target) . '"/>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    private function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:docDefaults><w:rPrDefault><w:rPr>'
            . '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Traditional Arabic"/>'
            . '<w:sz w:val="22"/><w:szCs w:val="24"/>'
            . '</w:rPr></w:rPrDefault></w:docDefaults>'
            . '<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style>'
            . '</w:styles>';
    }

    // -------------------------------------------------------------------------
    // DOCX XML Extraction Helpers
    // -------------------------------------------------------------------------

    private function parseRelsMap(ZipArchive $zip): array
    {
        $map = [];
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        if ($relsXml === false) {
            return $map;
        }
        $dom = new \DOMDocument();
        $prev = libxml_use_internal_errors(true);
        if ($dom->loadXML($relsXml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            foreach ($dom->getElementsByTagName('Relationship') as $rel) {
                $id = $rel->getAttribute('Id');
                $target = $rel->getAttribute('Target');
                if ($id && $target) {
                    $map[$id] = str_starts_with($target, 'word/') ? $target : ('word/' . ltrim($target, '/'));
                }
            }
        }
        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        return $map;
    }

    private function parseNumberingDefs(ZipArchive $zip): array
    {
        $defs = ['abstract' => [], 'numToAbstract' => []];
        $numXml = $zip->getFromName('word/numbering.xml');
        if ($numXml === false) {
            return $defs;
        }
        $dom = new \DOMDocument();
        $prev = libxml_use_internal_errors(true);
        if (!$dom->loadXML($numXml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
            return $defs;
        }
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $xp = new \DOMXPath($dom);
        $xp->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        foreach ($xp->query('//w:abstractNum') as $abs) {
            $absId = $abs->getAttribute('w:abstractNumId');
            foreach ($xp->query('./w:lvl', $abs) as $lvl) {
                $ilvl = $lvl->getAttribute('w:ilvl') ?: '0';
                $fmtNode = $xp->query('./w:numFmt', $lvl)->item(0);
                $startNode = $xp->query('./w:start', $lvl)->item(0);
                $fmt = $fmtNode ? $fmtNode->getAttribute('w:val') : 'decimal';
                $start = $startNode ? intval($startNode->getAttribute('w:val')) : 1;
                $defs['abstract'][$absId][$ilvl] = ['fmt' => $fmt, 'start' => $start];
            }
        }

        foreach ($xp->query('//w:num') as $num) {
            $numId = $num->getAttribute('w:numId');
            $absRef = $xp->query('./w:abstractNumId', $num)->item(0);
            if ($numId && $absRef) {
                $defs['numToAbstract'][$numId] = $absRef->getAttribute('w:val');
            }
        }

        return $defs;
    }

    private function resolveParagraphNumberPrefix(\DOMXPath $xp, \DOMNode $pNode, array $defs, array &$counters): string
    {
        $numIdNode = $xp->query('./w:pPr/w:numPr/w:numId', $pNode)->item(0);
        if (!$numIdNode) {
            return '';
        }
        $numId = $numIdNode->getAttribute('w:val');
        if (!$numId || $numId === '0') {
            return '';
        }
        $ilvlNode = $xp->query('./w:pPr/w:numPr/w:ilvl', $pNode)->item(0);
        $ilvl = $ilvlNode ? ($ilvlNode->getAttribute('w:val') ?: '0') : '0';

        $absId = $defs['numToAbstract'][$numId] ?? null;
        $lvlInfo = $absId ? ($defs['abstract'][$absId][$ilvl] ?? ['fmt' => 'decimal', 'start' => 1]) : ['fmt' => ($ilvl === '0' ? 'decimal' : 'upperLetter'), 'start' => 1];

        $fmt = $lvlInfo['fmt'];
        if ($fmt === 'bullet' || $fmt === 'none') {
            return '';
        }

        if (!isset($counters[$numId][$ilvl])) {
            $counters[$numId][$ilvl] = $lvlInfo['start'] ?? 1;
        } else {
            $counters[$numId][$ilvl]++;
        }
        // Reset deeper levels
        foreach (array_keys($counters[$numId]) as $k) {
            if (intval($k) > intval($ilvl)) {
                unset($counters[$numId][$k]);
            }
        }

        $val = $counters[$numId][$ilvl];
        if (in_array($fmt, ['upperLetter', 'lowerLetter', 'arabicAbjad', 'arabicAlpha', 'arabic1', 'arabic2'], true)) {
            $letter = chr(64 + max(1, min(26, $val)));
            return "{$letter}.";
        }
        if ($fmt === 'upperRoman') {
            $romans = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII'];
            return ($romans[$val] ?? $val) . '.';
        }

        return "{$val}.";
    }

    private function extractSingleParagraphBlock(
        \DOMXPath $xpath,
        \DOMNode $pNode,
        ZipArchive $zip,
        array $relsMap,
        array $numberingDefs,
        array &$numCounters,
        array &$extractedImagesCache
    ): array {
        $autoPrefix = $this->resolveParagraphNumberPrefix($xpath, $pNode, $numberingDefs, $numCounters);
        $parsed = $this->extractParagraphContent($xpath, $pNode, $zip, $relsMap, $extractedImagesCache);

        $plainText = $autoPrefix !== '' ? ($autoPrefix . ' ' . $parsed['text']) : $parsed['text'];
        $htmlText = $autoPrefix !== '' ? (e($autoPrefix) . ' ' . $parsed['html']) : $parsed['html'];

        return [
            'kind' => 'paragraph',
            'text' => trim($plainText),
            'html' => trim($htmlText),
            'image' => $parsed['image'],
        ];
    }

    private function extractTableBlocks(
        \DOMXPath $xpath,
        \DOMNode $tblNode,
        ZipArchive $zip,
        array $relsMap,
        array $numberingDefs,
        array &$numCounters,
        array &$extractedImagesCache
    ): array {
        $rawRows = [];
        $trNodes = $xpath->query('./w:tr', $tblNode);
        foreach ($trNodes as $trNode) {
            $rowCells = [];
            $tcNodes = $xpath->query('./w:tc', $trNode);
            foreach ($tcNodes as $tcNode) {
                $cellTexts = [];
                $cellHtmls = [];
                $cellImg = null;
                $pNodes = $xpath->query('.//w:p', $tcNode);
                foreach ($pNodes as $pNode) {
                    $b = $this->extractSingleParagraphBlock($xpath, $pNode, $zip, $relsMap, $numberingDefs, $numCounters, $extractedImagesCache);
                    if ($b['text'] !== '') {
                        $cellTexts[] = trim(preg_replace('/\x{00A0}/u', ' ', $b['text']));
                        $cellHtmls[] = $b['html'] !== '' ? '<p>' . $b['html'] . '</p>' : '';
                    }
                    if ($cellImg === null && !empty($b['image'])) {
                        $cellImg = $b['image'];
                    }
                }
                $rowCells[] = [
                    'text' => trim(implode("\n", $cellTexts)),
                    'html' => trim(implode('', $cellHtmls)),
                    'image' => $cellImg,
                ];
            }
            if (!empty($rowCells)) {
                $rawRows[] = $rowCells;
            }
        }

        if (empty($rawRows)) {
            return [];
        }

        // Classify whether table is a structured Matching or True/False question table
        $tableMeta = $this->classifyStructuredTable($rawRows);
        if ($tableMeta !== null) {
            $items = $tableMeta['type'] === 'matching'
                ? $this->parseMatchingTableRows($rawRows, $tableMeta)
                : $this->parseTrueFalseTableRows($rawRows, $tableMeta);

            if (!empty($items)) {
                return [[
                    'kind' => 'structured_table',
                    'table_type' => $tableMeta['type'],
                    'items' => $items,
                ]];
            }
        }

        // Check if this table is an Arabic vocabulary / fill-in / essay list table, or return table_rows so parseParagraphBlocks decides using active section context
        return [[
            'kind' => 'table_rows',
            'rows' => $rawRows,
        ]];
    }

    private function flattenTableRowsToParagraphs(array $rawRows): array
    {
        $flat = [];
        foreach ($rawRows as $r) {
            foreach ($r as $c) {
                if ($c['text'] === '' && !$c['image']) continue;
                $lines = explode("\n", $c['text']);
                foreach ($lines as $lIdx => $lText) {
                    $t = trim($lText);
                    if ($t === '' && ($lIdx > 0 || !$c['image'])) continue;
                    $flat[] = [
                        'kind' => 'paragraph',
                        'text' => $t,
                        'html' => $lIdx === 0 && $c['html'] !== '' ? $c['html'] : ($t !== '' ? e($t) : ''),
                        'image' => $lIdx === 0 ? $c['image'] : null,
                    ];
                }
            }
        }
        return $flat;
    }

    private function looksLikeArabicVocabularyTable(array $rawRows): bool
    {
        if (count($rawRows) < 2) {
            return false;
        }
        $firstJoined = implode(' | ', array_map(fn($c) => trim((string) ($c['text'] ?? '')), $rawRows[0]));
        if (preg_match('/المَعْنَى|المعنى|الْكَلِيْمَة|الكلمة|الرقم|الجَوّاب|الجواب|التَّحِيَّاتُ|التحيات/u', $firstJoined)) {
            return true;
        }
        foreach ($rawRows as $row) {
            foreach ($row as $cell) {
                if (preg_match('/^[…\.ـ_\-\s]{2,}$/u', trim((string) ($cell['text'] ?? '')))) {
                    return true;
                }
            }
        }
        return false;
    }

    private function isVocabularyTableHeaderRow(array $row): bool
    {
        $nonEmpty = [];
        foreach ($row as $c) {
            $t = trim((string) ($c['text'] ?? ''));
            if ($t !== '') $nonEmpty[] = $t;
        }
        if (empty($nonEmpty)) {
            return true;
        }
        $joined = implode(' ', $nonEmpty);
        if (preg_match('/\[(?:PG|PGK|ISIAN|URAIAN|ESSAY|BENAR|MENJODOHKAN)/iu', $joined)) {
            return true;
        }
        $headerHits = 0;
        foreach ($nonEmpty as $cellTxt) {
            if (preg_match('/^(?:المَعْنَى|المعنى|الْكَلِيْمَة|الْكَلِمَة|الكلمة|الرقم|الجَوّاب|الجَوَاب|الجواب|التَّحِيَّاتُ|التحيات|no\.?|nomor|arti|kata|soal|pertanyaan|jawaban)$/iu', $cellTxt)) {
                $headerHits++;
            }
        }
        return $headerHits >= 2 || (count($nonEmpty) === 1 && preg_match('/^(?:المَعْنَى|الْكَلِيْمَة|الرقم|الجَوّاب|التَّحِيَّاتُ)$/u', $nonEmpty[0]));
    }

    private function parseTableRowsAsQuestions(array $rawRows, string $questionType, int $startNum): array
    {
        $items = [];
        $seq = $startNum;
        $activePromptPrefix = '';

        foreach ($rawRows as $row) {
            $cellsText = array_map(fn($c) => trim((string) ($c['text'] ?? '')), $row);
            $joined = trim(implode(' ', array_filter($cellsText)));
            if ($joined === '') {
                continue;
            }
            $rowSectionType = $this->detectSectionHeaderType($joined);
            if ($rowSectionType !== null) {
                $questionType = $rowSectionType;
                $activePromptPrefix = '';
                continue;
            }
            if ($this->isVocabularyTableHeaderRow($row)) {
                if (preg_match('/المَعْنَى|المعنى/u', $joined)) {
                    $activePromptPrefix = 'Arti / Makna: ';
                } elseif (preg_match('/الجَوّاب|الجَوَاب|الجواب/u', $joined)) {
                    $activePromptPrefix = 'Respon / Jawaban: ';
                }
                continue;
            }

            $qNum = null;
            $promptParts = [];
            $htmlParts = [];
            $rowImg = null;
            foreach ($row as $cell) {
                $t = trim((string) ($cell['text'] ?? ''));
                $h = trim((string) ($cell['html'] ?? ''));
                if ($rowImg === null && !empty($cell['image'])) {
                    $rowImg = $cell['image'];
                }
                if ($t === '' || preg_match('/^[…\.ـ_\-\s]{2,}$/u', $t)) {
                    continue;
                }
                $normT = $this->normalizeLeadingArabicDigits($t);
                if ($qNum === null && preg_match('/^(\d{1,3})\s*[\.\)]?$/', $normT, $nm)) {
                    $qNum = intval($nm[1]);
                    continue;
                }
                if ($qNum === null && preg_match('/^(\d{1,3})\s*[\.\)]\s*(.+)$/us', $normT, $nm)) {
                    $qNum = intval($nm[1]);
                    $t = trim($nm[2]);
                    $h = $this->stripNumberPrefixFromHtml($h, $t);
                }
                $promptParts[] = $t;
                $htmlParts[] = $h !== '' ? $h : '<p>' . e($t) . '</p>';
            }

            $promptPlain = trim(implode(' — ', $promptParts));
            if ($promptPlain === '' && !$rowImg) {
                continue;
            }
            if (!$qNum) {
                $qNum = $seq;
            }
            $seq = $qNum + 1;

            if ($questionType === 'short_answer' && $activePromptPrefix !== '' && !str_contains($promptPlain, ':')) {
                $promptPlain = $activePromptPrefix . $promptPlain;
                $promptHtml = '<p><strong>' . e($activePromptPrefix) . '</strong>' . strip_tags(implode(' ', $htmlParts), '<b><strong><i><em><u><sup><sub>') . '</p>';
            } else {
                $promptHtml = implode('', $htmlParts);
            }

            $qItem = $this->initEmptyQuestion($qNum, $questionType, $promptPlain, $promptHtml, $rowImg);
            if ($promptHtml !== '' && !str_starts_with($qItem['question_text'], '<p><p>')) {
                $qItem['question_text'] = str_starts_with($promptHtml, '<p>') ? $promptHtml : '<p>' . $promptHtml . '</p>';
            } else {
                $qItem['question_text'] = $promptHtml;
            }
            $items[] = $qItem;
        }

        return $items;
    }

    private function classifyStructuredTable(array $rawRows): ?array
    {
        if (count($rawRows) < 2) {
            return null;
        }

        $header = $rawRows[0];
        $normHeader = array_map(fn($c) => strtolower(trim(preg_replace('/\s+/', ' ', $c['text']))), $header);
        $joinedHeader = implode(' | ', $normHeader);

        // 1. Matching Table (e.g. "KOLOM A | JAWABAN | KOLOM B" or "No | Pernyataan | Jawaban | Pilihan Pasangan")
        $hasMatchingHeader = str_contains($joinedHeader, 'kolom b')
            || str_contains($joinedHeader, 'pasangan')
            || str_contains($joinedHeader, 'pilihan jawaban')
            || (str_contains($joinedHeader, 'kolom a') && count($header) >= 2);

        if ($hasMatchingHeader) {
            $noIdx = null;
            $leftIdx = null;
            $ansIdx = null;
            $rightIdx = null;
            foreach ($normHeader as $i => $h) {
                if ($h === 'no' || $h === 'no.' || $h === 'nomor') $noIdx = $i;
                elseif (str_contains($h, 'kolom a') || str_contains($h, 'pernyataan') || str_contains($h, 'istilah') || str_contains($h, 'soal')) $leftIdx = $i;
                elseif ($h === 'jawaban' || $h === 'kunci' || $h === 'jwb') $ansIdx = $i;
                elseif (str_contains($h, 'kolom b') || str_contains($h, 'pasangan') || str_contains($h, 'pilihan')) $rightIdx = $i;
            }
            if ($leftIdx !== null && $rightIdx !== null) {
                return [
                    'type' => 'matching',
                    'has_header' => true,
                    'no_idx' => $noIdx,
                    'left_idx' => $leftIdx,
                    'ans_idx' => $ansIdx,
                    'right_idx' => $rightIdx,
                ];
            }
        }

        // 2. True / False Table (e.g. "No | Pernyataan | B | S" or "No | Pernyataan | Benar | Salah")
        $bIdx = null;
        $sIdx = null;
        $tfAnsIdx = null;
        $tfNoIdx = null;
        $tfStmtIdx = null;
        foreach ($normHeader as $i => $h) {
            if ($h === 'no' || $h === 'no.' || $h === 'nomor') $tfNoIdx = $i;
            elseif (str_contains($h, 'pernyataan') || str_contains($h, 'soal') || str_contains($h, 'kalimat')) $tfStmtIdx = $i;
            elseif ($h === 'b' || $h === 'benar') $bIdx = $i;
            elseif ($h === 's' || $h === 'salah') $sIdx = $i;
            elseif ($h === 'jawaban' || $h === 'kunci' || str_contains($h, 'b/s') || str_contains($h, 'benar/salah')) $tfAnsIdx = $i;
        }
        if ($tfStmtIdx !== null && (($bIdx !== null && $sIdx !== null) || $tfAnsIdx !== null)) {
            return [
                'type' => 'true_false',
                'has_header' => true,
                'no_idx' => $tfNoIdx,
                'stmt_idx' => $tfStmtIdx,
                'b_idx' => $bIdx,
                's_idx' => $sIdx,
                'ans_idx' => $tfAnsIdx,
            ];
        }

        // 3. Headerless 3 or 4-col Matching table detection via row pattern (Left numbered + Right "A. ...")
        $colCount = count($header);
        if ($colCount >= 3 && $colCount <= 4) {
            $matchVotes = 0;
            foreach ($rawRows as $r) {
                if (count($r) !== $colCount) continue;
                $lastText = trim($r[$colCount - 1]['text']);
                $hasNumLeft = preg_match('/^\d{1,3}\./', trim($r[0]['text'])) || preg_match('/^\d{1,3}\./', trim($r[1]['text']));
                if ($hasNumLeft && preg_match('/^[A-Za-z][\.\)]\s+/', $lastText)) {
                    $matchVotes++;
                }
            }
            if ($matchVotes >= 2) {
                return [
                    'type' => 'matching',
                    'has_header' => false,
                    'no_idx' => $colCount === 4 ? 0 : null,
                    'left_idx' => $colCount === 4 ? 1 : 0,
                    'ans_idx' => $colCount === 4 ? 2 : 1,
                    'right_idx' => $colCount - 1,
                ];
            }
        }

        return null;
    }

    private function parseMatchingTableRows(array $rawRows, array $meta): array
    {
        $items = [];
        $startIdx = !empty($meta['has_header']) ? 1 : 0;
        $seq = 1;

        for ($r = $startIdx; $r < count($rawRows); $r++) {
            $row = $rawRows[$r];
            $noText = $meta['no_idx'] !== null ? trim($row[$meta['no_idx']]['text'] ?? '') : '';
            $leftCell = $row[$meta['left_idx']] ?? ['text' => '', 'html' => '', 'image' => null];
            $ansText = $meta['ans_idx'] !== null ? trim($row[$meta['ans_idx']]['text'] ?? '') : '';
            $rightCell = $row[$meta['right_idx']] ?? ['text' => '', 'html' => '', 'image' => null];

            $leftPlain = trim($leftCell['text']);
            $leftHtml = trim($leftCell['html']);
            $rightPlain = trim($rightCell['text']);

            if ($leftPlain === '' && $rightPlain === '' && empty($leftCell['image'])) {
                continue;
            }

            $qNum = null;
            if (preg_match('/(\d{1,3})/', $noText, $nm)) {
                $qNum = intval($nm[1]);
            } elseif (preg_match('/^(\d{1,3})\s*[\.\)]\s*(.+)$/us', $leftPlain, $lm)) {
                $qNum = intval($lm[1]);
                $leftPlain = trim($lm[2]);
                $leftHtml = $this->stripNumberPrefixFromHtml($leftHtml, $leftPlain);
            }
            if (!$qNum) {
                $qNum = $seq;
            }
            $seq = $qNum + 1;

            // Parse right option letter & text (e.g. "A. Sesungguhnya rahmat-Ku...")
            $rightLetter = chr(65 + count($items));
            $rightCleanText = $rightPlain;
            if (preg_match('/^\(?([A-Za-z])\s*[\.\)]\s*(.+)$/us', $rightPlain, $rm)) {
                $rightLetter = strtoupper($rm[1]);
                $rightCleanText = trim(preg_replace('/\s+/', ' ', $rm[2]));
            }

            $ansLetter = null;
            if (preg_match('/^([A-Za-z])(?:\b|\.)/u', $ansText, $am)) {
                $ansLetter = strtoupper($am[1]);
            }

            $qItem = $this->initEmptyQuestion($qNum, 'matching', $leftPlain, $leftHtml ?: e($leftPlain), $leftCell['image'] ?? null);
            $qItem['matching_right_text'] = $rightCleanText;
            $qItem['correct_answer'] = $ansLetter ?: $rightLetter;
            // Ensure question_text doesn't double-wrap <p><p>...</p></p> if $leftHtml already has <p>
            if (str_starts_with($leftHtml, '<p>')) {
                $qItem['question_text'] = $leftHtml;
            }
            $items[] = $qItem;
        }

        return $items;
    }

    private function parseTrueFalseTableRows(array $rawRows, array $meta): array
    {
        $items = [];
        $startIdx = !empty($meta['has_header']) ? 1 : 0;
        $seq = 1;

        for ($r = $startIdx; $r < count($rawRows); $r++) {
            $row = $rawRows[$r];
            $noText = $meta['no_idx'] !== null ? trim($row[$meta['no_idx']]['text'] ?? '') : '';
            $stmtCell = $row[$meta['stmt_idx']] ?? ['text' => '', 'html' => '', 'image' => null];

            $stmtPlain = trim($stmtCell['text']);
            $stmtHtml = trim($stmtCell['html']);
            if ($stmtPlain === '' && empty($stmtCell['image'])) {
                continue;
            }

            $qNum = null;
            if (preg_match('/(\d{1,3})/', $noText, $nm)) {
                $qNum = intval($nm[1]);
            } elseif (preg_match('/^(\d{1,3})\s*[\.\)]\s*(.+)$/us', $stmtPlain, $sm)) {
                $qNum = intval($sm[1]);
                $stmtPlain = trim($sm[2]);
                $stmtHtml = $this->stripNumberPrefixFromHtml($stmtHtml, $stmtPlain);
            }
            if (!$qNum) {
                $qNum = $seq;
            }
            $seq = $qNum + 1;

            $ans = 'B';
            if ($meta['b_idx'] !== null && $meta['s_idx'] !== null) {
                $bVal = trim($row[$meta['b_idx']]['text'] ?? '');
                $sVal = trim($row[$meta['s_idx']]['text'] ?? '');
                if ($sVal !== '' && $bVal === '') {
                    $ans = 'S';
                } elseif (preg_match('/✓|✔|v|x|s/iu', $sVal) && !preg_match('/✓|✔|v|x|b/iu', $bVal)) {
                    $ans = 'S';
                }
            } elseif ($meta['ans_idx'] !== null) {
                $ansRaw = strtoupper(trim($row[$meta['ans_idx']]['text'] ?? ''));
                if (str_starts_with($ansRaw, 'S')) {
                    $ans = 'S';
                }
            }

            $qItem = $this->initEmptyQuestion($qNum, 'true_false', $stmtPlain, $stmtHtml ?: e($stmtPlain), $stmtCell['image'] ?? null);
            $qItem['correct_answer'] = $ans;
            if (str_starts_with($stmtHtml, '<p>')) {
                $qItem['question_text'] = $stmtHtml;
            }
            $items[] = $qItem;
        }

        return $items;
    }

    private function extractParagraphContent(\DOMXPath $xp, \DOMNode $pNode, ZipArchive $zip, array $relsMap, array &$imagesCache): array
    {
        $plain = '';
        $html = '';
        $imagePath = null;

        $runs = $xp->query('.//w:r[not(ancestor::*[local-name()="Fallback"])]', $pNode);
        foreach ($runs as $rNode) {
            // Check inline images in this run
            if ($imagePath === null) {
                $blip = $xp->query('.//a:blip', $rNode)->item(0);
                if ($blip) {
                    $rId = $blip->getAttribute('r:embed') ?: $blip->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'embed');
                    if ($rId && isset($relsMap[$rId])) {
                        $imagePath = $this->extractAndStoreZipImage($zip, $relsMap[$rId], $imagesCache);
                    }
                }
            }

            $runText = '';
            foreach ($rNode->childNodes as $child) {
                if ($child->nodeName === 'w:t') {
                    $runText .= $child->nodeValue;
                } elseif ($child->nodeName === 'w:tab') {
                    $runText .= '   ';
                } elseif ($child->nodeName === 'w:br') {
                    $runText .= "\n";
                }
            }

            if ($runText === '') {
                continue;
            }

            $plain .= $runText;

            $isBold = $xp->query('./w:rPr/w:b[not(@w:val="0") and not(@w:val="false")]', $rNode)->length > 0;
            $isItalic = $xp->query('./w:rPr/w:i[not(@w:val="0") and not(@w:val="false")]', $rNode)->length > 0;
            $isUnder = $xp->query('./w:rPr/w:u[@w:val and @w:val!="none"]', $rNode)->length > 0;
            $vertNode = $xp->query('./w:rPr/w:vertAlign', $rNode)->item(0);
            $vertVal = $vertNode ? $vertNode->getAttribute('w:val') : '';

            $chunkHtml = str_replace("\n", '<br>', e($runText));
            if ($vertVal === 'superscript') $chunkHtml = "<sup>{$chunkHtml}</sup>";
            elseif ($vertVal === 'subscript') $chunkHtml = "<sub>{$chunkHtml}</sub>";
            if ($isUnder) $chunkHtml = "<u>{$chunkHtml}</u>";
            if ($isItalic) $chunkHtml = "<i>{$chunkHtml}</i>";
            if ($isBold) $chunkHtml = "<b>{$chunkHtml}</b>";

            $html .= $chunkHtml;
        }

        return [
            'text' => $plain,
            'html' => $html,
            'image' => $imagePath,
        ];
    }

    private function extractAndStoreZipImage(ZipArchive $zip, string $zipMemberPath, array &$cache): ?string
    {
        if (isset($cache[$zipMemberPath])) {
            return $cache[$zipMemberPath];
        }

        $ext = strtolower(pathinfo($zipMemberPath, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_IMAGE_EXTS, true)) {
            return null;
        }

        $bytes = $zip->getFromName($zipMemberPath);
        if ($bytes === false || strlen($bytes) > 5242880) {
            return null;
        }

        $filename = 'exam-questions/docx_' . Str::random(16) . '.' . $ext;
        Storage::disk('public')->put($filename, $bytes);
        $cache[$zipMemberPath] = $filename;

        return $filename;
    }

    // -------------------------------------------------------------------------
    // Parsing State Machine Helpers
    // -------------------------------------------------------------------------

    private function isTemplateNoiseLine(string $line): bool
    {
        return (bool) preg_match('/^(?:TEMPLATE NASKAH SOAL|MTs AL-HASANAH\s*[—|-]\s*FORMAT|PETUNJUK PENGISIAN TEMPLATE:|\d+\.\s*(?:Tulis soal di bawah|Nomor soal ditulis|Baris \[Kunci|Mendukung tulisan Arab))/iu', $line);
    }

    private function isCoverInstructionLine(string $line): bool
    {
        return (bool) preg_match('/^(?:PETUNJUK\s+UMUM|Isilah\s+nama|Pergunakan\s+ballpoint|Dahulukan\s+menjawab|Periksalah\s+pekerjaan|Berilah\s+tanda\s+silang|Lembar\s+Soal|NAMA\s*:|KELAS\s*:|HARI\s*\/?\s*TANGGAL|WAKTU\s*:|\d+\.\s*(?:Berdo|Isilah\s+identitas|Isilah\s+nama|Pergunakan|Dahulukan|Periksalah|Bacalah\s+soal))/iu', $line);
    }

    private function isFooterClosingLine(string $line): bool
    {
        return (bool) preg_match('/^(?:kerjakan(?:\s*lah)?\s+soal\s+dengan\s+teliti|jangan\s+terburu|selamat\s+mengerjakan|semoga\s+sukses|---\s*akhir)/iu', $line);
    }

    private function normalizeLeadingArabicDigits(string $text): string
    {
        $converted = strtr($text, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
        return $converted;
    }

    private function normalizeOptionMarker(string $rawMarker): ?string
    {
        $clean = trim($rawMarker);
        if (preg_match('/^[A-Ea-e]$/', $clean)) {
            return strtoupper($clean);
        }
        return self::ARABIC_OPTION_MAP[$clean] ?? null;
    }

    private function splitSoftBreakParagraphBlock(array $block): array
    {
        $textLines = preg_split('/\r?\n/', (string) ($block['text'] ?? ''));
        $htmlLines = preg_split('/<br\s*\/?>/i', (string) ($block['html'] ?? ''));
        $useHtmlLines = is_array($htmlLines) && count($htmlLines) === count($textLines);

        $subBlocks = [];
        $imgUsed = false;
        foreach ($textLines as $i => $tLine) {
            $tClean = trim((string) $tLine);
            $hClean = '';
            if ($useHtmlLines) {
                $candidateHtml = trim((string) ($htmlLines[$i] ?? ''));
                // Guard against unclosed inline tags across soft breaks
                if (substr_count($candidateHtml, '<') === substr_count($candidateHtml, '</') * 2) {
                    $hClean = $candidateHtml;
                }
            }
            if ($hClean === '' && $tClean !== '') {
                $hClean = e($tClean);
            }
            $subImg = (!$imgUsed && !empty($block['image'])) ? $block['image'] : null;
            if ($subImg !== null) {
                $imgUsed = true;
            }
            if ($tClean === '' && !$subImg) {
                continue;
            }
            $subBlocks[] = [
                'kind' => 'paragraph',
                'text' => $tClean,
                'html' => $hClean,
                'image' => $subImg,
            ];
        }
        return $subBlocks;
    }

    private function isSectionSubInstructionLine(string $line): bool
    {
        return (bool) preg_match('/^(?:Pilihlah\s+(?:satu|salah\s+satu|lebih\s+dari|beberapa|jawaban)\s+.*|Berilah\s+tanda\s+(?:silang|centang).*|Tentukan\s+(?:Benar|pernyataan).*|Pasangkan\s+pernyataan.*|Jodohkan(?:lah)?\s+.*|Isilah\s+(?:titik|kolom|kotak).*|Jawablah\s+pertanyaan.*|Kerjakan(?:lah)?\s+soal.*|Amati(?:lah)?\s+tabel.*|Perhatikan(?:lah)?\s+tabel.*|Petunjuk\s*:.*|Contoh\s*:.*|KOLOM\s+[AB]|JAWABAN|PG|PGK|BENAR\s*\/\s*SALAH|MENJODOHKAN|ISIAN\s+SINGKAT|URAIAN)$/iu', $line);
    }

    private function detectSectionHeaderType(string $line): ?string
    {
        // Explicit bracket tags: [PG], [PGK], [BENAR_SALAH], [MENJODOHKAN], [ISIAN], [ESSAY]
        if (preg_match('/\[PGK\]|\[PG_KOMPLEKS\]/iu', $line)) return 'pg_complex';
        if (preg_match('/\[BENAR[_\s\/-]*SALAH\]|\[BS\]/iu', $line)) return 'true_false';
        if (preg_match('/\[MENJODOHKAN\]|\[MATCHING\]/iu', $line)) return 'matching';
        if (preg_match('/\[ISIAN(?:[_\s]*SINGKAT)?\]/iu', $line)) return 'short_answer';
        if (preg_match('/\[ESSAY\]|\[URAIAN\]/iu', $line)) return 'essay';
        if (preg_match('/\[PG\]/iu', $line)) return 'pg';

        // Tolerant bracketed headers (e.g. missing closing bracket like "[PILIHAN GANDA" or "[URAIAN")
        if (mb_strlen($line) <= 80 && str_contains($line, '[')) {
            if (preg_match('/\[[^\]]*(?:pilihan\s+ganda\s+kompleks|pg\s+kompleks)/iu', $line)) return 'pg_complex';
            if (preg_match('/\[[^\]]*(?:benar\s*\/\s*salah)/iu', $line)) return 'true_false';
            if (preg_match('/\[[^\]]*(?:menjodohkan)/iu', $line)) return 'matching';
            if (preg_match('/\[[^\]]*(?:isian(?:\s+singkat)?)/iu', $line)) return 'short_answer';
            if (preg_match('/\[[^\]]*(?:uraian|essay)/iu', $line)) return 'essay';
            if (preg_match('/\[[^\]]*(?:pilihan\s+ganda|pilihan\s+gand)/iu', $line)) return 'pg';
        }

        // Roman / numbered exam paper headers: "I. Pilihlah jawaban...", "I. PG", "II. Uraian", etc.
        if (preg_match('/^(?:[IVX]+\s*\.?\s*|[A-F]\.\s*)(?:pilihlah\s+jawaban|pilihan\s+ganda\s+kompleks|pg\s+kompleks|pgk|pilihan\s+ganda|pg\b|benar\s*\/\s*salah|menjodohkan|isian\s+singkat|uraian|essay)/iu', $line)) {
            if (preg_match('/kompleks|\bpgk\b/iu', $line)) return 'pg_complex';
            if (preg_match('/benar\s*\/\s*salah/iu', $line)) return 'true_false';
            if (preg_match('/menjodohkan/iu', $line)) return 'matching';
            if (preg_match('/isian/iu', $line)) return 'short_answer';
            if (preg_match('/uraian|essay/iu', $line)) return 'essay';
            return 'pg';
        }

        // Bare section title like "I. Pilihlah jawaban dibawah ini dengan tepat !!" or "I.   PG"
        if (preg_match('/^[IVX]+\.\s+(?:PG|Pilihlah\s+jawaban)/iu', $line)) {
            return 'pg';
        }

        return null;
    }

    private function isQuestionStartLine(string $line, ?array $current, array $existingQuestions): bool
    {
        if (!preg_match('/^(?:soal\s*no\.?\s*|no\.?\s*)?(\d{1,3})\s*([\.\)])\s*(.+)$/iu', $line, $m)) {
            return false;
        }
        $num = intval($m[1]);
        $delim = $m[2];
        $rest = trim($m[3]);

        if ($num < 1 || $num > 200 || $rest === '') {
            return false;
        }

        if (!$current) {
            return true;
        }

        // If current is PG/PGK and already has options collected, next sequential number is definitely a new question
        $hasOptions = !empty($current['options']['A'] ?? '') || !empty($current['options']['B'] ?? '');
        if ($num === $current['question_number'] + 1) {
            return true;
        }

        if ($hasOptions && $num > $current['question_number']) {
            return true;
        }

        // Guard against embedded statement sub-lists like "1) ..." inside a question prompt before options
        if ($delim === ')' && !$hasOptions && $num <= 5) {
            return false;
        }

        // If user re-numbered starting from 1 in Section II (e.g. Uraian No. 1), allow starting when section switched
        if ($num === 1 && $current['question_number'] !== 1 && $hasOptions) {
            return true;
        }

        return $num > $current['question_number'] && ($num - $current['question_number']) <= 3;
    }

    private function stripNumberPrefixFromHtml(string $html, string $fallbackPlain): string
    {
        if ($html === '') {
            return e($fallbackPlain);
        }
        // Strip leading "1. " or "<b>1. </b>" cleanly while keeping inner formatting
        $stripped = preg_replace('/^(\s*(?:<(?:b|strong|span)[^>]*>\s*)*)(?:soal\s*no\.?\s*|no\.?\s*)?\d{1,3}\s*[\.\)]\s*(?:<\/(?:b|strong|span)>\s*)*/iu', '$1', $html, 1);
        return trim($stripped) !== '' ? trim($stripped) : e($fallbackPlain);
    }

    private function tryParseOptionLine(string $line, string $html): array
    {
        $markerRegex = '(?:[A-Ea-e]|أ|ا|إ|آ|ب|ج|ت|د|ث|هـ|ه)';
        // Support multi-options on one line separated by tabs or 2+ spaces: "A. text   B. text" or "أ. ...  ج. ..."
        $chunks = preg_split('/(?:\t|\s{2,})(?=\(?' . $markerRegex . '\s*[\.\)])/u', $line);
        $result = [];

        // Two-column option recovery: when left column lost its marker (e.g. "سُلَّامٌ   ج. بُسْتَانٌ" -> A + C, B + D)
        if (
            count($chunks) === 2
            && !preg_match('/^\(?' . $markerRegex . '\s*[\.\)]\s*/u', trim(preg_replace('/[\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]/u', '', $chunks[0])))
            && preg_match('/^\(?(' . $markerRegex . ')\s*[\.\)]\s*(.*)$/u', trim(preg_replace('/[\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]/u', '', $chunks[1])), $rm)
        ) {
            $leftText = trim($chunks[0]);
            $rightLetter = $this->normalizeOptionMarker($rm[1]);
            $rightText = trim($rm[2]);
            $leftMap = ['C' => 'A', 'D' => 'B', 'B' => 'A'];
            if (
                $leftText !== ''
                && $rightLetter !== null
                && isset($leftMap[$rightLetter])
                && mb_strlen($leftText) <= 180
                && !preg_match('/^\d{1,3}\s*[\.\)]\s+/u', $leftText)
                && !preg_match('/(?:[?؟]|\.{3,}|…)\s*$/u', $leftText)
            ) {
                $result[$leftMap[$rightLetter]] = $leftText;
                foreach ($this->splitSequentialInlineOptions($rightLetter, $rightText) as $splitLetter => $splitVal) {
                    $result[$splitLetter] = $splitVal;
                }
                return $result;
            }
        }

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if (preg_match('/^\(?(' . $markerRegex . ')\s*[\.\)]\s*(.*)$/u', $chunk, $m)) {
                $letter = $this->normalizeOptionMarker($m[1]);
                if ($letter === null) {
                    return [];
                }
                $text = trim($m[2]);
                foreach ($this->splitSequentialInlineOptions($letter, $text) as $splitLetter => $splitVal) {
                    $result[$splitLetter] = $splitVal;
                }
            } elseif (!empty($result)) {
                return [];
            } else {
                return [];
            }
        }

        return $result;
    }

    /**
     * Split sequential inline options merged on one line with a single space,
     * e.g. "A. Teks A B. Teks B" -> ['A' => 'Teks A', 'B' => 'Teks B'].
     */
    private function splitSequentialInlineOptions(string $startLetter, string $text): array
    {
        $order = ['A', 'B', 'C', 'D', 'E'];
        $arabicCounterparts = [
            'B' => 'ب',
            'C' => 'ج',
            'D' => 'د',
            'E' => 'هـ|ه',
        ];
        $idx = array_search(strtoupper($startLetter), $order, true);
        if ($idx === false) {
            return [strtoupper($startLetter) => trim($text)];
        }

        $result = [];
        $currLetter = $order[$idx];
        $currText = trim($text);

        while ($idx + 1 < count($order)) {
            $nextLetter = $order[$idx + 1];
            $nextAlts = isset($arabicCounterparts[$nextLetter])
                ? '(?:' . $nextLetter . '|' . $arabicCounterparts[$nextLetter] . ')'
                : $nextLetter;
            $pattern = '/^(.*?)\s+(?:\(' . $nextAlts . '\)|' . $nextAlts . '\s*[\.\)])\s+(.+)$/su';
            if (preg_match($pattern, $currText, $m)) {
                $left = trim($m[1]);
                $right = trim($m[2]);
                // Avoid splitting on standalone single-letter initials like "H. B. Jassin"
                if ($left !== '' && !preg_match('/(?:^|\s)[A-Z]\.$/u', $left)) {
                    $result[$currLetter] = $left;
                    $currLetter = $nextLetter;
                    $currText = $right;
                    $idx++;
                    continue;
                }
            }
            break;
        }

        $result[$currLetter] = $currText;
        return $result;
    }

    private function splitInlineOptionsIntoQuestion(array &$current, string $restPlain): void
    {
        $parts = preg_split('/(?=(?:\b[A-E]|(?:أ|ا|إ|آ|ب|ج|د|هـ|ه))[\.\)]\s+)/u', $restPlain);
        if (count($parts) <= 1) {
            return;
        }
        $prompt = trim(array_shift($parts));
        $current['question_text'] = '<p>' . e($prompt) . '</p>';
        foreach ($parts as $part) {
            if (preg_match('/^([A-E]|أ|ا|إ|آ|ب|ج|د|هـ|ه)[\.\)]\s*(.*)$/u', trim($part), $m)) {
                $letter = $this->normalizeOptionMarker($m[1]);
                if ($letter !== null) {
                    $current['options'][$letter] = trim($m[2]);
                }
            }
        }
    }

    private function tryApplyMetadataLine(string $line, array &$current): bool
    {
        $matchedAny = false;

        // Parse bracketed tags: [Kunci: B] [Level: L1 | Kesukaran: mudah | Bobot: 1]
        if (preg_match_all('/\[([^\]]+)\]/u', $line, $bracketMatches)) {
            foreach ($bracketMatches[1] as $inner) {
                $segments = explode('|', $inner);
                foreach ($segments as $seg) {
                    if (preg_match('/^\s*(?:kunci|jawaban)\s*:\s*(.+)$/iu', $seg, $m)) {
                        $current['correct_answer'] = $this->normalizeAnswerKeyForType($current['question_type'], trim($m[1]));
                        $matchedAny = true;
                    } elseif (preg_match('/^\s*materi\s*:\s*(.+)$/iu', $seg, $m)) {
                        $current['material'] = trim($m[1]);
                        $matchedAny = true;
                    } elseif (preg_match('/^\s*indikator\s*:\s*(.+)$/iu', $seg, $m)) {
                        $current['indicator'] = trim($m[1]);
                        $matchedAny = true;
                    } elseif (preg_match('/^\s*level\s*:\s*(L[123])/iu', $seg, $m)) {
                        $current['cognitive_level'] = strtoupper($m[1]);
                        $matchedAny = true;
                    } elseif (preg_match('/^\s*kesukaran\s*:\s*(mudah|sedang|sukar)/iu', $seg, $m)) {
                        $current['difficulty_level'] = strtolower($m[1]);
                        $matchedAny = true;
                    } elseif (preg_match('/^\s*bobot\s*:\s*([\d\.]+)/iu', $seg, $m)) {
                        $wt = floatval($m[1]);
                        if ($wt > 0) {
                            $current['score_weight'] = round($wt, 2);
                            $current['weight_explicit'] = true;
                        }
                        $matchedAny = true;
                    } elseif (preg_match('/^\s*pasangan\s*:\s*(.+)$/iu', $seg, $m)) {
                        $current['matching_right_text'] = trim($m[1]);
                        $matchedAny = true;
                    }
                }
            }
            if ($matchedAny) {
                return true;
            }
        }

        // Plain metadata line like "Kunci: A" or "Jawaban: B"
        if (preg_match('/^(?:kunci\s*jawaban|kunci|jawaban)\s*[:=]\s*(.+)$/iu', $line, $m)) {
            $current['correct_answer'] = $this->normalizeAnswerKeyForType($current['question_type'], trim($m[1]));
            return true;
        }

        return false;
    }

    private function parseBottomAnswerKeysLine(string $line, array &$bottomKeys): void
    {
        if (preg_match_all('/(\d{1,3})\s*[\.\)\:\-]\s*([A-Ea-e,\s]+|benar|salah|b|s)(?=\s+\d{1,3}\s*[\.\)]|$)/iu', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $bottomKeys[intval($m[1])] = trim($m[2]);
            }
        }
    }

    private function initEmptyQuestion(int $qNum, string $type, string $plain, string $html, ?string $image): array
    {
        return [
            'question_number' => $qNum,
            'question_type' => $type,
            'question_text' => $html !== '' ? '<p>' . $html . '</p>' : '',
            'question_image' => $image,
            'options' => in_array($type, ['pg', 'pg_complex'], true)
                ? ['A' => '', 'B' => '', 'C' => '', 'D' => '']
                : null,
            'correct_answer' => null,
            'score_weight' => self::DEFAULT_WEIGHTS[$type] ?? 1.00,
            'weight_explicit' => false,
            'material' => null,
            'indicator' => null,
            'cognitive_level' => $type === 'essay' ? 'L3' : (in_array($type, ['pg_complex', 'matching'], true) ? 'L2' : 'L1'),
            'difficulty_level' => $type === 'essay' ? 'sukar' : 'sedang',
            'matching_right_text' => null,
        ];
    }

    private function finalizeQuestionItem(array $item): array
    {
        $type = $item['question_type'];
        $stimulus = !empty($item['stimulus']) ? trim((string) $item['stimulus']) : null;
        if ($type === 'true_false') {
            $ans = strtoupper((string) ($item['correct_answer'] ?? 'B'));
            if (str_starts_with($ans, 'S')) $ans = 'S';
            else $ans = 'B';
            $item['correct_answer'] = $ans;
            $opts = ['mode' => 'single', 'answer' => $ans];
            if ($stimulus) $opts['stimulus'] = $stimulus;
            $item['options'] = $opts;
        } elseif ($type === 'matching') {
            $ans = strtoupper(substr(trim((string) ($item['correct_answer'] ?? 'A')), 0, 1)) ?: 'A';
            $item['correct_answer'] = $ans;
            $opts = [
                'mode' => 'single',
                'right_text' => $item['matching_right_text'] ?? '',
                'key' => $ans,
            ];
            if ($stimulus) $opts['stimulus'] = $stimulus;
            $item['options'] = $opts;
        } elseif (in_array($type, ['pg', 'pg_complex'], true)) {
            $opts = is_array($item['options']) ? $item['options'] : [];
            if (isset($opts['E']) && trim((string) $opts['E']) === '') {
                unset($opts['E']);
            }
            $item['options'] = $opts;
        } else {
            $item['options'] = null;
        }

        unset($item['matching_right_text'], $item['is_unnumbered_preamble'], $item['stimulus']);
        return $item;
    }

    private function normalizeAnswerKeyForType(string $type, string $rawKey): ?string
    {
        $clean = trim($rawKey);
        if ($clean === '') {
            return null;
        }
        if ($type === 'pg') {
            if (preg_match('/([A-Ea-e])/', $clean, $m)) {
                return strtoupper($m[1]);
            }
            return strtoupper($clean);
        }
        if ($type === 'pg_complex') {
            $letters = preg_split('/[\s,;]+/', strtoupper($clean), -1, PREG_SPLIT_NO_EMPTY);
            $valid = array_values(array_unique(array_filter($letters, fn($l) => in_array($l, ['A', 'B', 'C', 'D', 'E'], true))));
            sort($valid);
            return !empty($valid) ? implode(',', $valid) : strtoupper($clean);
        }
        if ($type === 'true_false') {
            $up = strtoupper($clean);
            return str_starts_with($up, 'S') ? 'S' : 'B';
        }
        return $clean;
    }

    private function appendHtmlParagraph(string $existingHtml, string $lineHtml): string
    {
        $chunk = '<p>' . $lineHtml . '</p>';
        return $existingHtml !== '' ? ($existingHtml . $chunk) : $chunk;
    }
}
