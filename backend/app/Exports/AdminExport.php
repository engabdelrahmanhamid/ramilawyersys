<?php

namespace App\Exports;

use App\Helpers\ImageHelper;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use \Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminExport implements WithHeadings, WithCustomStartCell, WithEvents, WithStyles
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
                $sheet->setCellValue('D1', "المستخدمين");
                $sheet->append($this->userData(), 'A2');
                $sheet->autoSize();
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
                'name' => $row->name,
                'email' => $row->email,
                'phone' => $row->gregorian_date,
                'image' => ImageHelper::getInstance()->getImageUrl('Admin',$row->image),
                'type' => $row->type ? $row->type->name : '',
                'jop' => $row->jop ? $row->jop->name : '',
                'branch' => $row->branch ? $row->branch->name : '',
                'super' =>  ($row->super == 1) ? 'مستخدم رئيسي' : (($row->super == 2) ? 'مستخدم عادي' : ''),
                'date' =>date('d/m/Y', strtotime($row->created_at)),
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
            'الاسم',
            'البريد الالكتروني',
            'رقم التليفون',
            'صوره المستخدم',
            'نوع المستخدم',
            'وظيفه المستخدم',
            'الفرع',
            'صلاحيه المستخدم',
            'تاريخ التسجيل',
            'وقت التسجيل',
        ];
    }
}
