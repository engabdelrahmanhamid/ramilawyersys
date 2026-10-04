<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use \Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ServiceReceiptExport implements WithHeadings, WithCustomStartCell, WithEvents, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function styles(Worksheet $sheet)
    {
        $headerStyle=[
            'font' => ['bold' => true, 'color' => ['argb' => 'ffffff'], 'size' => 14],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '538DD5',
                ],
            ]];
        $subHeaderStyle= ['font' => ['size' => 12],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'C5D9F1',
                ],
            ]];
        return [
            'A1:R1' => $headerStyle,
            '2' =>$subHeaderStyle,
        ];
    }


    public function startCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $sheet){
                $sheet->getDelegate()->setRightToLeft(true);
                $sheet->getDelegate()->getStyle("A:Z")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            },
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Sheet $sheet */
                $sheet = $event->sheet;
                /** UserDate */
                $sheet->mergeCells('D1:F1');
                $sheet->setCellValue('D1', "ايصالات الخدمات");
                $sheet->append($this->userData(), 'A2');
                $sheet->autoSize();
                $sheet->freezePane('C2');
                foreach ($event->sheet->getColumnIterator('R') as $row) {
                    foreach ($row->getCellIterator() as $cell) {
                        if (str_contains($cell->getValue(), 'http')) {
                            $cell->setHyperlink(new Hyperlink($cell->getValue()));
                            // Upd: Link styling added
                            $event->sheet->getStyle($cell->getCoordinate())->applyFromArray([
                                'font' => [
                                    'color' => ['rgb' => '0000FF'],
                                    'underline' => 'single'
                                ]
                            ]);
                        }
                    }
                }
            },

        ];
    }


    /**
     * @return array
     */
    public function userData()
    {
        $data = $this->data;
        $array=[];
        foreach ($data as $row) {
            $product = [
                'id' => $row->id,
                'receipt_number' => $row->receipt_number,
                'amount' => $row->amount,
                'tax_amount' => $row->tax_amount,
                'total_amount' => $row->total_amount,
                'paid_amount' => $row->paid_amount,
                'unpaid_amount' => $row->unpaid_amount,
                'paid_to' => $row->unpaid_amount,
                'payment_status' => ($row->payment_status == 0) ? 'غير مكتمله الدفع' : (($row->payment_status == 1) ? 'مكتمله الدفع' : ''),
                'payment_method' => $row->payment_method ? $row->payment_method->name : '',
                'service' => $row->service ? $row->service->name : '',
                'date' =>date('d/m/Y', strtotime($row->date)),
                'created_at' =>date('d/m/Y', strtotime($row->created_at)),
                'time' =>date('H:i a', strtotime($row->created_at)),
            ];

            $array[] = $product;

        }
        return $array;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [

            'رقم الايصال',
            'رقم تسجيل الايصال',
            'قيمه الايصال',
            'قيمه ضريبه الايصال',
            'القيمه الكليه للايصال',
            'القيمه المدفوعه',
            'القيمه الغير مدفوعه',
            'مدفوع الي',
            'حاله الدفع',
            'طريقه الدفع',
            'اسم القضيه',
            'تاريخ الايصال',
            'تاريخ التسجيل',
            'وقت التسجيل',
        ];
    }
}
