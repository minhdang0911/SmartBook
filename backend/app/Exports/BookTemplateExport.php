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
            'ID Tác giả *',
            'ID Thể loại *', 
            'ID Nhà xuất bản (Chỉ cần nếu là Paper)',
            'Giá (Chỉ cần nếu là Paper)',
            'Giá giảm (Tùy chọn)',
            'Số lượng (Chỉ cần nếu là Paper)',
            'Loại sách * (paper/ebook)',
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
            // Sách giấy
            [
                'Lập trình PHP cơ bản',
                'Sách học lập trình PHP từ cơ bản đến nâng cao',
                $authors->count() > 0 ? ($authors->first()->id . ' - ' . $authors->first()->name) : '1',
                $categories->count() > 0 ? ($categories->first()->id . ' - ' . $categories->first()->name) : '1',
                $publishers->count() > 0 ? ($publishers->first()->id . ' - ' . $publishers->first()->name) : '1',
                150000, 120000, 50, 'paper',
                'https://example.com/php-book.jpg'
            ],
            // Ebook
            [
                'Laravel Framework (Ebook)',
                'Hướng dẫn sử dụng Laravel Framework - Phiên bản điện tử',
                $authors->count() > 1 ? ($authors->skip(1)->first()->id . ' - ' . $authors->skip(1)->first()->name) :
                    ($authors->count() > 0 ? ($authors->first()->id . ' - ' . $authors->first()->name) : '1'),
                $categories->count() > 1 ? ($categories->skip(1)->first()->id . ' - ' . $categories->skip(1)->first()->name) :
                    ($categories->count() > 0 ? ($categories->first()->id . ' - ' . $categories->first()->name) : '1'),
                '', // Ebook không cần publisher
                '', // Ebook có thể free
                '', // Ebook không cần giá giảm
                '', // Ebook không cần stock
                'ebook',
                'https://example.com/laravel-ebook.jpg'
            ]
        ];

        foreach ($sampleData as $rowIndex => $rowData) {
            $row = $rowIndex + 2;
            foreach ($rowData as $colIndex => $value) {
                $col = chr(65 + $colIndex);
                $sheet->setCellValue($col . $row, $value);
            }
            
            // Style khác nhau cho paper và ebook
            $fillColor = $rowData[8] == 'paper' ? 'E8F5E8' : 'E3F2FD';
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $fillColor]
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
            $this->addDropdownDirect($sheet, 'E', $publisherRange, 'Chọn nhà xuất bản (chỉ cần nếu là sách giấy)');
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
            '📚 HƯỚNG DẪN SỬ DỤNG TEMPLATE IMPORT:',
            '',
            '🔴 TRƯỜNG BẮT BUỘC:',
            '• Tất cả: Tên sách, ID Tác giả, ID Thể loại, Loại sách',
            '• Sách giấy: + ID Nhà xuất bản, Giá, Số lượng',
            '• Ebook: Chỉ cần 4 trường bắt buộc ở trên',
            '',
            '📝 CÁCH ĐIỀN:',
            '1. Chọn loại sách trước: "paper" hoặc "ebook"',
            '2. Chọn từ dropdown → chỉ lấy số ID đầu tiên',
            '3. VD: "1 - Nguyễn Văn A" → nhập "1"',
            '',
            '📋 QUY TẮC:',
            '• Paper: Phải có đầy đủ thông tin bán hàng',
            '• Ebook: Có thể để trống Publisher, Giá, Stock',
            '• Giá = 0 có nghĩa là miễn phí',
            '',
            '⚠️ LƯU Ý:',
            '• Xóa 2 dòng mẫu trước khi import!',
            '• File chỉ chấp nhận .xlsx, .xls, .csv',
            '• Tối đa 10MB',
            '',
            '🔍 THỐNG KÊ HỆ THỐNG:',
            'Authors: ' . $authors->count() . ' tác giả',
            'Categories: ' . $categories->count() . ' thể loại', 
            'Publishers: ' . $publishers->count() . ' nhà xuất bản',
            '',
            '🎯 MẪU DỮ LIỆU:',
            '• Dòng 2: Sách giấy (đầy đủ thông tin)',
            '• Dòng 3: Ebook (chỉ thông tin cơ bản)'
        ];

        foreach ($instructions as $i => $instruction) {
            $row = $i + 1;
            $sheet->setCellValue('L' . $row, $instruction);

            if (strpos($instruction, '📚') !== false) {
                $sheet->getStyle('L' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '1976D2'], 'size' => 12]
                ]);
            } elseif (strpos($instruction, '🔴') !== false || strpos($instruction, '📝') !== false || 
                     strpos($instruction, '📋') !== false || strpos($instruction, '⚠️') !== false ||
                     strpos($instruction, '🔍') !== false || strpos($instruction, '🎯') !== false) {
                $sheet->getStyle('L' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'D32F2F'], 'size' => 10]
                ]);
            } else {
                $sheet->getStyle('L' . $row)->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => '424242']]
                ]);
            }
        }
        $sheet->getColumnDimension('L')->setWidth(45);
    }
}