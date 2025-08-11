<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Models\{Author, Category, Publisher};

class BookTemplateExport implements WithHeadings, WithEvents
{
    public function headings(): array
    {
        return [
            'Tên sách *',
            'Mô tả',
            'ID Tác giả * (Chọn từ dropdown)',
            'ID Thể loại * (Chọn từ dropdown)',
            'ID Nhà xuất bản * (Chọn từ dropdown)',
            'Giá *',
            'Giá giảm',
            'Số lượng *',
            'Loại sách (paper/ebook) *',
            'Ảnh bìa (URL)'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $spreadsheet = $event->sheet->getParent();
                $mainSheet = $event->sheet->getDelegate();

                $authors = Author::orderBy('name')->get();
                $categories = Category::orderBy('name')->get();
                $publishers = Publisher::orderBy('name')->get();

                // Data columns P, Q, R, S
                foreach ($authors as $i => $author) {
                    $mainSheet->setCellValue('P' . ($i + 1), $author->id . ' - ' . $author->name);
                }
                foreach ($categories as $i => $category) {
                    $mainSheet->setCellValue('Q' . ($i + 1), $category->id . ' - ' . $category->name);
                }
                foreach ($publishers as $i => $publisher) {
                    $mainSheet->setCellValue('R' . ($i + 1), $publisher->id . ' - ' . $publisher->name);
                }

                $bookTypes = ['paper', 'ebook'];
                foreach ($bookTypes as $i => $type) {
                    $mainSheet->setCellValue('S' . ($i + 1), $type);
                }

                // Hide data columns
                foreach (['P', 'Q', 'R', 'S'] as $col) {
                    $mainSheet->getColumnDimension($col)->setVisible(false);
                }

                $this->styleHeaderRow($mainSheet);
                $this->addSampleData($mainSheet, $authors, $categories, $publishers);
                $this->createDropdownsSimple($mainSheet, $authors, $categories, $publishers);
                $this->addInstructions($mainSheet, $authors, $categories, $publishers);
            }
        ];
    }

    private function styleHeaderRow($sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50']
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        $columnWidths = [
            'A' => 25, 'B' => 30, 'C' => 25, 'D' => 25, 'E' => 25,
            'F' => 12, 'G' => 12, 'H' => 12, 'I' => 15, 'J' => 30
        ];
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->getRowDimension('1')->setRowHeight(25);
    }

    private function addSampleData($sheet, $authors, $categories, $publishers)
    {
        $sampleData = [
            [
                'Lập trình PHP cơ bản',
                'Sách học lập trình PHP từ cơ bản đến nâng cao',
                $authors->count() > 0 ? ($authors->first()->id . ' - ' . $authors->first()->name) : 'Không có tác giả',
                $categories->count() > 0 ? ($categories->first()->id . ' - ' . $categories->first()->name) : 'Không có thể loại',
                $publishers->count() > 0 ? ($publishers->first()->id . ' - ' . $publishers->first()->name) : 'Không có NXB',
                150000, 120000, 50, 'paper',
                'https://example.com/php-book.jpg'
            ],
            [
                'Laravel Framework',
                'Hướng dẫn sử dụng Laravel Framework',
                $authors->count() > 1 ? ($authors->skip(1)->first()->id . ' - ' . $authors->skip(1)->first()->name) :
                    ($authors->count() > 0 ? ($authors->first()->id . ' - ' . $authors->first()->name) : 'Không có tác giả'),
                $categories->count() > 1 ? ($categories->skip(1)->first()->id . ' - ' . $categories->skip(1)->first()->name) :
                    ($categories->count() > 0 ? ($categories->first()->id . ' - ' . $categories->first()->name) : 'Không có thể loại'),
                $publishers->count() > 1 ? ($publishers->skip(1)->first()->id . ' - ' . $publishers->skip(1)->first()->name) :
                    ($publishers->count() > 0 ? ($publishers->first()->id . ' - ' . $publishers->first()->name) : 'Không có NXB'),
                200000, '', 30, 'ebook', ''
            ]
        ];

        foreach ($sampleData as $rowIndex => $rowData) {
            $row = $rowIndex + 2;
            foreach ($rowData as $colIndex => $value) {
                $col = chr(65 + $colIndex);
                $sheet->setCellValue($col . $row, $value);
            }
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E8']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]);
        }
    }

    private function createDropdownsSimple($sheet, $authors, $categories, $publishers)
    {
        if ($authors->count() > 0) {
            $authorRange = '$P$1:$P$' . $authors->count();
            $this->addDropdownDirect($sheet, 'C', $authorRange, 'Chọn tác giả từ danh sách');
        }

        if ($categories->count() > 0) {
            $categoryRange = '$Q$1:$Q$' . $categories->count();
            $this->addDropdownDirect($sheet, 'D', $categoryRange, 'Chọn thể loại từ danh sách');
        }

        if ($publishers->count() > 0) {
            $publisherRange = '$R$1:$R$' . $publishers->count();
            $this->addDropdownDirect($sheet, 'E', $publisherRange, 'Chọn nhà xuất bản từ danh sách');
        }

        $this->addDropdownDirect($sheet, 'I', '$S$1:$S$2', 'Chọn: paper hoặc ebook');
    }

    private function addDropdownDirect($sheet, $column, $range, $inputMessage)
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1($range);
        $validation->setPromptTitle('Hướng dẫn');
        $validation->setPrompt($inputMessage);
        $validation->setErrorTitle('Lỗi nhập liệu');
        $validation->setError('Vui lòng chọn giá trị từ danh sách dropdown');

        for ($row = 2; $row <= 100; $row++) {
            $sheet->getCell($column . $row)->setDataValidation(clone $validation);
        }
    }

    private function addInstructions($sheet, $authors, $categories, $publishers)
    {
        $instructions = [
            'HƯỚNG DẪN SỬ DỤNG:',
            '',
            '1. Các trường có dấu (*) là bắt buộc',
            '2. Chọn Tác giả, Thể loại, NXB từ dropdown',
            '3. Sau khi chọn, chỉ lấy số ID đầu tiên',
            '4. VD: "1 - Nguyễn Văn A" → chỉ lấy "1"',
            '5. Giá phải là số, không có ký tự đặc biệt',
            '6. Loại sách: "paper" hoặc "ebook"',
            '7. URL ảnh bìa là tùy chọn',
            '',
            'Lưu ý: Xóa 2 dòng mẫu trước khi import!',
            '',
            '🔍 DEBUG INFO:',
            'Authors: ' . $authors->count() . ' items',
            'Categories: ' . $categories->count() . ' items',
            'Publishers: ' . $publishers->count() . ' items'
        ];

        foreach ($instructions as $i => $instruction) {
            $row = $i + 1;
            $sheet->setCellValue('L' . $row, $instruction);

            if ($i == 0) {
                $sheet->getStyle('L' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '2E7D32'], 'size' => 12]
                ]);
            } elseif (strpos($instruction, '🔍 DEBUG') !== false) {
                $sheet->getStyle('L' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FF5722'], 'size' => 10]
                ]);
            } else {
                $sheet->getStyle('L' . $row)->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '424242']]
                ]);
            }
        }
        $sheet->getColumnDimension('L')->setWidth(40);
    }
}
