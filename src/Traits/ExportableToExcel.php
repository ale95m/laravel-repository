<?php


namespace Easy\Traits;


use Easy\Exports\EasyExcelExport;
use Easy\Http\Requests\ExportExcelRequest;

/**
 * @property EasyExcelExport excel_export
 */
trait ExportableToExcel
{
    public function getExportableClass(): EasyExcelExport
    {
        return $this->excel_export;
    }

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
