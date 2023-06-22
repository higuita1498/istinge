<?php

namespace App\Exports\CRC\Formularios;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Formulario13Export implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        /*

         SELECT
    YEAR(co.created_at) as yea,
    QUARTER(co.created_at) as qua,
    m.codigo_completo,
    con.estrato,
    1,
    pv.download,
    pv.upload,
    co.tecnologia,
    co.state,
    COUNT(con.id),
    pv.price * COUNT(con.id),
    0
FROM factura f
    JOIN numeraciones_facturas nf ON (f.numeracion = nf.id)
    JOIN contracts co ON (co.id = f.contrato_id)
    JOIN planes_velocidad pv ON (pv.id = co.plan_id)
    JOIN contactos con ON (con.id = co.client_id)
    JOIN municipios m ON (con.fk_idmunicipio = m.id)
WHERE
    nf.tipo = 2
    AND f.estatus = 0
    AND co.created_at IS NOT NULL
GROUP BY
    yea,
    qua,
    m.codigo_completo,
    con.estrato,
    pv.download,
    pv.upload,
    co.tecnologia,
    co.state
ORDER BY yea DESC, qua DESC;
         * */
        $query = DB::table('factura')
            ->select('municipios.codigo_completo', 'contactos.estrato', 'planes_velocidad.download', 'planes_velocidad.upload', 'contracts.tecnologia', 'contracts.state')
            ->selectRaw('YEAR(contracts.created_at) as y')
            ->selectRaw('QUARTER(contracts.created_at) as q')
            ->selectRaw('1')
            ->selectRaw('COUNT(contactos.id)')
            ->selectRaw('planes_velocidad.price * COUNT(contactos.id)')
            ->selectRaw('0')
            ->join('numeraciones_facturas', 'numeraciones_facturas.id', '=', 'factura.numeracion')
            ->join('contracts', 'contracts.id', '=', 'factura.contrato_id')
            ->join('planes_velocidad', 'planes_velocidad.id', '=', 'contracts.plan_id')
            ->join('contactos', 'contactos.id', '=', 'contracts.client_id')
            ->join('municipios', 'municipios.id', '=', 'contactos.fk_idmunicipio')
            ->groupBy('y', 'q', 'municipios.codigo_completo', 'contactos.estrato', 'planes_velocidad.download', 'planes_velocidad.upload', 'contracts.tecnologia', 'contracts.state');

        return $query->get();
    }

    public function headings(): array
    {
        return ['YEA', 'QUA', 'CODIGO COMPLETO', 'ESTRATO', 'ESTADO', 'DOWNLOAD', 'UPLOAD', 'TECNOLGÍA', 'ESTADO'];
    }
}
