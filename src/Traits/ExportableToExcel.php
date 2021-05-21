<?php


namespace Easy\Traits;


use Easy\Http\Requests\ExportExcelRequest;

trait ExportableToExcel
{
    public function exportExcel(ExportExcelRequest $request)
    {
        $export = $this->excel_export ?? null;
        if (is_null($export)) {
            throw new \Exception('Not Implemented');
        } else {
            $password = $request['password'] ?? null;
            /** @var BaseExport $export */
            return $export->getExcel($request->all(), $password);
        }
    }
}
