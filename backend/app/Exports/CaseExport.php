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

class CaseExport implements WithHeadings, WithCustomStartCell, WithEvents, WithStyles
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
                $sheet->setCellValue('D1', "القضايا");
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
                'name' => $row->name,
                'address' => $row->address,
                'hijri_date' => $row->hijri_date,
                'gregorian_date' => $row->gregorian_date,
                'desc' => $row->desc,
                'court_name' => $row->court_name,
                'court_city' => $row->court_city,
                'court_circle' => $row->court_circle,
                'court_degree' => $row->court_degree,
                'opponent_name' => $row->opponent_name,
                'opponent_type' => $row->opponent_type ? $row->opponent_type->name : '' ,
                'client_characteristic' =>  ($row->client_characteristic == 1) ? 'المدعي' : (($row->client_characteristic == 2) ? 'المدعي عليه' : ''),
                'case_type' =>  $row->case_type ? $row->case_type->name : '',
                'case_status' => $row->status ? $row->status->name : '',
                'amount' => $row->amount,
                'deposit' => $row->deposit,
                'payment_status' => ($row->payment_status == 0) ? 'غير مكتمله الدفع' : (($row->payment_status == 1) ? 'مكتمله الدفع' : ''),
                'payment_type' =>  ($row->payment_type == 1) ? 'دفع كامل' : (($row->payment_type == 2) ? 'دفع اجل' : ''),
                'payment_method' => $row->payment_method ? $row->payment_method->name : '',
                'client' => $row->client ? $row->client->name : '',
                'admin' => $row->admin ? $row->admin->name : '',
                'admin_id' => $row->admin ? $row->admin->id : '',
                'branch' => $row->branch ? $row->branch->name : '',
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

            'رقم القضيه',
            'اسم القضيه',
            'العنوان',
            'التاريخ الهجري',
            'التاريخ الميلادي',
            'وصف القضيه',
            'اسم المحكمه',
            'مدينه المحكمه',
            'دائره المحكمه',
            'درجه المحكمه',
            'اسم الخصم',
            'نوع الخصم',
            'نوع الموكل',
            'نوع القضيه',
            'حاله القضيه',
            'قيمه القضيه',
            'قيمه المدفوع',
            'حاله الدفع',
            'خاصيه الدفع',
            'نوع الدفع',
            'اسم العميل',
            'اسم الموظف المختص',
            'رقم الموظف المختص',
            'الفرع',
            'تاريخ التسجيل',
            'وقت التسجيل',
        ];
    }
}
